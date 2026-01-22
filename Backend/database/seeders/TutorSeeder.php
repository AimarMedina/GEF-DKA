<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor')->insert([
            ['id_usuario' => 30001],
            ['id_usuario' => 30002],
            ['id_usuario' => 30003],
            ['id_usuario' => 30004],
            ['id_usuario' => 30005],
            ['id_usuario' => 30006],
            ['id_usuario' => 30007],
            ['id_usuario' => 30008],
            ['id_usuario' => 30009],
            ['id_usuario' => 30010],
            ['id_usuario' => 30011],
        ]);
    }
}
