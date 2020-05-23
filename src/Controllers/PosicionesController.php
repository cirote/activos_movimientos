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

	public function abiertas()
    {
        return view('movimientos::posiciones.abiertas')
        	->withPosiciones(Posicion::orderBy('fecha_apertura')->where('estado', 'Abierta')->paginate(10));
    }

	public function cerradas()
    {
        return view('movimientos::posiciones.cerradas')
            ->withPosiciones(Posicion::orderBy('fecha_apertura')->where('estado', 'Cerrada')->paginate(10));
    }

	public function prueba()
    {
        $iol = new Iol();

        $iol->execute();
    }
}
