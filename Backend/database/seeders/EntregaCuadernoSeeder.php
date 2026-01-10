<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntregaCuadernoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('entrega_cuaderno')->insert([
            ['id'=>1,'fecha_creacion'=>'2026-01-02','fecha_limite'=>'2026-01-09','id_grado'=>1],
            ['id'=>2,'fecha_creacion'=>'2026-01-02','fecha_limite'=>'2026-01-09','id_grado'=>2],
            ['id'=>3,'fecha_creacion'=>'2026-01-06','fecha_limite'=>'2026-01-13','id_grado'=>1],
        ]);
    }
}
