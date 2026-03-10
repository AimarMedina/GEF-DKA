<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Grado;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AssignmentImportController extends Controller
{
    /**
     * Import assignment CSV (semicolon-delimited) and create related entities.
     *
     * Expected CSV columns (case-insensitive):
     *  - alias_profesor: short alias for professor (used to generate an email)
     *  - nombre, apel1, apel2: professor name parts
     *  - grupo: code for the grado (used as unique key for Grado->Nombre)
     *  - des_asig: subject name (used to create Asignatura); if it contains 'tutor' the
     *    row is considered a tutoría and the tutor will be assigned to the Grado.
     *
     * Behaviour notes:
     *  - The CSV is semicolon-delimited.
     *  - For each row we create or reuse a `User` with tipo='tutor', a `Grado` and an
     *    `Asignatura`. Email is generated as LOWER(alias)@profesor.local to keep uniqueness.
     *  - Transactions are used per-row so one bad row doesn't abort the whole import.
     *  - Passwords for created users are set to 'changeme' (bcrypt). Change this after import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo abrir el archivo'], 400);
        }

        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'CSV vacío o con encabezado inválido'], 400);
        }

        $headers = array_map(function ($h) { return strtolower(trim($h)); }, $header);

        $expected = ['campus','grupo','modelo','regimen','des_grupo','des_asig','alias_profesor','nombre','apel1','apel2'];

        // allow flexibility: proceed if alias_profesor and nombre present
        if (!in_array('alias_profesor', $headers) || !in_array('nombre', $headers)) {
            return response()->json(['status' => 'error', 'message' => 'El CSV debe contener al menos las columnas alias_profesor y nombre'], 400);
        }

        $createdUsers = 0;
        $createdGrados = 0;
        $createdAsignaturas = 0;
        $updatedGrados = 0;

        $rowNumber = 2;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // normalize row to header length
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $data = array_combine($headers, array_map('trim', $row));

            // skip empty
            if (empty(array_filter($data))) { $rowNumber++; continue; }

            $alias = strtoupper($data['alias_profesor'] ?? '');
            $nombre = $data['nombre'] ?? '';
            $apel1 = $data['apel1'] ?? '';
            $apel2 = $data['apel2'] ?? '';
            $grupo = $data['grupo'] ?? '';
            $des_grupo = $data['des_grupo'] ?? $grupo;
            $des_asig = $data['des_asig'] ?? '';

            if (empty($alias) || empty($nombre)) { $rowNumber++; continue; }

            DB::beginTransaction();
            try {
                // Build email from alias to ensure uniqueness
                $email = strtolower($alias) . '@profesor.local';

                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::create([
                        'nombre' => $nombre,
                        'apellidos' => trim($apel1 . ' ' . $apel2),
                        'email' => $email,
                        'n_tel' => null,
                        'password' => bcrypt('changeme'),
                        'tipo' => 'tutor',
                    ]);
                    $createdUsers++;
                }

                // Grado: use grupo code as unique key
                $grado = Grado::firstOrCreate(
                    ['Nombre' => $grupo],
                    ['Curso' => null, 'ID_Tutor' => null]
                );
                if ($grado->wasRecentlyCreated) $createdGrados++;

                // Asignatura: by name + grado
                if (!empty($des_asig)) {
                    $asig = Asignatura::firstOrCreate([
                        'nombre' => $des_asig,
                        'ID_Grado' => $grado->id
                    ]);
                    if ($asig->wasRecentlyCreated) $createdAsignaturas++;
                }

                // If the subject indicates tutoría (contains 'tutor'), assign tutor to grado
                if (preg_match('/tutor/i', $des_asig)) {
                    if ($grado->ID_Tutor !== $user->id) {
                        $grado->ID_Tutor = $user->id;
                        $grado->save();
                        $updatedGrados++;
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // skip and continue
            }

            $rowNumber++;
        }

        fclose($handle);

        return response()->json([
            'status' => 'success',
            'created_users' => $createdUsers,
            'created_grados' => $createdGrados,
            'created_asignaturas' => $createdAsignaturas,
            'updated_grados' => $updatedGrados,
        ], 200);
    }
}
