<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ra')->delete();

        $ras = [
            // --- ASIGNATURA 1: DWEC (Desarrollo Cliente) ---
            ['id' => 1, 'ID_Asignatura' => 1, 'Descripcion' => 'RA1. Selecciona arquitecturas y herramientas de desarrollo adaptadas al navegador.'],
            ['id' => 2, 'ID_Asignatura' => 1, 'Descripcion' => 'RA2. Maneja la sintaxis del lenguaje (JS/TS) y estructuras de control básicas.'],
            ['id' => 3, 'ID_Asignatura' => 1, 'Descripcion' => 'RA3. Gestiona eventos y manipula el DOM para crear interfaces dinámicas.'],
            ['id' => 4, 'ID_Asignatura' => 1, 'Descripcion' => 'RA4. Valida formularios en cliente asegurando la integridad de los datos.'],
            ['id' => 5, 'ID_Asignatura' => 1, 'Descripcion' => 'RA5. Utiliza mecanismos de comunicación asíncrona (AJAX/Fetch) con el servidor.'],
            ['id' => 6, 'ID_Asignatura' => 1, 'Descripcion' => 'RA6. Desarrolla aplicaciones SPA utilizando Frameworks modernos (Vue/React).'],
            ['id' => 7, 'ID_Asignatura' => 1, 'Descripcion' => 'RA7. Gestiona el almacenamiento local y optimiza el rendimiento en cliente.'],

            // --- ASIGNATURA 2: DWES (Desarrollo Servidor) ---
            ['id' => 8, 'ID_Asignatura' => 2, 'Descripcion' => 'RA1. Prepara el entorno de desarrollo y la configuración del servidor web.'],
            ['id' => 9, 'ID_Asignatura' => 2, 'Descripcion' => 'RA2. Integra código de servidor (PHP/Laravel) generando contenido dinámico.'],
            ['id' => 10, 'ID_Asignatura' => 2, 'Descripcion' => 'RA3. Gestiona sesiones, autenticación de usuarios y control de acceso.'],
            ['id' => 11, 'ID_Asignatura' => 2, 'Descripcion' => 'RA4. Implementa el acceso a bases de datos utilizando ORMs y sentencias SQL.'],
            ['id' => 12, 'ID_Asignatura' => 2, 'Descripcion' => 'RA5. Desarrolla servicios web (APIs REST) para la interoperabilidad.'],
            ['id' => 13, 'ID_Asignatura' => 2, 'Descripcion' => 'RA6. Aplica técnicas de seguridad (Inyección SQL, XSS, CSRF) en el servidor.'],
            ['id' => 14, 'ID_Asignatura' => 2, 'Descripcion' => 'RA7. Genera aplicaciones híbridas y utiliza librerías de componentes externos.'],

            // --- ASIGNATURA 3: DIW (Diseño de Interfaces) ---
            ['id' => 15, 'ID_Asignatura' => 3, 'Descripcion' => 'RA1. Planifica la interfaz gráfica valorando usabilidad y experiencia de usuario (UX).'],
            ['id' => 16, 'ID_Asignatura' => 3, 'Descripcion' => 'RA2. Crea maquetaciones web avanzadas con CSS (Flexbox, Grid) y preprocesadores.'],
            ['id' => 17, 'ID_Asignatura' => 3, 'Descripcion' => 'RA3. Implementa diseños adaptativos (Responsive Design) para múltiples dispositivos.'],
            ['id' => 18, 'ID_Asignatura' => 3, 'Descripcion' => 'RA4. Integra y optimiza contenido multimedia (imágenes, vídeo, audio, canvas).'],
            ['id' => 19, 'ID_Asignatura' => 3, 'Descripcion' => 'RA5. Desarrolla interfaces accesibles cumpliendo los estándares WAI/WCAG.'],
            ['id' => 20, 'ID_Asignatura' => 3, 'Descripcion' => 'RA6. Diseña menús, formularios y elementos interactivos amigables.'],
            ['id' => 21, 'ID_Asignatura' => 3, 'Descripcion' => 'RA7. Utiliza frameworks CSS (Bootstrap/Tailwind) para agilizar el desarrollo.'],

            // --- ASIGNATURA 4: DAW (Despliegue) ---
            ['id' => 22, 'ID_Asignatura' => 4, 'Descripcion' => 'RA1. Administra servidores web (Apache/Nginx) y configura DNS/Red.'],
            ['id' => 23, 'ID_Asignatura' => 4, 'Descripcion' => 'RA2. Implanta aplicaciones web con seguridad (HTTPS, Certificados).'],
            ['id' => 24, 'ID_Asignatura' => 4, 'Descripcion' => 'RA3. Despliega aplicaciones en contenedores (Docker) y orquestadores.'],
            ['id' => 25, 'ID_Asignatura' => 4, 'Descripcion' => 'RA4. Utiliza sistemas de control de versiones (Git) y flujos de trabajo (GitFlow).'],
            ['id' => 26, 'ID_Asignatura' => 4, 'Descripcion' => 'RA5. Configura procesos de Integración y Despliegue Continuo (CI/CD).'],
            ['id' => 27, 'ID_Asignatura' => 4, 'Descripcion' => 'RA6. Monitoriza el rendimiento y logs de los servicios desplegados.'],
            ['id' => 28, 'ID_Asignatura' => 4, 'Descripcion' => 'RA7. Elabora documentación técnica y manuales de despliegue.'],
        ];

        DB::table('ra')->insert($ras);
    }
}
