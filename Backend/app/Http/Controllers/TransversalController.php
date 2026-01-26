<?php

namespace App\Http\Controllers;

use App\Models\Transversal;
use App\Models\NotaTransversal;
use App\Models\Alumno;
use Illuminate\Http\Request;

class TransversalController extends Controller
{
    /**
     * GET /api/transversales/alumno/{idAlumno}
     */
    public function getTransversalesAlumno(Request $request, $idAlumno)
    {
        // Verificar autorización
        $user = $request->user();
        $alumno = Alumno::findOrFail($idAlumno);

        // Solo admin, tutor del alumno o instructor del alumno pueden ver
        if (
            $user->tipo !== 'admin' &&
            $user->id != $alumno->ID_Tutor &&
            $user->id != $alumno->ID_Instructor
        ) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Obtener todas las transversales del sistema
        $transversales = Transversal::all()->map(function ($t) use ($idAlumno) {
            // Buscar si este alumno ya tiene nota en esta transversal
            $nota = NotaTransversal::where('ID_Transversal', $t->id)
                        ->where('ID_Alumno', $idAlumno)
                        ->first();
            
            return [
                'id' => $t->id,
                'descripcion' => $t->descripcion,
                'nota' => $nota ? $nota->Nota : null
            ];
        });

        return response()->json($transversales);
    }

    /**
     * Actualizar nota de transversal para un alumno
     * PUT /api/alumnos/{idAlumno}/transversales/{transversalId}/nota
     */
    public function actualizarNotaTransversal(Request $request, $idAlumno, $transversalId)
    {
        // Verificar autorización
        $user = $request->user();
        $alumno = Alumno::findOrFail($idAlumno);

        // Solo admin, tutor o instructor pueden actualizar notas
        if (
            $user->tipo !== 'admin' &&
            $user->id != $alumno->ID_Tutor &&
            $user->id != $alumno->ID_Instructor
        ) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'nota' => 'nullable|integer|min:1|max:4'
        ]);

        // Verificar que la transversal existe
        $transversal = Transversal::findOrFail($transversalId);

        if ($request->nota === null) {
            // Si la nota es null, eliminar el registro si existe
            NotaTransversal::where('ID_Transversal', $transversalId)
                ->where('ID_Alumno', $idAlumno)
                ->delete();
        } else {
            // Actualizar o crear la nota
            NotaTransversal::updateOrCreate(
                [
                    'ID_Alumno' => $idAlumno,
                    'ID_Transversal' => $transversalId
                ],
                [
                    'Nota' => $request->nota
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Nota transversal guardada correctamente'
        ]);
    }

    /**
     * GET /api/transversales
     */
    public function getTransversales()
    {
        $transversales = Transversal::all();
        return response()->json($transversales);
    }

    /**
     * POST /api/transversales
     */
    public function crearTransversal(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255'
        ], [
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres'
        ]);

        $transversal = Transversal::create([
            'descripcion' => $request->descripcion
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transversal creada correctamente',
            'transversal' => $transversal
        ], 201);
    }

    /**
     * PUT /api/transversales/{id}
     */
    public function actualizarTransversal(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255'
        ], [
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres'
        ]);

        $transversal = Transversal::findOrFail($id);
        $transversal->Descripcion = $request->descripcion;
        $transversal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transversal actualizada correctamente',
            'transversal' => $transversal
        ]);
    }

    /**
     * DELETE /api/transversales/{id}
     */
    public function eliminarTransversal($id)
    {
        $transversal = Transversal::findOrFail($id);
        
        // Verificar si tiene notas asociadas
        $notasCount = $transversal->notasTransversales()->count();
        
        if ($notasCount > 0) {
            return response()->json([
                'message' => "No se puede eliminar la transversal porque tiene {$notasCount} nota(s) asociada(s)"
            ], 422);
        }
        
        $transversal->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Transversal eliminada correctamente'
        ]);
    }
}