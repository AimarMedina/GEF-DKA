<?php

namespace App\Http\Services;

use App\Models\Asignatura;
use App\Models\EstanciaAlumno; 
use App\Models\NotaCuaderno; 
use App\Models\NotaTransversal; 
use App\Models\NotaCompetencia; 
use App\Models\NotaEgibide; 
use App\Models\Ra;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;

class NotasAlumnoService
{

   public function obtenerNotaCuaderno($idAlumno)
    {
        $nota = NotaCuaderno::where('ID_Alumno', $idAlumno)
                    ->value('Nota'); 

        return $nota ? (float)$nota : 0;
    }

  
    public function obtenerNotaTransversal($idAlumno){
        $media = NotaTransversal::where('ID_Alumno', $idAlumno)->avg('Nota');

        if (!$media) {
            return 0;
        }
        return round($media * 2.5, 2); //2.5 porque 4/10 da eso para hacerlo sobre 10 y 2 decimales
    }

       public function obtenerNotasCompetenciasTecnicas($idAlumno) {
        $notas = NotaCompetencia::where('ID_Alumno', $idAlumno)
                    ->pluck('Nota', 'ID_Competencia');
        
        return $notas;
    }
    /**
     * Calcula la nota técnica de cada asignatura.
     * LOGICA: 
     * 1. Busca los RAs de la asignatura.
     * 2. Calcula la media de cada RA (basado en sus competencias).
     * 3. Calcula la media de la asignatura (basado en sus RAs).
     * 4. Si la asignatura NO tiene nota técnica, se le asigna la Nota Transversal.
     */
    public function obtenerNotaTecnicaPorAsignatura($idAlumno, $asignaturas)
    {
        // 1. Obtener la Nota Transversal (el "comodín")
        // Llamamos a la función que ya creamos antes
        $notaTransversal = $this->obtenerNotaTransversal($idAlumno);

        // 2. Obtener todas las notas de competencias técnicas del alumno de golpe
        // Devuelve un array [ID_Competencia => Nota] (ej: [10 => 3, 11 => 4])
        // Asegúrate de importar el modelo NotaCompetencia arriba
        $notasCompetenciasAlumno = NotaCompetencia::where('ID_Alumno', $idAlumno)
                                    ->pluck('Nota', 'ID_Competencia');

        $notasPorAsignatura = [];

        foreach ($asignaturas as $asig) {
            
            // A. Buscar los RAs de esta asignatura
            $ras = Ra::where('ID_Asignatura', $asig->id)->get();
            
            $mediasDeLosRAs = [];

            foreach ($ras as $ra) {
                // B. Buscar qué competencias tiene este RA en la tabla pivote 'comp_ra'
                $idsCompetenciasDelRa = \Illuminate\Support\Facades\DB::table('comp_ra')
                                            ->where('ID_Ra', $ra->id)
                                            ->pluck('ID_Comp');
                
                // C. Ver cuáles de esas competencias han sido evaluadas
                $notasDelRa = [];
                foreach ($idsCompetenciasDelRa as $idComp) {
                    if (isset($notasCompetenciasAlumno[$idComp])) {
                        $notasDelRa[] = $notasCompetenciasAlumno[$idComp];
                    }
                }

                // D. Si este RA tiene competencias evaluadas, hacemos su media (sobre 4)
                if (count($notasDelRa) > 0) {
                    $mediaRa = array_sum($notasDelRa) / count($notasDelRa);
                    $mediasDeLosRAs[] = $mediaRa;
                }
            }

            // --- CÁLCULO FINAL DE LA ASIGNATURA ---
            
            if (count($mediasDeLosRAs) > 0) {
                // CASO 1: Hay RAs evaluados. Hacemos la media y convertimos a base 10.
                $mediaAsignaturaSobre4 = array_sum($mediasDeLosRAs) / count($mediasDeLosRAs);
                $notaTecnicaFinal = $mediaAsignaturaSobre4 * 2.5; 
                
                // Guardamos la nota calculada
                $notasPorAsignatura[$asig->id] = round($notaTecnicaFinal, 2);
            } else {
                // CASO 2: No hay RAs evaluados (vacío).
                // AQUI APLICAMOS TU REGLA: Se asigna la nota de transversales.
                $notasPorAsignatura[$asig->id] = $notaTransversal;
            }
        }

        return $notasPorAsignatura;
    }

