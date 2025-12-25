<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Proveedor::create([
            'nombre_encargado' => 'Jose Ramirez',
            'nombre_proveedor' => 'Surtidos CDR',
            'tipo_documento' => 'rif',
            'nro_documento' => 'j-36589990-0',
            'email' => 'surtidoscdk@gmail.com',
            'telefono' => '0414-19879987',
            'direccion' => 'el tigre'
     
        ]);
    }
}
