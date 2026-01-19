<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompRaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comp_ra')->delete();

        $relaciones = [
            // --- DWEC (Cliente) ---
            // RA1 (Arquitectura/Herramientas) -> Frontend(1), Optimización(9)
            ['id_ra' => 1, 'id_comp' => 1, 'id_asignatura' => 1],
            ['id_ra' => 1, 'id_comp' => 9, 'id_asignatura' => 1],

            // RA2 (Eventos/DOM) -> Frontend(1), UI/UX(4), Testing(8)
            ['id_ra' => 2, 'id_comp' => 1, 'id_asignatura' => 1],
            ['id_ra' => 2, 'id_comp' => 4, 'id_asignatura' => 1],
            ['id_ra' => 2, 'id_comp' => 8, 'id_asignatura' => 1], // Testing en cliente

            // RA3 (Asincronía/AJAX) -> Frontend(1), APIs(7)
            ['id_ra' => 3, 'id_comp' => 1, 'id_asignatura' => 1],
            ['id_ra' => 3, 'id_comp' => 7, 'id_asignatura' => 1],

            // --- DWES (Servidor) ---
            // RA4 (Lógica PHP) -> Backend(2), Testing(8)
            ['id_ra' => 4, 'id_comp' => 2, 'id_asignatura' => 2],
            ['id_ra' => 4, 'id_comp' => 8, 'id_asignatura' => 2], // Unit Testing PHP

            // RA5 (BBDD) -> Backend(2), BBDD(3)
            ['id_ra' => 5, 'id_comp' => 2, 'id_asignatura' => 2],
            ['id_ra' => 5, 'id_comp' => 3, 'id_asignatura' => 2],

            // RA6 (APIs REST) -> APIs(7), Seguridad(6), Backend(2)
            ['id_ra' => 6, 'id_comp' => 7, 'id_asignatura' => 2],
            ['id_ra' => 6, 'id_comp' => 6, 'id_asignatura' => 2],
            ['id_ra' => 6, 'id_comp' => 2, 'id_asignatura' => 2],

            // --- DIW (Diseño) ---
            // RA7 (Usabilidad) -> UI/UX(4), Optimización(9)
            ['id_ra' => 7, 'id_comp' => 4, 'id_asignatura' => 3],
            ['id_ra' => 7, 'id_comp' => 9, 'id_asignatura' => 3], // Accesibilidad

            // RA8 (CSS/Responsive) -> UI/UX(4), Frontend(1)
            ['id_ra' => 8, 'id_comp' => 4, 'id_asignatura' => 3],
            ['id_ra' => 8, 'id_comp' => 1, 'id_asignatura' => 3],

            // RA9 (Multimedia) -> UI/UX(4), Optimización(9)
            ['id_ra' => 9, 'id_comp' => 4, 'id_asignatura' => 3],
            ['id_ra' => 9, 'id_comp' => 9, 'id_asignatura' => 3], // Optimización de carga

            // --- DAW (Despliegue) ---
            // RA10 (Servidores) -> Despliegue(5), Seguridad(6)
            ['id_ra' => 10, 'id_comp' => 5, 'id_asignatura' => 4],
            ['id_ra' => 10, 'id_comp' => 6, 'id_asignatura' => 4],

            // RA11 (Docker) -> Despliegue(5), Git(10)
            ['id_ra' => 11, 'id_comp' => 5, 'id_asignatura' => 4],
            ['id_ra' => 11, 'id_comp' => 10, 'id_asignatura' => 4], // CI/CD suele ir con Git

            // RA12 (Git/Docs) -> Git(10), Despliegue(5)
            ['id_ra' => 12, 'id_comp' => 10, 'id_asignatura' => 4],
            ['id_ra' => 12, 'id_comp' => 5, 'id_asignatura' => 4],
        ];

        DB::table('comp_ra')->insert($relaciones);
    }
}
