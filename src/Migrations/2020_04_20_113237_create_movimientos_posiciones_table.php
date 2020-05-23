<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cirote\Activos\Config\Config;

class CreateMovimientosPosicionesTable extends Migration
{
    public function up()
    {
        Schema::create(Config::PREFIJO . Config::MOVIMIENTOS_POSICIONES, function (Blueprint $table) 
        {
            $table->increments('id');

            $table->integer('posicion_id')->unsigned()->nullable();
            $table->foreign('posicion_id')->references('id')->on(Config::PREFIJO . Config::POSICIONES);

            $table->integer('movimiento_id')->unsigned();
            $table->foreign('movimiento_id')->references('id')->on(Config::PREFIJO . Config::MOVIMIENTOS);

            $table->double('cantidad')->nullable();

            $table->integer('moneda_original_id')->unsigned()->nullable();
            $table->foreign('moneda_original_id')->references('id')->on(Config::PREFIJO . Config::ACTIVOS);

            $table->decimal('precio_en_moneda_original', 10, 2)->nullable();
            $table->decimal('monto_parcial_en_moneda_original', 10, 2)->nullable();

            $table->decimal('precio_en_dolares', 10, 2)->nullable();
            $table->decimal('monto_parcial_en_dolares', 10, 2)->nullable();

            $table->decimal('precio_en_pesos', 10, 2)->nullable();
            $table->decimal('monto_parcial_en_pesos', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::PREFIJO . Config::MOVIMIENTOS_POSICIONES);
    }
}
