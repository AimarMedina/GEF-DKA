<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EstanciaCompetenciaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\NotasCompetenciaController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoEntregaController;
use App\Http\Controllers\CompRaController;
use App\Http\Controllers\EntregaCuadernoController;
use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\NotaCuadernoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotasEmpresaController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\RaController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\TransversalController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {


    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/auth', [UserController::class, 'auth']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/user/create', [UserController::class, 'create']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
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
    Route::get('/instructores/{id}/alumnos', [AlumnoController::class, 'alumnosDeInstructor']);
    Route::put('/alumnos/{idAlumno}/asignar-instructor', [AlumnoController::class, 'asignarInstructor']);



    /*
|--------------------------------------------------------------------------
| Tutores y Alumnos
|--------------------------------------------------------------------------
*/
    Route::get('/tutores/{id}/alumnos', [AlumnoController::class, 'alumnosDeTutor']);
    Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']); // Tutor
    Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']); // Alumno
    Route::get('/empresa/{cif}/alumnos', [EstanciaController::class, 'getCompanyAlumnos']);
    Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']);


    //Cuaderno
    Route::get('/alumno/{id}', [AlumnoController::class, 'getGrado']);
    Route::get('/entregas/alumno/{id}', [EntregaCuadernoController::class, 'entregasAlumno']);
    Route::post('/grado/{gradoId}/entregas', [EntregaCuadernoController::class, 'store']);
    Route::get('/grado/{id}/entregas', [EntregaCuadernoController::class, 'porGrado']);
    Route::post('/entregarCuaderno/alumno/{id}', [AlumnoEntregaController::class, 'entregarCuaderno']);
    Route::post('/nota-cuaderno', [NotaCuadernoController::class, 'notaCuaderno']);
    Route::post('/observacionesCuadernoAlumno', [NotaCuadernoController::class, 'observacionesCuadernoAlumno']);
    Route::get('/grados', [GradoController::class, 'getGrados']);
    Route::get('/alumno/entregas/descargar/{id}', [AlumnoEntregaController::class, 'descargarCuaderno']);


    Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store']);

    Route::get('/tutor/{id}/grados', [TutorController::class, 'grados']);

    /*
|--------------------------------------------------------------------------
| Estancias
|--------------------------------------------------------------------------
*/
    // Para tutor
    Route::post('asignarEstancia', [EstanciaController::class, 'asignarEstancia']);
    Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);
    Route::delete('/estancia/{id}', [EstanciaController::class, 'eliminarEstancia']);
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
    Route::get('/entregas/alumno/{id}', [EntregaCuadernoController::class, 'entregasAlumno']);
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
    Route::get('/alumno/{id}/mis-notasAlumno', [AlumnoController::class, 'misNotasAlumno']);
    Route::post('/alumnos/{idAlumno}/nota-egibide', [AlumnoController::class, 'guardarNotaEgibide']);

    // Notas por alumno (empresa)
    Route::get('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'show']);
    Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store']);

    /*
|--------------------------------------------------------------------------
| Grados
|--------------------------------------------------------------------------
*/
    Route::get('/grados', [GradoController::class, 'getGrados']);
    Route::post('/grados', [GradoController::class, 'crearGrado']);
    Route::delete('/grados/{id}', [GradoController::class, 'eliminarGrado']);
    Route::get('/grados/{id}/asignaturas', [GradoController::class, 'getAsignaturas']);
    Route::get('/grados/{id}/competencias', [GradoController::class, 'getCompetencias']);
    Route::get('/tutor/{id}/notas-cuaderno', [NotaCuadernoController::class, 'notasPorTutor']);
    Route::get('/mi-grado/gestion', [GradoController::class, 'getDatosGestionTutor']);

    /*
|--------------------------------------------------------------------------
| Seguimiento
|--------------------------------------------------------------------------
*/
    Route::get('/estancia/{id}/seguimientos', [SeguimientoController::class, 'index']);
    Route::post('/seguimiento', [SeguimientoController::class, 'crearSeguimiento']);
    Route::put('/seguimiento/{id}', [SeguimientoController::class, 'ModificarSeguimiento']);
    Route::delete('/seguimiento/{id}', [SeguimientoController::class, 'eliminarSeguimiento']);



    /*
|--------------------------------------------------------------------------
| Asignaturas y RAs
|--------------------------------------------------------------------------
*/
    Route::get('/asignaturas/{id}/ras', [AsignaturaController::class, 'getRas']);
    Route::post('/ras', [RaController::class, 'store']);
    Route::delete('/ras/{id}', [RaController::class, 'destroy']);
    Route::post('/asignaturas', [AsignaturaController::class, 'store']);
    Route::delete('/asignaturas/{id}', [AsignaturaController::class, 'destroy']);
    Route::post('/competencias', [CompetenciaController::class, 'store']);
    Route::delete('/competencias/{id}', [CompetenciaController::class, 'destroy']);

    Route::get('/grado/{id}/matriz-competencias/', [CompRaController::class, 'getCompRa']);
    Route::post('compRa/create', [CompRaController::class, 'createOrDelete']);

    Route::get(
        '/estancias/{id}/competencias',
        [EstanciaController::class, 'competencias']
    );
    Route::post(
        '/estancias/{estancia}/competencias',
        [EstanciaCompetenciaController::class, 'create']
    );

    Route::put('/alumnos/{alumnoId}/competencias/{competenciaId}/nota', [NotasCompetenciaController::class, 'guardarNota']);
    Route::delete('estancias/{estanciaId}/competencias/{competenciaId}', [EstanciaCompetenciaController::class, 'delete']);
    Route::delete('/grado/{gradoId}/entregas/{entregaId}', [EntregaCuadernoController::class, 'destroy']);
    Route::delete('/users/{id}', [UserController::class, 'delete']);
    Route::put('/users/{id}', [UserController::class, 'update']);


/*
|--------------------------------------------------------------------------
| Transversales
|--------------------------------------------------------------------------
*/
    Route::get('/transversales', [TransversalController::class, 'getTransversales']);
    Route::get('/transversales/alumno/{idAlumno}', [TransversalController::class, 'getTransversalesAlumno']);
    Route::put('/alumnos/{idAlumno}/transversales/{transversalId}/nota', [TransversalController::class, 'actualizarNotaTransversal']);
    Route::post('/transversales', [TransversalController::class, 'crearTransversal']);
    Route::put('/transversales/{id}', [TransversalController::class, 'actualizarTransversal']);
    Route::delete('/transversales/{id}', [TransversalController::class, 'eliminarTransversal']);
});
