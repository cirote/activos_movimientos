<?php

namespace Cirote\Movimientos\Models;

use Illuminate\Database\Eloquent\Model;
use Cirote\Activos\Config\Config;
use Cirote\Activos\Models\Activo;
use App\Models\Broker;

class Movimiento extends Model
{
    protected $table = Config::PREFIJO . Config::MOVIMIENTOS;

    protected $guarded = [];

    protected $dates = [
        'fecha_operacion',
        'fecha_liquidacion'
    ];

    public function Activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function Broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function getRemanenteAttribute()
    {
    	return $this->cantidad - $this->cantidad_imputada;
    }
}
