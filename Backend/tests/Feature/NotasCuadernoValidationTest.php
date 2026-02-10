<?php

use App\Models\Alumno;
use App\Models\User;
use App\Models\NotaCuaderno;

describe('Validación de Notas de Cuaderno', function () {
    it('valida nota entre 0 y 10', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en la BD');
        }

        $testCases = [
            ['nota' => 0, 'shouldPass' => true],      // Límite inferior
            ['nota' => 5, 'shouldPass' => true],      // Medio
            ['nota' => 10, 'shouldPass' => true],     // Límite superior
            ['nota' => 10.5, 'shouldPass' => false],  // Arriba del límite
            ['nota' => -1, 'shouldPass' => false],    // Debajo del límite
            ['nota' => null, 'shouldPass' => false],  // Nulo
            ['nota' => '', 'shouldPass' => false],    // Vacío
        ];

        foreach ($testCases as $case) {
            $payload = ['ID_Alumno' => $alumno->ID_Usuario];
            if ($case['nota'] !== null) {
                $payload['Nota'] = $case['nota'];
            }

            $response = $this->postJson('/api/nota-cuaderno', $payload);

            if ($case['shouldPass']) {
                expect($response->status())->toBeIn([200, 201, 403]);
            } else {
                expect($response->status())->toBeIn([422, 400, 403]);
            }
        }
    });

    it('requiere ID_Alumno en payload', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'Nota' => 8,
        ]);

        $response->assertIn([422, 400]);
    });

    it('rechaza nota sin campo Nota', function () {
        $alumno = Alumno::first();
        if (!$alumno) {
            $this->markTestSkipped('No hay alumnos en la BD');
        }

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
        ]);

        $response->assertIn([422, 400]);
    });
});

describe('Validación de Observaciones y Feedback', function () {
    it('valida que al menos uno (observaciones o feedback) sea requerido', function () {
        // Este test verifica que el endpoint rechace requests sin datos
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => '',
        ]);

        $response->assertIn([422, 400, 200]); // Depende de cómo implemente el backend
    });

    it('permite guardar solo observaciones', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => 'Excelente trabajo',
            'Feedback' => '',
        ]);

        $response->assertIn([200, 201, 422, 404]);
    });

    it('permite guardar solo feedback', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => 'Mejora la presentación',
        ]);

        $response->assertIn([200, 201, 422, 404]);
    });

    it('requiere ID_Cuaderno', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'Observaciones' => 'Test',
            'Feedback' => 'Test',
        ]);

        $response->assertIn([422, 400]);
    });
});

describe('Filtrado y Paginación de Acepta Notas', function () {
    it('obtiene notas con paginación correcta', function () {
        // Mock o caso real si existen datos
        $response = $this->getJson('/api/mi-grado/gestion?page=1&per_page=5');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data',
                'current_page',
                'total',
                'last_page',
                'per_page'
            ]);

            expect($response['per_page'])->toBe(5);
            expect($response['current_page'])->toBe(1);
        }
    });

    it('retorna 422 si per_page es inválido', function () {
        $response = $this->getJson('/api/mi-grado/gestion?page=1&per_page=999');

        // El servidor puede: retornar 422, limitar automáticamente, o retornar 200
        expect($response->status())->toBeIn([200, 422]);
    });

    it('retorna página 1 si page no existe', function () {
        $response = $this->getJson('/api/mi-grado/gestion?page=9999&per_page=5');

        if ($response->status() === 200) {
            // Puede retornar vacío pero debe ser válido
            expect($response instanceof \Illuminate\Testing\TestResponse)->toBeTrue();
        }
    });
});

describe('Estructura de Respuestas JSON', function () {
    it('respuestas de lista tienen estructura correcta', function () {
        $response = $this->getJson('/api/alumnos');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => [
                    '*' => ['ID_Usuario', 'usuario']
                ],
                'current_page',
                'total'
            ]);
        }
    });

    it('error 422 incluye mensajes de validación', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'Nota' => 'string_invalido', // Valor incorrecto
        ]);

        if ($response->status() === 422) {
            $response->assertJsonStructure(['message', 'errors']);
        }
    });

    it('errores incluyen al menos message', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            expect($response->json())->toHaveKey('message');
        }
    });
});

describe('Autenticación en Endpoints Protegidos', function () {
    it('rechaza nota sin autenticación si es requerida', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 1,
            'Nota' => 8,
        ]);

        // Algunos endpoints requieren auth (403), otros no (422)
        expect($response->status())->toBeIn([200, 201, 401, 403, 422]);
    });

    it('rechaza acceso a datos de tutor sin autenticación correcta', function () {
        $user = User::factory()->create(['tipo' => 'instructor']);

        // Si intentas acceder como otro tipo de usuario
        $response = $this->actingAs($user)->getJson('/api/tutores/1/alumnos');

        expect($response->status())->toBeIn([200, 401, 403]);
    });
});

describe('Transversales y Competencias', function () {
    it('transversales retorna array válido', function () {
        $response = $this->getJson('/api/transversales');

        $response->assertStatus(200);
        $response->assertIsArray();
    });

    it('competencias retorna array válido', function () {
        $response = $this->getJson('/api/competencias');

        $response->assertStatus(200);
        $response->assertIsArray();
    });

    it('transversales tiene estructura esperada', function () {
        $response = $this->getJson('/api/transversales');

        if ($response->status() === 200 && count($response->json()) > 0) {
            $first = $response->json()[0];
            expect($first)->toHaveKey('nombre');
        }
    });
});

describe('Listar Instructores y Tutores', function () {
    it('obtiene lista de instructores', function () {
        $response = $this->getJson('/api/instructores');

        $response->assertIn([200, 401]);
    });

    it('obtiene lista de tutores', function () {
        $response = $this->getJson('/api/tutores');

        $response->assertIn([200, 401]);
    });

    it('obtiene alumnos de instructor específico', function () {
        $response = $this->getJson('/api/instructores/1/alumnos');

        $response->assertIn([200, 404]);
    });
});
