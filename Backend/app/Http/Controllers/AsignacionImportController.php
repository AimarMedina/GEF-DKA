<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Tutor;
use App\Models\Grado;
use App\Models\Asignatura;
use App\Models\TutorAsignatura;
use App\Models\User;

class AsignacionImportController extends Controller {
    /**
     * Importar asignaciones desde CSV
     */
    public function importar(Request $request) {
        set_time_limit(300);

        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'errores' => $validator->errors()->all()
            ], 422);
        }

        try {
            $archivo = $request->file('archivo');
            $opciones = json_decode($request->input('opciones', '{}'), true);

            // Leer CSV
            $datos = $this->leerCSV($archivo->getPathname());

            $resultado = $this->procesarDatos($datos, $opciones);
            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'importados' => 0,
                'errores' => [['mensaje' => $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Leer archivo CSV
     */
    private function leerCSV($rutaArchivo) {
        $datos = [];
        $handle = fopen($rutaArchivo, 'r');

        if ($handle === false) {
            throw new \Exception('No se pudo abrir el archivo CSV');
        }

        // Leer encabezados
        $headers = fgetcsv($handle, 0, ';');

        if ($headers === false) {
            throw new \Exception('El archivo CSV está vacío');
        }

        // Convertir de latin-1 a utf-8
        $headers = array_map(function ($header) {
            return mb_convert_encoding($header, 'UTF-8', 'ISO-8859-1');
        }, $headers);

        // Leer filas
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Convertir encoding
            $row = array_map(function ($field) {
                return mb_convert_encoding($field, 'UTF-8', 'ISO-8859-1');
            }, $row);

            $datos[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $datos;
    }

    /**
     * Procesar datos del CSV
     */
    private function procesarDatos(array $datos, array $opciones) {
        // Pre-hashear contraseña una sola vez
        $contraseña = $opciones['contraseñaDefecto'] ?? 'Egibide2025';
        $hashUnico = Hash::make($contraseña);

        $errores = [];
        $importados = 0;
        $estadisticas = [
            'tutores_creados' => 0,
            'grados_creados' => 0,
            'asignaturas_creadas' => 0,
            'asignaciones_creadas' => 0
        ];

        // Procesar por lotes
        $lotes = array_chunk($datos, 50);

        foreach ($lotes as $indiceLote => $lote) {
            DB::beginTransaction();

            try {
                foreach ($lote as $index => $fila) {
                    try {
                        $resultado = $this->procesarAsignacion($fila, $hashUnico, $opciones);

                        $estadisticas['tutores_creados'] += $resultado['tutor_nuevo'] ? 1 : 0;
                        $estadisticas['grados_creados'] += $resultado['grado_nuevo'] ? 1 : 0;
                        $estadisticas['asignaturas_creadas'] += $resultado['asignatura_nueva'] ? 1 : 0;
                        $estadisticas['asignaciones_creadas'] += $resultado['asignacion_nueva'] ? 1 : 0;

                        $importados++;
                    } catch (\Exception $e) {
                        $errores[] = [
                            'fila' => ($indiceLote * 50) + $index + 2,
                            'profesor' => $fila['Alias_Profesor'] ?? 'desconocido',
                            'mensaje' => $e->getMessage()
                        ];
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errores[] = [
                    'mensaje' => 'Error en lote ' . ($indiceLote + 1) . ': ' . $e->getMessage()
                ];
            }
        }

        return [
            'exito' => count($errores) === 0,
            'importados' => $importados,
            'errores' => $errores,
            'estadisticas' => $estadisticas
        ];
    }

    /**
     * Procesar una asignación individual
     */
    private function procesarAsignacion(array $fila, string $hashUnico, array $opciones) {
        $resultado = [
            'tutor_nuevo' => false,
            'grado_nuevo' => false,
            'asignatura_nueva' => false,
            'asignacion_nueva' => false
        ];

        // 1. CREAR/OBTENER TUTOR
        $email = $this->generarEmail($fila, $opciones);

        $usuario = User::where('email', $email)->first();

        if (!$usuario) {
            $usuario = User::create([
                'nombre' => trim($fila['Nombre']),
                'apellidos' => trim($fila['Apel1'] . ' ' . $fila['Apel2']),
                'n_tel' => null,
                'email' => $email,
                'password' => $hashUnico,
                'tipo' => 'tutor'
            ]);
            $resultado['tutor_nuevo'] = true;
        }

        // Crear registro en tabla Tutor si no existe
        $tutor = Tutor::find($usuario->id);
        if (!$tutor) {
            Tutor::create(['ID_Usuario' => $usuario->id]);
        }

        // 2. CREAR/OBTENER GRADO
        $grado = Grado::where('Nombre', trim($fila['Grupo']))->first();

        if (!$grado) {
            $grado = Grado::create([
                'Nombre' => trim($fila['Grupo']),
                'Curso' => '2025-2026',
                'ID_Tutor' => null
            ]);
            $resultado['grado_nuevo'] = true;
        }

        // 3. CREAR/OBTENER ASIGNATURA
        $asignatura = Asignatura::where('nombre', trim($fila['Des_Asig']))
            ->where('ID_Grado', $grado->id)
            ->first();

        if (!$asignatura) {
            $asignatura = Asignatura::create([
                'nombre' => trim($fila['Des_Asig']),
                'ID_Grado' => $grado->id
            ]);
            $resultado['asignatura_nueva'] = true;
        }

        // 4. CREAR RELACIÓN TUTOR-ASIGNATURA
        $relacionExiste = TutorAsignatura::where('ID_Tutor', $usuario->id)
            ->where('ID_Asignatura', $asignatura->id)
            ->exists();

        if (!$relacionExiste) {
            TutorAsignatura::create([
                'ID_Tutor' => $usuario->id,
                'ID_Asignatura' => $asignatura->id
            ]);
            $resultado['asignacion_nueva'] = true;
        }

        return $resultado;
    }

    /**
     * Generar email para el tutor
     */
    private function generarEmail(array $fila, array $opciones) {
        // Formato: aimar.etxeberria@egibide.org
        $nombre = $this->limpiarTexto($fila['Nombre']);
        $apellido = $this->limpiarTexto($fila['Apel1']);
        return strtolower($nombre . '.' . $apellido) . '@egibide.org';
    }

    /**
     * Limpiar texto (quitar acentos, espacios, etc.)
     */
    private function limpiarTexto($texto) {
        $texto = trim($texto);
        $texto = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
            $texto
        );
        return preg_replace('/[^a-zA-Z0-9]/', '', $texto);
    }

    /**
     * Vista previa del CSV
     */
    public function vistaPrevia(Request $request) {
        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $archivo = $request->file('archivo');
            $datos = $this->leerCSV($archivo->getPathname());

            // Retornar primeras 10 filas
            $preview = array_slice($datos, 0, 10);

            return response()->json([
                'datos' => $preview,
                'total' => count($datos)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al leer el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
