<?php

namespace App\Http\Controllers;

use App\Models\EntregaCuaderno;
use Illuminate\Http\Request;

class EntregaCuadernoController extends Controller
{
     public function crearEntregaCuaderno(Request $request)
    {
        return EntregaCuaderno::create([
            'Fecha_creacion' => now(),
            'Fecha_Limite' => $request->Fecha_Limite,
            'ID_Grado' => $request->ID_Grado
        ]);
    }

    public function porGrado($idGrado)
    {
        return EntregaCuaderno::with([
            'alumnoEntrega.alumno.usuario',
            'alumnoEntrega.nota'
        ])
        ->where('ID_Grado', $idGrado)
        ->get();
    }
    public function entregasAlumno(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json([], 401);

        $alumno = $user->alumno;
        if (!$alumno) return response()->json([]);

        $entregas = EntregaCuaderno::where('ID_Grado', $alumno->ID_Grado)
            ->with(['alumnoEntrega' => function($q) use ($alumno) {
                $q->where('ID_Alumno', $alumno->ID_Usuario)
                ->with(['nota']);
            }])
            ->get();

        $entregas->transform(fn($entrega) => tap($entrega, fn($e) => $e->alumnoEntrega = $e->alumnoEntrega ?? []));

        return response()->json($entregas);
    }

}
