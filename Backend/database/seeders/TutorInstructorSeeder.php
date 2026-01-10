<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorInstructorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor_instructor')->insert([
            ['id' => 1, 'ID_Tutor' => 30001, 'ID_Instructor' => 40001],
            ['id' => 2, 'ID_Tutor' => 30001, 'ID_Instructor' => 40002],
            ['id' => 3, 'ID_Tutor' => 30002, 'ID_Instructor' => 40002],
            ['id' => 4, 'ID_Tutor' => 30002, 'ID_Instructor' => 40001],
        ]);
    }
}
