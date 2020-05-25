<?php

namespace Cirote\Movimientos\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cirote\Movimientos\Models\Posicion;
use Cirote\Activos\Models\Activo;
use App\Models\Broker;

class PosicionesController extends Controller
{
	public function index()
    {
    	dd(Posicion::all());
    }

    public function resumen()
    {
        return view('movimientos::posiciones.resumen')
            ->withPosiciones(Posicion::with(['activo.ticker', 'broker'])->abiertas()->resumir()->paginate(10));
    }

	public function abiertas(Activo $activo = null, Broker $broker = null)
    {
        $posiciones = Posicion::with(['activo.ticker', 'broker'])->abiertas()->byApertura();

        if ($activo)
            $posiciones->byActivo($activo);

        if ($broker)
            $posiciones->byBroker($broker);

        return view('movimientos::posiciones.abiertas')
        	->withPosiciones($posiciones->paginate(10));
    }

	public function cerradas()
    {
        return view('movimientos::posiciones.cerradas')
            ->withPosiciones(Posicion::with(['activo.ticker', 'broker'])->cerradas()->byCierre()->paginate(10));
    }

	public function prueba()
    {
        $iol = new Iol();

        $iol->execute();
    }
}
