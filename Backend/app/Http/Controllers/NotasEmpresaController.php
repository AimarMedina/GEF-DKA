<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Transversal;
use App\Models\NotaTransversal;
use App\Models\NotaCompetencia;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;

class NotasEmpresaController extends Controller
{
    /**
     * Verifica que el usuario tenga permiso para ver/editar a este alumno.
     */
    private function authorizeAlumno(Request $request, int $idAlumno): Alumno
    {
        $user = $request->user();
        // Buscamos al alumno
        $alumno = Alumno::query()->findOrFail($idAlumno);

        // 1. Si es Admin, pase libre
        if ($user->tipo === 'admin') return $alumno;

        // 2. Verificamos si es su Tutor o su Instructor
        // Convertimos a int por seguridad
        $isTutor = ($user->tipo === 'tutor' && (int)$alumno->ID_Tutor === (int)$user->id);
        $isInstructor = ($user->tipo === 'instructor' && (int)$alumno->ID_Instructor === (int)$user->id);

        if (!$isTutor && !$isInstructor) {
            abort(403, 'No tienes permiso para gestionar las notas de este alumno.');
        }

        return $alumno;
    }

    /**
     * GET /api/alumnos/{id}/notas
     * Muestra las competencias y las notas actuales.
     */
    public function show(Request $request, int $idAlumno)
    {
        // 1. Seguridad y obtención del alumno
        $alumno = $this->authorizeAlumno($request, $idAlumno);

        // 2. Carga ansiosa (Eager Loading) para obtener todo el árbol de una sola vez
        // Esto evita hacer cientos de consultas a la base de datos.
        $alumno->load(['usuario', 'grado.asignaturas.ras.compRas.competencia']);

        // --- A. COMPETENCIAS TRANSVERSALES ---
        // Obtenemos todas las transversales del sistema
        $transversales = Transversal::all()->map(function ($t) use ($idAlumno) {
            // Buscamos si este alumno ya tiene nota en esta competencia
            $nota = NotaTransversal::where('ID_Transversal', $t->id)
                        ->where('ID_Alumno', $idAlumno)
                        ->first();

            return [
                'id' => $t->id,
                'descripcion' => $t->Descripcion,
                'nota' => $nota ? $nota->Nota : null // Devuelve la nota (1-4) o null
            ];
        });

        // --- B. COMPETENCIAS TÉCNICAS (El camino largo) ---
        $competenciasTecnicas = collect();

        // Si el alumno tiene grado...
        if ($alumno->grado) {
            // Recorremos sus asignaturas
            foreach ($alumno->grado->asignaturas as $asignatura) {
                // Recorremos los Resultados de Aprendizaje (RAs) de la asignatura
                foreach ($asignatura->ras as $ra) {
                    // Recorremos la tabla intermedia (las "X" del Excel)
                    foreach ($ra->compRas as $compRa) {
                        // Si hay una competencia vinculada, la guardamos
                        if ($compRa->competencia) {
                            $competenciasTecnicas->push($compRa->competencia);
                        }
                    }
                }
            }
        }

        // Limpieza:
        // 1. unique('id'): Porque una competencia puede trabajarse en varias asignaturas.
        // 2. map(): Para buscar la nota de cada una.
        // 3. values(): Para reordenar el array y que llegue limpio al JSON.
        $tecnicas = $competenciasTecnicas->unique('id')->map(function ($comp) use ($idAlumno) {
            $nota = NotaCompetencia::where('ID_Competencia', $comp->id)
                        ->where('ID_Alumno', $idAlumno)
                        ->first();

            return [
                'id' => $comp->id,
                'descripcion' => $comp->Descripcion, // Asegúrate que en BD sea 'Descripcion' o 'Nombre'
                'nota' => $nota ? $nota->Nota : null
            ];
        })->values();

        // 3. Devolvemos todo al Vue
        return response()->json([
            'status' => 'success',
            'data' => [
                'alumno_nombre' => $alumno->usuario->nombre . ' ' . $alumno->usuario->apellidos,
                'transversales' => $transversales,
                'tecnicas' => $tecnicas,
            ]
        ]);
    }

    /**
     * POST /api/alumnos/{id}/notas
     * Guarda o actualiza las notas.
     */
    public function store(Request $request, int $idAlumno)
    {
        $this->authorizeAlumno($request, $idAlumno);

        // Validamos que lleguen arrays (pueden estar vacíos si no se ha tocado nada)
        $data = $request->validate([
            'transversales' => 'nullable|array',
            'tecnicas' => 'nullable|array',
        ]);

        // 1. Guardar Transversales
        if (!empty($data['transversales'])) {
            foreach ($data['transversales'] as $idTransversal => $notaVal) {
                // updateOrCreate: Si existe la actualiza, si no la crea.
                if ($notaVal !== null) {
                    NotaTransversal::updateOrCreate(
                        ['ID_Alumno' => $idAlumno, 'ID_Transversal' => $idTransversal],
                        ['Nota' => $notaVal]
                    );
                }
            }
        }

        // 2. Guardar Técnicas
        if (!empty($data['tecnicas'])) {
            foreach ($data['tecnicas'] as $idCompetencia => $notaVal) {
                if ($notaVal !== null) {
                    NotaCompetencia::updateOrCreate(
                        ['ID_Alumno' => $idAlumno, 'ID_Competencia' => $idCompetencia],
                        ['Nota' => $notaVal]
                    );
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Notas guardadas correctamente']);
    }
}
