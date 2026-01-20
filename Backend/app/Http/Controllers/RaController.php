<?php

namespace App\Http\Controllers;

use App\Models\Ra;
use Illuminate\Http\Request;

class RaController extends Controller
{
    // Crear un nuevo RA
    public function store(Request $request)
    {
        // 1. Validar
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'ID_Asignatura' => 'required|exists:asignatura,id' // Verifica que la asignatura exista
        ]);

        // 2. Crear
        // Asegúrate de que en App\Models\Ra.php tengas 'descripcion' e 'ID_Asignatura' en $fillable
        $ra = Ra::create([
            'descripcion' => $validated['descripcion'],
            'ID_Asignatura' => $validated['ID_Asignatura']
        ]);

        return response()->json($ra, 201);
    }

    // Eliminar un RA
    public function destroy($id)
    {
        $ra = Ra::find($id);

        if (!$ra) {
            return response()->json(['message' => 'RA no encontrado'], 404);
        }

        $ra->delete();

        return response()->json(['message' => 'RA eliminado correctamente']);
    }
}