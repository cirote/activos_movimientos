<?php

namespace Cirote\Movimientos\Actions\MigratosDatosOriginales;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Cirote\Activos\Models\Activo;

use App\Models\Broker;

class CopiarDatosDeIolAlFormatoComunAction extends Base 
{
    protected $darVuelta = true;

	protected $broker = 'IOL';

    protected $archivos = ['MovimientosHistoricos.xlsx', 'Movimientos 2020.xlsx'];

    protected function fecha_operacion($datos): ?Carbon
    {
        return $this->fecha($datos, 'D');
    }

    protected function fecha_liquidacion($datos): ?Carbon
    {
        return $this->fecha($datos, 'E');
    }

    protected function valida($datos): ?String
    {
    	$estado = trim($datos['F']);

    	if ($estado == 'Terminada')
    		return true;

    	return false;
    }

    protected function observaciones($datos): ?String
    {
    	return trim($datos['C']);
    }

    protected function activo($datos): ?Activo
    {
    	return Activo::byTicker($this->nombreTicker($this->observaciones($datos)));
    }

    private function nombreTicker($nombre): ?String
    {
        $posInicial = strpos($nombre, '(');

        if ($posInicial === false) 
        {
            $ticker = null;
        } 

        else 
        {
            $posFinal = strpos($nombre, ')');

            if ($posFinal === false) 
            {
                $ticker = null;
            } 

            else
            {
                $ticker = substr($nombre, $posInicial + 1, $posFinal - 1 - $posInicial);
            }
        }

        return $ticker;
    }

    protected function numero_operacion($datos): ?String
    {
    	$operacion = trim($datos['A']);

    	if ($operacion == '0')
    		return null;

    	return $operacion;
    }

    protected function numero_boleto($datos): ?String
    {
    	$boleto = trim($datos['B']);

    	if ($boleto == '0')
    		return null;

    	return $boleto;
    }

    protected function tipo_operacion($datos): ?String
    {
    	if (Str::startsWith($this->observaciones($datos), 'Depósito de Fondos')) 
        {
        	return static::OP_DEPOSITO;
        }

    	if (Str::startsWith($this->observaciones($datos), 'Extracción de Fondos')) 
        {
        	return static::OP_RETIRO;
        }

        if (Str::startsWith($this->observaciones($datos), 'Compra('))
        {
        	return static::OP_COMPRA;
        }

        if (Str::startsWith($this->observaciones($datos), 'Venta('))
        {
        	return static::OP_VENTA;
        }

        return null;
    }

    protected function cantidad($datos): ?float
    {
    	return $this->toFloat($datos['G']) ?: null;
    }

    protected function precio_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['H']) ?: null;
    }

    protected function monto_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['L']) ?: null;
    }

    protected function comisiones_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['I']) ?: null;
    }

    protected function iva_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['J']) ?: null;
    }

    protected function otros_impuestos_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['K']) ?: null;
    }

    protected function saldo_en_moneda_original($datos): ?float
    {
    	return null;
    }

    protected function cuenta_corriente($datos, $planilla, $file): ?string
    {
    	$texto = trim($datos['N']);

    	if ($texto == 'Inversion Argentina Dolares')
    		return static::DOLAR_MEP;

    	if ($texto == 'Inversion Argentina Pesos')
    		return static::PESOS;

    	if ($texto == 'Administrada Argentina Pesos')
    		return static::ADMINISTRADA_PESOS;

    	return 'Cuenta Inexistente';
    }
}
