<?php

namespace Cirote\Movimientos\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cirote\Movimientos\Actions\MigratosDatosOriginales\Iol;

class MovimientosController extends Controller
{
	public function prueba()
    {
        $iol = new Iol();

        $iol->execute();
    }
}
