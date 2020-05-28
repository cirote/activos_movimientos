<?php

namespace Cirote\Movimientos\Actions;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use Cirote\Movimientos\Models\Movimiento;
use Cirote\Movimientos\Models\Posicion;

use Cirote\Activos\Models\Activo;
use App\Models\Broker;

class ImputarMovimientosOriginalesEnPosicionesAction
{
	public function execute()
    {
    	foreach(Movimiento::with('activo', 'broker')->whereNotNull('activo_id')->orderBy('fecha_operacion')->get() as $movimiento)
    	{
    		if ($movimiento->tipo_operacion == 'Compra')
    		{
                while ($movimiento->remanente)
                {
                    if ($posicion_a_cerrar = $this->posicionesCortas($movimiento->activo, $movimiento->broker)->first())
                    {
                        $this->cerrarPosicion($posicion_a_cerrar, $movimiento);

                    } else {
    
                        $this->crearPosicion($movimiento);
                    }

                    $movimiento->refresh();
                } 

    			echo $movimiento->id . ' - ' . $movimiento->cantidad . ' => ' . $this->posicionesCortas($movimiento->activo, $movimiento->broker)->count() . ' => ' . $this->posicionesLargas($movimiento->activo, $movimiento->broker)->count() . "\n";
    		}

    		if ($movimiento->tipo_operacion == 'Venta')
    		{
				while ($movimiento->remanente)
				{
    				if ($posicion_a_cerrar = $this->posicionesLargas($movimiento->activo, $movimiento->broker)->first())
    				{
	    				$this->cerrarPosicion($posicion_a_cerrar, $movimiento);

    				} else {
	
	    				$this->crearPosicion($movimiento);
    				}

					$movimiento->refresh();
				} 
 
    			echo $movimiento->id . ' - ' . $movimiento->cantidad . ' => ' . $this->posicionesCortas($movimiento->activo, $movimiento->broker)->count() . ' => ' . $this->posicionesLargas($movimiento->activo, $movimiento->broker)->count() . "\n";
    		}
    	}
    }

    private function posicionesCortas(Activo $activo, Broker $broker)
    {
    	return Posicion::cortas()->abiertas()->byActivo($activo)->byBroker($broker)->byApertura();
    }

    private function posicionesLargas(Activo $activo, Broker $broker)
    {
        return Posicion::largas()->abiertas()->byActivo($activo)->byBroker($broker)->byApertura();
    }

    private function crearPosicion(Movimiento $movimiento, $cantidad_solicitada = 0)
    {
        if (! $cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            return null;
        }

        if ($cantidad_solicitada > $cantidad_remanente)
        {
            die("Error en la cantidad solicitada");
        }

        $posicion = Posicion::create([
            'fecha_apertura' => $movimiento->fecha_operacion,
            'tipo'           => $movimiento->tipo_operacion == 'Compra' ? 'Larga' : 'Corta',
            'estado'         => 'Abierta',
            'activo_id'      => $movimiento->activo_id,
            'broker_id'      => $movimiento->broker_id,
            'moneda_original_id' => $movimiento->moneda_original_id
        ]);

        $this->primerMovimiento($posicion, $movimiento, $cantidad_solicitada ?: $cantidad_remanente);
    }

    private function primerMovimiento(Posicion $posicion, Movimiento $movimiento, $cantidad_solicitada)
    {
        $posicion->cantidad = $cantidad_solicitada;

        $ponderador_movimiento = $cantidad_solicitada / $movimiento->cantidad;

        $posicion->precio_en_moneda_original = $movimiento->precio_en_moneda_original;
        $posicion->monto_en_moneda_original = $ponderador_movimiento * $movimiento->monto_en_moneda_original;

        $posicion->precio_en_dolares = $movimiento->precio_en_dolares;
        $posicion->monto_en_dolares = $ponderador_movimiento * $movimiento->monto_en_dolares;

        $posicion->precio_en_pesos = $movimiento->precio_en_pesos;
        $posicion->monto_en_pesos = $ponderador_movimiento * $movimiento->monto_en_pesos;

        $posicion->save();

        $this->agregarMovimiento($posicion, $movimiento, $cantidad_solicitada, $ponderador_movimiento);
    }

    private function agregarMovimiento(Posicion $posicion, Movimiento $movimiento, $cantidad_solicitada, $ponderador_movimiento)
    {
        $posicion->movimientos()->save($movimiento, [
            'cantidad'      => $cantidad_solicitada,

            'moneda_original_id' => $movimiento->moneda_original_id,
            'precio_en_moneda_original'        => $movimiento->precio_en_moneda_original,
            'monto_parcial_en_moneda_original' => $ponderador_movimiento * $movimiento->monto_en_moneda_original,

            'precio_en_dolares'        => $movimiento->precio_en_dolares,
            'monto_parcial_en_dolares' => $ponderador_movimiento * $movimiento->monto_en_dolares,

            'precio_en_pesos'        => $movimiento->precio_en_pesos,
            'monto_parcial_en_pesos' => $ponderador_movimiento * $movimiento->monto_en_pesos,
        ]);

        $movimiento->cantidad_imputada += $cantidad_solicitada;

        $movimiento->save();
    }

