<?php

namespace Cirote\Movimientos\Actions\MigratosDatosOriginales;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Cirote\Activos\Models\Activo;

use App\Models\Broker;

class CopiarDatosDePpiAlFormatoComunAction extends Base 
{
	protected $broker = 'PPI';

    protected $archivos = ['Movimientos 01.xlsx', 'Movimientos 2019.xlsx', 'Movimientos 2020.xlsx'];

    protected function fecha_operacion($datos): ?Carbon
    {
        return $this->fecha($datos, 'A');
    }

    protected function fecha_liquidacion($datos): ?Carbon
    {
        return null;
    }

    protected function valida($datos): ?String
    {
    	return true;
    }

    protected function observaciones($datos): ?String
    {
    	return trim($datos['B']);
    }

    protected function activo($datos): ?Activo
    {
        $partes = explode(' ', $this->observaciones($datos));

        if (count($partes) == 2)
        {
            return Activo::byTicker(trim($partes[1]));
        }

    	return null;
    }

    protected function numero_operacion($datos): ?String
    {
    	return null;
    }

    protected function numero_boleto($datos): ?String
    {
        return null;
    }

    protected function tipo_operacion($datos): ?String
    {
    	if (Str::startsWith($this->observaciones($datos), 'Ingreso de Fondos')) 
        {
        	return static::OP_DEPOSITO;
        }

    	if (Str::startsWith($this->observaciones($datos), 'Retiro de Fondos')) 
        {
        	return static::OP_RETIRO;
        }

        if (Str::startsWith($this->observaciones($datos), 'COMPRA '))
        {
        	return static::OP_COMPRA;
        }

        if (Str::startsWith($this->observaciones($datos), 'VENTA '))
        {
        	return static::OP_VENTA;
        }

        return null;
    }

    protected function cantidad($datos): ?float
    {
    	return $this->toFloat($datos['C']) ?: null;
    }

    protected function precio_en_moneda_original($datos): ?float
    {
        $valor = $this->toFloat($datos['D']);

        if ($activo = $this->activo($datos))
        {   
            if ($activo->clase == 'Bono')
            {
                return $valor ? $valor * 100 : null;
            }
        }

    	return $valor;
    }

    protected function monto_en_moneda_original($datos): ?float
    {
    	return $this->toFloat($datos['E']) ?: null;
    }

    protected function comisiones_en_moneda_original($datos): ?float
    {
    	return null;
    }

    protected function iva_en_moneda_original($datos): ?float
    {
    	return null;
    }

    protected function otros_impuestos_en_moneda_original($datos): ?float
    {
    	return null;
    }

    protected function saldo_en_moneda_original($datos): ?float
    {
        return $this->toFloat($datos['F']) ?: null;
    }

    protected function cuenta_corriente($datos, $planilla, $file): ?string
    {
    	return $planilla;
    }
}