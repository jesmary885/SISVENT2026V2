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
        Schema::create('apertura_cierre_cajas', function (Blueprint $table) {
             $table->id();
            
              $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            
            $table->decimal('monto_inicial_bs', 15, 2)->default(0);
            $table->decimal('monto_inicial_dolares', 15, 2)->default(0);
            
            $table->decimal('monto_final_bs', 15, 2)->nullable();
            $table->decimal('monto_final_dolares', 15, 2)->nullable();
            
            $table->decimal('ventas_bs', 15, 2)->nullable();
            $table->decimal('ventas_dolares', 15, 2)->nullable();
            
            $table->decimal('diferencia_bs', 15, 2)->nullable();
            $table->decimal('diferencia_dolares', 15, 2)->nullable();
            
            $table->text('observaciones')->nullable();
            
            $table->unsignedBigInteger('caja_id');
            $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('cascade');
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
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
        Schema::dropIfExists('apertura_cierre_cajas');
    }
};
