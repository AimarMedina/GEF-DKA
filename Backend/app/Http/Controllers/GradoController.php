<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Asignatura;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function getGrados(Request $request)
    {
        // Iniciamos la consulta
        $query = Grado::query();

        // Si recibimos el parámetro 'q' (búsqueda), filtramos
        if ($request->has('q') && $request->input('q') != '') {
            $search = $request->input('q');
            
            // Buscamos coincidencias en Nombre O en Curso
            $query->where(function($q) use ($search) {
                $q->where('Nombre', 'LIKE', "%{$search}%")
                  ->orWhere('Curso', 'LIKE', "%{$search}%");
            });
        }

        // Paginamos el resultado (ya filtrado o completo)
        $grados = $query->paginate(5); 
        
        return response()->json($grados);
    }
    // Obtener asignaturas simples
    public function getAsignaturas($id)
    {
        // Buscamos las asignaturas que pertenezcan a este grado
        // Asegúrate de usar 'asignaturas' si es así en tu modelo
        $asignaturas = Asignatura::where('ID_Grado', $id)->orderBy('id')->get();
        return response()->json($asignaturas);
    }

    // Obtener competencias (Grado -> Asignatura -> Ra -> CompRa -> Competencia)
    public function getCompetencias($id)
    {
        $grado = Grado::with('asignaturas.ras.compRas.competencia')->orderBy('id')->find($id);

        if (!$grado) {
            return response()->json([], 404);
        }

        // Usamos colecciones de Laravel para "aplanar" la estructura y sacar solo las competencias únicas
        $competencias = $grado->asignaturas
            ->pluck('ras')->flatten()
            ->pluck('compRas')->flatten()
            ->pluck('competencia')
            ->filter()
            ->unique('id') 
            ->sortBy('id')
            ->values(); 

        return response()->json($competencias);
    }
}