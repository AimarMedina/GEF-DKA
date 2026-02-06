<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Grado;
use App\Models\Alumno;
use App\Models\User;

class AlumnoImportController extends Controller {
    /**
     * Importar alumnos desde archivo Excel
     */
    public function importar(Request $request) {
        // Validar archivo
        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'errores' => $validator->errors()->all()
            ], 422);
        }

        $archivo = $request->file('archivo');
        $opciones = json_decode($request->input('opciones', '{}'), true);

        try {
            // Leer Excel
            $spreadsheet = IOFactory::load($archivo->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $datos = $sheet->toArray();

            // Procesar datos
            $resultado = $this->procesarDatos($datos, $opciones);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'importados' => 0,
                'errores' => [
                    ['mensaje' => 'Error al procesar el archivo: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }

    /**
     * Procesar datos del Excel
     */
    private function procesarDatos(array $datos, array $opciones) {
        $headers = array_shift($datos); // Primera fila = encabezados
        $errores = [];
        $importados = 0;

        // Crear índices de columnas
        $indices = $this->mapearColumnas($headers);

        if (!$indices) {
            return [
                'exito' => false,
                'importados' => 0,
                'errores' => [
                    ['mensaje' => 'El formato del archivo no es válido. Faltan columnas requeridas.']
                ]
            ];
        }

        // Preparar grados si está habilitado
        if ($opciones['crearGrados'] ?? true) {
            $this->prepararGrados($datos, $indices);
        }

        // Hashear la contraseña por defecto 1 vez.
        $contraseña = $opciones['contraseñaDefecto'] ?? 'Egibide2025';

        $contraseñaHasheada = Hash::make($contraseña);

        // Importar alumnos en transacción
        DB::beginTransaction();

        try {
            foreach ($datos as $index => $fila) {
                $numeroFila = $index + 2; // +2 porque Excel empieza en 1 y quitamos header

                try {
                    // Extraer datos
                    $datosAlumno = $this->extraerDatosAlumno($fila, $indices);

                    // Validar datos
                    $this->validarDatosAlumno($datosAlumno);

                    // Crear usuario y alumno
                    $this->crearAlumno($datosAlumno, $contraseñaHasheada);

                    $importados++;
                } catch (\Exception $e) {
                    $errores[] = [
                        'fila' => $numeroFila,
                        'email' => $datosAlumno['email'] ?? 'desconocido',
                        'mensaje' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'exito' => false,
                'importados' => 0,
                'errores' => [
                    ['mensaje' => 'Error en la transacción: ' . $e->getMessage()]
                ]
            ];
        }

        return [
            'exito' => count($errores) === 0,
            'importados' => $importados,
            'errores' => $errores
        ];
    }

    /**
     * Mapear columnas del Excel
     */
    private function mapearColumnas(array $headers) {
        $columnasRequeridas = [
            'NOMBRE ALUMNO',
            'APELLIDO1 ALUMNO',
            'APELLIDO2 ALUMNO',
            'EMAIL ALUMNO',
            'CLASE',
            'ID PERSONA'
        ];

        $indices = [];
        foreach ($columnasRequeridas as $columna) {
            $indice = array_search($columna, $headers);
            if ($indice === false) {
                return null; // Falta columna requerida
            }
            $indices[$columna] = $indice;
        }

        return $indices;
    }

    /**
     * Extraer datos del alumno de una fila
     */
    private function extraerDatosAlumno(array $fila, array $indices) {
        return [
            'nombre' => trim($fila[$indices['NOMBRE ALUMNO']] ?? ''),
            'apellido1' => trim($fila[$indices['APELLIDO1 ALUMNO']] ?? ''),
            'apellido2' => trim($fila[$indices['APELLIDO2 ALUMNO']] ?? ''),
            'email' => trim($fila[$indices['EMAIL ALUMNO']] ?? ''),
            'clase' => trim($fila[$indices['CLASE']] ?? ''),
            'id_persona' => $fila[$indices['ID PERSONA']] ?? null,
        ];
    }

    /**
     * Validar datos del alumno
     */
    private function validarDatosAlumno(array $datos) {
        if (empty($datos['nombre'])) {
            throw new \Exception('El nombre es obligatorio');
        }

        if (empty($datos['email'])) {
            throw new \Exception('El email es obligatorio');
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('El email no es válido');
        }

        if (empty($datos['id_persona'])) {
            throw new \Exception('El ID es obligatorio');
        }

        // Verificar duplicados
        if (User::find($datos['id_persona'])) {
            throw new \Exception("Ya existe un usuario con ID {$datos['id_persona']}");
        }

        if (User::where('Email', $datos['email'])->exists()) {
            throw new \Exception("Ya existe un usuario con email {$datos['email']}");
        }
    }

    /**
     * Crear alumno en la base de datos
     */
    private function crearAlumno(array $datos, string $contraseñaHasheada) {
        // Crear Usuario
        $usuario = User::create([
            'id' => $datos['id_persona'],
            'nombre' => $datos['nombre'],
            'apellidos' => trim($datos['apellido1'] . ' ' . $datos['apellido2']),
            'n_tel' => null,
            'email' => $datos['email'],
            'password' => $contraseñaHasheada,
            'tipo' => 'alumno'
        ]);

        // Buscar ID del grado
        $grado = Grado::where('Nombre', $datos['clase'])->first();

        // Crear Alumno
        Alumno::create([
            'ID_Usuario' => $usuario->id,
            'ID_Grado' => $grado ? $grado->id : null,
            'ID_Tutor' => null,
            'ID_Instructor' => null
        ]);
    }

    /**
     * Preparar grados (crear si no existen)
     */
    private function prepararGrados(array $datos, array $indices) {
        // Extraer clases únicas
        $clasesUnicas = [];
        foreach ($datos as $fila) {
            $clase = trim($fila[$indices['CLASE']] ?? '');
            if ($clase && !in_array($clase, $clasesUnicas)) {
                $clasesUnicas[] = $clase;
            }
        }

        // Crear grados que no existan
        foreach ($clasesUnicas as $clase) {
            if (!Grado::where('Nombre', $clase)->exists()) {
                Grado::create([
                    'Nombre' => $clase,
                    'Curso' => '2025-2026',
                    'ID_Tutor' => null
                ]);
            }
        }
    }

    /**
     * Obtener vista previa del archivo
     */
    public function vistaPrevia(Request $request) {
        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $archivo = $request->file('archivo');
            $spreadsheet = IOFactory::load($archivo->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $datos = $sheet->toArray();

            $headers = array_shift($datos);

            // Retornar primeras 10 filas
            $preview = array_slice($datos, 0, 10);

            return response()->json([
                'headers' => $headers,
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
