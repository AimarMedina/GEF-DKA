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
            'entregasAlumno.alumno.usuario',
            'entregasAlumno.nota'
        ])
        ->where('ID_Grado', $idGrado)
        ->get();
    }
}
