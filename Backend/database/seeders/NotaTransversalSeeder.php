<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotaTransversalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nota_transversal')->insert([
            ['id' => 1, 'ID_Transversal' => 1, 'ID_Alumno' => 20001, 'Nota' => 4],
            ['id' => 2, 'ID_Transversal' => 2, 'ID_Alumno' => 20001, 'Nota' => 4],
            ['id' => 3, 'ID_Transversal' => 3, 'ID_Alumno' => 20001, 'Nota' => 4],
            ['id' => 4, 'ID_Transversal' => 1, 'ID_Alumno' => 20002, 'Nota' => 3],
            ['id' => 5, 'ID_Transversal' => 2, 'ID_Alumno' => 20003, 'Nota' => 3],
            ['id' => 6, 'ID_Transversal' => 3, 'ID_Alumno' => 20004, 'Nota' => 3],
            ['id' => 7, 'ID_Transversal' => 2, 'ID_Alumno' => 20005, 'Nota' =>4],
        ]);
    }
}