 /**
     * Calcula la Nota Final de Empresa para cada asignatura.
     * FÓRMULA:
     * - 20% Nota Cuaderno (Global)
     * - 20% Nota Transversal (Global)
     * - 60% Nota Técnica (Específica de la asignatura)
     * * @param float $notaCuaderno
     * @param float $notaTransversal
     * @param array $notasTecnicas Array [ID_Asignatura => NotaTecnica]
     * @return array [ID_Asignatura => NotaEmpresa]
     */
    public function calcularNotaFinalEmpresa($notaCuaderno, $notaTransversal, $notasTecnicas)
    {
        $notasFinalesEmpresa = [];

        // Recorremos las notas técnicas (que ya están calculadas por asignatura)
        foreach ($notasTecnicas as $idAsignatura => $notaTecnica) {
            
            // Aplicamos la fórmula ponderada
            $calculo = ($notaCuaderno * 0.20) + 
                       ($notaTransversal * 0.20) + 
                       ($notaTecnica * 0.60);

            // Guardamos el resultado redondeado a 2 decimales
            $notasFinalesEmpresa[$idAsignatura] = round($calculo, 2);
        }

        return $notasFinalesEmpresa;
    }

    public function obtenerNotasEgibide($idAlumno)
    {
        $notasEgibide = NotaEgibide::where('ID_Alumno', $idAlumno)
                            ->pluck('nota', 'ID_Asignatura');

        return $notasEgibide;
    }

    public function calcularNotasFinalesPorAsignatura($notasEmpresa, $notasEgibide)
    {
        $notasFinales = [];

        // Recorremos las asignaturas presentes en el array de notas de empresa
        // (Asumimos que notasEmpresa tiene todas las asignaturas del grado calculadas)
        foreach ($notasEmpresa as $idAsignatura => $notaEmpresa) {
            
            // Verificamos si existe nota de Egibide para esta asignatura
            if (isset($notasEgibide[$idAsignatura]) && is_numeric($notasEgibide[$idAsignatura])) {
                
                $notaTutor = $notasEgibide[$idAsignatura];

                // FÓRMULA FINAL
                // 0.2 (20%) de la Nota Empresa + 0.8 (80%) de la Nota Egibide
                $calculoFinal = ($notaEmpresa * 0.20) + ($notaTutor * 0.80);

                $notasFinales[$idAsignatura] = round($calculoFinal, 2);
            } else {
                // Si falta la nota de Egibide, no se puede calcular la final.
                // Devolvemos null o un guion para indicarlo en el frontend.
                $notasFinales[$idAsignatura] = null; 
            }
        }

        return $notasFinales;
    }
    /**
     * Devuelve un array con todas las notas calculadas para un alumno
     */
    // public function obtenerNotasDelAlumno($idAlumno, $idGrado)
    // {
    //     // 1. Buscar la estancia activa del alumno
    //     // Ajusta esto según tu lógica de estancias (ej: la del año actual)
    //     $estancia = EstanciaAlumno::where('ID_Alumno', $idAlumno)->first();

    //     if (!$estancia) {
    //         return []; // Si no tiene estancia, no tiene notas de empresa
    //     }

    //     // --- A. NOTAS COMUNES (Transversal y Cuaderno) ---

    //     // 1. Nota Cuaderno (Suponiendo que se guarda en la tabla estancia o una tabla notas_estancia)
    //     $notaCuaderno = $estancia->nota_cuaderno ?? 0; // Valor 0-10

    //     // 2. Nota Transversal (Calculada de las competencias transversales evaluadas 1-4)
    //     $notaTransversal = $this->calcularMediaTransversal($estancia->id);

    //     // --- B. NOTAS POR ASIGNATURA ---

    //     $asignaturas = Asignatura::where('ID_Grado', $idGrado)->get();
    //     $notasPorAsignatura = [];

