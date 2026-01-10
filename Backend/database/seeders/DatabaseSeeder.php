<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            EmpresaSeeder::class,
            TutorSeeder::class,
            InstructorSeeder::class,
            GradoSeeder::class,
            AlumnoSeeder::class,
            TutorInstructorSeeder::class,
            TutorGradoSeeder::class,
            AsignaturaSeeder::class,
            EntregaCuadernoSeeder::class,
            AlumnoEntregaSeeder::class,
            NotasCuadernoSeeder::class,
            CompetenciaSeeder::class,
            RaSeeder::class,
            CompRaSeeder::class,
            NotaCompetenciaSeeder::class,
            NotaEgibideSeeder::class,
            TransversalesSeeder::class,
            NotaTransversalSeeder::class,
            EstanciaAlumnoSeeder::class,
            HorarioSeeder::class,
            SeguimientoSeeder::class,
        ]);

    }
}
