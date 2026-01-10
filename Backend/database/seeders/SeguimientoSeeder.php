<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeguimientoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('seguimiento')->insert([
            ['id'=>1,'id_estancia'=>1,'fecha'=>'2026-01-07','hora'=>'10:15:00','accion_seguimiento'=>'Revisión de tareas de la semana','seguimiento_actividad'=>'Implementación de endpoints REST y validaciones'],
            ['id'=>2,'id_estancia'=>1,'fecha'=>'2026-01-09','hora'=>'12:05:00','accion_seguimiento'=>'Feedback sobre documentación','seguimiento_actividad'=>'Mejora de README y guía de despliegue'],
            ['id'=>3,'id_estancia'=>2,'fecha'=>'2026-01-08','hora'=>'11:00:00','accion_seguimiento'=>'Control de progreso','seguimiento_actividad'=>'Migraciones y seed de datos'],
            ['id'=>4,'id_estancia'=>3,'fecha'=>'2026-01-08','hora'=>'09:30:00','accion_seguimiento'=>'Incidencia con acceso a repo','seguimiento_actividad'=>'Se corrige permisos y se configura SSH'],
            ['id'=>5,'id_estancia'=>4,'fecha'=>'2026-01-09','hora'=>'08:45:00','accion_seguimiento'=>'Prueba de backup','seguimiento_actividad'=>'Verificación de snapshots y restauración parcial'],
            ['id'=>6,'id_estancia'=>5,'fecha'=>'2026-01-10','hora'=>'13:10:00','accion_seguimiento'=>'Planificación semana siguiente','seguimiento_actividad'=>'Refactor de servicios y tests unitarios'],
        ]);
    }
}
