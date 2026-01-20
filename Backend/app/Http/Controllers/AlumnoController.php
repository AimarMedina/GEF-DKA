<?php

namespace App\Http\Controllers;

use App\Models\NotaCuaderno;
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
                'estanciaActual:id,ID_Alumno'
            ])
            ->paginate($perPage);

        // Transformación de datos para el Front
        $alumnos->getCollection()->transform(function ($a) {
            return [
                'ID_Usuario'     => $a->ID_Usuario,
                'Nombre'         => $a->usuario?->nombre,
                'Apellidos'      => $a->usuario?->apellidos,
                'Email'          => $a->usuario?->email,
                'Tipo'           => $a->usuario?->tipo,
                'Grado'          => $a->grado?->nombre,
                'Tiene_estancia' => $a->estanciaActual !== null,
                'estancia_id'    => $a->estanciaActual?->id
            ];
        });

        return response()->json(['data' => $alumnos]);
    }

    /**
     * Obtener alumnos de un INSTRUCTOR específico.
     * Ruta: /api/instructores/{id}/alumnos
     */
    public function alumnosDeInstructor(Request $request, int $id)
    {
        $user = $request->user();

        // --- SEGURIDAD ---
        // 1. Si es Admin, entra siempre.
        // 2. Si es Instructor, verificamos que su ID sea igual al ID de la ruta.
        if ($user->tipo !== 'admin') {
            if ($user->tipo !== 'instructor' || (int)$user->id !== $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado: No puedes ver los alumnos de otro instructor.'
                ], 403);
            }
        }
        // ----------------

        // Misma lógica de búsqueda pero filtrando por 'id_instructor'
        $perPage = (int) $request->query('per_page', 5);
        $q = trim((string) $request->query('q', ''));

        $query = Alumno::query()->where('id_instructor', $id);

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
                'estanciaActual:id,ID_Alumno'
            ])
            ->paginate($perPage);

        $alumnos->getCollection()->transform(function ($a) {
            return [
                'ID_Usuario'     => $a->ID_Usuario,
                'Nombre'         => $a->usuario?->nombre,
                'Apellidos'      => $a->usuario?->apellidos,
                'Email'          => $a->usuario?->email,
                'Tipo'           => $a->usuario?->tipo,
                'Grado'          => $a->grado?->nombre,
                'Tiene_estancia' => $a->estanciaActual !== null,
                'estancia_id'    => $a->estanciaActual?->id
            ];
        });

        return response()->json(['data' => $alumnos]);
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

}
