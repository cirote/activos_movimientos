<?php

namespace Cirote\Movimientos\Actions\MigratosDatosOriginales;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Cirote\Activos\Models\Activo;
use Cirote\Activos\Models\Moneda;
use Cirote\Movimientos\Models\Movimiento;
use App\Models\Broker;

class Base 
{
    const DOLAR_MEP = 'Dolar MEP';

    const DOLAR_CABLE = 'Dolar Cable';

    const DOLAR_PPI_GLOBAL = 'Dolar PPI Global';

    const DOLAR_7000 = 'Dolares CV 7000';

    const PESOS = 'Pesos';

    const ADMINISTRADA_PESOS = 'Administrada Pesos';

    CONST OP_DEPOSITO = 'Deposito';

    CONST OP_RETIRO = 'Extraccion';

    const OP_COMPRA = 'Compra';

    const OP_VENTA = 'Venta';

    public function execute()
    {
        $this->cargarDatos();
    }

    protected $darVuelta = false;

    protected $peso;

    protected $dolar;

    public function __construct()
    {
        $this->peso = Activo::byTicker('$');

        $this->dolar = Activo::byTicker('USD');
    }

    protected function leerLibro($file)
    {
        $url = storage_path("app/historico/BCBA/{$this->getBroker()->sigla}/{$file}");

        if (! file_exists($url))
        {
            throw new \error("El archivo [$url] no existe");
        }

        $reader = IOFactory::createReaderForFile($url);

        $reader->setReadDataOnly(true);

        return $reader->load($url);
    }

    private $_broker;

    protected function getBroker(): Broker
    {
        if (! $this->_broker) 
        {
            $this->_broker = Broker::bySigla($this->broker);
        }

        return $this->_broker;
    }

    protected function cargarDatos()
    {
        foreach ($this->archivos as $archivo) 
        {
            $this->migrarArchivo($archivo);
        }
    }

    protected function migrarArchivo($file)
    {
        $libro = $this->leerLibro($file);

        $planillas = $libro->getSheetNames();

        foreach ($planillas as $planilla)
        {
            $datos = $libro->getSheetByName($planilla)->toArray(null, true, true, true);

            if ($this->darVuelta)
            {
                $datos = array_reverse($datos, true);
            }

            foreach ($datos as $dato)
            {
                $this->migrate($dato, $planilla, $file);
            }
        }
    }

    protected function migrate($renglon, $planilla, $file)
    {
        $datos = $this->getDatos($renglon, $planilla, $file);

        if ($this->datos_validos($datos))
        {
            unset($datos['valida']);

            Movimiento::create($datos);        
        }
    }

    protected function getDatos($renglon, $planilla, $file)
    {
        return [
            'fecha_operacion'   => $this->fecha_operacion($renglon),
            'fecha_liquidacion' => $this->fecha_liquidacion($renglon),

            'activo_id'  => $this->activo($renglon) ? $this->activo($renglon)->id : null,

            'numero_operacion' => $this->numero_operacion($renglon),
            'numero_boleto'    => $this->numero_boleto($renglon),
            'tipo_operacion'   => $this->tipo_operacion($renglon),

            'cantidad' => $this->cantidad($renglon),

            'moneda_original_id' => $this->moneda_original($renglon, $planilla, $file) ? $this->moneda_original($renglon, $planilla, $file)->id : null,

            'precio_en_moneda_original'      => $this->precio_en_moneda_original($renglon),
            'monto_en_moneda_original'       => $this->monto_en_moneda_original($renglon),
            'comisiones_en_moneda_original'  => $this->comisiones_en_moneda_original($renglon),
            'iva_en_moneda_original'         => $this->iva_en_moneda_original($renglon),
            'otros_impuestos_en_moneda_original'  => $this->otros_impuestos_en_moneda_original($renglon),
            'saldo_en_moneda_original'       => $this->saldo_en_moneda_original($renglon),

            'precio_en_dolares' => $this->precio_en_dolares($renglon, $planilla, $file),
            'monto_en_dolares'  => $this->monto_en_dolares($renglon, $planilla, $file),

            'precio_en_pesos' => $this->precio_en_pesos($renglon, $planilla, $file),
            'monto_en_pesos'  => $this->monto_en_pesos($renglon, $planilla, $file),

            'broker_id' => $this->getBroker()->id,

            'cuenta_corriente' => $this->cuenta_corriente($renglon, $planilla, $file),
            'hoja'  => $planilla,

            'observaciones' => $this->observaciones($renglon),

            'valida' => $this->valida($renglon),
        ];
    }

