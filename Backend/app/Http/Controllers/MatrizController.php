<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Competencia;
use Illuminate\Http\Request;

class MatrizController extends Controller
{
    public function getCompRa($gradoId)
    {
        // Competencias del grado
        $competencias = Competencia::whereHas('compRas.asignatura', function ($q) use ($gradoId) {
            $q->where('ID_Grado', $gradoId);
        })->orderBy('id')->get();

        // Asignaturas del grado con RAs y competencias
        $asignaturas = Asignatura::where('ID_Grado', $gradoId)
            ->with([
                'ras.compRas' => function ($q) {
                    $q->select('ID_Ra', 'ID_Comp');
                }
            ])
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'competencias' => $competencias,
            'asignaturas' => $asignaturas
        ]);
    }

}
