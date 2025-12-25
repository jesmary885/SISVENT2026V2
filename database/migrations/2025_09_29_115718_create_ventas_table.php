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
          
            $table->float('descuento')->nullable();
            $table->float('subtotal_dolares')->nullable();
            $table->float('subtotal_bolivares')->nullable();

            $table->float('total_dolares');
            $table->float('total_bolivares');
            
            $table->float('impuesto')->nullable();
            $table->float('exento')->nullable();

             $table->longText('comentario')->nullable();

            $table->float('total_pagado_cliente')->nullable();
           // $table->float('deuda_cliente')->nullable();

           $table->float('monto_pagado_dolares')->nullable();
           $table->float('monto_pagado_bolivares')->nullable();

           $table->float('deuda_dolares')->nullable();
           $table->float('deuda_bolivares')->nullable();
           $table->string('tipo_comprobante')->nullable();
           $table->string('estado_pago')->nullable();

            $table->string('estado')->nullable();
            $table->float('vuelto')->nullable();      

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