    protected function datos_validos($datos)
    {
        if (! $datos['valida'])
        {
            return false;
        }

        if (! $datos['fecha_operacion'])
        {
            return false;
        }

        if (! $datos['monto_en_moneda_original'])
        {
            return false;
        }

        return true;
    }

    protected function tofloat($num)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return abs(floatval(preg_replace("/[^0-9]/", "", $num)));
        }

        $float = floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
        );

        return abs($float);
    }

    protected function precio_en_dolares($datos, $planilla, $file): ?float
    {
        return $this->en_dolares(
            $this->precio_en_moneda_original($datos),
            $this->moneda_original($datos, $planilla, $file),
            $this->fecha_operacion($datos)
        );
    }

    protected function monto_en_dolares($datos, $planilla, $file): ?float
    {
        return $this->en_dolares(
            $this->monto_en_moneda_original($datos),
            $this->moneda_original($datos, $planilla, $file),
            $this->fecha_operacion($datos)
        );
    }

    protected function en_dolares($dato_original, $moneda_original, $fecha): ?float
    {
        if (! $dato_original)
            return null;

        if (! $moneda_original)
            return null;

        if ($moneda_original->id == $this->dolar->id)
        {
            return $dato_original;
        }

        if ($moneda_original->id == $this->peso->id)
        {
            return $dato_original / Moneda::cotizacion($fecha);
        }

        return -1;
    }

    protected function precio_en_pesos($datos, $planilla, $file): ?float
    {
        return $this->en_pesos(
            $this->precio_en_moneda_original($datos),
            $this->moneda_original($datos, $planilla, $file),
            $this->fecha_operacion($datos)
        );
    }

    protected function monto_en_pesos($datos, $planilla, $file): ?float
    {
        return $this->en_pesos(
            $this->monto_en_moneda_original($datos),
            $this->moneda_original($datos, $planilla, $file),
            $this->fecha_operacion($datos)
        );
    }

    protected function en_pesos($dato_original, $moneda_original, $fecha): ?float
    {
        if (! $dato_original)
            return null;

        if (! $moneda_original)
            return null;

        if ($moneda_original->id == $this->dolar->id)
        {
            return $dato_original * Moneda::cotizacion($fecha);
        }

        $peso = Activo::byTicker('$');

        if ($moneda_original->id == $this->peso->id)
        {
            return $dato_original;
        }

        return -1;
    }

    protected function fecha($datos, $celda): ?Carbon
    {
        $fecha = trim($datos[$celda]);

        $patron = "/[a-zA-Z]/";

        if (preg_match($patron, $fecha))
            return null;

        if (is_numeric($fecha))
            return null;

        if (strlen($fecha) == 8)
        {
            return Carbon::createFromFormat("d/m/y H:i:s", $fecha . ' 00:00:00');
        }

        if (strlen($fecha) == 10)
        {
            return Carbon::createFromFormat("d/m/Y H:i:s", $fecha . ' 00:00:00');
        }

        return null;
    }

    protected function moneda_original($datos, $planilla, $file)
    {
        switch($this->cuenta_corriente($datos, $planilla, $file))
        {
            case static::DOLAR_MEP:
            case static::DOLAR_CABLE:
            case static::DOLAR_PPI_GLOBAL:
            case static::DOLAR_7000:
                return $this->dolar;

            case static::PESOS:
            case static::ADMINISTRADA_PESOS:
                return $this->peso;

            default:
                return null;
        }
    }


}
