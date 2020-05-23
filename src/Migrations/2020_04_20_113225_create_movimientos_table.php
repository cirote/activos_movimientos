<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cirote\Activos\Config\Config;

class CreateMovimientosTable extends Migration
{
    public function up()
    {
        Schema::create(Config::PREFIJO . Config::MOVIMIENTOS, function (Blueprint $table) 
        {
            $table->increments('id');

            $table->date('fecha_operacion');
            $table->date('fecha_liquidacion')->nullable();

            $table->integer('activo_id')->unsigned()->nullable();
            $table->foreign('activo_id')->references('id')->on(Config::PREFIJO . Config::ACTIVOS);

            $table->string('tipo_operacion')->nullable();
            $table->string('numero_operacion')->nullable();
            $table->string('numero_boleto')->nullable();
            $table->string('observaciones');

            $table->double('cantidad')->nullable();
            $table->double('cantidad_imputada')->default(0);

            $table->integer('moneda_original_id')->unsigned()->nullable();
            $table->foreign('moneda_original_id')->references('id')->on(Config::PREFIJO . Config::ACTIVOS);

            $table->decimal('precio_en_moneda_original', 10, 2)->nullable();
            $table->decimal('monto_en_moneda_original', 10, 2)->nullable();
            $table->decimal('comisiones_en_moneda_original', 10, 2)->nullable();
            $table->decimal('iva_en_moneda_original', 10, 2)->nullable();
            $table->decimal('otros_impuestos_en_moneda_original', 10, 2)->nullable();
            $table->decimal('saldo_en_moneda_original', 10, 2)->nullable();
            $table->decimal('saldo_calculado_en_moneda_original', 10, 2)->default(0);

            $table->decimal('precio_en_dolares', 10, 2)->nullable();
            $table->decimal('monto_en_dolares', 10, 2)->nullable();

            $table->decimal('precio_en_pesos', 10, 2)->nullable();
            $table->decimal('monto_en_pesos', 10, 2)->nullable();

            $table->integer('broker_id')->unsigned();
            $table->foreign('broker_id')->references('id')->on(Config::PREFIJO . Config::BROKERS);

            $table->string('cuenta_corriente');
            $table->string('hoja')->nullable();

	        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::PREFIJO . Config::MOVIMIENTOS);
    }
}
