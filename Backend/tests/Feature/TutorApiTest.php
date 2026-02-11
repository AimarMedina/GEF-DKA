<?php

use App\Models\User;
use App\Models\Grado;

test('get available tutors', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor', 'nombre' => 'Test Tutor']);
    
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/tutores/disponibles');

    $response->assertStatus(200);
});

test('available tutors requires authentication', function () {
    $response = $this->get('/api/tutores/disponibles');
    $response->assertStatus(401);
});

test('available tutors list correct when tutor has no grade', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor', 'nombre' => 'Free Tutor']);
    
    $admin = User::factory()->create(['tipo' => 'admin']);
    $token = $admin->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/tutores/disponibles');

    $response->assertStatus(200)
        ->assertJsonStructure(['*' => ['id', 'nombre']]);
});
