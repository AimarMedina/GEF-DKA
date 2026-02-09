<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller{
    private function validateRequest(Request $req) {

        return $req->validate([
            'Nombre' => 'required|string|max:255',
            'Direccion' => 'nullable|string|max:255',
            'CIF' => ['required', 'string', 'max:20', 'unique:empresa,CIF'],
            'Email' => ['nullable', 'email', 'max:255', 'unique:empresa,Email'],
            'N_Tel' => ['nullable', 'string', 'regex:/^[0-9]{9}$/', 'unique:empresa,N_Tel'],
        ], [
            'Nombre.required' => 'El nombre de la empresa es obligatorio.',
            'Nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'Direccion.max' => 'La dirección no puede superar los 255 caracteres.',
            'CIF.required' => 'El CIF es obligatorio.',
            'CIF.max' => 'El CIF no puede superar los 20 caracteres.',
            'CIF.unique' => 'Este CIF ya está registrado.',
            'Email.email' => 'El email no tiene un formato válido.',
            'Email.unique' => 'El email ya está registrado.',
            'N_Tel.regex' => 'El teléfono debe tener exactamente 9 dígitos.',
            'N_Tel.unique' => 'El teléfono ya está registrado.',
        ]);
    }

    public function getInstructores($cif){
        $empresa = Empresa::where('CIF', $cif)->firstOrFail();

        $instructores = $empresa->instructores()
            ->whereNotNull('ID_Usuario') // asegurarse de que tengan usuario
            // Cargar campos necesarios del usuario para la vista (nombre, apellidos, email, telefono)
            ->with('user:id,nombre,apellidos,email,n_tel') // carga el usuario con más campos
            ->get()
            ->filter(fn($i) => $i->user !== null); // opcional, por seguridad

        return response()->json([
            'data' => $instructores
        ]);
    }





    public function index(){
        // Traemos todas las empresas con solo los campos necesarios
        $empresas = Empresa::all(['CIF', 'Nombre']);

        // Retornamos en formato JSON
        return response()->json([
            'data' => $empresas
        ]);
    }






   public function getCompanys(Request $req)
{
        $q = trim((string) $req->query('q', ''));

        $perPage = $req->query('per_page', 5);

        $query = Empresa::query();

        if ($q !== '') {
            $query->where(function ($u) use ($q) {
                $u->where('CIF', 'like', "%{$q}%")
                    ->orWhere('Nombre', 'like', "%{$q}%")
                    ->orWhere('Direccion', 'like', "%{$q}%")
                    ->orWhere('Email', 'like', "%{$q}%")
                    ->orWhere('N_Tel', 'like', "%{$q}%");
            });
        }

        $empresas = $query->paginate($perPage);

        return response()->json($empresas);
    }
    public function create(Request $req){
        $data = $this->validateRequest($req);

        Empresa::create([
            'CIF' => $data['CIF'],
            'Direccion' => $data['Direccion'],
            'Nombre' => $data['Nombre'],
            'N_Tel' => $data['N_Tel'],
            'Email' => $data['Email'],

        ]);

        return response()->json($data, 201);
    }

}
