<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $marcas = [
            [
               'nombre' => 'Marca 1',
            ],
            [
               'nombre' => 'Marca 2',
            ],
            [
               'nombre' => 'Marca 3',
            ],
            [
               'nombre' => 'Marca 4',
            ],
            [
               'nombre' => 'Marca 5',
            ],


            ];

             foreach ($marcas as $marca){
                Marca::create($marca);
             }
    }
}
