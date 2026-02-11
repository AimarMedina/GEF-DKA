<?php

use App\Models\User;
use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Grado;
use App\Models\EstanciaAlumno;

// ESTANCIA ENDPOINTS

test('get current estancia for student', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $grado = Grado::create(['Nombre' => 'DAM1', 'Curso' => '2º']);
    $empresa = Empresa::create(['CIF' => 'EST123456', 'Nombre' => 'Tech Corp', 'Direccion' => 'Tech Ave', 'Email' => 'tech@corp.com', 'N_Tel' => '600000006']);
    
    // User model auto-creates Alumno, just update it with grado info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();
    
    $estancia = EstanciaAlumno::create([
        'ID_Alumno' => $alumno->ID_Usuario,
        'CIF_Empresa' => 'EST123456',
        'Fecha_inicio' => now(),
        'Fecha_fin' => now()->addMonths(3),
    ]);

    $token = $student->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/alumno/{$alumno->ID_Usuario}/estancia");

    $response->assertStatus(200)
        ->assertJsonStructure(['ID_Alumno']);
});

test('get estancia history for student', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $grado = Grado::create(['Nombre' => 'DAM2', 'Curso' => '2º']);
    $empresa = Empresa::create(['CIF' => 'HIST123456', 'Nombre' => 'Old Company', 'Direccion' => 'Old St', 'Email' => 'old@comp.com', 'N_Tel' => '600000007']);
    
    // User model auto-creates Alumno, just update it with grado and tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
        'id_tutor' => $tutor->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();
    
    EstanciaAlumno::create([
        'ID_Alumno' => $alumno->ID_Usuario,
        'CIF_Empresa' => 'HIST123456',
        'Fecha_inicio' => now()->subMonths(6),
        'Fecha_fin' => now()->subMonths(3),
    ]);

    $token = $tutor->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/tutor/alumno/{$alumno->ID_Usuario}/estancias");

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['ID_Alumno']
        ]);
});

test('get company students in internship', function () {
    $empresa = Empresa::create(['CIF' => 'COM123456', 'Nombre' => 'Company', 'Direccion' => 'Com St', 'Email' => 'com@test.com', 'N_Tel' => '600000008']);
    $student = User::factory()->create(['tipo' => 'alumno']);
    $grado = Grado::create(['Nombre' => 'DAM3', 'Curso' => '2º']);
    
    // User model auto-creates Alumno, just update it with grado info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();
    
    EstanciaAlumno::create([
        'ID_Alumno' => $alumno->ID_Usuario,
        'CIF_Empresa' => 'COM123456',
        'Fecha_inicio' => now(),
        'Fecha_fin' => now()->addMonths(3),
    ]);

    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/empresa/COM123456/alumnos");

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['ID_Alumno']
        ]);
});

test('assign estancia successfully', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $empresa = Empresa::create(['CIF' => 'ASIG123456', 'Nombre' => 'New Company', 'Direccion' => 'New St', 'Email' => 'new@comp.com', 'N_Tel' => '600000009']);
    $grado = Grado::create(['Nombre' => 'DAM4', 'Curso' => '2º']);
    
    // User model auto-creates Alumno, just update it with grado and tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
        'id_tutor' => $tutor->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();

    $token = $tutor->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/asignarEstancia', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'CIF_Empresa' => 'ASIG123456',
            'Fecha_inicio' => '2026-03-01',
            'Fecha_fin' => '2026-06-01',
            'ID_Instructor' => $instructor->id,
            'horarios' => [
                ['Dia' => 'Lunes', 'Horario1' => '09:00-13:00', 'Horario2' => '14:00-17:00'],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'ID_Alumno']);
});

test('assign estancia fails without required fields', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $token = $tutor->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/asignarEstancia', [
            'ID_Alumno' => 99999,
        ]);

    $response->assertStatus(422);
});

test('delete estancia successfully', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $empresa = Empresa::create(['CIF' => 'DEL123456', 'Nombre' => 'Delete Company', 'Direccion' => 'Del St', 'Email' => 'del@comp.com', 'N_Tel' => '600000010']);
    $grado = Grado::create(['Nombre' => 'DAM5', 'Curso' => '2º']);
    
    // User model auto-creates Alumno, just update it with grado and tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
        'id_tutor' => $tutor->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();
    
    $estancia = EstanciaAlumno::create([
        'ID_Alumno' => $alumno->ID_Usuario,
        'CIF_Empresa' => 'DEL123456',
        'Fecha_inicio' => now(),
        'Fecha_fin' => now()->addMonths(3),
    ]);

    $token = $tutor->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete("/api/estancia/{$estancia->id}");

    $response->assertStatus(200);
});

test('delete estancia blocks non-tutor access', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $empresa = Empresa::create(['CIF' => 'UNAUTH123', 'Nombre' => 'Unauthorized', 'Direccion' => 'Una St', 'Email' => 'una@test.com', 'N_Tel' => '600000011']);
    $grado = Grado::create(['Nombre' => 'DAM6', 'Curso' => '2º']);
    
    // User model auto-creates Alumno, just update it with grado info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();
    
    $estancia = EstanciaAlumno::create([
        'ID_Alumno' => $alumno->ID_Usuario,
        'CIF_Empresa' => 'UNAUTH123',
        'Fecha_inicio' => now(),
        'Fecha_fin' => now()->addMonths(3),
    ]);

    $token = $student->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete("/api/estancia/{$estancia->id}");

    $response->assertStatus(403);
});

test('get estancia without authentication', function () {
    $response = $this->get("/api/alumno/99999/estancia");

    $response->assertStatus(401);
});
