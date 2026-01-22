<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorGradoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor_grado')->insert([
            // Grado 1
            ['ID_Grado' => 1, 'ID_Tutor' => 30001],
            ['ID_Grado' => 1, 'ID_Tutor' => 30002],

            // Grado 2
            ['ID_Grado' => 2, 'ID_Tutor' => 30002],
            ['ID_Grado' => 2, 'ID_Tutor' => 30003],

            // Grado 3
            ['ID_Grado' => 3, 'ID_Tutor' => 30003],
            ['ID_Grado' => 3, 'ID_Tutor' => 30004],

            // Grado 4
            ['ID_Grado' => 4, 'ID_Tutor' => 30004],
            ['ID_Grado' => 4, 'ID_Tutor' => 30005],

            // Grado 5
            ['ID_Grado' => 5, 'ID_Tutor' => 30005],
            ['ID_Grado' => 5, 'ID_Tutor' => 30006],

            // Grado 6
            ['ID_Grado' => 6, 'ID_Tutor' => 30006],
            ['ID_Grado' => 6, 'ID_Tutor' => 30007],

            // Grado 7
            ['ID_Grado' => 7, 'ID_Tutor' => 30007],
            ['ID_Grado' => 7, 'ID_Tutor' => 30008],

            // Grado 8
            ['ID_Grado' => 8, 'ID_Tutor' => 30008],
            ['ID_Grado' => 8, 'ID_Tutor' => 30009],

            // Grado 9
            ['ID_Grado' => 9, 'ID_Tutor' => 30009],
            ['ID_Grado' => 9, 'ID_Tutor' => 30010],

            // Grado 10
            ['ID_Grado' => 10, 'ID_Tutor' => 30010],
            ['ID_Grado' => 10, 'ID_Tutor' => 30011],

            // Grado 11
            ['ID_Grado' => 11, 'ID_Tutor' => 30011],
            ['ID_Grado' => 11, 'ID_Tutor' => 30001],
        ]);
    }
}
