<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla antes de insertar
        DB::table('competencia')->delete();

        $competencias = [
            // =======================================================
            // GRADO 1: Desarrollo de Aplicaciones Web (DAW)
            // =======================================================
            ['id' => 1, 'descripcion' => 'Desarrollo Frontend y SPA', 'ID_Grado' => 1],
            ['id' => 2, 'descripcion' => 'Lógica de Servidor (Backend)', 'ID_Grado' => 1],
            ['id' => 3, 'descripcion' => 'Gestión de Bases de Datos', 'ID_Grado' => 1],
            ['id' => 4, 'descripcion' => 'Diseño de Interfaces y Usabilidad (UI/UX)', 'ID_Grado' => 1],
            ['id' => 5, 'descripcion' => 'Despliegue, Servidores y Contenedores', 'ID_Grado' => 1],
            ['id' => 6, 'descripcion' => 'Desarrollo de APIs y Servicios Web', 'ID_Grado' => 1],
            ['id' => 7, 'descripcion' => 'Pruebas de Software (Testing y QA)', 'ID_Grado' => 1],

            // =======================================================
            // GRADO 2: Administración de Sistemas (ASIR)
            // =======================================================
            ['id' => 8, 'descripcion' => 'Administración de Sistemas Operativos', 'ID_Grado' => 2],
            ['id' => 9, 'descripcion' => 'Planificación y Administración de Redes', 'ID_Grado' => 2],
            ['id' => 10, 'descripcion' => 'Seguridad Informática y Alta Disponibilidad', 'ID_Grado' => 2],
            ['id' => 11, 'descripcion' => 'Servicios de Red e Internet', 'ID_Grado' => 2],
            ['id' => 12, 'descripcion' => 'Implantación de Aplicaciones Web', 'ID_Grado' => 2],
            ['id' => 13, 'descripcion' => 'Administración de Bases de Datos', 'ID_Grado' => 2],

            // =======================================================
            // GRADO 3: Desarrollo Multiplataforma (DAM)
            // =======================================================
            ['id' => 14, 'descripcion' => 'Desarrollo de Aplicaciones Móviles (Android/iOS)', 'ID_Grado' => 3],
            ['id' => 15, 'descripcion' => 'Programación Multimedia y Dispositivos', 'ID_Grado' => 3],
            ['id' => 16, 'descripcion' => 'Sistemas de Gestión Empresarial (ERP/CRM)', 'ID_Grado' => 3],
            ['id' => 17, 'descripcion' => 'Desarrollo de Interfaces Gráficas', 'ID_Grado' => 3],

            // =======================================================
            // GRADO 5: Mecatrónica Industrial
            // =======================================================
            ['id' => 18, 'descripcion' => 'Sistemas Mecánicos, Neumáticos e Hidráulicos', 'ID_Grado' => 5],
            ['id' => 19, 'descripcion' => 'Sistemas y Circuitos Eléctricos', 'ID_Grado' => 5],
            ['id' => 20, 'descripcion' => 'Programación de Sistemas Secuenciales (PLCs)', 'ID_Grado' => 5],
            ['id' => 21, 'descripcion' => 'Robótica Industrial', 'ID_Grado' => 5],

            // =======================================================
            // GRADO 9: Administración y Finanzas
            // =======================================================
            ['id' => 22, 'descripcion' => 'Gestión de la Documentación Jurídica', 'ID_Grado' => 9],
            ['id' => 23, 'descripcion' => 'Contabilidad y Fiscalidad', 'ID_Grado' => 9],
            ['id' => 24, 'descripcion' => 'Gestión de Recursos Humanos', 'ID_Grado' => 9],
        ];

        DB::table('competencia')->insert($competencias);
    }
}