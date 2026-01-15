<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
   public function alumnosDeTutor(Request $request, int $id)
{
    $perPage = (int) $request->query('per_page', 10);
    $q = trim((string) $request->query('q', ''));

    $query = Alumno::query()->where('id_tutor', $id);

    if ($q !== '') {
        $query->whereHas('usuario', function ($u) use ($q) {
            $u->where('nombre', 'like', "%{$q}%")
              ->orWhere('apellidos', 'like', "%{$q}%")
              ->orWhere('email', 'like', "%{$q}%");
        });
    }
        $alumnos = $query
            ->with([
                'usuario:id,nombre,apellidos,email,tipo',
                'grado:id,nombre',
                'estanciaActual:id,ID_Alumno'
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
                'Tiene_estancia'=> $a->estanciaActual !==null,
                'estancia_id'   =>$a->estanciaActual?->id
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

        $q = trim((string) $request->query('q', ''));

         $query = Alumno::query()->where('id_instructor', $id);

        if ($q !== '') {
            $query->whereHas('usuario', function ($u) use ($q) {
                $u->where('nombre', 'like', "%{$q}%")
                ->orWhere('apellidos', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
            });
      }

        $alumnos = $query
            ->with([
                'usuario:id,nombre,apellidos,email,tipo',
                'grado:id,nombre',
                'estanciaActual:id,ID_Alumno'
            ])
            ->paginate($perPage);

        $alumnos->getCollection()->transform(function ($a) {
            return [
                'ID_Usuario' => $a->ID_Usuario,
                'Nombre'     => $a->usuario?->nombre,
                'Apellidos'  => $a->usuario?->apellidos,
                'Email'      => $a->usuario?->email,
                'Grado'      => $a->grado?->nombre,
                'Tiene_estancia'=> $a->estanciaActual !==null,
                'estancia_id'   =>$a->estanciaActual?->id
            ];
        });

        return response()->json([
            'data' => $alumnos
        ]);
    }
}
