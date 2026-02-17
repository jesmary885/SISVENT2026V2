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
        Schema::create('producto_presentaciones', function (Blueprint $table) {
            $table->id();

            $table->string('nombre'); // unidad, caja, kg (o caja24, pack6, etc)
            $table->decimal('factor_base', 12, 3)->default(1); // 1, 24, 6...
            $table->decimal('precio_usd', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
             $table->decimal('cantidad_de_cajas', 12, 3)->nullable(); // 1, 24, 6...

            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos');

            
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
        Schema::dropIfExists('producto_presentaciones');
    }
};
