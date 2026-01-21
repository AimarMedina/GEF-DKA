<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use Illuminate\Http\Request;

class CompetenciaController extends Controller
{
    public function store(Request $request){
        $validated= $request->validate([
            'descripcion' => 'required|string|max:255',
            'ID_Grado' => 'required|exists:grado,id'

        ]);

        $competencia = Competencia::create($validated);

        return response()->json($competencia,201);
    }

    public function destroy($id){
        $competencia = Competencia::find($id);
        if(!$competencia){
            return response()->json(['message' => 'Competencia no encontrada'], 404);
        }

        $competencia->delete();
        return response()-> json(['message'=> "Competencia eliminada correctamente"]);
    }
}