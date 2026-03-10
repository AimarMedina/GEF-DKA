<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Grado;
use App\Models\Asignatura;

class AssignmentImporterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Place your CSV at storage/app/imports/Asignacion_2026-02-04_estructura.csv
     *
     * Notes:
     *  - CSV must be semicolon-delimited and include at least 'alias_profesor' and 'nombre'.
     *  - This seeder is idempotent (uses firstOrCreate) so it is safe to re-run.
     *  - Created users have password 'changeme' (bcrypt). Update passwords afterwards.
     */
    public function run()
    {
        $relPath = storage_path('app/imports/Asignacion_2026-02-04_estructura.csv');
        if (!file_exists($relPath)) {
            $this->command->info("CSV not found at: $relPath. Skipping Assignment import.");
            return;
        }

        $handle = fopen($relPath, 'r');
        if (!$handle) {
            $this->command->error('Unable to open CSV file.');
            return;
        }

        $header = fgetcsv($handle, 0, ';');
        $headers = array_map(function ($h) { return strtolower(trim($h)); }, $header);

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < count($headers)) $row = array_pad($row, count($headers), '');
            $data = array_combine($headers, array_map('trim', $row));
            if (empty(array_filter($data))) continue;

            $alias = strtoupper($data['alias_profesor'] ?? '');
            $nombre = $data['nombre'] ?? '';
            $apel1 = $data['apel1'] ?? '';
            $apel2 = $data['apel2'] ?? '';
            $grupo = $data['grupo'] ?? '';
            $des_asig = $data['des_asig'] ?? '';

            if (empty($alias) || empty($nombre)) continue;

            DB::transaction(function() use ($alias, $nombre, $apel1, $apel2, $grupo, $des_asig) {
                $email = strtolower($alias) . '@profesor.local';
                $user = User::firstOrCreate(['email' => $email], [
                    'nombre' => $nombre,
                    'apellidos' => trim($apel1 . ' ' . $apel2),
                    'n_tel' => null,
                    'password' => bcrypt('changeme'),
                    'tipo' => 'tutor',
                ]);

                $grado = Grado::firstOrCreate(['Nombre' => $grupo], ['Curso' => null, 'ID_Tutor' => null]);
                Asignatura::firstOrCreate(['nombre' => $des_asig, 'ID_Grado' => $grado->id]);

                if (preg_match('/tutor/i', $des_asig)) {
                    $grado->ID_Tutor = $user->id;
                    $grado->save();
                }
            });
        }

        fclose($handle);
        $this->command->info('Assignment import finished.');
    }
}
