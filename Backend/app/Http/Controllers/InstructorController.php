<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function getCompanyInstructor($cif){
        $instructores = Instructor::with('user')->where('CIF_Empresa',$cif)->get();
        return response()->json($instructores);
    }
     public function crearInstructor(Request $request)
    {
        // Validamos los datos básicos
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => ['required','email','max:255','unique:users,email'],
            'n_tel' => ['nullable','string','regex:/^[0-9]{9}$/','unique:users,n_tel'],
            'password' => 'required|string|min:6',
            'CIF_Empresa' => 'required|string|exists:empresa,CIF',
        ], [
            'nombre.required' => 'El nombre del instructor es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'apellidos.max' => 'Los apellidos no pueden superar los 255 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Debes introducir un email válido.',
            'email.unique' => 'Este email ya está registrado.',
            'n_tel.regex' => 'El número de teléfono debe tener exactamente 9 dígitos.',
            'n_tel.unique' => 'Este número de teléfono ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'CIF_Empresa.required' => 'Debes seleccionar una empresa.',
            'CIF_Empresa.exists' => 'La empresa seleccionada no existe.',
        ]);



        // Creamos el usuario
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? null,
            'email' => $data['email'],
            'n_tel' => $data['n_tel'] ?? null,
            'password' => $data['password'],
            'tipo' => 'instructor',
        ]);

        $user->load('instructor');

        // Asignamos la empresa al instructor recién creado
        $instructor = $user->instructor;

        if ($instructor) {
            $instructor->CIF_Empresa = $data['CIF_Empresa'];
            $instructor->save();
        }


        return response()->json([
            'message' => 'Instructor creado correctamente',
            'instructor' => $user->instructor()->with('user')->first()
        ], 201);
    }
}