    //     foreach ($asignaturas as $asig) {

    //         // 1. Nota Técnica (Calculada compleja RA -> Competencias)
    //         $notaTecnica = $this->calcularNotaTecnica($asig->id, $estancia->id, $notaTransversal);

    //         // 2. Nota Egibide (Directa de BD, puesta por el tutor)
    //         // Suponemos una tabla 'calificaciones_centro' o similar
    //         $notaEgibide = DB::table('nota_asignatura') // <--- CAMBIA ESTO POR TU TABLA REAL
    //             ->where('ID_Alumno', $idAlumno)
    //             ->where('ID_Asignatura', $asig->id)
    //             ->value('nota_egibide') ?? null; // Null si aún no se ha puesto

    //         $notasPorAsignatura[$asig->id] = [
    //             'tecnica'     => round($notaTecnica, 2),
    //             'transversal' => round($notaTransversal, 2), // Es la misma para todas
    //             'cuaderno'    => round($notaCuaderno, 2),    // Es la misma para todas
    //             'egibide'     => $notaEgibide !== null ? round($notaEgibide, 2) : '-',

    //             // Cálculo provisional de la final (si tienes los datos)
    //             // (Tecnica*0.6 + Trans*0.2 + Cuaderno*0.2)*0.2 + Egibide*0.8
    //             'final'       => '-' // Lo dejaremos para el siguiente paso si quieres
    //         ];
    //     }

    //     return $notasPorAsignatura;
    // }

    // --- FUNCIONES AUXILIARES ---

    // private function calcularMediaTransversal($idEstancia)
    // {
    //     // Buscamos competencias transversales evaluadas en esta estancia
    //     // Suponemos tabla 'evaluacion_competencia' con ID_Estancia, ID_Competencia, Nota (1-4)
    //     // Y que las competencias transversales tienen un flag o IDs específicos.
    //     // AQUI ASUMO que en tu tabla competencia hay un campo 'tipo'='transversal'

    //     $medias = DB::table('evaluacion_competencia')
    //         ->join('competencia', 'evaluacion_competencia.ID_Competencia', '=', 'competencia.id')
    //         ->where('evaluacion_competencia.ID_Estancia', $idEstancia)
    //         ->where('competencia.tipo', 'transversal') // <--- AJUSTA ESTO
    //         ->avg('evaluacion_competencia.nota'); // Media 1-4

    //     // Si no hay notas, devolvemos 0
    //     if (!$medias) return 0;

    //     // Convertimos escala 1-4 a 0-10
    //     return $medias * 2.5;
    // }

    // private function calcularNotaTecnica($idAsignatura, $idEstancia, $notaTransversalFallback)
    // {
    //     // 1. Obtener RAs de la asignatura
    //     $ras = Ra::where('ID_Asignatura', $idAsignatura)->get();

    //     $notasDeRAs = [];

    //     foreach ($ras as $ra) {
    //         // Obtener competencias técnicas vinculadas a este RA (Tabla pivote comp_ra)
    //         $competenciasIds = DB::table('comp_ra')
    //             ->where('ID_Ra', $ra->id)
    //             ->pluck('ID_Comp');

    //         // Obtener las notas que el instructor ha puesto a esas competencias
    //         $notas = DB::table('evaluacion_competencia')
    //             ->where('ID_Estancia', $idEstancia)
    //             ->whereIn('ID_Competencia', $competenciasIds)
    //             ->pluck('nota'); // Colección de notas 1-4

    //         if ($notas->count() > 0) {
    //             // Media de las competencias de ESTE RA
    //             $notasDeRAs[] = $notas->avg();
    //         }
    //     }

    //     // 2. Calcular media del módulo
    //     if (count($notasDeRAs) > 0) {
    //         $mediaModulo4 = collect($notasDeRAs)->avg(); // Media sobre 4
    //         return $mediaModulo4 * 2.5; // Conversión a sobre 10
    //     } else {
    //         // Si no hay competencias técnicas evaluadas para esta asignatura,
    //         // HEREDA la nota transversal
    //         return $notaTransversalFallback;
    //     }
    // }
}
