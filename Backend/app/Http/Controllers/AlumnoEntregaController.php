<?php

namespace App\Http\Controllers;

use App\Models\AlumnoEntrega;
use Illuminate\Http\Request;

class AlumnoEntregaController extends Controller
{
    public function entregarCuaderno(Request $request)
    {
        // Si recibes archivo PDF desde un form-data
        $urlCuaderno = $request->URL_Cuaderno;

        //Para subir archivo real:
        if($request->hasFile('cuaderno')){
            $path = $request->file('cuaderno')->store('cuadernos', 'public');
            $urlCuaderno = asset('storage/'.$path);
        }

        return AlumnoEntrega::updateOrCreate(
            [
                'ID_Alumno' => $request->ID_Alumno,
                'ID_Entrega' => $request->ID_Entrega
            ],
            [
                'URL_Cuaderno' => $urlCuaderno,
                'Fecha_Entrega' => now()
            ]
        );
    }
}
