<?php

namespace Cirote\Movimientos\Models;

use Illuminate\Database\Eloquent\Model;
use Cirote\Activos\Config\Config;
use Cirote\Activos\Models\Activo;
use App\Models\Broker;

class Posicion extends Model
{
    protected $table = Config::PREFIJO . Config::POSICIONES;

    protected $guarded = [];

    protected $dates = [
        'fecha_apertura',
        'fecha_cierre'
    ];

    public function Activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function Broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function Movimientos()
    {
        return $this->belongsToMany(Movimiento::class, Config::PREFIJO . Config::MOVIMIENTOS_POSICIONES)
            ->as('asignado');
    }

    public function scopeAbiertas($query)
    {
        $query->where('estado', 'Abierta');
    }

    public function scopeCerradas($query)
    {
        $query->where('estado', 'Cerrada');
    }

    public function scopeCortas($query)
    {
        $query->where('tipo', 'Corta');
    }

    public function scopeLargas($query)
    {
        $query->where('tipo', 'Larga');
    }

    public function scopeByActivo($query, Activo $activo)
    {
        $query->where('activo_id', $activo->id);
    }

    public function scopeByBroker($query, Broker $broker)
    {
        $query->where('broker_id', $broker->id);
    }

    public function scopeOrdenadas($query)
    {
        $query->orderBy('fecha_apertura');
    }
}
