<?php

namespace Database\Seeders;

use App\Models\Negocio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NegocioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Negocio::create([
            'nombre' => 'prueba',
            'email' => 'prueba@prueba.com',
            'telefono' => '5555555555',
            'tipo_documento' => 'RIF',
            'direccion' => 'DIRECCION DE PRUEBA',
            'nro_documento' => '111111111111',
            'logo' => 'logo/logo.png',
        ]);
    }
}
