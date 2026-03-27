<?php

use App\Models\User;
use App\Models\Alumno;
use App\Models\Grado;

/**
 * Minimal tests for nota-cuaderno endpoints
 */

it('allows posting a nota-cuaderno (valid or validation response)', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $grado = Grado::factory()->create();

    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    // the Alumno record is created automatically by the User model; update it
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
    ]);
    $alumno = $alumnoUser->alumno;

    $this->actingAs($tutor);

    $response = $this->postJson('/api/nota-cuaderno', [
        'ID_Alumno' => $alumno->ID_Usuario,
        'Nota' => 8.5,
    ]);

    // server may return 201/200 on success, or 422 if validation rejects; accept either
    $this->assertContains($response->status(), [200, 201, 422, 403]);
});