    private function sumarMovimiento(Posicion $posicion, Movimiento $movimiento, $cantidad_solicitada)
    {
        if ($cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            if ($cantidad_solicitada > $cantidad_remanente)
            {
                die("Error en la cantidad solicitada");
            }

            $cantidad_original = $posicion->cantidad;

            $posicion->cantidad += $cantidad_solicitada;

            $ponderador_original = $cantidad_original / $posicion->cantidad;

            $ponderador_remanente = $cantidad_solicitada / $posicion->cantidad;

            $ponderador_movimiento = $cantidad_solicitada / $movimiento->cantidad;

            $posicion->precio_en_moneda_original = ($posicion->precio_en_moneda_original * $ponderador_original) + ($movimiento->precio_en_moneda_original * $ponderador_remanente);
            $posicion->monto_en_moneda_original += $ponderador_movimiento * $movimiento->monto_en_moneda_original;

            $posicion->precio_en_dolares = ($posicion->precio_en_dolares * $ponderador_original) + ($movimiento->precio_en_dolares * $ponderador_remanente);
            $posicion->monto_en_dolares += $ponderador_movimiento * $movimiento->monto_en_dolares;

            $posicion->precio_en_pesos = ($posicion->precio_en_pesos * $ponderador_original) + ($movimiento->precio_en_pesos * $ponderador_remanente);
            $posicion->monto_en_pesos += $ponderador_movimiento * $movimiento->monto_en_pesos;

            $posicion->save();

            $posicion->movimientos()->save($movimiento, [
                'cantidad'      => $cantidad_solicitada,

                'moneda_original_id' => $movimiento->moneda_original_id,
                'precio_en_moneda_original'        => $movimiento->precio_en_moneda_original,
                'monto_parcial_en_moneda_original' => $ponderador_movimiento * $movimiento->monto_en_moneda_original,

                'precio_en_dolares'        => $movimiento->precio_en_dolares,
                'monto_parcial_en_dolares' => $ponderador_movimiento * $movimiento->monto_en_dolares,

                'precio_en_pesos'        => $movimiento->precio_en_pesos,
                'monto_parcial_en_pesos' => $ponderador_movimiento * $movimiento->monto_en_pesos,
            ]);

            $movimiento->cantidad_imputada += $cantidad_solicitada;

            $movimiento->save();
        }

        return $this;
    }

    private function cerrarPosicion(Posicion $posicion, Movimiento $movimiento, $cantidad_solicitada = null)
    {
        if (! $cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            return null;
        }

        if (! $cantidad_solicitada)
        {
            $cantidad_solicitada = $cantidad_remanente;
        }

        if ($cantidad_solicitada > $cantidad_remanente)
        {
            die("Error en la cantidad solicitada");
        }

        if ($movimiento->remanente < $posicion->cantidad)
        {
            $this->split($posicion, $movimiento->remanente);

            return;
        }

        if ($movimiento->remanente >= $posicion->cantidad)
        {
            $ponderador = $posicion->cantidad / $movimiento->cantidad;

            $posicion->fecha_cierre = $movimiento->fecha_operacion;

            $posicion->precio_de_cierre_en_dolares = $movimiento->precio_en_dolares;

            $posicion->estado = 'Cerrada';

            $posicion->resultado_en_moneda_original = ($ponderador * $movimiento->monto_en_moneda_original) - $posicion->monto_en_moneda_original;

            $posicion->resultado_en_dolares = ($ponderador * $movimiento->monto_en_dolares) - $posicion->monto_en_dolares;

            $posicion->resultado_en_pesos = ($ponderador * $movimiento->monto_en_pesos) - $posicion->monto_en_pesos;

            $posicion->save();

            $this->agregarMovimiento($posicion, $movimiento, $posicion->cantidad, $ponderador);

            return;
        }

        if ($movimiento->remanente > $posicion->cantidad)
        {
            dd('Hay que cerrar la posicion usando parcialmente el movimiento');
        }

        dump($movimiento->remanente);
    }

    private function split(Posicion $posicion, $cantidad)
    {
        /*  Esta funcion divide una posicion en dos posiciones. La primera de esas posiciones, que conserva el id de la
            original, contiene $cantidad
            La nueva posicion, contiene el resto de las cantidades
            La implementación actual solo contempla el caso en que la posicion tenia un solo movimiento. Habria que analizar como implementar en el caso de multiples movimientos
        */

        $movimiento_orginal = $posicion->movimientos()->first();

        $movimiento_orginal->cantidad_imputada = 0;

        $movimiento_orginal->save();

        $posicion->movimientos()->detach($movimiento_orginal);

        $this->crearPosicion($movimiento_orginal, $posicion->cantidad - $cantidad);

        $this->primerMovimiento($posicion, $movimiento_orginal, $cantidad);
    }

}