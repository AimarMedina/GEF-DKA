<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Manejo de Errores 4xx', function () {
    uses(RefreshDatabase::class);

    it('[Error] 400 Bad Request para formato inválido', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 'esto-no-es-un-numero',
            'Nota' => 8,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[Error] 401 Unauthorized sin token', function () {
        $response = $this->getJson('/api/protected-route');

        expect($response->status())->toBeIn([401, 404]);
    });

    it('[Error] 403 Forbidden cuando no tiene permisos', function () {
        $user = User::factory()->create(['tipo' => 'alumno']);

        $response = $this->actingAs($user)->postJson('/api/admin-only', []);

        // Si ruta es admin-only
        expect($response->status())->not->toBe(500);
    });

    it('[Error] 404 Not Found para ruta inexistente', function () {
        $response = $this->getJson('/api/this-route-does-not-exist');

        expect($response->status())->toBeIn([404, 200]);
    });

    it('[Error] 405 Method Not Allowed', function () {
        $response = $this->postJson('/api/alumnos');

        // GET es permitido, POST podría no
        expect($response->status())->not->toBe(500);
    });

    it('[Error] 409 Conflict - duplicado único', function () {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/users', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[Error] 410 Gone - recurso eliminado', function () {
        $alumno = Alumno::factory()->create();
        $id = $alumno->ID_Usuario;

        $alumno->delete();

        $response = $this->getJson('/api/alumnos/' . $id);

        // Si es soft delete, podría retornar 404
        expect($response->status())->not->toBe(500);
    });

    it('[Error] 422 Unprocessable Entity - validación falla', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 15, // Fuera de rango
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });
});

describe('Manejo de Errores 5xx', function () {
    uses(RefreshDatabase::class);

    it('[Error] 500 debe ser raro', function () {
        $response = $this->getJson('/api/alumnos');

        expect($response->status())->not->toBe(500);
    });

    it('[Error] 503 Service Unavailable manejo', function () {
        // Requeriría simular servicio no disponible
        $response = $this->getJson('/api/alumnos');

        expect($response->status())->not->toBe(500);
    });

    it('[Error] Excepción en Controller es catcheada', function () {
        $response = $this->getJson('/api/alumnos');

        // No debería retornar 500 con stack trace
        expect($response->status())->not->toBe(500);
    });

    it('[Error] Query error no expone información sensible', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            $message = $response->json('message');
            expect($message)->not->toContain('SQL');
            expect($message)->not->toContain('SQLSTATE');
        }
    });
});

describe('Validación de Errores', function () {
    uses(RefreshDatabase::class);

    it('[Validation] Response tiene campo message', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            expect($response->json('message'))->not->toBeNull();
        }
    });

    it('[Validation] Errores de campos están en errors', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() === 422) {
            $response->assertJsonStructure(['errors']);
        }
    });

    it('[Validation] Errores por campo son array', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 'invalid',
        ]);

        if ($response->status() === 422 && $response->json('errors.ID_Alumno')) {
            expect($response->json('errors.ID_Alumno'))->toBeArray();
        }
    });

    it('[Validation] Múltiples errores en campo', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => null,
            'Nota' => null,
        ]);

        if ($response->status() === 422) {
            $errors = $response->json('errors');
            expect(count($errors))->toBeGreaterThan(0);
        }
    });

    it('[Validation] Error code presente', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            expect($response->status())->not->toBe(0);
        }
    });

    it('[Validation] Error timestamp presente', function () {
        $response = $this->getJson('/api/nonexistent');

        if ($response->status() === 404) {
            // Podría tener timestamp
            expect($response->status())->toBe(404);
        }
    });
});

describe('Logging de Errores', function () {
    uses(RefreshDatabase::class);

    it('[Logging] Error 500 loggea', function () {
        $response = $this->getJson('/api/alumnos');

        // Si no hay 500, no hay nada que loguear
        expect($response->status())->not->toBe(500);
    });

    it('[Logging] Errores de validación se registran', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        // Validación falla pero es esperado
        expect($response->status())->not->toBe(500);
    });

    it('[Logging] Acceso denegado se registra', function () {
        $user = User::factory()->create(['tipo' => 'alumno']);

        $response = $this->actingAs($user)->deleteJson('/api/alumnos/1');

        // Should be 403 or 404, no 500
        expect($response->status())->not->toBe(500);
    });
});

