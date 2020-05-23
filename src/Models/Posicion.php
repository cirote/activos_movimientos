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

    public static function crear(Movimiento $movimiento, $cantidad_solicitada = 0)
    {
        if (! $cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            return null;
        }

        if ($cantidad_solicitada > $cantidad_remanente)
        {
            die("Error en la cantidad solicitada");
        }

    	$posicion = static::create([
    	    'fecha_apertura' => $movimiento->fecha_operacion,
            'tipo'			 => $movimiento->tipo_operacion == 'Compra' ? 'Larga' : 'Corta',
            'estado'		 => 'Abierta',
            'activo_id' 	 => $movimiento->activo_id,
			'broker_id' 	 => $movimiento->broker_id,
            'moneda_original_id' => $movimiento->moneda_original_id
    	]);

    	$posicion->primerMovimiento($movimiento, $cantidad_solicitada ?: $cantidad_remanente);
    }

    private function primerMovimiento(Movimiento $movimiento, $cantidad_solicitada)
    {
        $this->cantidad = $cantidad_solicitada;

        $ponderador_movimiento = $cantidad_solicitada / $movimiento->cantidad;

        $this->precio_en_moneda_original = $movimiento->precio_en_moneda_original;
        $this->monto_en_moneda_original = $ponderador_movimiento * $movimiento->monto_en_moneda_original;

        $this->precio_en_dolares = $movimiento->precio_en_dolares;
        $this->monto_en_dolares = $ponderador_movimiento * $movimiento->monto_en_dolares;

        $this->precio_en_pesos = $movimiento->precio_en_pesos;
        $this->monto_en_pesos = $ponderador_movimiento * $movimiento->monto_en_pesos;

        $this->save();

        $this->agregarMovimiento($movimiento, $cantidad_solicitada, $ponderador_movimiento);
    }

    private function agregarMovimiento(Movimiento $movimiento, $cantidad_solicitada, $ponderador_movimiento)
    {
        $this->movimientos()->save($movimiento, [
            'cantidad'      => $cantidad_solicitada,

            'moneda_original_id' => $movimiento->moneda_original_id,
            'precio_en_moneda_original'        => $movimiento->precio_en_moneda_original,
            'monto_parcial_en_moneda_original' => $ponderador_movimiento * $movimiento->monto_en_moneda_original,

            'precio_en_dolares'        => $movimiento->precio_en_dolares,
            'monto_parcial_en_dolares' => $ponderador_movimiento * $movimiento->monto_en_dolares,

            'precio_en_pesos'        => $movimiento->precio_en_pesos,
            'monto_parcial_en_pesos' => $ponderador_movimiento * $movimiento->monto_en_pesos,
        ]);

        $movimiento->cantidad_imputada += $cantidad_solicitada;

        $movimiento->save();
    }

    public function sumarMovimiento(Movimiento $movimiento, $cantidad_solicitada)
    {
        if ($cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            if ($cantidad_solicitada > $cantidad_remanente)
            {
                die("Error en la cantidad solicitada");
            }

            $cantidad_original = $this->cantidad;

            $this->cantidad += $cantidad_solicitada;

            $ponderador_original = $cantidad_original / $this->cantidad;

            $ponderador_remanente = $cantidad_solicitada / $this->cantidad;

            $ponderador_movimiento = $cantidad_solicitada / $movimiento->cantidad;

            $this->precio_en_moneda_original = ($this->precio_en_moneda_original * $ponderador_original) + ($movimiento->precio_en_moneda_original * $ponderador_remanente);
            $this->monto_en_moneda_original += $ponderador_movimiento * $movimiento->monto_en_moneda_original;

            $this->precio_en_dolares = ($this->precio_en_dolares * $ponderador_original) + ($movimiento->precio_en_dolares * $ponderador_remanente);
            $this->monto_en_dolares += $ponderador_movimiento * $movimiento->monto_en_dolares;

            $this->precio_en_pesos = ($this->precio_en_pesos * $ponderador_original) + ($movimiento->precio_en_pesos * $ponderador_remanente);
            $this->monto_en_pesos += $ponderador_movimiento * $movimiento->monto_en_pesos;

            $this->save();

            $this->movimientos()->save($movimiento, [
                'cantidad'      => $cantidad_solicitada,

                'moneda_original_id' => $movimiento->moneda_original_id,
                'precio_en_moneda_original'        => $movimiento->precio_en_moneda_original,
                'monto_parcial_en_moneda_original' => $ponderador_movimiento * $movimiento->monto_en_moneda_original,

                'precio_en_dolares'        => $movimiento->precio_en_dolares,
                'monto_parcial_en_dolares' => $ponderador_movimiento * $movimiento->monto_en_dolares,

                'precio_en_pesos'        => $movimiento->precio_en_pesos,
                'monto_parcial_en_pesos' => $ponderador_movimiento * $movimiento->monto_en_pesos,
            ]);

            $movimiento->cantidad_imputada += $cantidad_solicitada;

            $movimiento->save();
        }

        return $this;
    }

    public function cerrar(Movimiento $movimiento, $cantidad_solicitada = null)
    {
        if (! $cantidad_remanente = $movimiento->cantidad - $movimiento->cantidad_imputada)
        {
            return null;
        }

        if (! $cantidad_solicitada)
        {
            $cantidad_solicitada = $cantidad_remanente;
        }

        if ($cantidad_solicitada > $cantidad_remanente)
        {
            die("Error en la cantidad solicitada");
        }

        if ($movimiento->remanente < $this->cantidad)
        {
            $this->split($movimiento->remanente);

            return;
        }

        if ($movimiento->remanente >= $this->cantidad)
        {
            $ponderador = $this->cantidad / $movimiento->cantidad;

            $this->fecha_cierre = $movimiento->fecha_operacion;

            $this->estado = 'Cerrada';

            $this->resultado_en_moneda_original = ($ponderador * $movimiento->monto_en_moneda_original) - $this->monto_en_moneda_original;

            $this->resultado_en_dolares = ($ponderador * $movimiento->monto_en_dolares) - $this->monto_en_dolares;

            $this->resultado_en_pesos = ($ponderador * $movimiento->monto_en_pesos) - $this->monto_en_pesos;

            $this->save();

            $this->agregarMovimiento($movimiento, $this->cantidad, $ponderador);

            return;
        }

        if ($movimiento->remanente > $this->cantidad)
        {
            dd('Hay que cerrar la posicion usando parcialmente el movimiento');
        }

        dump($movimiento->remanente);
    }

    private function split($cantidad)
    {
        /*  Esta funcion divide una posicion en dos posiciones. La primera de esas posiciones, que conserva el id de la
            original, contiene $cantidad
            La nueva posicion, contiene el resto de las cantidades
            La implementación actual solo contempla el caso en que la posicion tenia un solo movimiento. Habria que analizar como implementar en el caso de multiples movimientos
        */

        $movimiento_orginal = $this->movimientos()->first();

        $movimiento_orginal->cantidad_imputada = 0;

        $movimiento_orginal->save();

        $this->movimientos()->detach($movimiento_orginal);

        static::crear($movimiento_orginal, $this->cantidad - $cantidad);

        $this->primerMovimiento($movimiento_orginal, $cantidad);
    }

    public function Movimientos()
    {
        return $this->belongsToMany(Movimiento::class, Config::PREFIJO . Config::MOVIMIENTOS_POSICIONES)->as('asignado');
    }

    public function Activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function Broker()
    {
        return $this->belongsTo(Broker::class);
    }
}
