<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alumno')->insert([
            ['ID_Usuario' => 20001, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20002, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20003, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40002],
            ['ID_Usuario' => 20004, 'ID_Grado' => 2, 'ID_Tutor' => 30002, 'ID_Instructor' => 40002],
            ['ID_Usuario' => 20005, 'ID_Grado' => 2, 'ID_Tutor' => 30002, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20006, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20007, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20008, 'ID_Grado' => 2, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20009, 'ID_Grado' => 2, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['ID_Usuario' => 20010, 'ID_Grado' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
        ]);
    }
}
