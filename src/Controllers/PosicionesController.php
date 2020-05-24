<?php

namespace Cirote\Movimientos\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cirote\Movimientos\Models\Posicion;

class PosicionesController extends Controller
{
	public function index()
    {
    	dd(Posicion::all());
    }

    public function resumen()
    {
        return view('movimientos::posiciones.resumen')
            ->withPosiciones(Posicion::abiertas()->resumir()->paginate(10));
    }

	public function abiertas()
    {
        return view('movimientos::posiciones.abiertas')
        	->withPosiciones(Posicion::abiertas()->ordenadas()->paginate(10));
    }

	public function cerradas()
    {
        return view('movimientos::posiciones.cerradas')
            ->withPosiciones(Posicion::cerradas()->ordenadas()->paginate(10));
    }

	public function prueba()
    {
        $iol = new Iol();

        $iol->execute();
    }
}
