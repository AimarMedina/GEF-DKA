<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function alumnosDeTutor(Request $request, int $id)
    {
        $usuario= $request->user();
        //  if($usuario->tipo =='tutor'){
        $perPage = (int) $request->query('per_page', 10);

        $alumnos = Alumno::query()
            ->where('id_tutor', $id)
            ->with([
                'usuario:id,nombre,apellidos,email,tipo',
                'grado:id,nombre',
                'estanciaAlumno:id,ID_Alumno'
            ])
            ->paginate($perPage);

        
        $alumnos->getCollection()->transform(function ($a) {
            return [
               'ID_Usuario' => $a->ID_Usuario,
                'Nombre'     => $a->usuario?->nombre,
                'Apellidos'  => $a->usuario?->apellidos,
                'Email'      => $a->usuario?->email,
                'Tipo'      =>  $a->usuario?->tipo,
                'Grado'      => $a->grado?->nombre,
                'Tiene_estancia'=> $a->estanciaAlumno !==null,
                'estancia_id'   =>$a->estanciaAlumno?->id
            ];
        });

        return response()->json([
            'data' => $alumnos
        ]);
    //  } else{
    //      echo "Error";
    //  }
    }

    public function alumnosDeInstructor(Request $request, int $id)
    {
        $perPage = (int) $request->query('per_page', 10);

        $alumnos = Alumno::query()
            ->where('id_instructor', $id)
            //Nombre de las funciones de las relaciones en el modelo de alumno
            ->with([
                'usuario:id,nombre,apellidos,email,tipo',
                'grado:id,nombre',
                'estanciaAlumno:id,ID_Alumno'
            ])
            ->paginate($perPage);

        $alumnos->getCollection()->transform(function ($a) {
            return [
                'ID_Usuario' => $a->ID_Usuario,
                'Nombre'     => $a->usuario?->nombre,
                'Apellidos'  => $a->usuario?->apellidos,
                'Email'      => $a->usuario?->email,
                'Grado'      => $a->grado?->nombre,
                'Tiene_estancia'=> $a->estanciaAlumno !==null,
                'estancia_id'   =>$a->estanciaAlumno?->id
            ];
        });

        return response()->json([
            'data' => $alumnos
        ]);
    }
}
