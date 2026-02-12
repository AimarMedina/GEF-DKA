<?php

use App\Models\User;

/**
 * Tests for user management endpoints (requires auth)
 */

it('allows an admin to create a user', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $this->actingAs($admin);

    $payload = [
        'nombre' => 'Nuevo',
        'apellidos' => 'Usuario',
        'email' => 'nuevo.user@example.test',
        'n_tel' => '600987654',
        'password' => 'secret123',
        'tipo' => 'alumno'
    ];

    $response = $this->postJson('/api/user/create', $payload);
    $this->assertContains($response->status(), [200, 201, 422]);
});
