<?php

namespace Cirote\Movimientos\Actions;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use Cirote\Movimientos\Models\Movimiento;
use Cirote\Movimientos\Models\Posicion;

use App\Models\Broker;

class ImputarMovimientosOriginalesEnPosiciones 
{
	public function execute()
    {
    	foreach(Movimiento::where('activo_id', 10)->where('broker_id', 2)->orderBy('fecha_operacion')->get() as $movimiento)
    	{
    		if ($movimiento->tipo_operacion == 'Compra')
    		{
    			if (! $this->posicionesCortas()->count())
    			{
    				Posicion::crear($movimiento);
    			}

    			echo $movimiento->id . ' - ' . $movimiento->cantidad . ' => ' . $this->posicionesCortas()->count() . ' => ' . $this->posicionesLargas()->count() . "\n";
    		}

    		if ($movimiento->tipo_operacion == 'Venta')
    		{
				while ($movimiento->remanente)
				{
    				if ($posicion_a_cerrar = $this->posicionesLargas()->first())
    				{
	    				$posicion_a_cerrar->cerrar($movimiento);

    				} else {
	
	    				Posicion::crear($movimiento);
    				}

					$movimiento->refresh();
				} 
 
    			echo $movimiento->id . ' - ' . $movimiento->cantidad . ' => ' . $this->posicionesCortas()->count() . ' => ' . $this->posicionesCortas()->count() . "\n";
    		}
    	}

    	die();

    	dd($operaciones);

    	$m1 = Movimiento::find(1866);

    	$p1 = Posicion::crear($m1);

    	dd($m1);

    	dd('Jeje');
    }

    private function posicionesCortas()
    {
    	return Posicion::where('tipo', 'Corta')->where('estado', 'Abierta')->where('activo_id', 10)->where('broker_id', 2)->orderBy('fecha_apertura');
    }

    private function posicionesLargas()
    {
    	return Posicion::where('tipo', 'Larga')->where('estado', 'Abierta')->where('activo_id', 10)->where('broker_id', 2)->orderBy('fecha_apertura');
    }
}