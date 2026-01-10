<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('competencia')->insert([
            ['id'=>1,'descripcion'=>'Trabajo en equipo'],
            ['id'=>2,'descripcion'=>'Comunicación efectiva'],
            ['id'=>3,'descripcion'=>'Resolución de problemas'],
        ]);
    }
}
