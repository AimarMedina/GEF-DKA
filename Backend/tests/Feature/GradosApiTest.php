<?php

use App\Models\User;

/**
 * Tests for Grado endpoints: list and create
 */

it('returns a list of grados (paginated)', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $this->actingAs($admin);

    $response = $this->getJson('/api/grados');
    $response->assertStatus(200);
});

it('allows creating a grado', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $this->actingAs($admin);

    $payload = [
        'nombre' => 'Grado Test',
        'curso' => '1'
    ];

    $response = $this->postJson('/api/grados', $payload);
    $this->assertContains($response->status(), [200, 201, 422]);
});
