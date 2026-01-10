<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('horario')->insert([
            ['id'=>1,'id_estancia'=>1,'dias'=>'L-M-X-J-V','horario1'=>'08:00-12:00','horario2'=>'13:00-15:00'],
            ['id'=>2,'id_estancia'=>2,'dias'=>'L-M-X-J-V','horario1'=>'09:00-13:00','horario2'=>'14:00-16:00'],
            ['id'=>3,'id_estancia'=>3,'dias'=>'L-M-X-J','horario1'=>'08:30-12:30','horario2'=>'13:30-15:30'],
            ['id'=>4,'id_estancia'=>4,'dias'=>'L-M-X-J-V','horario1'=>'07:00-11:00','horario2'=>'12:00-14:00'],
            ['id'=>5,'id_estancia'=>5,'dias'=>'L-M-X-J-V','horario1'=>'08:00-12:00','horario2'=>'13:00-15:00'],
        ]);
    }
}
