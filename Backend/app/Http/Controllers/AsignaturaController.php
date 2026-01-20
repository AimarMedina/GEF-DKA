<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Ra;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    // Obtener RAs de una asignatura (ya lo tenías)
    public function getRas(Request $request, int $id){
        $Ras = Ra::where('ID_Asignatura', $id)->get();
        return response()->json($Ras);
    }

    // Crear Asignatura (lo añadimos en el paso anterior)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ID_Grado' => 'required|exists:grado,id'
        ]);

        $asignatura = Asignatura::create([
            'nombre' => $validated['nombre'],
            'ID_Grado' => $validated['ID_Grado']
        ]);

        return response()->json($asignatura, 201);
    }

    // --- NUEVO: Eliminar Asignatura ---
    public function destroy($id)
    {
        $asignatura = Asignatura::find($id);

        if (!$asignatura) {
            return response()->json(['message' => 'Asignatura no encontrada'], 404);
        }

        // Al borrar la asignatura, se borrarán sus RAs automáticamente 
        // si configuraste onDelete('cascade') en la migración.
        $asignatura->delete();

        return response()->json(['message' => 'Asignatura eliminada correctamente']);
    }
}