<?php

namespace Cirote\Movimientos\Models;

use Illuminate\Database\Eloquent\Model;
use Cirote\Activos\Config\Config;

class Movimiento extends Model
{
    protected $table = Config::PREFIJO . Config::MOVIMIENTOS;

    protected $guarded = [];

}
