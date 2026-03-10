<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AlumnoImportController extends Controller
{
    /**
     * Descargar plantilla CSV para alumnos
     */
    public function downloadTemplate()
    {
        $fileName = 'plantilla_alumnos_' . now()->format('Y-m-d_His') . '.csv';

        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $data = fopen('php://memory', 'r+');

        $headers_row = ['nombre', 'apellidos', 'email', 'n_tel', 'password'];
        fputcsv($data, $headers_row, ',', '"');

        fputcsv($data, ['Juan', 'García López', 'juan.alumno@centro.local', '600123456', 'password123'], ',', '"');
        fputcsv($data, ['María', 'Rodríguez Pérez', 'maria.alumno@centro.local', '600234567', 'password456'], ',', '"');

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
     * Importar alumnos desde CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser CSV o TXT.',
            'file.max' => 'El archivo no puede exceder 5MB.',
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            $file_handle = fopen($filePath, 'r');
            $headers = fgetcsv($file_handle);

            if (!$headers) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El archivo CSV está vacío o mal formateado.'
                ], 400);
            }

            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            $required_fields = ['nombre', 'apellidos', 'email', 'n_tel', 'password'];
            $missing_fields = array_diff($required_fields, $headers);

            if (!empty($missing_fields)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)
                ], 400);
            }

            $created = 0;
            $failed = 0;
            $errors = [];
            $row_number = 2;

            while (($row = fgetcsv($file_handle)) !== false) {
                if (count($row) === 1 && $row[0] === '' || count($row) === 0) {
                    continue;
                }

                $row = array_map('trim', $row);

                // Verificar si la fila está completamente vacía después del trim
                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                } elseif (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }

                $row_data = array_combine($headers, $row);

                try {
                    // Trim values in row_data before validation
                    $row_data = array_map(function($value) {
                        return is_string($value) ? trim($value) : $value;
                    }, $row_data);

                    // Skip if any required field is empty
                    $required = ['nombre', 'apellidos', 'email', 'n_tel', 'password'];
                    if (empty(array_filter(array_intersect_key($row_data, array_flip($required))))) {
                        $row_number++;
                        continue;
                    }

                    $this->validateAlumnoRow($row_data, $row_number);

                    DB::transaction(function () use ($row_data) {
                        $user = User::create([
                            'nombre' => $row_data['nombre'],
                            'apellidos' => $row_data['apellidos'],
                            'email' => $row_data['email'],
                            'n_tel' => $row_data['n_tel'],
                            'password' => Hash::make($row_data['password']),
                            'tipo' => 'alumno',
                        ]);

                        Alumno::create(['ID_Usuario' => $user->id]);
                    });

                    $created++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Fila $row_number: " . $e->getMessage();
                }

                $row_number++;
            }

            fclose($file_handle);

            return response()->json([
                'status' => 'success',
                'message' => "Se han importado $created alumnos correctamente.",
                'data' => [
                    'created' => $created,
                    'failed' => $failed,
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
     * Validar fila de alumno
     */
    private function validateAlumnoRow(array $row, int $row_number)
    {
        $required = ['nombre', 'apellidos', 'email', 'n_tel', 'password'];

        foreach ($required as $field) {
            if (empty($row[$field] ?? null)) {
                throw new \Exception("El campo '$field' es obligatorio.");
            }
        }

        // Email único
        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("El email '{$row['email']}' ya está registrado.");
        }

        // Teléfono único
        if (User::where('n_tel', $row['n_tel'])->exists()) {
            throw new \Exception("El teléfono '{$row['n_tel']}' ya está registrado.");
        }

        // Email válido
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("El email '{$row['email']}' no tiene un formato válido.");
        }

        // Teléfono válido
        if (!preg_match('/^[0-9]{9}$/', $row['n_tel'])) {
            throw new \Exception("El teléfono '{$row['n_tel']}' debe tener exactamente 9 dígitos.");
        }
    }
}
