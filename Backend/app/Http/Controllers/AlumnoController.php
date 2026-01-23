<?php

namespace App\Http\Controllers;

use App\Models\NotaCuaderno;
use App\Models\NotaEgibide;
use App\Models\Alumno;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function alumnosDeTutor(Request $request, int $id)
    {
        $user = $request->user();

        // --- SEGURIDAD ---
        // 1. Si es Admin, entra siempre.
        // 2. Si es Tutor, verificamos que su ID sea igual al ID de la ruta.
        if ($user->tipo !== 'admin') {
            if ($user->tipo !== 'tutor' || (int)$user->id !== $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado: No puedes ver los alumnos de otro tutor.'
                ], 403);
            }
        }
        // ----------------

        // Lógica de búsqueda y paginación (la mantenemos igual)
        $perPage = (int) $request->query('per_page', 5);
        $q = trim((string) $request->query('q', ''));

        $query = Alumno::query()->where('id_tutor', $id);

        if ($q !== '') {
            $query->whereHas('usuario', function ($u) use ($q) {
                $u->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $alumnos = $query
            ->with([
                'usuario:id,nombre,apellidos,email,tipo',
                'grado:id,nombre',
                'estanciaActual'
            ])
            ->get();

        return response()->json($alumnos);
    }

    /**
     * Obtener alumnos de un INSTRUCTOR específico.
     * Ruta: /api/instructores/{id}/alumnos
     */
    public function alumnosDeInstructor(Request $request, int $id)
{
    $user = $request->user();

    // Seguridad
    if ($user->tipo !== 'admin' && ($user->tipo !== 'instructor' || $user->id != $id)) {
        return response()->json([
            'status' => 'error',
            'message' => 'No autorizado: No puedes ver los alumnos de otro instructor.'
        ], 403);
    }

    $alumnos = Alumno::where('ID_Instructor', $id)
        ->with(['usuario', 'grado', 'estanciaActual'])
        ->get();

    return response()->json($alumnos);
}

    
    public function getGrado($id)
    {
        return Alumno::with('grado')->findOrFail($id);
    }

    public function misNotas($id)
    {


        if (!$id) {
            return response()->json([
                'alumno' => null,
                'cuadernos' => [],
                'competencias' => [],
                'transversales' => [],
                'egibide' => [],
            ]);
        }

        $alumno = Alumno::with([
            'usuario:id,nombre,apellidos',
            'grado:id,nombre',
            'notasCompetencias.competencia',
            'notasTransversales.transversal',
            'notasEgibide.asignatura',
            'notaCuaderno'
        ])->where('ID_Usuario', $id)->first();


        return response()->json($alumno);
    }

    public function guardarNotaEgibide(Request $request, $idAlumno)
    {
        $request->validate([
            'id' => 'required|integer|exists:nota_egibide,id',
            'nota' => 'required|numeric|min:0|max:10',
        ]);

        // Autorización
        $alumno = Alumno::findOrFail($idAlumno);
        $user = $request->user();
        if ($user->tipo !== 'admin' && $user->id != $alumno->ID_Tutor && $user->id != $alumno->ID_Instructor) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $nota = NotaEgibide::findOrFail($request->id);
        $nota->nota = $request->nota;
        $nota->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Nota guardada correctamente',
            'nota' => $nota
        ]);
    }
}
