<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Competencia;
use Illuminate\Http\Request;

class MatrizController extends Controller
{
    public function index()
    {
        // 1. Cargamos todas las competencias (columnas de la tabla)
        $competencias = Competencia::all();

        // 2. Cargamos las asignaturas con sus RAs y, dentro de cada RA, la relación 'compRas'
        //    Usamos 'ras.compRas' porque así llamaste a las funciones en tus modelos.
        $asignaturas = Asignatura::with(['ras.compRas'])
                        ->get();

        return response()->json([
            'competencias' => $competencias,
            'asignaturas' => $asignaturas
        ]);
    }
}
