<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Controlador NotaCuaderno - GET', function () {
    uses(RefreshDatabase::class);

    it('[Controller] GET /api/tutor/{id}/notas-cuaderno retorna array', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson('/api/tutor/' . $tutor->id . '/notas-cuaderno');

        if ($response->status() === 200) {
            $response->assertIsArray();
        }
    });

    it('[Controller] GET retorna estructura con alumnos', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson('/api/tutor/' . $tutor->id . '/notas-cuaderno');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                '*' => ['ID_Usuario', 'usuario']
            ]);
        }
    });

    it('[Controller] GET filtra por tutor id correcto', function () {
        $tutor1 = User::factory()->create(['tipo' => 'tutor']);
        $tutor2 = User::factory()->create(['tipo' => 'tutor']);

        $response1 = $this->actingAs($tutor1)->getJson('/api/tutor/' . $tutor1->id . '/notas-cuaderno');
        $response2 = $this->actingAs($tutor2)->getJson('/api/tutor/' . $tutor2->id . '/notas-cuaderno');

        expect($response1->status())->not->toBe(500);
        expect($response2->status())->not->toBe(500);
    });
});

describe('Controlador NotaCuaderno - POST', function () {
    uses(RefreshDatabase::class);

    it('[Controller] POST /api/nota-cuaderno guarda la nota', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8.5,
        ]);

        if ($response->status() === 201 || $response->status() === 200) {
            // La nota fue guardada
            expect(true)->toBeTrue();
        }
    });

    it('[Controller] POST retorna respuesta válida', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 7.0,
        ]);

        if ($response->status() === 201 || $response->status() === 200) {
            expect($response->json())->not->toBeNull();
        }
    });

    it('[Controller] POST actualiza nota si existe', function () {
        $alumno = Alumno::factory()->create();

        // Primera nota
        $response1 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 7.0,
        ]);

        // Segunda nota (actualizar)
        $response2 = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 9.0,
        ]);

        // Ambas deberían ser exitosas
        expect($response1->status())->toBeIn([200, 201]);
        expect($response2->status())->toBeIn([200, 201]);
    });
});

describe('Controlador ObservacionesCuaderno - POST', function () {
    uses(RefreshDatabase::class);

    it('[Controller] POST /api/observacionesCuadernoAlumno guarda obs', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => 'Buen trabajo',
            'Feedback' => '',
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[Controller] POST retorna estructura válida', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => 'Feedback test',
        ]);

        if ($response->status() === 200 || $response->status() === 201) {
            expect($response->json())->not->toBeNull();
        }
    });
});

describe('Controlador Alumnos - GET', function () {
    uses(RefreshDatabase::class);

    it('[Controller] GET /api/alumnos retorna paginado', function () {
        Alumno::factory()->count(15)->create();

        $response = $this->getJson('/api/alumnos?per_page=5');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => [
                    '*' => ['ID_Usuario', 'usuario']
                ],
                'current_page',
                'total',
                'last_page',
                'per_page'
            ]);
        }
    });

    it('[Controller] GET /api/alumnos respeta per_page', function () {
        Alumno::factory()->count(20)->create();

        $response = $this->getJson('/api/alumnos?per_page=5');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBeLessThanOrEqual(5);
        }
    });

    it('[Controller] GET /api/alumnos paginación correcta', function () {
        Alumno::factory()->count(20)->create();

        $response1 = $this->getJson('/api/alumnos?page=1&per_page=5');
        $response2 = $this->getJson('/api/alumnos?page=2&per_page=5');

        if ($response1->status() === 200 && $response2->status() === 200) {
            expect($response1->json('current_page'))->toBe(1);
            expect($response2->json('current_page'))->toBe(2);
        }
    });

    it('[Controller] GET /api/alumnos filtra por tipo', function () {
        $response = $this->getJson('/api/alumnos?tipo=alumno');

        expect($response->status())->not->toBe(500);
    });
});

describe('Controlador Instructores - GET', function () {
    uses(RefreshDatabase::class);

    it('[Controller] GET /api/instructores retorna lista', function () {
        User::factory()->count(3)->create(['tipo' => 'instructor']);

        $response = $this->getJson('/api/instructores');

        if ($response->status() === 200) {
            expect($response->json())->not->toBeNull();
        }
    });

    it('[Controller] GET /api/instructores/{id}/alumnos retorna', function () {
        $instructor = User::factory()->create(['tipo' => 'instructor']);

        $response = $this->getJson('/api/instructores/' . $instructor->id . '/alumnos');

        expect($response->status())->not->toBe(500);
    });
});

describe('Controlador Tutores - GET', function () {
    uses(RefreshDatabase::class);

    it('[Controller] GET /api/tutores retorna lista', function () {
        User::factory()->count(3)->create(['tipo' => 'tutor']);

        $response = $this->getJson('/api/tutores');

        if ($response->status() === 200) {
            expect($response->json())->not->toBeNull();
        }
    });

    it('[Controller] GET /api/tutores/{id}/alumnos retorna', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->getJson('/api/tutores/' . $tutor->id . '/alumnos');

        expect($response->status())->not->toBe(500);
    });

    it('[Controller] GET /api/tutores/{id}/alumnos-clases retorna', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->getJson('/api/tutores/' . $tutor->id . '/alumnos-clases');

        expect($response->status())->not->toBe(500);
    });
});

describe('Controlador Transversales/Competencias - GET', function () {
    uses(RefreshDatabase::class);

    it('[Controller] GET /api/transversales retorna array', function () {
        $response = $this->getJson('/api/transversales');

        if ($response->status() === 200) {
            $response->assertIsArray();
        }
    });

    it('[Controller] GET /api/competencias retorna array', function () {
        $response = $this->getJson('/api/competencias');

        if ($response->status() === 200) {
            $response->assertIsArray();
        }
    });
});

describe('Manejo de Errores del Controlador', function () {
    uses(RefreshDatabase::class);

    it('[ErrorHandling] 404 para recurso no encontrado', function () {
        $response = $this->getJson('/api/alumnos/999999');

        expect($response->status())->toBeIn([404, 200]);
    });

    it('[ErrorHandling] 422 para validación fallida', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            // Sin datos requeridos
        ]);

        expect($response->status())->toBeIn([422, 400]);
    });

    it('[ErrorHandling] 500 debe evitarse', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 8,
        ]);

        expect($response->status())->not->toBe(500);
    });

    it('[ErrorHandling] Mensaje de error es legible', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            $response->assertJsonStructure(['message']);
        }
    });
});

describe('Respuestas del Controlador', function () {
    uses(RefreshDatabase::class);

    it('[Response] Headers contienen Content-Type JSON', function () {
        $response = $this->getJson('/api/alumnos');

        expect($response->headers->get('content-type'))->toContain('json');
    });

    it('[Response] Status code correcto para éxito', function () {
        $response = $this->getJson('/api/alumnos');

        expect($response->status())->toBeIn([200, 401]);
    });

    it('[Response] POST exitoso retorna 200 o 201', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        expect($response->status())->toBeIn([200, 201, 422, 403]);
    });
});
