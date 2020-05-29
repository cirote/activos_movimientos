<?php

namespace Cirote\Movimientos\Models;

use Illuminate\Database\Eloquent\Model;
use Cirote\Activos\Config\Config;
use Cirote\Activos\Models\Activo;
use Cirote\Activos\Models\Broker;

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

    public function scopeDeFondos($query)
    {
        return $query->where('tipo_operacion', 'Deposito')->orWhere('tipo_operacion', 'Extraccion');
    }

    public function scopeResumir($query)
    {
        return $query->selectRaw('broker_id, sum(IF(`tipo_operacion` = "Deposito", `monto_en_pesos`, 0)) as suma_de_depositos_en_pesos, sum(IF(`tipo_operacion` = "Extraccion", `monto_en_pesos`, 0)) as suma_de_extracciones_en_pesos, sum(IF(`tipo_operacion` = "Deposito", `monto_en_dolares`, 0)) as suma_de_depositos_en_dolares, sum(IF(`tipo_operacion` = "Extraccion", `monto_en_dolares`, 0)) as suma_de_extracciones_en_dolares')
            ->groupBy(['broker_id']);
    }
}
