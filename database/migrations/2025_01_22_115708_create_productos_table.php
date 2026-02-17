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
            $table->decimal('stock_base', 12, 3)->default(0);
            $table->string('unidad_base')->default('unidad'); // unidad | kg
            $table->integer('stock_minimo');
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
