<?php

namespace Database\Seeders;

use App\Models\Tasa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TasaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
         Tasa::create([
            'tasa_actual' => 156.89,
     
        ]);
    }
}
