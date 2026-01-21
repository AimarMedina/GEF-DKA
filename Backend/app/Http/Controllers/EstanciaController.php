<?php

namespace App\Http\Controllers;

use App\Models\EstanciaAlumno;
use App\Models\Alumno;


class EstanciaController extends Controller
{
    public function getEstancias(){
        $estancias = EstanciaAlumno::all();
        return response()->json($estancias);
    }

    // Tutor: historial completo de estancias de un alumno
    public function historialEstanciasAlumno($idAlumno)
    {
        $estancias = EstanciaAlumno::with([
            'empresa',
            'horario',
            'alumno.tutor.user',
            'alumno.instructor.user',
            'alumno.grado'
        ])->where('ID_Alumno', $idAlumno)
          ->orderBy('Fecha_inicio', 'desc')
          ->get();

        return response()->json($estancias);
    }

    // Alumno: solo su estancia actual
    public function getEstanciaActual($idAlumno)
    {
        $alumno = Alumno::with('estanciaActual.empresa',
                               'estanciaActual.horario',
                               'estanciaActual.alumno.tutor.user',
                               'estanciaActual.alumno.instructor.user')
                        ->findOrFail($idAlumno);

        return response()->json($alumno->estanciaActual);
    }

    public function getCompanyAlumnos($CIF){
        $estanciasEmpresa = EstanciaAlumno::with(['alumno.usuario','alumno.instructor.user'])->where('CIF_Empresa', $CIF)->get();
        return response()->json($estanciasEmpresa);
    }

    public function asignarEstancia(Request $request){
        $data=$request->validate([
            'ID_Alumno' =>'required',
            'CIF_Empresa' => 'required|exists:Empresa,CIF',
            'Fecha_inicio'=> 'required|date',
            'Fecha_fin' =>'required|date'
        ]);

        $estancia=EstanciaAlumno::create($data);
        return response()->json($estancia,201);
    }

}

