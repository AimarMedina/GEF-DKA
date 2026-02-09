<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;

class GradoImportController extends Controller
{
    /**
     * Descargar plantilla CSV para grados
     */
    public function downloadTemplate()
    {
        $fileName = 'plantilla_cursos_' . now()->format('Y-m-d_His') . '.csv';

        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $data = fopen('php://memory', 'r+');

        $headers_row = ['nombre', 'curso'];
        fputcsv($data, $headers_row, ',', '"');

        fputcsv($data, ['Desarrollo de Aplicaciones Multiplataforma (DAM)', '1º DAM'], ',', '"');
        fputcsv($data, ['Desarrollo de Aplicaciones Web (DAW)', '1º DAW'], ',', '"');
        fputcsv($data, ['Administración de Sistemas Informáticos (ASIR)', '2º ASIR'], ',', '"');

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
     * Importar grados desde CSV
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

            $required_fields = ['nombre', 'curso'];
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

                    // Skip if any required field is empty after trimming
                    if (empty($row_data['nombre'] ?? null) || empty($row_data['curso'] ?? null)) {
                        $row_number++;
                        continue;
                    }

                    $this->validateGradoRow($row_data, $row_number);

                    Grado::create([
                        'nombre' => $row_data['nombre'],
                        'curso' => $row_data['curso'],
                    ]);

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
                'message' => "Se han importado $created cursos correctamente.",
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
     * Validar fila de grado
     */
    private function validateGradoRow(array $row, int $row_number)
    {
        $required = ['nombre', 'curso'];

        foreach ($required as $field) {
            if (empty($row[$field] ?? null)) {
                throw new \Exception("El campo '$field' es obligatorio.");
            }
        }

        if (strlen($row['nombre']) > 150) {
            throw new \Exception("El nombre no puede superar 150 caracteres.");
        }

        if (strlen($row['curso']) > 50) {
            throw new \Exception("El curso no puede superar 50 caracteres.");
        }
    }
}
