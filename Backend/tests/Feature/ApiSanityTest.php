<?php

use App\Models\User;
use App\Models\Empresa;

/**
 * Basic API sanity checks: login, users and instructor creation (requires an empresa)
 */

beforeEach(function () {
    // migrations are handled by Pest RefreshDatabase in tests/Pest.php
});

it('can login with valid credentials and receive a token', function () {
    $password = 'password';
    $user = User::factory()->create(['password' => bcrypt($password)]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['status', 'user', 'token']);
});

it('creates an instructor when empresa exists', function () {
    // create an empresa record required by the instructor creation endpoint
    Empresa::create([
        'CIF' => 'TEST-CIF-1',
        'Nombre' => 'Empresa Test',
        'Direccion' => 'Calle Falsa 123',
        'Email' => 'empresa@test.local',
        'N_Tel' => '600000001'
    ]);

    // authenticate as any user
    $user = User::factory()->create();
    $this->actingAs($user);

    $payload = [
        'nombre' => 'InstructorTest',
        'apellidos' => 'Apellido',
        'email' => 'inst_test@example.local',
        'n_tel' => '600123456',
        'password' => 'secret123',
        'CIF_Empresa' => 'TEST-CIF-1'
    ];

    $response = $this->postJson('/api/empresa/instructor/create', $payload);

    // controller should return 201 on created; accept 201 or 200
    $this->assertContains($response->status(), [200, 201]);
    $response->assertJsonFragment(['message' => 'Instructor creado correctamente']);
});
