<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstanciaAlumnoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estancia_alumno')->insert([
            ['id' => 1, 'ID_Alumno' => 20001, 'CIF_Empresa' => 'B12345678', 'Fecha_inicio' => '2025-11-04', 'Fecha_fin' => null],
            ['id' => 2, 'ID_Alumno' => 20002, 'CIF_Empresa' => 'B12345678', 'Fecha_inicio' => '2025-11-04', 'Fecha_fin' => null],
            ['id' => 3, 'ID_Alumno' => 20003, 'CIF_Empresa' => 'A87654321', 'Fecha_inicio' => '2025-11-04', 'Fecha_fin' => null],
            ['id' => 4, 'ID_Alumno' => 20004, 'CIF_Empresa' => 'A87654321', 'Fecha_inicio' => '2025-11-04', 'Fecha_fin' => null],
            ['id' => 5, 'ID_Alumno' => 20005, 'CIF_Empresa' => 'B12345678', 'Fecha_inicio' => '2025-11-04', 'Fecha_fin' => null],
        ]);
    }
}
