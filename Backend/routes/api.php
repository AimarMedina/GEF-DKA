<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoEntregaController;
use App\Http\Controllers\EntregaCuadernoController;
use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\NotaCuadernoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotasEmpresaController;

Route::get('/auth', [UserController::class, 'auth'])->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/empresas', [EmpresaController::class, 'getCompanys']);
Route::get('/empresa/{cif}/instructores', [InstructorController::class, 'getCompanyInstructor']);
Route::post('/empresa/instructor/create', [InstructorController::class, 'crearInstructor']);
Route::get('/tutores/{id}/alumnos', [AlumnoController::class, 'alumnosDeTutor']);
Route::get('/instructores/{id}/alumnos', [AlumnoController::class, 'alumnosDeInstructor']);
Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);// Tutor
Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']);// Alumno
Route::get('/empresa/{cif}/alumnos', [EstanciaController::class, 'getCompanyAlumnos']);


//Cuaderno
Route::post('/entregas', [EntregaCuadernoController::class, 'crearEntregaCuaderno']); // tutor
Route::get('/grado/{id}/entregas', [EntregaCuadernoController::class, 'porGrado']);
Route::post('/alumno/entrega', [AlumnoEntregaController::class, 'entregarCuaderno']);
Route::post('/nota-cuaderno', [NotaCuadernoController::class, 'notaCuaderno']);



Route::get('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'show'])->middleware('auth:sanctum');
Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store'])->middleware('auth:sanctum');
Route::get('/grados',[GradoController::class,'getGrados']);

