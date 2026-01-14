<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;

class NotasEmpresaController extends Controller
{
    private function authorizeAlumno(Request $request, int $idAlumno): Alumno
    {
        $user = $request->user();

        $alumno = Alumno::query()->findOrFail($idAlumno);

        // admin puede todo 
         if ($user->tipo === 'admin') return $alumno;

        $isTutor = ($user->tipo === 'tutor' && (int)$alumno->ID_Tutor === (int)$user->id);
        $isInstructor = ($user->tipo === 'instructor' && (int)$alumno->ID_Instructor === (int)$user->id);

        if (!$isTutor && !$isInstructor) {
            abort(403, 'No autorizado para ver las notas de este alumno');
        }

        return $alumno;
    }

    public function show(Request $request, int $idAlumno)
    {
        $alumno = $this->authorizeAlumno($request, $idAlumno);

    
        return response()->json([
            'status' => 'success',
            'data' => [
                'id_alumno' => $alumno->ID_Usuario,
                'puede_editar' => in_array($request->user()->tipo, ['tutor','instructor','admin']),
            ]
        ]);
    }

    public function store(Request $request, int $idAlumno)
    {
        $alumno = $this->authorizeAlumno($request, $idAlumno);

        // Aquí luego validas y guardas notas.
        return response()->json(['status' => 'success']);
    }
}
