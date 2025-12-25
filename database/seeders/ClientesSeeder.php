<?php

namespace Database\Seeders;

use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         Cliente::create([
            'nombre' => 'general',
            'tipo' => 'general'
         ]);
    }
}
