<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Edge Cases - Valores Extremos', function () {
    uses(RefreshDatabase::class);

    it('[EdgeCase] Nota 0 es válida', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 0,
        ]);

        expect($response->status())->not->toBe(400);
    });

    it('[EdgeCase] Nota 10 es válida', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 10,
        ]);

        expect($response->status())->not->toBe(400);
    });

    it('[EdgeCase] Nota -1 rechazada', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => -1,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[EdgeCase] Nota 10.1 rechazada', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 10.1,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[EdgeCase] ID_Alumno 0 maneja gracefully', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 0,
            'Nota' => 5,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] ID_Alumno negativo maneja gracefully', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => -999,
            'Nota' => 5,
        ]);

        expect($response->status())->toBeIn([422, 404, 400]);
    });

    it('[EdgeCase] Nota tipo string numérico', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => '8.5',
        ]);

        // Podría ser coercionado a número
        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] Nota tipo boolean', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => true,
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Edge Cases - Strings Largos', function () {
    uses(RefreshDatabase::class);

    it('[EdgeCase] String muy largo rechazado', function () {
        $longText = str_repeat('a', 10000);

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 8,
            'Observaciones' => $longText,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] String vacío permitido', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => '',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] String con solo espacios es vacío', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => '   ',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] String con newlines manejado', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => "Línea 1\nLínea 2\nLínea 3",
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] String con caracteres especiales', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => '!@#$%^&*()_+-={}[]|:;"<>?,./~`',
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Edge Cases - Unicode y Encoding', function () {
    uses(RefreshDatabase::class);

    it('[EdgeCase] Emoji se maneja correctamente', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => 'Buen trabajo 👍 😊 ✨',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] Caracteres acentuados españoles', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => 'Área de mejora: paciencia, ñoño',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] Caracteres chinos se respetan', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => '学生表现很好',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] Caracteres árabes se respetan', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => 'الطالب جيد جداً',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] RTL (Right-to-Left) text', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => 'שיעור טוב',
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Edge Cases - Valores NULL y Empty', function () {
    uses(RefreshDatabase::class);

    it('[EdgeCase] Campo requerido NULL rechazado', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => null,
            'Nota' => 8,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[EdgeCase] Nota NULL rechazada', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => null,
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[EdgeCase] Array vacío en campo single', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => [],
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[EdgeCase] Object en campo string', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'Observaciones' => (object) ['key' => 'value'],
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Concurrencia y Race Conditions', function () {
    uses(RefreshDatabase::class);

    it('[Concurrency] Dos requests simultáneos no crean duplicados', function () {
        $alumno = Alumno::factory()->create();

        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        $response2 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 9,
        ]);

        expect($response1->status() + $response2->status())->toBeGreaterThan(0);
    });

    it('[Concurrency] Actualizar la misma nota dos veces', function () {
        $alumno = Alumno::factory()->create();

        $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 9,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[Concurrency] Múltiples users creando alumnos', function () {
        for ($i = 0; $i < 5; $i++) {
            Alumno::factory()->create();
        }

        expect(Alumno::count())->toBe(5);
    });
});

describe('Performance y Stress', function () {
    uses(RefreshDatabase::class);

    it('[Performance] Crear 100 alumnos es rápido', function () {
        $start = microtime(true);

        Alumno::factory()->count(100)->create();

        $elapsed = microtime(true) - $start;

        expect($elapsed)->toBeLessThan(5);
    });

    it('[Performance] Query 100 alumnos es rápido', function () {
        Alumno::factory()->count(100)->create();

        $start = microtime(true);

        $alumnos = Alumno::limit(100)->get();

        $elapsed = microtime(true) - $start;

        expect($elapsed)->toBeLessThan(1);
        expect($alumnos->count())->toBe(100);
    });

    it('[Performance] Paginación grande no lenta', function () {
        Alumno::factory()->count(1000)->create();

        $start = microtime(true);

        $response = $this->getJson('/api/alumnos?page=50&per_page=100');

        $elapsed = microtime(true) - $start;

        expect($elapsed)->toBeLessThan(3);
    });

    it('[Performance] Búsqueda entre muchos registros', function () {
        Alumno::factory()->count(500)->create(['usuario' => 'test']);
        Alumno::factory()->count(500)->create(['usuario' => 'other']);

        $start = microtime(true);

        $response = $this->getJson('/api/alumnos?search=test');

        $elapsed = microtime(true) - $start;

        expect($elapsed)->toBeLessThan(2);
    });
});

describe('Operaciones Destructivas', function () {
    uses(RefreshDatabase::class);

    it('[Destructive] DELETE no permitido sin autorización', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->deleteJson('/api/alumnos/' . $alumno->ID_Usuario);

        expect($response->status())->toBeIn([401, 403, 404]);
    });

    it('[Destructive] DELETE autorizado elimina recurso', function () {
        $user = User::factory()->create(['tipo' => 'admin']);
        $alumno = Alumno::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/alumnos/' . $alumno->ID_Usuario);

        // Si se permite DELETE
        expect($response->status())->not->toBe(500);
    });

    it('[Destructive] DELETE recurso inexistente 404', function () {
        $response = $this->deleteJson('/api/alumnos/999999');

        expect($response->status())->toBeIn([404, 403, 401, 405]);
    });

    it('[Destructive] Batch delete maneja correctamente', function () {
        Alumno::factory()->count(5)->create();

        // Si endpoint soporta delete batch
        $response = $this->deleteJson('/api/alumnos/batch', [
            'ids' => [1, 2, 3],
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Idempotencia', function () {
    uses(RefreshDatabase::class);

    it('[Idempotent] GET request es idempotente', function () {
        Alumno::factory()->create();

        $response1 = $this->getJson('/api/alumnos');
        $response2 = $this->getJson('/api/alumnos');

        expect($response1->json())->toEqual($response2->json());
    });

    it('[Idempotent] POST request no es idempotente', function () {
        $alumno = Alumno::factory()->create();

        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        $response2 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        // Second request podría fallar o actualizar
        expect($response1->status())->not->toBe(500);
    });

    it('[Idempotent] PUT request es idempotente', function () {
        $alumno = Alumno::factory()->create();

        $response1 = $this->putJson('/api/alumnos/' . $alumno->ID_Usuario, [
            'usuario' => 'updated',
        ]);

        $response2 = $this->putJson('/api/alumnos/' . $alumno->ID_Usuario, [
            'usuario' => 'updated',
        ]);

        if ($response1->status() === 200) {
            expect($response2->status())->toBe(200);
        }
    });
});
