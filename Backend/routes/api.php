<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoEntregaController;
use App\Http\Controllers\EntregaCuadernoController;
use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\NotaCuadernoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotasEmpresaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth', [UserController::class, 'auth'])->middleware('auth:sanctum');
Route::get('/users', [UserController::class, 'getUsers']);
Route::post('/user/create', [UserController::class, 'create']);

/*
|--------------------------------------------------------------------------
| Empresas
|--------------------------------------------------------------------------
*/
Route::get('/empresas', [EmpresaController::class, 'getCompanys']);
Route::post('/empresa/create', [EmpresaController::class, 'create']);

/*
|--------------------------------------------------------------------------
| Instructores
|--------------------------------------------------------------------------
*/
Route::get('/empresa/{cif}/instructores', [InstructorController::class, 'getCompanyInstructor']);
Route::post('/empresa/instructor/create', [InstructorController::class, 'crearInstructor']);

/*
|--------------------------------------------------------------------------
| Tutores y Alumnos
|--------------------------------------------------------------------------
*/
Route::get('/tutores/{id}/alumnos', [AlumnoController::class, 'alumnosDeTutor'])-> middleware('auth:sanctum');
Route::get('/instructores/{id}/alumnos', [AlumnoController::class, 'alumnosDeInstructor']) ->middleware('auth:sanctum');

Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);// Tutor
Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']);// Alumno
Route::get('/empresa/{cif}/alumnos', [EstanciaController::class, 'getCompanyAlumnos']);


//Cuaderno
Route::get('/alumno/{id}', [AlumnoController::class, 'getGrado']);
Route::get('/entregas/alumno/{id}', [EntregaCuadernoController::class, 'entregasAlumno'])->middleware('auth:sanctum');
Route::post('/entregas', [EntregaCuadernoController::class, 'crearEntregaCuaderno']); // tutor
Route::get('/grado/{id}/entregas', [EntregaCuadernoController::class, 'porGrado']);
Route::post('/entregarCuaderno/alumno/{id}', [AlumnoEntregaController::class, 'entregarCuaderno']);
Route::post('/nota-cuaderno', [NotaCuadernoController::class, 'notaCuaderno']);
Route::post('/observacionesCuadernoAlumno', [NotaCuadernoController::class, 'observacionesCuadernoAlumno']);
Route::get('/grados',[GradoController::class,'getGrados']);
Route::get('/alumno/entregas/descargar/{id}', [AlumnoEntregaController::class, 'descargarCuaderno']);


Route::get('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'misNotas'])->middleware('auth:sanctum');
Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store'])->middleware('auth:sanctum');

Route::get('/tutor/{id}/grados', [TutorController::class, 'grados']);

/*
|--------------------------------------------------------------------------
| Estancias
|--------------------------------------------------------------------------
*/
// Para tutor
Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);
// Para alumno
Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']);
Route::get('/empresa/{cif}/alumnos', [EstanciaController::class, 'getCompanyAlumnos']);

/*
|--------------------------------------------------------------------------
| Cuadernos y Entregas
|--------------------------------------------------------------------------
*/

// Obtener grado del alumno
Route::get('/alumno/{id}', [AlumnoController::class, 'getGrado']);
// Entregas de un alumno
Route::get('/entregas/alumno/{id}', [EntregaCuadernoController::class, 'entregasAlumno'])->middleware('auth:sanctum');
// Crear nueva entrega (tutor)
Route::post('/entregas', [EntregaCuadernoController::class, 'crearEntregaCuaderno']);
// Entregas por grado
Route::get('/grado/{id}/entregas', [EntregaCuadernoController::class, 'porGrado']);
// Subir cuaderno del alumno
Route::post('/entregarCuaderno/alumno/{id}', [AlumnoEntregaController::class, 'entregarCuaderno']);
// Descargar cuaderno del alumno
Route::get('/alumno/entregas/descargar/{id}', [AlumnoEntregaController::class, 'descargarCuaderno']);

/*
|--------------------------------------------------------------------------
| Notas
|--------------------------------------------------------------------------
*/
// Notas de alumno
Route::get('/alumno/{id}/mis-notas', [AlumnoController::class, 'misNotas']);
// Notas por alumno (empresa)
Route::get('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'show'])->middleware('auth:sanctum');
Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Grados
|--------------------------------------------------------------------------
*/
Route::get('/grados', [GradoController::class, 'getGrados']);
Route::get('/grados/{id}/asignaturas', [GradoController::class, 'getAsignaturas']);
Route::get('/grados/{id}/competencias', [GradoController::class, 'getCompetencias']);
Route::get('/tutor/{id}/notas-cuaderno', [NotaCuadernoController::class, 'notasPorTutor']);
