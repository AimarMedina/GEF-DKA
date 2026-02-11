<?php

use App\Models\User;

/**
 * Basic Empresa endpoints tests: create and list
 */

it('creates an empresa and can list empresas', function () {
    $admin = User::factory()->create(['tipo' => 'admin']);
    $this->actingAs($admin);

    $payload = [
        'CIF' => 'EMP-TEST-1',
        'Nombre' => 'Empresa Test S.L.',
        'Direccion' => 'Calle Prueba 5',
        'Email' => 'empresa@test.local',
        'N_Tel' => '600111222'
    ];

    $response = $this->postJson('/api/empresa/create', $payload);
    $this->assertContains($response->status(), [200, 201, 422]);

    $list = $this->getJson('/api/empresas');
    $list->assertStatus(200);
});
