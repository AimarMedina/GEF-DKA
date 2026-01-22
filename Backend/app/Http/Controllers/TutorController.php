<?php

namespace App\Http\Controllers;

use App\Models\Tutor;

class TutorController extends Controller
{
    public function grados($id)
    {
        return Tutor::findOrFail($id)->grados;
    }
}
