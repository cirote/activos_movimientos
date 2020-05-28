<?php

namespace Cirote\Movimientos\Actions;

use Cirote\Movimientos\Models\Posicion;

class CalcularValorActualDeLasPosicionesAbiertasAction
{
	public function execute()
    {
        $valor = 0;

        foreach(Posicion::with(['activo.precio'])->abiertas()->resumir()->get() as $posicion)
        {
            $valor += $posicion->activo->precioActualDolares * $posicion->cantidad;
        }

        return $valor;
    }
}