<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Alumno;
use App\Models\Tutor;
use App\Models\Instructor;
use App\Models\UserImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserImportController extends Controller
{
    /**
     * Le da nombre al csv con plantillas_uusarios y la fecha + .csv
     */
    public function downloadTemplate()
    {
        $fileName = 'plantilla_usuarios_' . now()->format('Y-m-d_His') . '.csv';

        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $data = fopen('php://memory', 'r+');

        // cabeceros del csv
        $headers_row = [
            'nombre',
            'apellidos',
            'email',
            'n_tel',
            'password',
            'tipo'
        ];

        fputcsv($data, $headers_row, ',', '"');

        // filas de ejemplo para escribir
        fputcsv($data, [
            'Juan',
            'García López',
            'juan.garcia@centro.local',
            '600123456',
            'password123',
            'alumno'
        ], ',', '"');

        rewind($data);

        return response()->stream(
            function () use ($data) {
                fpassthru($data);
            },
            200,
            $headers
        );
    }

    /**
     * Importar usuarios desde CSV
     */
    public function import(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // máx 10MB
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser CSV o TXT.',
            'file.max' => 'El archivo no puede exceder 10MB.',
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            // Obre el contenido del archivo CSV en modo read
            $file_handle = fopen($filePath, 'r');
            $headers = fgetcsv($file_handle);

            if (!$headers) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El archivo CSV está vacío o mal formateado.'
                ], 400);
            }

            // Limpiar headers (trim de espacios y convertir a minúsculas) para insert
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            // Validar que tenga los campos necesarios
            $required_fields = ['nombre', 'apellidos', 'email', 'n_tel', 'password', 'tipo'];
            $missing_fields = array_diff($required_fields, $headers);

            if (!empty($missing_fields)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)
                ], 400);
            }

            $users_created = 0;
            $users_failed = 0;
            $errors = [];

            // Procesar cada fila
            $row_number = 2; // Primera fila de datos (después del header)

            while (($row = fgetcsv($file_handle)) !== false) {
                if (count($row) === 1 && $row[0] === '' || count($row) === 0) {
                    continue;  //esto sirve para filas vaicas, para saltarlas
                }

                // Limpiar espacios en blanco de cada celda
                $row = array_map('trim', $row);

                // Alinear número de elementos entre headers y row
                // Si la fila tiene menos columnas, rellenar con valores vacíos
                // Si tiene más, truncar
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                } elseif (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }

                $row_data = array_combine($headers, $row);

                try {
                    // Validar datos
                    $this->validateUserRow($row_data, $row_number);

                    // Crear usuario
                    $this->createUserFromRow($row_data);
                    $users_created++;

                } catch (\Exception $e) {
                    $users_failed++;
                    $errors[] = "Fila $row_number: " . $e->getMessage();
                }

                $row_number++;
            }

            fclose($file_handle);

            // Registrar la importación
            UserImport::create([
                'user_id' => $request->user()->id,
                'total_users' => $row_number - 2, // Restar 2: la fila del header y el contador que empieza en 2
                'successful_users' => $users_created,
                'failed_users' => $users_failed,
                'errors' => !empty($errors) ? $errors : null,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Se han importado $users_created usuarios correctamente.",
                'data' => [
                    'created' => $users_created,
                    'failed' => $users_failed,
                    'errors' => $errors
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar fila del CSV
     */
    private function validateUserRow(array $row)
    {
        // Campos requeridos
        $required = ['nombre', 'apellidos', 'email', 'n_tel', 'password', 'tipo'];

        foreach ($required as $field) {
            if (empty($row[$field] ?? null)) {
                throw new \Exception("El campo '$field' es obligatorio.");
            }
        }

        // Validar email único
        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("El email '{$row['email']}' ya está registrado.");
        }

        // Validar n_tel único
        if (User::where('n_tel', $row['n_tel'])->exists()) {
            throw new \Exception("El teléfono '{$row['n_tel']}' ya está registrado.");
        }

        // Validar formato email
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("El email '{$row['email']}' no tiene un formato válido.");
        }

        // Validar teléfono (9 dígitos)
        if (!preg_match('/^[0-9]{9}$/', $row['n_tel'])) {
            throw new \Exception("El teléfono '{$row['n_tel']}' debe tener exactamente 9 dígitos.");
        }

        // Validar tipo
        $tipos_validos = ['admin', 'alumno', 'tutor', 'instructor'];
        if (!in_array($row['tipo'], $tipos_validos)) {
            throw new \Exception("El tipo '{$row['tipo']}' no es válido. Tipos permitidos: " . implode(', ', $tipos_validos));
        }

        // Validar longitud de nombre y apellido
        if (strlen($row['nombre']) > 255) {
            throw new \Exception("El nombre no puede superar 255 caracteres.");
        }

        if (strlen($row['apellidos']) > 255) {
            throw new \Exception("Los apellidos no pueden superar 255 caracteres.");
        }
    }

    /**
     * Crear usuario desde fila del CSV
     */
    private function createUserFromRow(array $row): User
    {
        return DB::transaction(function () use ($row) {
            // Crear usuario
            $user = User::create([
                'nombre' => trim($row['nombre']),
                'apellidos' => trim($row['apellidos']),
                'email' => trim($row['email']),
                'n_tel' => trim($row['n_tel']),
                'password' => Hash::make($row['password']),
                'tipo' => trim($row['tipo']),
            ]);

            // Crear registros relacionados según el tipo
            match ($user->tipo) {
                'alumno' => Alumno::create(['ID_Usuario' => $user->id]),
                'tutor' => Tutor::create(['ID_Usuario' => $user->id]),
                'instructor' => Instructor::create(['ID_Usuario' => $user->id]),
                default => null,
            };

            return $user;
        });
    }
}
