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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
          
            $table->decimal('descuento', 15, 2)->nullable();
            $table->decimal('subtotal_dolares', 15, 2)->nullable();
            $table->decimal('subtotal_bolivares', 15, 2)->nullable();

            $table->decimal('total_dolares', 15, 2);
            $table->decimal('total_bolivares', 15, 2);
            
            $table->decimal('impuesto', 15, 2)->nullable();
            $table->decimal('exento', 15, 2)->nullable();

             $table->longText('comentario')->nullable();

            $table->decimal('total_pagado_cliente', 15, 2)->nullable();
           // $table->float('deuda_cliente')->nullable();

           $table->decimal('monto_pagado_dolares', 15, 2)->nullable();
           $table->decimal('monto_pagado_bolivares', 15, 2)->nullable();

           $table->decimal('deuda_dolares', 15, 2)->nullable();
           $table->decimal('deuda_bolivares', 15, 2)->nullable();
           $table->string('tipo_comprobante')->nullable();
           $table->string('estado_pago')->nullable();

            $table->string('estado')->nullable();
            $table->decimal('vuelto', 15, 2)->nullable();      

            $table->string('metodo_pago');
             $table->string('metodo_pago_vuelto')->nullable();

              $table->string('mesa_ubicacion')->nullable();

            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullable();

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
        Schema::dropIfExists('ventas');
    }
};
