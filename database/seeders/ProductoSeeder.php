<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $productos= [
            [
            'nombre' => 'Prueba 1 prueba prueba prueba',
            'cod_barra' => '1111111111',
            'estado' => 'Activo',
            'cantidad' => '50',
            'presentacion' => 'Unidad',
            'stock_minimo' => '1',
            'categoria' => 'Viveres',
            'marca_id' => '1',
            'precio_venta' => '2',
            ],
            [
            'nombre' => 'Prueba 2 prueba prueba prueba',
            'cod_barra' => '22222222222',
            'estado' => 'Activo',
            'cantidad' => '55',
            'presentacion' => 'Unidad',
            'stock_minimo' => '1',
            'categoria' => 'Viveres',
            'marca_id' => '2',
            'precio_venta' => '3',
            ],
        ];

      foreach ($productos as $producto){
        Producto::create($producto);
     }
    }
}
