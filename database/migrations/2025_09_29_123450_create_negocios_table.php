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
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();


             $table->string('nombre');
            $table->string('email')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('nro_documento')->nullable();
          /*  $table->string('nombre_impuesto')->nullable();
            $table->string('impuesto')->nullable();*/

            $table->boolean('facturar_con_iva')->default(false);
            $table->decimal('porcentaje_iva', 5, 2)->default(16.00);
            $table->string('nombre_impuesto')->default('IVA');
            // $table->string('porcentaje_puntos');
            $table->string('logo')->nullable();

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
        Schema::dropIfExists('negocios');
    }
};
