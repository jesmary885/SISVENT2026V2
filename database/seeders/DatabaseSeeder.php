<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(TasaSeeder::class);
        $this->call(ProveedorSeeder::class);
        $this->call(CajaSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(MarcaSeeder::class);
        $this->call(ProductoSeeder::class);
        $this->call(ClientesSeeder::class);
        $this->call(NegocioSeeder::class);
        
    }
}
