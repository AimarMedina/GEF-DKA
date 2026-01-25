<?php

namespace App\Http\Controllers;

use App\Http\Services\NotasAlumnoService;
use App\Models\Asignatura;
use App\Models\NotaCuaderno;
use App\Models\NotaEgibide;
use App\Models\Alumno;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
        protected $notasService;

    public function __construct(NotasAlumnoService $notasService)
    {
        $this->notasService = $notasService;
    }
    public function alumnosDeTutor(Request $request, int $id)
    {
        $user = $request->user();

        // --- SEGURIDAD ---
        // 1. Si es Admin, entra siempre.
        // 2. Si es Tutor, verificamos que su ID sea igual al ID de la ruta.
        if ($user->tipo !== 'admin') {
            if ($user->tipo !== 'tutor' || (int) $user->id !== $id) {
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
                'estanciaActual.empresa',
                'instructor.user'
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

    public function misNotasAlumno(Request $request, $id)
{
    $alumno = Alumno::with('grado')->findOrFail($id); // Alumno con su grado
    $grado = $alumno->grado;
    $asignaturas = Asignatura::where('ID_Grado', $grado->id)->get();


    $notaCuaderno = $this->notasService->obtenerNotaCuaderno($id);
    $notaTransversal = $this->notasService->obtenerNotaTransversal($id);
    $notasTecnicas = $this->notasService->obtenerNotaTecnicaPorAsignatura($id, $asignaturas);
    $notasEmpresa = $this->notasService->calcularNotaFinalEmpresa($notaCuaderno, $notaTransversal, $notasTecnicas);
    $notasEgibide = $this->notasService->obtenerNotasEgibide($id);
    $notasFinales = $this->notasService->calcularNotasFinalesPorAsignatura($notasEmpresa, $notasEgibide);

    $packNotas = [];
    foreach ($asignaturas as $asig) {
        $packNotas[$asig->id] = [
            'cuaderno' => $notaCuaderno,
            'transversal' => $notaTransversal,
            'tecnica' => $notasTecnicas[$asig->id] ?? '-',
            'egibide' => $notasEgibide[$asig->id] ?? '-',
            'nota_empresa_calculada' => $notasEmpresa[$asig->id] ?? '-',
            'final' => $notasFinales[$asig->id] ?? '-'
        ];
    }

    return response()->json([
        'usuario' => $alumno->usuario,
        'grado' => $grado,
        'asignaturas' => $asignaturas,
        'nota_cuaderno' => $notaCuaderno,
        'notas_competencias' => $notasTecnicas,
        'notas_transversales' => $notaTransversal,
        'notas_egibide' => $notasEgibide,
        'notas_calculadas' => $packNotas,
    ]);
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
            'grado.asignaturas.notaEgibide' => function ($q) use ($id) {
                $q->where('ID_Alumno', $id);
            },
            'notaCuaderno',
            'instructor.user',
            'estanciaActual.empresa'
        ])->where('ID_Usuario', $id)->first();

        return response()->json($alumno);
    }

    public function guardarNotaEgibide(Request $request, $idAlumno)
    {
        $request->validate([
            'id_asignatura' => 'required|integer|exists:asignatura,id',
            'nota' => 'required|numeric|min:0|max:10',
        ]);

        // Autorización
        $alumno = Alumno::findOrFail($idAlumno);
        $user = $request->user();

        if (
            $user->tipo !== 'admin' &&
            $user->id != $alumno->ID_Tutor &&
            $user->id != $alumno->ID_Instructor
        ) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // updateOrCreate
        $nota = NotaEgibide::updateOrCreate(
            [
                'ID_Alumno' => $idAlumno,
                'ID_Asignatura' => $request->id_asignatura,
            ],
            [
                'nota' => $request->nota
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Nota guardada correctamente',
            'nota' => $nota
        ]);
    }

    public function asignarInstructor(Request $request, $idAlumno)
    {
        $user = $request->user();

        // Solo tutor o admin
        if ($user->tipo !== 'admin' && $user->tipo !== 'tutor') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'ID_Instructor' => 'required|exists:users,id'
        ]);

        $alumno = Alumno::findOrFail($idAlumno);

        $alumno->ID_Instructor = $request->ID_Instructor;
        $alumno->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Instructor asignado correctamente',
            'alumno' => $alumno->load(['instructor.user'])
        ]);
    }
}
