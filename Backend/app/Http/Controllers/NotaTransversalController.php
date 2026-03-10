<?php

namespace App\Http\Controllers;
use App\Models\NotaTransversal;

use Illuminate\Http\Request;

class NotaTransversalController extends Controller
{
    public function update(Request $request, $id){
        $nota = NotaTransversal::findOrFail($id);

        $validated = $request->validate([
            'nota'       => 'required|numeric|min:0|max:10',
            'comentario' => 'nullable|string',
        ]);

        $nota->update($validated);

        return response()->json([
            'message' => 'Nota transversal actualizada',
            'data'    => $nota
        ]);
    }

}
