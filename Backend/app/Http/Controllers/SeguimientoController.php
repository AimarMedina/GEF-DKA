<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use App\Models\EstanciaAlumno;
use Illuminate\Http\Request;

class SeguimientoController extends Controller {
    // Obtener todos los seguimientos de un alumno por ID_Alumno
    public function index($idEstancia) {
        // Buscar la estancia directamente
        $estancia = EstanciaAlumno::find($idEstancia);

        if (!$estancia) {
            return response()->json([]);
        }

        // Traer seguimientos de esa estancia
        $seguimientos = Seguimiento::where('ID_Estancia', $idEstancia)->get();

        $seguimientos = $seguimientos->map(function ($s) {
            return [
                'id' => $s->id,
                'ID_Estancia' => $s->ID_Estancia,
                'Fecha' => $s->Fecha,
                'Hora' => $s->Hora,
                'Accion_seguimiento' => $s->Accion_seguimiento,
                'Seguimiento_actividad' => $s->Seguimiento_actividad,
            ];
        });

        return response()->json($seguimientos);
    }



    // Crear nuevo seguimiento
    public function crearSeguimiento(Request $request) {
        $data = $request->validate([
            'ID_Estancia' => 'required|exists:estancia_alumno,ID',
            'Fecha' => 'required|date',
            'Hora' => 'required',
            'Accion_seguimiento' => 'required|string',
            'Seguimiento_actividad' => 'nullable|string',
        ]);

        $seguimiento = Seguimiento::create($data);
        return response()->json($seguimiento, 201);
    }

    // Actualizar seguimiento
    public function ModificarSeguimiento(Request $request, $id) {
        $seguimiento = Seguimiento::findOrFail($id);

        $data = $request->validate([
            'Fecha' => 'required|date',
            'Hora' => 'required',
            'Accion_seguimiento' => 'required|string',
            'Seguimiento_actividad' => 'nullable|string',
        ]);

        $seguimiento->update($data);
        return response()->json($seguimiento);
    }

    public function eliminarSeguimiento($id) {
        $seguimiento = Seguimiento::find($id);

        if (!$seguimiento) {
            return response()->json([
                'message' => 'Seguimiento no encontrado'
            ], 404);
        }

        $seguimiento->delete();

        return response()->json([
            'message' => 'Seguimiento eliminado',
            'seguimientio' => $seguimiento
        ], 200);
    }
}
