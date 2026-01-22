<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\EntregaCuaderno;
use Illuminate\Http\Request;

class EntregaCuadernoController extends Controller
{
    public function store(Request $request, $gradoId)
    {
        $request->validate([
            'Descripcion' => 'required|string',
            'Fecha_Limite' => 'required|date',
        ]);

        $entrega = EntregaCuaderno::create([
            'ID_Grado' => $gradoId,
            'Descripcion' => $request->Descripcion,
            'Fecha_Limite' => $request->Fecha_Limite,
            'Fecha_creacion' => now(),
        ]);

        return response()->json($entrega);
    }


    public function porGrado($gradoId, Request $request)
    {
        $tutorId = $request->query('tutor_id');

        return EntregaCuaderno::where('id_grado', $gradoId)
            ->with([
                'alumnoEntrega' => function ($q) use ($tutorId) {
                    $q->whereHas('alumno', function ($q2) use ($tutorId) {
                        $q2->where('ID_Tutor', $tutorId);
                    })
                        ->with('alumno.usuario');
                }
            ])
            ->get();
    }



    public function entregasAlumno(Request $request, $id)
    {
        // Seguridad
        if ($request->user()->id != $id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Obtener grado del alumno
        $idGrado = Alumno::where('ID_Usuario', $id)->value('ID_Grado');

        if (!$idGrado) {
            return response()->json([], 200);
        }

        // Entregas del grado + entrega del alumno (si existe)
        $entregas = EntregaCuaderno::with([
            'alumnoEntrega' => function ($q) use ($id) {
                $q->where('ID_Alumno', $id);
            }
        ])
            ->where('ID_Grado', $idGrado)
            ->get();

        return response()->json($entregas);
    }

    public function destroy($gradoId, $entregaId)
    {
        $entrega = EntregaCuaderno::where('id', $entregaId)
            ->where('ID_Grado', $gradoId)
            ->firstOrFail();

        $entrega->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }
}
