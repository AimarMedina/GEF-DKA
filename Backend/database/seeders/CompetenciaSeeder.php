<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('competencia')->delete();

        $competencias = [
            // BÁSICAS / FUNDAMENTALES
            ['id' => 1, 'descripcion' => 'Desarrollo Frontend y SPA'],
            ['id' => 2, 'descripcion' => 'Lógica de Servidor (Backend)'],
            ['id' => 3, 'descripcion' => 'Gestión de Bases de Datos'],

            // DISEÑO Y UX
            ['id' => 4, 'descripcion' => 'Diseño de Interfaces y Usabilidad (UI/UX)'],

            // INFRAESTRUCTURA
            ['id' => 5, 'descripcion' => 'Despliegue, Servidores y Contenedores'],
            ['id' => 6, 'descripcion' => 'Seguridad y Criptografía'],

            // INTEGRACIÓN
            ['id' => 7, 'descripcion' => 'Desarrollo de APIs y Servicios Web'],

            // CALIDAD Y RENDIMIENTO (NUEVAS)
            ['id' => 8, 'descripcion' => 'Pruebas de Software (Testing y QA)'],
            ['id' => 9, 'descripcion' => 'Optimización, SEO y Accesibilidad'],

            // GESTIÓN TÉCNICA (NUEVA)
            ['id' => 10, 'descripcion' => 'Control de Versiones (Git) e Integración'],
        ];

        DB::table('competencia')->insert($competencias);
    }
}
