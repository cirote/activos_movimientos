<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cirote\Activos\Config\Config;

class CreatePosicionesTable extends Migration
{
    public function up()
    {
        Schema::create(Config::PREFIJO . Config::POSICIONES, function (Blueprint $table) 
        {
            $table->increments('id');

            $table->date('fecha_apertura');
            $table->date('fecha_cierre')->nullable()->default(null);

            $table->string('tipo');
            $table->string('estado');

            $table->integer('activo_id')->unsigned()->nullable();
            $table->foreign('activo_id')->references('id')->on(Config::PREFIJO . Config::ACTIVOS);

            $table->integer('broker_id')->unsigned();
            $table->foreign('broker_id')->references('id')->on(Config::PREFIJO . Config::BROKERS);

            $table->double('cantidad')->default(0);

            $table->integer('moneda_original_id')->unsigned()->nullable();
            $table->foreign('moneda_original_id')->references('id')->on(Config::PREFIJO . Config::ACTIVOS);

            $table->decimal('precio_en_moneda_original', 10, 2)->default(0);
            $table->decimal('monto_en_moneda_original', 10, 2)->default(0);
            $table->decimal('resultado_en_moneda_original', 10, 2)->nullable()->default(null);

            $table->decimal('precio_en_dolares', 10, 2)->default(0);
            $table->decimal('monto_en_dolares', 10, 2)->default(0);
            $table->decimal('resultado_en_dolares', 10, 2)->nullable()->default(null);

            $table->decimal('precio_en_pesos', 10, 2)->default(0);
            $table->decimal('monto_en_pesos', 10, 2)->default(0);
            $table->decimal('resultado_en_pesos', 10, 2)->nullable()->default(null);

            $table->decimal('precio_de_cierre_en_dolares', 10, 2)->nullable()->default(null);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::PREFIJO . Config::POSICIONES);
    }
}
