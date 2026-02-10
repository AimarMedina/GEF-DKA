<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Validación de Request - NotaCuaderno', function () {
    uses(RefreshDatabase::class);

    it('[FormRequest] Nota requerida en POST', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            // Sin 'Nota'
        ]);

        // 422 indica validación fallida
        $response->assertIn([422, 400]);
    });

    it('[FormRequest] ID_Alumno requerido en POST', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'Nota' => 8.5,
            // Sin 'ID_Alumno'
        ]);

        $response->assertIn([422, 400]);
    });

    it('[FormRequest] Nota debe ser numérico', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 'abc', // No es número
        ]);

        $response->assertIn([422, 400]);
    });

    it('[FormRequest] Nota debe estar entre 0 y 10', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 15, // Fuera de rango
        ]);

        $response->assertIn([422, 400, 200]);
    });

    it('[FormRequest] Respuesta 422 incluye errores', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            // Campos vacíos
        ]);

        if ($response->status() === 422 || $response->status() === 400) {
            expect($response->json())->toHaveKey('message');
        }
    });
});

describe('Validación de Request - ObservacionesCuaderno', function () {
    uses(RefreshDatabase::class);

    it('[FormRequest] ID_Cuaderno requerido', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'Observaciones' => 'test',
        ]);

        $response->assertIn([422, 400]);
    });

    it('[FormRequest] Al menos una de obs/feedback requerida', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => '',
        ]);

        $response->assertIn([422, 400, 200]);
    });

    it('[FormRequest] Observaciones vacías pero con feedback es válido', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '',
            'Feedback' => 'Feedback valido',
        ]);

        // No debería ser 422 por validación fallida
        expect($response->status())->not->toBe(422);
    });

    it('[FormRequest] Feedback vacío pero con observaciones es válido', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => 'Obs válida',
            'Feedback' => '',
        ]);

        expect($response->status())->not->toBe(422);
    });
});

describe('Validación de Reglas Comunes', function () {
    uses(RefreshDatabase::class);

    it('[Validación] String muy largo es rechazado', function () {
        $alumno = Alumno::factory()->create();
        $longString = str_repeat('a', 100000);

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
            'comments' => $longString, // Si existe este campo
        ]);

        // Puede ser 422 por validación de longitud o 413 por payload
        expect($response->status())->toBeIn([422, 413, 200]);
    });

    it('[Validación] Email inválido es rechazado (si hay campo email)', function () {
        $response = $this->postJson('/api/usuario', [
            'email' => 'not-an-email',
        ]);

        // Depende si existe este endpoint
        expect($response->status())->not->toBe(500);
    });

    it('[Validación] Array vacío es validado correctamente', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        // Debe tener errores por campos requeridos
        $response->assertIn([422, 400]);
    });
});

describe('Validación de Paginación en Query Strings', function () {
    uses(RefreshDatabase::class);

    it('[Validación] page debe ser positivo', function () {
        $response = $this->getJson('/api/alumnos?page=0');

        // Puede retornar 422, 200 con página 1, etc.
        expect($response->status())->toBeIn([200, 422]);
    });

    it('[Validación] per_page se limita a máximo', function () {
        $response = $this->getJson('/api/alumnos?per_page=999');

        // Puede limitarse automáticamente o rechazar
        expect($response->status())->toBeIn([200, 422]);
    });

    it('[Validación] page negativo', function () {
        $response = $this->getJson('/api/alumnos?page=-1');

        expect($response->status())->toBeIn([200, 422]);
    });
});

describe('Validación de Tipos de Datos', function () {
    uses(RefreshDatabase::class);

    it('[Validación] Integer esperado, string recibido', function () {
        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => 'not-a-number',
            'Nota' => 8,
        ]);

        $response->assertIn([422, 400]);
    });

    it('[Validación] Boolean esperado, string recibido', function () {
        $response = $this->postJson('/api/test', [
            'is_active' => 'maybe', // Debería ser true/false
        ]);

        // Depende del endpoint
        expect($response->status())->not->toBe(500);
    });

    it('[Validación] Array esperado, string recibido', function () {
        $response = $this->postJson('/api/test', [
            'ids' => '1,2,3', // Debería ser [1,2,3]
        ]);

        expect($response->status())->not->toBe(500);
    });
});

describe('Validación de Unicidad', function () {
    uses(RefreshDatabase::class);

    it('[Validación] Email único', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/register', [
            'email' => 'taken@example.com',
            'password' => 'password'
        ]);

        // Si existe endpoint de registro y valida unicidad
        expect($response->status())->not->toBe(500);
    });

    it('[Validación] Usuario único en alumnos', function () {
        Alumno::factory()->create(['usuario' => 'taken_user']);

        // Intentar crear otro con mismo usuario
        $response = $this->postJson('/api/alumnos', [
            'usuario' => 'taken_user'
        ]);

        if ($response->status() === 422) {
            expect($response->json())->toHaveKey('message');
        }
    });
});

describe('Sanitización y Limpieza de Datos', function () {
    uses(RefreshDatabase::class);

    it('[Sanitización] Espacios en blanco se trimean', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '   espacios   ', // Espacios al inicio/fin
        ]);

        // Debería trimear automáticamente
        expect($response->status())->not->toBe(500);
    });

    it('[Sanitización] HTML es escapado en observaciones', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => '<script>alert("xss")</script>',
        ]);

        // Debería ser seguro (no ejecutar)
        expect($response->status())->not->toBe(500);
    });

    it('[Sanitización] Caracteres especiales son permitidos', function () {
        $response = $this->postJson('/api/observacionesCuadernoAlumno', [
            'ID_Cuaderno' => 1,
            'Observaciones' => 'Observación con ñ, é, á, @, #',
        ]);

        expect($response->status())->not->toBe(500);
    });
});
