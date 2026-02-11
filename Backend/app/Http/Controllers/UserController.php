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
    /**
     * Cargar relaciones según tipo de usuario
     */
    private function cargarRelaciones(User $user)
    {
        switch ($user->tipo) {
            case 'alumno':
                $user->load([
                    'alumno.grado',
                    'alumno.tutor.user',
                    'alumno.instructor.user',
                    'alumno.estanciaActual.empresa'
                ]);
                break;
            case 'tutor':
                $user->load(['tutor']);
                break;
            case 'instructor':
                $user->load(['instructor.empresa']);
                break;
        }
        
        return $user;
    }

    /**
     * Verificar si el tutor tiene grado asignado
     */
    private function checkEsTutor($user)
    {
        if ($user->tipo === 'tutor') {
            $existe = DB::table('grado')
                ->where('id_tutor', $user->id)
                ->exists();
            $user->es_tutor = $existe;
        } else {
            $user->es_tutor = false;
        }
        return $user;
    }

    /**
     * LOGIN
     */
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
            ], 401);
        }

        $user = Auth::user();
        
        // Eliminar tokens antiguos
        $user->tokens()->delete();
        
        // Crear nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Cargar relaciones
        $user = $this->cargarRelaciones($user);
        $user = $this->checkEsTutor($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * AUTH - Usuario autenticado
     */
    public function auth(Request $req)
    {
        $user = $req->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        // Cargar relaciones
        $user = $this->cargarRelaciones($user);
        $user = $this->checkEsTutor($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticado',
            'user' => $user
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

    /**
     * CAMBIAR CONTRASEÑA
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    /**
     * LISTAR USUARIOS
     */
    public function getUsers(Request $req)
    {
        $perPage = $req->get('per_page', 5);
        $query = User::query()->orderBy('id');

        if ($req->filled('tipo')) {
            $query->where('tipo', $req->tipo);
        }

        if ($req->filled('id_grado')) {
            $query->whereHas('alumno', function ($q) use ($req) {
                $q->where('ID_Grado', $req->id_grado);
            });
        }

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
            $query->with(['instructor.empresa']);
        }

        $usuarios = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $usuarios
        ]);
    }

    /**
     * CREAR USUARIO
     */
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
            'password' => Hash::make($data['password']),
            'tipo' => $data['tipo'],
        ]);

        if ($req->tipo === "alumno" && isset($data['id_grado'])) {
            $user->alumno()->updateOrCreate([], ['ID_Grado' => $data['id_grado']]);
        }

        return response()->json([
            'message' => "{$data['tipo']} creado correctamente",
            'usuario' => $user
        ], 201);
    }

    /**
     * ACTUALIZAR USUARIO
     */
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
            'password' => isset($data['password']) ? Hash::make($data['password']) : $user->password,
            'tipo' => $data['tipo'] ?? $user->tipo,
        ]);

        if ($user->tipo === "alumno" && isset($data['id_grado'])) {
            $user->alumno()->updateOrCreate([], ['ID_Grado' => $data['id_grado']]);
        }

        return response()->json([
            'message' => "Usuario actualizado correctamente",
            'usuario' => $user
        ]);
    }

    /**
     * ELIMINAR USUARIO
     */
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($user->tipo === 'instructor') {
            Alumno::where('ID_Instructor', $user->id)
                ->update(['ID_Instructor' => null]);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
    
    public function getUser($id)
    {
        return User::find($id);
    }
}