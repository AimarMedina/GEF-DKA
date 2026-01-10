<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompRaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comp_ra')->insert([
            ['id'=>1,'id_comp'=>1,'id_ra'=>2,'id_asignatura'=>1],
            ['id'=>2,'id_comp'=>2,'id_ra'=>1,'id_asignatura'=>1],
            ['id'=>3,'id_comp'=>3,'id_ra'=>3,'id_asignatura'=>2],
            ['id'=>4,'id_comp'=>3,'id_ra'=>2,'id_asignatura'=>2],
        ]);
    }
}
