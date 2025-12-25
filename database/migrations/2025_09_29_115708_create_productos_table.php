<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('cod_barra')->nullable();
            $table->string('estado')->nullable();
            //$table->enum('estado',['Habilitado','Deshabilitado'])->default('Habilitado');
            $table->integer('cantidad')->default(0);
            $table->string('presentacion')->nullable();
           
            $table->string('categoria')->nullable();

            $table->string('precio_venta');

            //$table->enum('presentacion',['Unidad','Libra','Kg','Caja','Paquete','Lata','Galon','Botella','Tira','Sobre','Saco','Tarjeta','Otro'])->default('Unidad');
            $table->integer('stock_minimo');
            // $table->enum('vencimiento',['Si','No'])->default('No');
           $table->enum('exento',['Si','No'])->default('Si')->nullable();
          

            $table->unsignedBigInteger('marca_id');
            $table->foreign('marca_id')->references('id')->on('marcas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
