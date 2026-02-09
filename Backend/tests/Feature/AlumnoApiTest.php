<?php

use App\Models\User;
use App\Models\Alumno;
use App\Models\Grado;

// ALUMNO ENDPOINTS

test('get students of tutor', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $token = $tutor->createToken('auth_token')->plainTextToken;

    $student = User::factory()->create(['tipo' => 'alumno']);
    // User model auto-creates Alumno, just update it with tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'id_tutor' => $tutor->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/tutores/{$tutor->id}/alumnos");

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['ID_Usuario']
        ]);
});

test('get students of tutor blocks unauthorized access', function () {
    $tutor1 = User::factory() ->create(['tipo' => 'tutor']);
    $tutor2 = User::factory()->create(['tipo' => 'tutor']);
    $token = $tutor1->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/tutores/{$tutor2->id}/alumnos");

    $response->assertStatus(403)
        ->assertJson(['status' => 'error']);
});

test('admin can view tutor students', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $student = User::factory()->create(['tipo' => 'alumno']);
    // User model auto-creates Alumno, just update it with tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'id_tutor' => $tutor->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/tutores/{$tutor->id}/alumnos");

    $response->assertStatus(200);
});

test('tutor can search their students', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $token = $tutor->createToken('auth_token')->plainTextToken;

    $student = User::factory()->create([
        'tipo' => 'alumno',
        'nombre' => 'Carlos',
    ]);
    // User model auto-creates Alumno, just update it with tutor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'id_tutor' => $tutor->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/tutores/{$tutor->id}/alumnos?q=Carlos");

    $response->assertStatus(200);
});

test('get students of instructor', function () {
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $student = User::factory()->create(['tipo' => 'alumno']);
    // User model auto-creates Alumno, just update it with instructor info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Instructor' => $instructor->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/instructores/{$instructor->id}/alumnos");

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['ID_Usuario']
        ]);
});

test('get student grade', function () {
    $grado = Grado::create(['Nombre' => 'DAM', 'Curso' => '2º']);
    $student = User::factory()->create(['tipo' => 'alumno']);
    // User model auto-creates Alumno, just update it with grade info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);
    $alumno = Alumno::where('ID_Usuario', $student->id)->first();

    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/alumno/{$alumno->ID_Usuario}");

    $response->assertStatus(200)
        ->assertJsonStructure(['ID_Usuario']);
});

test('get student personal notes', function () {
    $student = User::factory()->create(['tipo' => 'alumno']);
    $grado = Grado::create(['Nombre' => 'DAM', 'Curso' => '2º']);
    // User model auto-creates Alumno, just update it with grade info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);

    $token = $student->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/alumno/{$student->id}/mis-notas");

    $response->assertStatus(200);
});

test('get student notes without authentication', function () {
    $response = $this->get("/api/alumno/99999/mis-notas");

    $response->assertStatus(401);
});
