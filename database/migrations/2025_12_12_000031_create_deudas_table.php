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
        Schema::create('deudas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained()->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->decimal('monto_dolares', 10, 2);
            $table->decimal('monto_bolivares', 10, 2);
            $table->date('fecha_limite');
            $table->text('comentario')->nullable();
            $table->enum('estado', ['pendiente', 'pagada', 'cancelada'])->default('pendiente');
            $table->foreignId('registrada_por')->constrained('users');
            $table->timestamp('fecha_pago')->nullable();
            $table->text('comentario_pago')->nullable();
            $table->timestamps();
            
            $table->index('estado');
            $table->index('fecha_limite');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deudas');
    }
};
