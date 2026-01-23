<?php

namespace App\Http\Controllers;

use App\Http\Services\NotasAlumnoService;
use App\Models\Grado;
use App\Models\Asignatura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradoController extends Controller
{
protected $notasService;

    public function __construct(NotasAlumnoService $notasService)
    {
        $this->notasService = $notasService;
    }



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
        $grado = Grado::find($id);

        if (!$grado) {
            return response()->json([], 404);
        }

        $competencias = $grado->competencias()->orderBy('id')->get();

        return response()->json($competencias);
    }

  public function getDatosGestionTutor(Request $request)
    {
        $user = $request->user();

        // 1. Obtener Grado del Tutor
        $grado = DB::table('grado')->where('ID_Tutor', $user->id)->first();
        if (!$grado) return response()->json(['message' => 'Sin grado asignado'], 404);

        // 2. Obtener Asignaturas del Grado
        // Necesitamos los modelos (Eloquent) para pasarlos al servicio, no el Query Builder
        $asignaturas = Asignatura::where('ID_Grado', $grado->id)->get();

        // 3. Obtener Alumnos matriculados en ese grado
        $alumnos = User::where('tipo', 'alumno')
            ->whereHas('alumno', function($q) use ($grado) {
                $q->where('ID_Grado', $grado->id);
            })
            ->with('alumno') // Cargamos la relación para acceder a sus IDs
            ->orderBy('apellidos', 'asc')
            ->get();

        // 4. CALCULAR NOTAS PARA CADA ALUMNO
        $alumnos = $alumnos->map(function ($usuarioAlumno) use ($asignaturas) {
            
            // IMPORTANTE: Según tus migraciones, la FK es 'id_usuario' en la tabla alumno
            $idAlumno = $usuarioAlumno->alumno->id_usuario; 

            // A. Notas Globales
            $notaCuaderno    = $this->notasService->obtenerNotaCuaderno($idAlumno);
            $notaTransversal = $this->notasService->obtenerNotaTransversal($idAlumno);

            // B. Notas Técnicas por asignatura (Calcula medias de RAs)
            // Devuelve: [ID_Asig => NotaTecnica]
            $notasTecnicas   = $this->notasService->obtenerNotaTecnicaPorAsignatura($idAlumno, $asignaturas);

            // C. Notas de Empresa (Aplica la fórmula 20% + 20% + 60%)
            // Devuelve: [ID_Asig => NotaEmpresa]
            $notasEmpresa    = $this->notasService->calcularNotaFinalEmpresa($notaCuaderno, $notaTransversal, $notasTecnicas);

            // D. Notas de Egibide (Del tutor)
            // Devuelve: [ID_Asig => NotaEgibide]
            $notasEgibide    = $this->notasService->obtenerNotasEgibide($idAlumno);

            // E. NOTA FINAL ABSOLUTA (20% Empresa + 80% Egibide)
            $notasFinales    = $this->notasService->calcularNotasFinalesPorAsignatura($notasEmpresa, $notasEgibide);

            // F. Empaquetar todo en un formato fácil para Vue
            // Creamos un array donde la clave es el ID de la asignatura
            $packNotas = [];
            foreach ($asignaturas as $asig) {
                $id = $asig->id;
                $packNotas[$id] = [
                    'cuaderno'    => $notaCuaderno,    // Igual para todas
                    'transversal' => $notaTransversal, // Igual para todas
                    
                    // Usamos null coalescing (??) por si no existe la clave
                    'tecnica'     => $notasTecnicas[$id] ?? '-', 
                    'egibide'     => $notasEgibide[$id] ?? '-',
                    
                    // Totales
                    'nota_empresa_calculada' => $notasEmpresa[$id] ?? '-',
                    'final'       => $notasFinales[$id] ?? '-'
                ];
            }

            // Inyectamos el paquete de notas en el objeto alumno
            $usuarioAlumno->notas_calculadas = $packNotas;

            return $usuarioAlumno;
        });

        return response()->json([
            'grado' => $grado,
            'alumnos' => $alumnos,
            'asignaturas' => $asignaturas
        ]);
    }
}
