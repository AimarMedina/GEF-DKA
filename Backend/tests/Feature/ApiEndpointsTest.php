<?php

use App\Models\User;
use App\Models\Grado;
use App\Models\Estancia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('autenticacion funciona', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/auth');

    $response->assertStatus(200);
});

it('autenticacion rechaza sin login', function () {
    $response = $this->getJson('/api/auth');
    $response->assertStatus(401);
});

it('tutores obtiene alumnos', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $grado = Grado::factory()->create();

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
        'ID_Instructor' => $instructor->id,
    ]);

    Estancia::factory()->create([
        'ID_Alumno' => $alumnoUser->id,
        'estado' => 'actual'
    ]);

    $response = $this->actingAs($tutor)
        ->getJson("/api/tutores/{$tutor->id}/alumnos");

    $response->assertStatus(200);
    $response->assertJsonStructure(['*' => ['ID_Usuario', 'usuario', 'estancia_actual']]);
});

it('tutores obtiene alumnos clases', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $grado = Grado::factory()->create();

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
        'ID_Instructor' => $instructor->id,
    ]);

    $response = $this->actingAs($tutor)
        ->getJson("/api/tutores/{$tutor->id}/alumnos-clases");

    $response->assertStatus(200);
});

it('instructores obtiene alumnos', function () {
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $grado = Grado::factory()->create();

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
        'ID_Instructor' => $instructor->id,
    ]);

    $response = $this->actingAs($instructor)
        ->getJson("/api/instructores/{$instructor->id}/alumnos");

    $response->assertStatus(200);
});

it('alumnos obtiene estancia actual', function () {
    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    Estancia::factory()->create([
        'ID_Alumno' => $alumnoUser->id,
        'estado' => 'actual'
    ]);

    $response = $this->actingAs($alumnoUser)
        ->getJson("/api/alumnos/{$alumnoUser->id}/estancia-actual");

    $response->assertStatus(200);
});

it('alumnos obtiene grado', function () {
    $grado = Grado::factory()->create();
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $instructor = User::factory()->create(['tipo' => 'instructor']);

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
        'ID_Instructor' => $instructor->id,
    ]);

    $response = $this->actingAs($alumnoUser)
        ->getJson("/api/alumnos/{$alumnoUser->id}/grado");

    $response->assertStatus(200);
});

it('tutor guarda nota cuaderno', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $instructor = User::factory()->create(['tipo' => 'instructor']);
    $grado = Grado::factory()->create();

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
        'ID_Instructor' => $instructor->id,
    ]);

    $response = $this->actingAs($tutor)
        ->postJson("/api/tutores/{$tutor->id}/nota-cuaderno", [
            'ID_Alumno' => $alumnoUser->id,
            'nota' => 9
        ]);

    $response->assertStatus(201);
});

it('nota cuaderno sin ID_Alumno falla', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);

    $response = $this->actingAs($tutor)
        ->postJson("/api/tutores/{$tutor->id}/nota-cuaderno", [
            'nota' => 9
        ]);

    $response->assertStatus(422); // Validación falla
});