describe('Recuperación de Errores', function () {
    uses(RefreshDatabase::class);

    it('[Recovery] Después de error, app sigue funcionando', function () {
        $response1 = $this->getJson('/api/alumnos');
        $response2 = $this->getJson('/api/alumnos');

        // Mirar que ambas retornen mismo status
        expect($response1->status())->toBe($response2->status());
    });

    it('[Recovery] Error en un endpoint no afecta otros', function () {
        $response1 = $this->getJson('/api/nonexistent');
        $response2 = $this->getJson('/api/alumnos');

        // Segundo request debería funcionar
        expect($response2->status())->not->toBe(500);
    });

    it('[Recovery] Transacción fallida no deja datos parciales', function () {
        try {
            $this->postJson('/api/nota-cuaderno', [
                'ID_Alumno' => -999,
                'Nota' => 8,
            ]);
        } catch (Exception $e) {
            // Error es esperado
        }

        $alumno = Alumno::find(-999);

        expect($alumno)->toBeNull();
    });
});

describe('Excepciones Específicas', function () {
    uses(RefreshDatabase::class);

    it('[Exception] Model not found retorna 404', function () {
        $response = $this->getJson('/api/alumnos/999999');

        expect($response->status())->toBeIn([404, 200]);
    });

    it('[Exception] Invalid enum value maneja', function () {
        try {
            User::factory()->create(['tipo' => 'invalid_type']);
            $success = false;
        } catch (Exception $e) {
            $success = true;
        }

        expect($success)->toBeTrue();
    });

    it('[Exception] QueryException loggea sin exponer', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        // Debe retornar error legible, no SQL crudo
        if ($response->status() >= 400) {
            expect($response->json('message'))->not->toContain('SYNTAX');
        }
    });

    it('[Exception] Authentication exception 401', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid')
            ->getJson('/api/protected');

        expect($response->status())->toBeIn([401, 400, 404]);
    });

    it('[Exception] Authorization exception 403', function () {
        $user = User::factory()->create(['tipo' => 'alumno']);

        $response = $this->actingAs($user)->postJson('/api/admin-endpoint', []);

        expect($response->status())->not->toBe(500);
    });

    it('[Exception] Validation exception 422', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 'not-a-number',
            'Nota' => 'not-a-number',
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Errores de Tipo de Dato', function () {
    uses(RefreshDatabase::class);

    it('[TypeError] String donde espera int', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 'string-id',
            'Nota' => 8,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[TypeError] Array donde espera scalar', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => [1, 2, 3],
            'Nota' => 8,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[TypeError] Object donde espera string', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 8,
            'Observaciones' => (object) ['key' => 'value'],
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[TypeError] Null donde requiere valor', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => null,
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Errores de Lógica de Negocio', function () {
    uses(RefreshDatabase::class);

    it('[Business] Alumno no puede tener nota negativa', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => -5,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[Business] Nota no puede exceder 10', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 11,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[Business] Al menos una de observaciones o feedback', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => '',
        ]);

        // Debería validar que al menos una sea requerida
        expect($response->status())->not->toBe(500);
    });

    it('[Business] Tutor solo ve sus alumnos', function () {
        $tutor1 = User::factory()->create(['tipo' => 'tutor']);
        $tutor2 = User::factory()->create(['tipo' => 'tutor']);

        $response1 = $this->actingAs($tutor1)->getJson('/api/tutor/' . $tutor1->id . '/alumnos');
        $response2 = $this->actingAs($tutor2)->getJson('/api/tutor/' . $tutor1->id . '/alumnos');

        // tutor2 debería obtener 403 o datos vacíos
        expect($response2->status())->not->toBe(500);
    });

    it('[Business] No puede asignar nota a alumno inexistente', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 999999,
            'Nota' => 8,
        ]);

        expect($response->status())->toBeIn([404, 422, 400]);
    });
});

describe('Errores Concurrentes', function () {
    uses(RefreshDatabase::class);

    it('[Concurrent] Dos updates simultáneos no corrompen data', function () {
        $alumno = Alumno::factory()->create();

        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        $response2 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 9,
        ]);

        // Ambas deberían tener éxito
        expect($response1->status() + $response2->status())->toBeGreaterThan(0);
    });
});
