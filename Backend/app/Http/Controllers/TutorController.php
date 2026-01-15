<?php

namespace App\Http\Controllers;

use App\Models\Tutor;

class TutorController extends Controller
{
    // GET /api/tutor/{id}/grados
    public function grados($id)
    {
        return Tutor::findOrFail($id)->grados;
    }
}
