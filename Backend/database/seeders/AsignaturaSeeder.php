<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('asignatura')->insert([
            ['id' => 1, 'Nombre' => 'Despliegue de Aplicaciones Web', 'ID_Grado' => 1],
            ['id' => 2, 'Nombre' => 'Desarrollo Web en Entorno Servidor', 'ID_Grado' => 1],
            ['id' => 3, 'Nombre' => 'Administración de Sistemas Operativos', 'ID_Grado' => 2],
            ['id' => 4, 'Nombre' => 'Seguridad y Alta Disponibilidad', 'ID_Grado' => 2],
        ]);
    }
}
