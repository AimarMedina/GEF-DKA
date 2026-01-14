
<?php

use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AlumnoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\EstanciaAlumno;

Route::get('/auth', [UserController::class, 'auth'])->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users',[UserController::class,'getUsers']);
Route::get('/empresas',[EmpresaController::class,'getCompanys']);
Route::get('/empresa/{cif}/instructores', [InstructorController::class, 'getCompanyInstructor']);
Route::post('/empresa/instructor/create',[InstructorController::class,'crearInstructor']);
Route::get('/tutores/{id}/alumnos', [AlumnoController::class, 'alumnosDeTutor']);
Route::get('/instructores/{id}/alumnos', [AlumnoController::class, 'alumnosDeInstructor']);
Route::get('/alumno/{id}/estancia',[EstanciaController::class,'getEstanciaAlumno']);
Route::get('/estancias',[EstanciaController::class,'getEstancias']);
Route::get('/empresa/{cif}/alumnos',[EstanciaController::class,'getCompanyAlumnos']);
