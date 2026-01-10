<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorGradoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor_grado')->insert([
            ['id' => 1, 'ID_Tutor' => 30001, 'ID_Grado' => 1],
            ['id' => 2, 'ID_Tutor' => 30002, 'ID_Grado' => 2],
        ]);
    }
}
