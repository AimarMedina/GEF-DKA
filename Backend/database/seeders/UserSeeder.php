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

        // TUTORES (para los 11 grados)
        User::create([
            'id' => 30001,
            'nombre' => 'Maite',
            'apellidos' => 'García Lasa',
            'email' => 'maite.tutor@centro.local',
            'n_tel' => '600222333',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30002,
            'nombre' => 'Iñigo',
            'apellidos' => 'Sáez Arriola',
            'email' => 'inigo.tutor@centro.local',
            'n_tel' => '600333444',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30003,
            'nombre' => 'Elena',
            'apellidos' => 'Martínez Ruiz',
            'email' => 'elena.tutor@centro.local',
            'n_tel' => '600444555',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30004,
            'nombre' => 'Javier',
            'apellidos' => 'González Pérez',
            'email' => 'javier.tutor@centro.local',
            'n_tel' => '600555666',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30005,
            'nombre' => 'Laura',
            'apellidos' => 'Fernández López',
            'email' => 'laura.tutor@centro.local',
            'n_tel' => '600666777',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30006,
            'nombre' => 'David',
            'apellidos' => 'Santos Moreno',
            'email' => 'david.tutor@centro.local',
            'n_tel' => '600777888',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30007,
            'nombre' => 'Ana',
            'apellidos' => 'Lopez Muñoz',
            'email' => 'ana.tutor@centro.local',
            'n_tel' => '600888999',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30008,
            'nombre' => 'Pedro',
            'apellidos' => 'Ramírez Díaz',
            'email' => 'pedro.tutor@centro.local',
            'n_tel' => '600999000',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30009,
            'nombre' => 'Lucía',
            'apellidos' => 'García Moreno',
            'email' => 'lucia.tutor@centro.local',
            'n_tel' => '601000111',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30010,
            'nombre' => 'Carlos',
            'apellidos' => 'Hernández Ruiz',
            'email' => 'carlos.tutor@centro.local',
            'n_tel' => '601111222',
            'password' => bcrypt('tutor'),
            'tipo' => 'tutor',
        ]);
        User::create([
            'id' => 30011,
            'nombre' => 'Marta',
            'apellidos' => 'Sánchez Pérez',
            'email' => 'marta.tutor@centro.local',
            'n_tel' => '601222333',
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
        // MÁS ALUMNOS
        User::create([
            'id' => 20006,
            'nombre' => 'Aitor',
            'apellidos' => 'Etxeberria Salinas',
            'email' => 'aitor.alumno@centro.local',
            'n_tel' => '620666777',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'id' => 20007,
            'nombre' => 'June',
            'apellidos' => 'Larrinaga Múgica',
            'email' => 'june.alumno@centro.local',
            'n_tel' => '620777888',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'id' => 20008,
            'nombre' => 'Iker',
            'apellidos' => 'Goikoetxea Aramburu',
            'email' => 'iker.alumno@centro.local',
            'n_tel' => '620888999',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'id' => 20009,
            'nombre' => 'Nahia',
            'apellidos' => 'Uribe Lete',
            'email' => 'nahia.alumno@centro.local',
            'n_tel' => '620999000',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);

        User::create([
            'id' => 20010,
            'nombre' => 'Asier',
            'apellidos' => 'Mendia Otaola',
            'email' => 'asier.alumno@centro.local',
            'n_tel' => '621000111',
            'password' => bcrypt('alumno'),
            'tipo' => 'alumno',
        ]);
    }
}
