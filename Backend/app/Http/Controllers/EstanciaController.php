<?php

namespace App\Http\Controllers;

use App\Models\EstanciaAlumno;
use App\Models\Alumno;

use App\Models\Horario;

use App\Models\Competencia;

use Illuminate\Http\Request;

class EstanciaController extends Controller
{
    public function getEstancias()
    {
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
        $alumno = Alumno::with(
            'estanciaActual.empresa',
            'estanciaActual.horario',
            'estanciaActual.alumno.tutor.user',
            'estanciaActual.alumno.instructor.user'
        )
            ->findOrFail($idAlumno);

        return response()->json($alumno->estanciaActual);
    }

    public function getCompanyAlumnos($CIF)
    {
        $estanciasEmpresa = EstanciaAlumno::with(['alumno.usuario', 'alumno.instructor.user'])->where('CIF_Empresa', $CIF)->get();
        return response()->json($estanciasEmpresa);
    }


    public function asignarEstancia(Request $request)
    {
        $data = $request->validate([
            'ID_Alumno' => 'required|exists:Alumno,ID_Usuario',
            'CIF_Empresa' => 'required|exists:Empresa,CIF',
            'Fecha_inicio' => 'required|date',
            'Fecha_fin' => 'required|date',
            'ID_Instructor' => 'nullable|exists:Instructor,ID_Usuario',

            'horarios' => 'required|array|min:1',
            'horarios.*.Dia' => 'required|string',
            'horarios.*.Horario1' => 'required|string',
            'horarios.*.Horario2' => 'nullable|string',
        ]);

        // Crear estancia
        $estancia = EstanciaAlumno::create([
            'ID_Alumno'    => $data['ID_Alumno'],
            'CIF_Empresa'  => $data['CIF_Empresa'],
            'Fecha_inicio' => $data['Fecha_inicio'],
            'Fecha_fin'    => $data['Fecha_fin'],
        ]);

        // Actualizar ID_Instructor en la tabla Alumno si viene
        if (!empty($data['ID_Instructor'])) {
            Alumno::where('ID_Usuario', $data['ID_Alumno'])
                ->update(['ID_Instructor' => $data['ID_Instructor']]);
        }

        // Agrupar días por horarios iguales
        $grupos = [];
        foreach ($data['horarios'] as $h) {
            if (!$h['Horario1'] && !$h['Horario2']) continue; // saltar si no hay horarios

            $key = $h['Horario1'] . '|' . ($h['Horario2'] ?? '');
            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'Dias' => [],
                    'Horario1' => $h['Horario1'],
                    'Horario2' => $h['Horario2'] ?? null,
                ];
            }
            $grupos[$key]['Dias'][] = $h['Dia'];
        }

        // Insertar cada grupo en BD
        foreach ($grupos as $g) {
            Horario::create([
                'ID_Estancia' => $estancia->id,
                'Dias'        => implode('-', array_map(function ($d) {
                    return match ($d) {
                        'Lunes' => 'L',
                        'Martes' => 'M',
                        'Miércoles' => 'X',
                        'Jueves' => 'J',
                        'Viernes' => 'V',
                        default => ''
                    };
                }, $g['Dias'])),
                'Horario1'    => $g['Horario1'],
                'Horario2'    => $g['Horario2'],
            ]);
        }

        return response()->json($estancia->load('horario'), 201);
    }

    public function competencias(int $id, Request $req)
    {
        $idAlumno = $req->ID_Alumno;

        $competencias = EstanciaAlumno::with([
            'competencias:id,descripcion',
            'competencias.notas' => function ($query) use ($idAlumno) {
                $query->where('ID_Alumno', $idAlumno); // filtra solo las notas del alumno
            }
        ])->findOrFail($id);

        return response()->json($competencias);
    }
}
