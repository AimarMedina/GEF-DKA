<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::create([
            'nombre' => 'Admin',
            'apellidos' => 'admin',
            'email' => 'admin@test.com',
            'n_tel' => '600111222',
            'password' => bcrypt('admin'),
            'tipo' => 'admin',
        ]);

        // TUTORES
        User::create([
            'nombre' => 'Maite',
            'apellidos' => 'García Lasa',
            'email' => 'maite.tutor@centro.local',
            'n_tel' => '600222333',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);

        User::create([
            'nombre' => 'Iñigo',
            'apellidos' => 'Sáez Arriola',
            'email' => 'inigo.tutor@centro.local',
            'n_tel' => '600333444',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);

        // INSTRUCTORES
        User::create([
            'nombre' => 'Nerea',
            'apellidos' => 'López Mendieta',
            'email' => 'nerea.instructor@empresa.local',
            'n_tel' => '610111222',
            'password' => bcrypt('instructor'),
            'tipo' => 'instructor',
        ]);

        User::create([
            'nombre' => 'Jon',
            'apellidos' => 'Martín Urrutia',
            'email' => 'jon.instructor@empresa.local',
            'n_tel' => '610222333',
            'password' => bcrypt('instructor'),
            'tipo' => 'instructor',
        ]);

        // ALUMNOS
        User::create([
            'nombre' => 'Ane',
            'apellidos' => 'Zubizarreta Ochoa',
            'email' => 'ane.alumno@centro.local',
            'n_tel' => '620111222',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'nombre' => 'Unai',
            'apellidos' => 'Alonso Ibarrola',
            'email' => 'unai.alumno@centro.local',
            'n_tel' => '620222333',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'nombre' => 'Irati',
            'apellidos' => 'Serrano Garmendia',
            'email' => 'irati.alumno@centro.local',
            'n_tel' => '620333444',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'nombre' => 'Mikel',
            'apellidos' => 'Ruiz Echevarría',
            'email' => 'mikel.alumno@centro.local',
            'n_tel' => '620444555',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'nombre' => 'Leire',
            'apellidos' => 'Navarro Etxaniz',
            'email' => 'leire.alumno@centro.local',
            'n_tel' => '620555666',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);
    }
}
