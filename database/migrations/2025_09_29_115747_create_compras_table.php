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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->integer('cantidad');

            $table->string('metodo_pago');

            $table->string('moneda_compra');

            $table->decimal('total_original', 15, 2)->nullable();
        
            $table->float('tasa_cambio_compra')->nullable();
            $table->decimal('total_usd_equivalente', 15, 2)->nullable();
            $table->decimal('precio_unitario', 15, 2)->nullable();

            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedors');

            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('caja_id')->nullable();
            $table->foreign('caja_id')->references('id')->on('cajas');



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
        Schema::dropIfExists('compras');
    }
};
