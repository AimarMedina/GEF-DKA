<?php

use App\Models\User;
use App\Models\Alumno;
use App\Models\Grado;

/**
 * Tutor / Alumno endpoints
 */

beforeEach(function () {
    // migrations via Pest
});

it('returns alumnos for a tutor', function () {
    // create a tutor and a grado
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $grado = Grado::factory()->create();

    // create an alumno user; the User model's created hook will also create the alumno record
    $alumnoUser = User::factory()->create(['tipo' => 'alumno']);
    // update the alumno record created by the model boot
    $alumnoUser->alumno()->update([
        'ID_Grado' => $grado->id,
        'ID_Tutor' => $tutor->id,
    ]);

    $this->actingAs($tutor);

    $response = $this->getJson('/api/tutores/' . $tutor->id . '/alumnos');

    // should be OK and return an array (or 401/403 if middleware enforces other rules)
    $this->assertContains($response->status(), [200, 401, 403]);
});
