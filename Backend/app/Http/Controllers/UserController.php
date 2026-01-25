<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    private function checkEsTutor($user)
    {
        // Solo hacemos la comprobación si el usuario es de tipo 'tutor'
        if ($user->tipo === 'tutor') {

            // CORRECCIÓN: Buscamos en la tabla 'grado' por la columna 'ID_Tutor'
            $existe = DB::table('grado')
                ->where('id_tutor', $user->id)
                ->exists(); // Devuelve true si encuentra al menos uno

            // Añadimos la propiedad al objeto usuario para el frontend
            $user->es_tutor = $existe;
        } else {
            // Si no es tutor (es alumno, admin, etc), por defecto false (o lo que prefieras)
            $user->es_tutor = false;
        }
        return $user;
    }

    public function auth(Request $req)
    {
        $userAuth = $req->user();
        if (!$userAuth) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }
        $userAuth = $this->checkEsTutor($userAuth);
        return response()->json([
            'status' => 'success',
            'message' => 'Autenticado',
            'user' => $userAuth
        ]);
    }
    public function getUser($id)
    {
        return User::find($id);
    }

    public function login(Request $req)
    {
        $credentials = $req->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas',
            ], 200);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $user = $this->checkEsTutor($user);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

  public function getUsers(Request $req)
    {
        $perPage = $req->get('per_page', 5);

        $query = User::query()->orderBy('id');

        // 1. Filtrar por tipo
        if ($req->filled('tipo')) {
            $query->where('tipo', $req->tipo);
        }

        // 2. Filtrar por id_grado si es alumno
        if ($req->filled('id_grado')) {
            $query->whereHas('alumno', function ($q) use ($req) {
                $q->where('ID_Grado', $req->id_grado);
            });
        }

        // 3. NUEVO: Buscador por texto (Nombre, Apellidos o Email)
        if ($req->filled('search')) {
            $search = $req->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellidos', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

       
        if ($req->tipo === 'alumno') {
            $query->with(['alumno.grado']);
        } elseif ($req->tipo === 'instructor') {
            $query->with(['instructor.empresa']); //Si es instructor buscamos la empresa para facilitar la busqyeda al admin
        }

        $usuarios = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $usuarios
        ]);
    }


    // Crear usuario
    public function create(Request $req)
    {
        $data = $req->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'n_tel' => ['nullable', 'string', 'regex:/^[0-9]{9}$/', 'unique:users,n_tel'],
            'password' => 'required|string|min:6',
            'tipo' => 'required|string|in:alumno,tutor,instructor,admin',
            'id_grado' => 'nullable|exists:grado,id',
        ]);

        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? null,
            'email' => $data['email'],
            'n_tel' => $data['n_tel'] ?? null,
            'password' => bcrypt($data['password']),
            'tipo' => $data['tipo'],
        ]);

        if ($req->tipo === "alumno" && isset($data['id_grado'])) {
            $user->alumno()->updateOrCreate([], ['ID_Grado' => $data['id_grado']]);
        }

        return response()->json(['message' => "{$data['tipo']} creado correctamente", 'usuario' => $user], 201);
    }

    // Actualizar usuario
    public function update(Request $req, $id)
    {
        $user = User::findOrFail($id);

        $data = $req->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $id],
            'n_tel' => ['nullable', 'string', 'regex:/^[0-9]{9}$/', 'unique:users,n_tel,' . $id],
            'password' => 'nullable|string|min:6',
            'tipo' => 'sometimes|required|string|in:alumno,tutor,instructor,admin',
            'id_grado' => 'nullable|exists:grado,id',
        ]);

        $user->update([
            'nombre' => $data['nombre'] ?? $user->nombre,
            'apellidos' => $data['apellidos'] ?? $user->apellidos,
            'email' => $data['email'] ?? $user->email,
            'n_tel' => $data['n_tel'] ?? $user->n_tel,
            'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
            'tipo' => $data['tipo'] ?? $user->tipo,
        ]);

        if ($user->tipo === "alumno" && isset($data['id_grado'])) {
            $user->alumno()->updateOrCreate([], ['ID_Grado' => $data['id_grado']]);
        }

        return response()->json(['message' => "Usuario actualizado correctamente", 'usuario' => $user]);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // 1. Verificar que la contraseña actual sea correcta
        // Usamos Hash::check para comparar texto plano con el hash guardado
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        // 2. Actualizar la contraseña usando el helper bcrypt()
        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    public function delete($id)
    {
        // Buscamos al usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Si es un instructor, revisar alumnos
        if ($user->tipo === 'instructor') {
            // Poner a null el ID_Instructor en los alumnos asociados
            Alumno::where('ID_Instructor', $user->id)
                ->update(['ID_Instructor' => null]);
        }

        // Borrar el usuario
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

}


