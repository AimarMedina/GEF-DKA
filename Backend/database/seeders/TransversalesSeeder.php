<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransversalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transversales')->insert([
            ['id'=>1,'descripcion'=>'Puntualidad'],
            ['id'=>2,'descripcion'=>'Actitud'],
            ['id'=>3,'descripcion'=>'Organización'],
        ]);
    }
}
