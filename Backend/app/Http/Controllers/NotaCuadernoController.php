<?php

namespace App\Http\Controllers;

use App\Models\NotaCuaderno;
use Illuminate\Http\Request;

class NotaCuadernoController extends Controller
{
    public function notaCuaderno(Request $request)
    {
        return NotaCuaderno::updateOrCreate(
            ['ID_Cuaderno' => $request->ID_Cuaderno],
            [
                'ID_Tutor' => auth()->id(),
                'Fecha' => now(),
                'Nota' => $request->Nota
            ]
        );
    }
      
}
