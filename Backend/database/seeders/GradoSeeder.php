<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('grado')->insert([
            ['id' => 1, 'Nombre' => 'Desarrollo de Aplicaciones Web (DAW)', 'Curso' => '2º', 'ID_Tutor' => 30001],
            ['id' => 2, 'Nombre' => 'Administración de Sistemas (ASIR)', 'Curso' => '2º', 'ID_Tutor' => 30002],
        ]);
    }
}
