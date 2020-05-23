<?php

namespace Cirote\Movimientos\Models;

use Illuminate\Database\Eloquent\Model;
use Cirote\Activos\Config\Config;

class Movimiento extends Model
{
    protected $table = Config::PREFIJO . Config::MOVIMIENTOS;

    protected $guarded = [];

    protected $dates = [
        'fecha_operacion',
        'fecha_liquidacion'
    ];

    public function getRemanenteAttribute()
    {
    	return $this->cantidad - $this->cantidad_imputada;
    }
}
