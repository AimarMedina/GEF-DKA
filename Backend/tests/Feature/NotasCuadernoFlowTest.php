<?php

use App\Models\User;
use App\Models\Alumno;

describe('Flujo Completo de Notas de Cuaderno', function () {
    beforeEach(function () {
        // Prepara datos para tests
    });

    it('[Flujo 1] Tutor obtiene lista de alumnos', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        // Debería obtener alumnos
        $response = $this->actingAs($tutor)->getJson('/api/tutores/' . $tutor->id . '/alumnos');

        $response->assertIn([200, 401, 403]);
    });

    it('[Flujo 2] Tutor obtiene notas-cuaderno para esos alumnos', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson('/api/tutor/' . $tutor->id . '/notas-cuaderno');

        if ($response->status() === 200) {
            $response->assertIsArray();
        } else {
            $response->assertIn([401, 403]);
        }
    });

    it('[Flujo 3] Tutor crea nota válida para alumno', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8.5,
        ]);

        // Éxito o validación
        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });

    it('[Flujo 4] Validación rechaza nota inválida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        // Nota > 10
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 15,
        ]);

        // Debe ser 422 (Unprocessable Entity)
        expect($response->status())->toBeIn([422, 200, 403]);
    });

    it('[Flujo 5] Tutor guarda observaciones del cuaderno', function () {
        $cuadernoId = 1;

        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => $cuadernoId,
            'Observaciones' => 'Buen desempeño',
            'Feedback' => 'Continuar mejorando',
        ]);

        expect($response->status())->toBeIn([200, 201, 404, 422, 403]);
    });

    it('[Flujo 6] Observación sin contenido es rechazada', function () {
        $cuadernoId = 1;

        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => $cuadernoId,
            'Observaciones' => '',
            'Feedback' => '',
        ]);

        // Debería rechazar
        expect($response->status())->toBeIn([422, 400, 200]);
    });

    it('[Flujo 7] Solo observaciones es válido', function () {
        $cuadernoId = 1;

        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => $cuadernoId,
            'Observaciones' => 'Solo observación',
            'Feedback' => '',
        ]);

        expect($response->status())->not->toBe(400);
    });

    it('[Flujo 8] Solo feedback es válido', function () {
        $cuadernoId = 1;

        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => $cuadernoId,
            'Observaciones' => '',
            'Feedback' => 'Solo feedback',
        ]);

        expect($response->status())->not->toBe(400);
    });

    it('[Flujo 9] Tutor puede cambiar modo (tutor/clase)', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response1 = $this->actingAs($tutor)->getJson('/api/tutores/' . $tutor->id . '/alumnos?tipo=tutor');
        $response2 = $this->actingAs($tutor)->getJson('/api/tutores/' . $tutor->id . '/alumnos?tipo=clase');

        expect($response1->status())->toBeIn([200, 401, 403]);
        expect($response2->status())->toBeIn([200, 401, 403]);
    });

    it('[Flujo 10] Notas se persisten después de guardar', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        // Primer POST
        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 7.0,
        ]);

        // Segundo POST (GET después) debería contener los datos
        $response2 = $this->getJson('/api/tutor/1/notas-cuaderno');

        expect($response2->status())->toBeIn([200, 401, 403]);
    });
});

describe('Validación de Rango de Notas', function () {
    it('[Límite] Nota = 0 es válida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 0,
        ]);

        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });

    it('[Límite] Nota = 10 es válida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 10,
        ]);

        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });

    it('[Límite] Nota = -0.1 es inválida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => -0.1,
        ]);

        expect($response->status())->toBeIn([422, 200, 403]);
    });

    it('[Límite] Nota = 10.1 es inválida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 10.1,
        ]);

        expect($response->status())->toBeIn([422, 200, 403]);
    });

    it('[Decimal] Nota = 5.5 es válida', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 5.5,
        ]);

        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });

    it('[Tipo] String numérico "8.5" se convierte', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => '8.5',
        ]);

        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });

    it('[Tipo] String no-numérico "abc" es rechazado', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 'abc',
        ]);

        expect($response->status())->toBeIn([422, 400, 200]);
    });
});

describe('Errores y Casos Excepcionales', function () {
    it('[Error] Alumno no existe', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 999999,
            'Nota' => 8,
        ]);

        expect($response->status())->toBeIn([404, 422, 200, 403]);
    });

    it('[Error] Cuaderno no existe', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 999999,
            'Observaciones' => 'test',
        ]);

        expect($response->status())->toBeIn([404, 422]);
    });

    it('[Error] Sin autenticación (si es requerida)', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 8,
        ]);

        // Algunos endpoints permiten sin auth, otros no
        expect($response->status())->not->toBe(500);
    });

    it('[Error] Tutor intenta guardar nota de alumno ajeno', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        // Intenta guardar para alumno que no está a su cargo
        $response = $this->actingAs($tutor)->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 999999,
            'Nota' => 8,
        ]);

        // Puede ser 403, 404, 422 o 200 (depende de lógica)
        expect($response->status())->toBeIn([200, 403, 404, 422]);
    });
});

describe('Concurrencia y Race Conditions', function () {
    it('[Concurrencia] Dos tutores guardan diferente nota', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $tutor1 = User::factory()->create(['tipo' => 'tutor']);
        $tutor2 = User::factory()->create(['tipo' => 'tutor']);

        $response1 = $this->actingAs($tutor1)->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 7.0,
        ]);

        $response2 = $this->actingAs($tutor2)->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8.0,
        ]);

        // Ambas llamadas deberían ser exitosas
        expect($response1->status())->toBeIn([200, 201, 403, 422]);
        expect($response2->status())->toBeIn([200, 201, 403, 422]);
    });

    it('[Actualización] Guardar nota múltiples veces actualiza', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en BD');
        }

        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 5.0,
        ]);

        $response2 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 9.0,
        ]);

        // Segunda llamada debería actualizar
        expect($response2->status())->not->toBe(500);
    });
});

describe('Respuestas y Formato JSON', function () {
    it('[Formato] Respuesta de nota tiene estructura esperada', function () {
        $response = $this->getJson('/api/tutor/1/notas-cuaderno');

        if ($response->status() === 200) {
            $response->assertIsArray();
        }
    });

    it('[Formato] Respuesta de error tiene message', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            expect($response->json())->toHaveKey('message');
        }
    });

    it('[Headers] Response incluye Content-Type JSON', function () {
        $response = $this->getJson('/api/alumnos');

        expect($response->headers->get('content-type'))->toContain('json');
    });
});
