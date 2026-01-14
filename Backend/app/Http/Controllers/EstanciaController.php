<?php

namespace App\Http\Controllers;

use App\Models\EstanciaAlumno;

class EstanciaController extends Controller
{
    public function getEstancias(){
        $estancias = EstanciaAlumno::all();
        return response()->json($estancias);
    }
    
    // Obtener la estancia del alumno
    public function getEstanciaAlumno($idAlumno)
    {
        $estancia = EstanciaAlumno::with([
            'empresa',
            'horario',
            'alumno.tutor.usuario',
            'alumno.instructor.usuario'
        ])->where('ID_Alumno', $idAlumno)
            ->get(); 

        return response()->json($estancia);
    }
    public function getCompanyAlumnos($CIF){
        $estanciasEmpresa = EstanciaAlumno::with(['alumno.usuario','alumno.instructor.user'])->where('CIF_Empresa', $CIF)->get();
        return response()->json($estanciasEmpresa);
    }


}

