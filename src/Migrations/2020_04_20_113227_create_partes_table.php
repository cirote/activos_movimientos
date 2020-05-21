<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cirote\Activos\Config\Config;

class CreatePartesTable extends Migration
{
    public function up()
    {
        Schema::create(Config::PREFIJO . Config::PARTES, function (Blueprint $table) 
        {
            $table->increments('id');

            $table->integer('movimiento_id')->unsigned();
            $table->foreign('movimiento_id')->references('id')->on(Config::PREFIJO . Config::MOVIMIENTOS);

            $table->string('concepto');

            $table->integer('moneda_id');

            $table->integer('precio');
            $table->integer('monto_total');
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::PREFIJO . Config::PARTES);
    }
}
