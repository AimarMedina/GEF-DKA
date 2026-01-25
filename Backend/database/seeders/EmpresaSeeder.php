<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('empresa')->insert([
            // 1
            [
                'CIF' => 'B12345678',
                'Nombre' => 'TechBizi SL',
                'Direccion' => 'C/ Gran Vía 12, Bilbao',
                'Email' => 'rrhh@techbizi.local',
                'N_Tel' => '944000111',
            ],
            // 2
            [
                'CIF' => 'A87654321',
                'Nombre' => 'IndusGoi SA',
                'Direccion' => 'Pol. Ugaldeguren I, Nave 7',
                'Email' => 'contacto@indusgoi.local',
                'N_Tel' => '944000222',
            ],
            // 3
            [
                'CIF' => 'B01234567',
                'Nombre' => 'SoftAraba Solutions',
                'Direccion' => 'Parque Tecnológico de Álava, E8',
                'Email' => 'info@softaraba.local',
                'N_Tel' => '945000333',
            ],
            // 4
            [
                'CIF' => 'A98765432',
                'Nombre' => 'Mecanizados Vitoria',
                'Direccion' => 'Pol. Jundiz, C/ Paduleta 55',
                'Email' => 'admin@mecanizadosvitoria.local',
                'N_Tel' => '945111444',
            ],
            // 5
            [
                'CIF' => 'B11223344',
                'Nombre' => 'WebGuneak Digital',
                'Direccion' => 'Av. Gasteiz 45, Vitoria-Gasteiz',
                'Email' => 'hola@webguneak.local',
                'N_Tel' => '945222555',
            ],
            // 6
            [
                'CIF' => 'A55667788',
                'Nombre' => 'Logística Euskadi',
                'Direccion' => 'CTV, Nave 12, Vitoria-Gasteiz',
                'Email' => 'transporte@logisticaeuskadi.local',
                'N_Tel' => '945333666',
            ],
            // 7
            [
                'CIF' => 'B99887766',
                'Nombre' => 'Consultoría IT Norte',
                'Direccion' => 'C/ Dato 15, Vitoria-Gasteiz',
                'Email' => 'soporte@itnorte.local',
                'N_Tel' => '945444777',
            ],
            // 8
            [
                'CIF' => 'A44332211',
                'Nombre' => 'Energías Renovables Zadorra',
                'Direccion' => 'Pol. Betoño, C/ Larragana 4',
                'Email' => 'green@zadorraenergy.local',
                'N_Tel' => '945555888',
            ],
            // 9
            [
                'CIF' => 'B77889900',
                'Nombre' => 'CyberSecurity Alava',
                'Direccion' => 'C/ San Prudencio 8, Vitoria-Gasteiz',
                'Email' => 'security@csalava.local',
                'N_Tel' => '945666999',
            ],
            // 10
            [
                'CIF' => 'A12312312',
                'Nombre' => 'Automoción Gasteiz',
                'Direccion' => 'Pol. Ali-Gobeo, C/ Alibarra 20',
                'Email' => 'rrhh@autogasteiz.local',
                'N_Tel' => '945777000',
            ],
            // 11
            [
                'CIF' => 'B45645645',
                'Nombre' => 'Diseño Gráfico Armentia',
                'Direccion' => 'Paseo de la Zumaquera 30',
                'Email' => 'studio@armentiadesign.local',
                'N_Tel' => '945888111',
            ],
        ]);
    }
}