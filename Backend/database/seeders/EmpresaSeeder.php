<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('empresa')->insert([
            [
                'CIF' => 'B12345678',
                'Nombre' => 'TechBizi SL',
                'Direccion' => 'C/ Gran Vía 12, Bilbao',
                'Email' => 'rrhh@techbizi.local',
                'N_Tel' => '944000111',
            ],
            [
                'CIF' => 'A87654321',
                'Nombre' => 'IndusGoi SA',
                'Direccion' => 'Pol. Ugaldeguren I, Nave 7',
                'Email' => 'contacto@indusgoi.local',
                'N_Tel' => '944000222',
            ],
        ]);
    }
}
