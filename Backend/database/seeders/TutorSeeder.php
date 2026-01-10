<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor')->insert([
            ['id_usuario' => 30001], // Maite
            ['id_usuario' => 30002], // Iñigo
        ]);
    }
}
