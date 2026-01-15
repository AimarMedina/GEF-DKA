<?php

namespace App\Http\Controllers;

use App\Models\NotaCuaderno;
use Illuminate\Http\Request;

class NotaCuadernoController extends Controller
{
     // POST /api/nota-cuaderno
    public function notaCuaderno(Request $request)
    {
        $request->validate([
            'ID_Cuaderno' => 'required',
            'Nota' => 'required|numeric|min:0|max:10'
        ]);

        NotaCuaderno::updateOrCreate(
            ['ID_Cuaderno' => $request->ID_Cuaderno],
            [
                'Nota' => $request->Nota,
                'Fecha' => now()
            ]
        );

        return response()->json(['message' => 'Nota guardada']);
    }
      
}
