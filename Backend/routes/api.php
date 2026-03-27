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
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\EmpresaImportController;
use App\Http\Controllers\GradoImportController;
use App\Http\Controllers\AlumnoImportController;
use App\Http\Controllers\TeacherImportController;

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
| User Import
|--------------------------------------------------------------------------
*/
    Route::get('/users/import/template', [UserImportController::class, 'downloadTemplate']);
    Route::post('/users/import', [UserImportController::class, 'import'])->middleware('can:manage-imports');

    /*
|--------------------------------------------------------------------------
| Empresa Import
|--------------------------------------------------------------------------
*/
    Route::middleware('auth:sanctum')->group(function () {

        // Common auth endpoints
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/auth', [UserController::class, 'auth']);
        Route::post('/change-password', [UserController::class, 'changePassword']);

        /*
        |--------------------------------------------------------------------------
        | Admin routes (imports, user and system management)
        |--------------------------------------------------------------------------
        */
        // Users
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::post('/user/create', [UserController::class, 'create']);
        Route::delete('/users/{id}', [UserController::class, 'delete']);
        Route::put('/users/{id}', [UserController::class, 'update']);

        // Imports (admin-level)
        Route::get('/users/import/template', [UserImportController::class, 'downloadTemplate']);
        Route::post('/users/import', [UserImportController::class, 'import'])->middleware('can:manage-imports');

        Route::get('/empresas/import/template', [EmpresaImportController::class, 'downloadTemplate']);
        Route::post('/empresas/import', [EmpresaImportController::class, 'import'])->middleware('can:manage-imports');

        Route::get('/grados/import/template', [GradoImportController::class, 'downloadTemplate']);
        Route::post('/grados/import', [GradoImportController::class, 'import'])->middleware('can:manage-imports');

        Route::get('/alumnos/import/template', [AlumnoImportController::class, 'downloadTemplate']);
        Route::post('/alumnos/import', [AlumnoImportController::class, 'import'])->middleware('can:manage-imports');

        Route::get('/teachers/import/template', [TeacherImportController::class, 'downloadTemplate']);
        Route::post('/teachers/import', [TeacherImportController::class, 'import'])->middleware('can:manage-imports');

        /*
        |--------------------------------------------------------------------------
        | Company (empresa) management - typically admin or company staff
        |--------------------------------------------------------------------------
        */
        Route::get('/empresas', [EmpresaController::class, 'index']);
        Route::post('/empresa/create', [EmpresaController::class, 'create']);
        Route::get('/empresa/{cif}/instructores', [EmpresaController::class, 'getInstructores']);

        /*
        |--------------------------------------------------------------------------
        | Instructor routes
        |--------------------------------------------------------------------------
        */
        Route::post('/empresa/instructor/create', [InstructorController::class, 'crearInstructor']);
        Route::get('/instructores/{id}/alumnos', [AlumnoController::class, 'alumnosDeInstructor']);
        Route::put('/alumnos/{idAlumno}/asignar-instructor', [AlumnoController::class, 'asignarInstructor']);

        /*
        |--------------------------------------------------------------------------
        | Tutor routes
        |--------------------------------------------------------------------------
        */
        Route::get('/tutores/{id}/alumnos', [AlumnoController::class, 'alumnosDeTutor']);
        Route::get('/tutores/{id}/alumnos-clases', [AlumnoController::class, 'alumnosDeTutorClases']);
        Route::get('/tutor/{id}/grados', [TutorController::class, 'grados']);
        Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);
        Route::get('/tutor/{id}/notas-cuaderno', [NotaCuadernoController::class, 'notasPorTutor']);
        Route::get('/mi-grado/gestion', [GradoController::class, 'getDatosGestionTutor']);
        Route::get('/tutores/disponibles', [TutorController::class, 'getTutoresDisponibles']);

        /*
        |--------------------------------------------------------------------------
        | Student (alumno) routes
        |--------------------------------------------------------------------------
        */
        Route::get('/alumno/{id}', [AlumnoController::class, 'getGrado']);
        Route::get('/alumno/{id}/mis-notas', [AlumnoController::class, 'misNotas']);
        Route::get('/alumno/{id}/mis-notasAlumno', [AlumnoController::class, 'misNotasAlumno']);
        Route::post('/alumnos/{idAlumno}/nota-egibide', [AlumnoController::class, 'guardarNotaEgibide']);

        /*
        |--------------------------------------------------------------------------
        | Cuadernos y entregas (students & tutors)
        |--------------------------------------------------------------------------
        */
        Route::get('/entregas/alumno/{id}', [EntregaCuadernoController::class, 'entregasAlumno']);
        Route::post('/grado/{gradoId}/entregas', [EntregaCuadernoController::class, 'store']);
        Route::get('/grado/{id}/entregas', [EntregaCuadernoController::class, 'porGrado']);
        Route::post('/entregarCuaderno/alumno/{id}', [AlumnoEntregaController::class, 'entregarCuaderno']);
        Route::get('/alumno/entregas/descargar/{id}', [AlumnoEntregaController::class, 'descargarCuaderno']);
        Route::post('/nota-cuaderno', [NotaCuadernoController::class, 'notaCuaderno']);
        Route::post('/observacionesCuadernoAlumno', [NotaCuadernoController::class, 'observacionesCuadernoAlumno']);

        /*
        |--------------------------------------------------------------------------
        | Company/Estancias
        |--------------------------------------------------------------------------
        */
        Route::post('/asignarEstancia', [EstanciaController::class, 'asignarEstancia']);
        Route::get('/tutor/alumno/{id}/estancias', [EstanciaController::class, 'historialEstanciasAlumno']);
        Route::delete('/estancia/{id}', [EstanciaController::class, 'eliminarEstancia']);
        Route::get('/alumno/{id}/estancia', [EstanciaController::class, 'getEstanciaActual']);
        Route::get('/empresa/{cif}/alumnos', [EstanciaController::class, 'getCompanyAlumnos']);

        /*
        |--------------------------------------------------------------------------
        | Notas (company & general grading endpoints)
        |--------------------------------------------------------------------------
        */
        Route::get('/competencias', [CompetenciaController::class, 'index']);
        Route::post('/alumno/{idAlumno}/notaTec/{idCompetencia}', [NotasEmpresaController::class, 'storeUpdate']);
        Route::post('/alumno/{id}/notaTrans', [TransversalController::class, 'update']);
        Route::post('/alumno/{id}/notaTec', [NotasEmpresaController::class, 'update']);
        Route::post('/alumno/{id}/notaCuad', [NotaCuadernoController::class, 'update']);
        Route::get('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'show']);
        Route::post('/alumnos/{idAlumno}/notas', [NotasEmpresaController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | Grados (admin/tutor)
        |--------------------------------------------------------------------------
        */
        Route::get('/grados', [GradoController::class, 'getGrados']);
        Route::post('/grados', [GradoController::class, 'crearGrado']);
        Route::delete('/grados/{id}', [GradoController::class, 'eliminarGrado']);
        Route::get('/grados2', [GradoController::class, 'getGradosSinPaginar']);
        Route::get('/grados/{id}/asignaturas', [GradoController::class, 'getAsignaturas']);
        Route::get('/grados/{id}/competencias', [GradoController::class, 'getCompetencias']);

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

        Route::get('/estancias/{id}/competencias', [EstanciaController::class, 'competencias']);
        Route::post('/estancias/{estancia}/competencias', [EstanciaCompetenciaController::class, 'create']);

        Route::put('/alumnos/{alumnoId}/competencias/{competenciaId}/nota', [NotasCompetenciaController::class, 'guardarNota']);
        Route::delete('estancias/{estanciaId}/competencias/{competenciaId}', [EstanciaCompetenciaController::class, 'delete']);
        Route::delete('/grado/{gradoId}/entregas/{entregaId}', [EntregaCuadernoController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Transversales
        |--------------------------------------------------------------------------
        */
        Route::get('/transversales', [TransversalController::class, 'getTransversales']);
        Route::get('/transversales/alumno/{idAlumno}', [TransversalController::class, 'getTransversalesAlumno']);
        Route::put('/alumnos/{idAlumno}/transversales/{transversalId}/nota', [TransversalController::class, 'actualizarNotaTransversal']);
        Route::post('/alumnos/{idAlumno}/transversales/{transversalId}/notaa', [TransversalController::class, 'storeUpdate']);

        Route::post('/transversales', [TransversalController::class, 'crearTransversal']);
        Route::put('/transversales/{id}', [TransversalController::class, 'actualizarTransversal']);
        Route::delete('/transversales/{id}', [TransversalController::class, 'eliminarTransversal']);

    });
});
