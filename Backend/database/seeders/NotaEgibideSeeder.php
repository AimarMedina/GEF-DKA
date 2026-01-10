<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotaEgibideSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nota_egibide')->insert([
            ['id' => 1, 'ID_Asignatura' => 1, 'ID_Alumno' => 20001, 'Nota' => 8.5],
            ['id' => 2, 'ID_Asignatura' => 2, 'ID_Alumno' => 20001, 'Nota' => 7.5],
            ['id' => 3, 'ID_Asignatura' => 1, 'ID_Alumno' => 20002, 'Nota' => 9.0],
            ['id' => 4, 'ID_Asignatura' => 2, 'ID_Alumno' => 20003, 'Nota' => 6.75],
            ['id' => 5, 'ID_Asignatura' => 3, 'ID_Alumno' => 20004, 'Nota' => 8.25],
            ['id' => 6, 'ID_Asignatura' => 4, 'ID_Alumno' => 20005, 'Nota' => 7.5],
        ]);

    }
}
