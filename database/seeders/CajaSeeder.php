<?php

namespace Database\Seeders;

use App\Models\Caja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          Caja::create([
            'nombre' => 'Principal',
            'status' => 'Habilitada',
            'saldo_bolivares' => '2000',
            'saldo_dolares' => '50'
     
        ]);
    }
}
