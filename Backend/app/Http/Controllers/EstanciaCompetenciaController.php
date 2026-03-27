<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\EstanciaAlumno;
use App\Models\NotaCompetencia;
use DB;
use Illuminate\Http\Request;

class EstanciaCompetenciaController extends Controller
{


    public function create(Request $request, $estanciaId)
    {
        $request->validate([
            'ID_Comp' => 'required|exists:competencia,id'
        ]);

        $competenciaId = $request->ID_Comp;


        if (
            DB::table('comp_estancia')
                ->where('ID_Estancia', $estanciaId)
                ->where('ID_Comp', $request->ID_Comp)
                ->exists()
        ) {
            return response()->json([
                'message' => 'La competencia ya está asignada'
            ], 409);
        }


        DB::table('comp_estancia')->insert([
            'ID_Estancia' => $estanciaId,
            'ID_Comp' => $competenciaId,
        ]);

        return response()->json([
            'message' => 'Competencia añadida correctamente'
        ], 201);
    }

    public function delete($estanciaId, $competenciaId)
    {
        $estancia = EstanciaAlumno::findOrFail($estanciaId);

        if (!$estancia->competencias()->where('competencia.id', $competenciaId)->exists()) {
            return response()->json(['message' => 'Competencia no asignada a esta estancia'], 404);
        }

        $estancia->competencias()->detach($competenciaId);
        $notaCompetencia = NotaCompetencia::where('ID_Alumno', $estancia->ID_Alumno)->where('ID_Competencia', $competenciaId)->delete();
        return response()->json([
            'message' => 'Competencia eliminada de la estancia',
            'competencia_id' => $competenciaId,
            'estancia_id' => $estanciaId,
            'notaCompetencia' => $notaCompetencia
        ]);
    }
}
