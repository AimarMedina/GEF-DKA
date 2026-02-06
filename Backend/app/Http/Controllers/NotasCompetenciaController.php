<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotasCompetenciaController extends Controller {
    public function guardarNota(Request $request, $alumnoId, $competenciaId) {
        $request->validate([
            'nota' => 'required|integer|between:1,4'
        ]);

        DB::table('nota_competencia')->updateOrInsert(
            [
                'ID_Alumno' => $alumnoId,
                'ID_Competencia' => $competenciaId
            ],
            [
                'nota' => $request->nota
            ]
        );

        return response()->json([
            'message' => 'Nota guardada correctamente'
        ]);
    }
}
