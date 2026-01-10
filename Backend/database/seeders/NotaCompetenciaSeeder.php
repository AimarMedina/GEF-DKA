<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotaCompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nota_competencia')->insert([
            ['id' => 1, 'ID_Competencia' => 1, 'ID_Alumno' => 20001, 'Nota' => 8.00],
            ['id' => 2, 'ID_Competencia' => 2, 'ID_Alumno' => 20001, 'Nota' => 7.50],
            ['id' => 3, 'ID_Competencia' => 3, 'ID_Alumno' => 20001, 'Nota' => 8.75],
            ['id' => 4, 'ID_Competencia' => 1, 'ID_Alumno' => 20002, 'Nota' => 7.00],
            ['id' => 5, 'ID_Competencia' => 3, 'ID_Alumno' => 20003, 'Nota' => 7.50],
            ['id' => 6, 'ID_Competencia' => 2, 'ID_Alumno' => 20004, 'Nota' => 6.75],
            ['id' => 7, 'ID_Competencia' => 1, 'ID_Alumno' => 20005, 'Nota' => 8.25],
        ]);
    }
}
