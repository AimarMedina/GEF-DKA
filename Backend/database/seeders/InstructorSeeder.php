<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('instructor')->insert([
            ['id_usuario' => 40001, 'CIF_Empresa' => 'B12345678'], // Nerea
            ['id_usuario' => 40002, 'CIF_Empresa' => 'A87654321'], // Jon
        ]);
    }
}
