<?php

use App\Models\User;
use App\Models\Grado;
use App\Models\Alumno;

// GRADO ENDPOINTS

test('list grados with pagination', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    Grado::create(['Nombre' => 'ListDAM', 'Curso' => '2º']);
    Grado::create(['Nombre' => 'ListDAW', 'Curso' => '2º']);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/grados');

    $response->assertStatus(200);
});

test('search grados by name', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    Grado::create(['Nombre' => 'Desarrollo de Aplicaciones Web', 'Curso' => '2º']);
    Grado::create(['Nombre' => 'Sistemas Informáticos', 'Curso' => '1º']);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/grados?q=Web');

    $response->assertStatus(200)
        ->assertJsonCount(1);
});

test('get all grados without pagination', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    Grado::create(['Nombre' => 'DAM', 'Curso' => '2º']);
    Grado::create(['Nombre' => 'DAW', 'Curso' => '2º']);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/gradosTodos');

    $response->assertStatus(200);
});

test('create grado successfully', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/grados', [
            'nombre' => 'Desarrollo Multiplataforma',
            'curso' => '1º',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'grado' => ['id', 'Nombre', 'Curso']]);

    $this->assertDatabaseHas('grado', [
        'Nombre' => 'Desarrollo Multiplataforma',
        'Curso' => '1º',
    ]);
});

test('create grado fails without name', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->post('/api/grados', [
            'curso' => '2º',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('nombre');
});

test('delete grado successfully', function () {
    $grado = Grado::create(['Nombre' => 'Grado to Delete', 'Curso' => '1º']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete("/api/grados/{$grado->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['message']);
});

test('delete grado fails with assigned students', function () {
    $grado = Grado::create(['Nombre' => 'Grado with Students', 'Curso' => '1º']);
    $student = User::factory()->create(['tipo' => 'alumno']);
    // User model auto-creates Alumno, just update it with grado info
    Alumno::where('ID_Usuario', $student->id)->update([
        'ID_Grado' => $grado->id,
    ]);

    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->delete("/api/grados/{$grado->id}");

    $response->assertStatus(422)
        ->assertJson(['message' => 'No se puede eliminar el grado porque tiene 1 alumno(s) asignado(s)']);
});

test('get grado asignaturas', function () {
    $grado = Grado::create(['Nombre' => 'AsigDAM', 'Curso' => '2º']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/grados/{$grado->id}/asignaturas");

    $response->assertStatus(200);
});
test('get grado competencias', function () {
    $grado = Grado::create(['Nombre' => 'CompDAM', 'Curso' => '2º']);
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get("/api/grados/{$grado->id}/competencias");

    $response->assertStatus(200);
});
