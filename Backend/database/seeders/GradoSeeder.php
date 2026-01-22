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
            ['id' => 3, 'Nombre' => 'Desarrollo de Aplicaciones Multiplataforma (DAM)', 'Curso' => '1º', 'ID_Tutor' => 30003],
            ['id' => 4, 'Nombre' => 'Desarrollo de Aplicaciones Multiplataforma (DAM)', 'Curso' => '2º', 'ID_Tutor' => 30004],
            ['id' => 5, 'Nombre' => 'Mecatrónica Industrial', 'Curso' => '1º', 'ID_Tutor' => 30005],
            ['id' => 6, 'Nombre' => 'Mecatrónica Industrial', 'Curso' => '2º', 'ID_Tutor' => 30006],
            ['id' => 7, 'Nombre' => 'Automoción', 'Curso' => '1º', 'ID_Tutor' => 30007],
            ['id' => 8, 'Nombre' => 'Automoción', 'Curso' => '2º', 'ID_Tutor' => 30008],
            ['id' => 9, 'Nombre' => 'Administración y Finanzas', 'Curso' => '2º', 'ID_Tutor' => 30009],
            ['id' => 10, 'Nombre' => 'Ciberseguridad (Especialización)', 'Curso' => '1º', 'ID_Tutor' => 30010],
            ['id' => 11, 'Nombre' => 'Robótica Colaborativa (Especialización)', 'Curso' => '1º', 'ID_Tutor' => 30011],
        ]);
    }
}
