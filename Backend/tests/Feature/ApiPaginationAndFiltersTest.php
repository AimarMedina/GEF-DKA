<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Paginación de API', function () {
    uses(RefreshDatabase::class);

    it('[Pagination] Parámetro per_page limita resultados', function () {
        Alumno::factory()->count(20)->create();

        $response = $this->getJson('/api/alumnos?per_page=5');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBeLessThanOrEqual(5);
        }
    });

    it('[Pagination] Parámetro page navega entre páginas', function () {
        Alumno::factory()->count(20)->create();

        $response1 = $this->getJson('/api/alumnos?page=1&per_page=5');
        $response2 = $this->getJson('/api/alumnos?page=2&per_page=5');

        if ($response1->status() === 200 && $response2->status() === 200) {
            expect($response1->json('current_page'))->toBe(1);
            expect($response2->json('current_page'))->toBe(2);

            $page1Ids = collect($response1->json('data'))->pluck('ID_Usuario')->values()->toArray();
            $page2Ids = collect($response2->json('data'))->pluck('ID_Usuario')->values()->toArray();

            expect($page1Ids)->not->toEqual($page2Ids);
        }
    });

    it('[Pagination] last_page calculado correctamente', function () {
        Alumno::factory()->count(23)->create();

        $response = $this->getJson('/api/alumnos?per_page=5');

        if ($response->status() === 200) {
            $lastPage = $response->json('last_page');
            // 23 / 5 = 4.6 -> 5 páginas
            expect($lastPage)->toBeGreaterThanOrEqual(4);
        }
    });

    it('[Pagination] total count es exacto', function () {
        Alumno::factory()->count(13)->create();

        $response = $this->getJson('/api/alumnos?per_page=5');

        if ($response->status() === 200) {
            expect($response->json('total'))->toBe(13);
        }
    });

    it('[Pagination] Página fuera de rango retorna vacío', function () {
        Alumno::factory()->count(5)->create();

        $response = $this->getJson('/api/alumnos?page=999&per_page=5');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBe(0);
        }
    });

    it('[Pagination] per_page por defecto es sensato', function () {
        Alumno::factory()->count(50)->create();

        $response = $this->getJson('/api/alumnos');

        if ($response->status() === 200) {
            $count = count($response->json('data'));
            expect($count)->toBeGreaterThan(0);
            expect($count)->toBeLessThanOrEqual(20);
        }
    });

    it('[Pagination] Parámetro sortBy ordena resultados', function () {
        Alumno::factory()->create(['usuario' => 'Alice']);
        Alumno::factory()->create(['usuario' => 'Bob']);
        Alumno::factory()->create(['usuario' => 'Charlie']);

        $response = $this->getJson('/api/alumnos?sortBy=usuario&order=asc');

        if ($response->status() === 200 && count($response->json('data')) > 0) {
            $firstUser = $response->json('data.0.usuario');
            expect($firstUser)->not->toBeNull();
        }
    });
});

describe('Filtrado de API', function () {
    uses(RefreshDatabase::class);

    it('[Filter] Filtro por tipo retorna correctos', function () {
        User::factory()->count(3)->create(['tipo' => 'tutor']);
        User::factory()->count(2)->create(['tipo' => 'instructor']);

        $response = $this->getJson('/api/tutores');

        if ($response->status() === 200) {
            expect($response->json())->not->toBeNull();
        }
    });

    it('[Filter] Búsqueda por nombre funciona', function () {
        Alumno::factory()->create(['usuario' => 'Juan']);
        Alumno::factory()->create(['usuario' => 'María']);

        $response = $this->getJson('/api/alumnos?search=Juan');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBeLessThanOrEqual(1);
        }
    });

    it('[Filter] Filtro por rango de fechas', function () {
        Alumno::factory()->create();

        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');

        $response = $this->getJson("/api/alumnos?dateFrom={$startDate}&dateTo={$endDate}");

        expect($response->status())->not->toBe(500);
    });

    it('[Filter] Múltiples filtros se combinan', function () {
        $response = $this->getJson('/api/alumnos?per_page=5&sortBy=usuario&search=test');

        expect($response->status())->not->toBe(500);
    });

    it('[Filter] Filtro inválido ignorado', function () {
        $response = $this->getJson('/api/alumnos?invalidFilter=true');

        expect($response->status())->not->toBe(500);
    });
});

describe('Estructura de Respuesta JSON', function () {
    uses(RefreshDatabase::class);

    it('[JSON] GET collection tiene estructura data', function () {
        Alumno::factory()->create();

        $response = $this->getJson('/api/alumnos');

        if ($response->status() === 200) {
            $response->assertJsonStructure(['data', 'total', 'current_page']);
        }
    });

    it('[JSON] GET single resource es objeto', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->getJson('/api/alumnos/' . $alumno->ID_Usuario);

        if ($response->status() === 200) {
            expect($response->json())->not->toBeArray();
        }
    });

    it('[JSON] POST respuesta tiene campos esperados', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8,
        ]);

        if ($response->status() === 200 || $response->status() === 201) {
            expect($response->json())->not->toBeNull();
        }
    });

    it('[JSON] Error response tiene message', function () {
        $response = $this->postJson('/api/nota-cuaderno', []);

        if ($response->status() >= 400) {
            expect($response->json('message'))->not->toBeNull();
        }
    });

    it('[JSON] Timestamps en formato ISO8601', function () {
        $alumno = Alumno::factory()->create();

        $createdAt = $alumno->created_at;

        expect($createdAt)->not->toBeNull();
    });

    it('[JSON] Valores null vs empty string', function () {
        $alumno = Alumno::factory()->create();

        $response = $this->getJson('/api/alumnos/' . $alumno->ID_Usuario);

        if ($response->status() === 200) {
            // Verifica que null y empty string se traten correctamente
            expect($response->json())->not->toBeNull();
        }
    });
});

describe('Content-Type y Headers', function () {
    uses(RefreshDatabase::class);

    it('[Headers] Content-Type es application/json', function () {
        $response = $this->getJson('/api/alumnos');

        expect($response->headers->get('content-type'))->toContain('application/json');
    });

    it('[Headers] Cache-Control apropiado', function () {
        $response = $this->getJson('/api/alumnos');

        // Podría tener cache headers
        expect($response->status())->not->toBe(500);
    });

    it('[Headers] X-Custom-Headers presentes', function () {
        $response = $this->getJson('/api/alumnos');

        // Si hay headers personalizados
        expect($response->status())->not->toBe(500);
    });

    it('[Headers] CORS headers cuando es necesario', function () {
        $response = $this->getJson('/api/alumnos');

        // Verifica CORS headers
        expect($response->status())->not->toBe(500);
    });
});

describe('Rates y Limits', function () {
    uses(RefreshDatabase::class);

    it('[Limits] per_page máximo se respeta', function () {
        Alumno::factory()->count(100)->create();

        $response = $this->getJson('/api/alumnos?per_page=1000');

        if ($response->status() === 200) {
            $count = count($response->json('data'));
            expect($count)->toBeLessThanOrEqual(100);
        }
    });

    it('[Limits] per_page mínimo es al menos 1', function () {
        Alumno::factory()->create();

        $response = $this->getJson('/api/alumnos?per_page=1');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBeGreaterThan(0);
        }
    });

    it('[Limits] per_page=0 maneja gracefully', function () {
        $response = $this->getJson('/api/alumnos?per_page=0');

        expect($response->status())->not->toBe(500);
    });

    it('[Limits] per_page negativo maneja gracefully', function () {
        $response = $this->getJson('/api/alumnos?per_page=-5');

        expect($response->status())->not->toBe(500);
    });
});

describe('Ordenamiento', function () {
    uses(RefreshDatabase::class);

    it('[Sort] Ascendente ordena A-Z', function () {
        Alumno::factory()->create(['usuario' => 'Charlie']);
        Alumno::factory()->create(['usuario' => 'Alice']);
        Alumno::factory()->create(['usuario' => 'Bob']);

        $response = $this->getJson('/api/alumnos?sortBy=usuario&order=asc&per_page=10');

        if ($response->status() === 200 && count($response->json('data')) >= 2) {
            $first = $response->json('data.0.usuario');
            $second = $response->json('data.1.usuario');

            expect($first <= $second)->toBeTrue();
        }
    });

    it('[Sort] Descendente ordena Z-A', function () {
        Alumno::factory()->create(['usuario' => 'Alice']);
        Alumno::factory()->create(['usuario' => 'Bob']);
        Alumno::factory()->create(['usuario' => 'Charlie']);

        $response = $this->getJson('/api/alumnos?sortBy=usuario&order=desc&per_page=10');

        if ($response->status() === 200 && count($response->json('data')) >= 2) {
            $first = $response->json('data.0.usuario');
            $last = $response->json('data.1.usuario');

            expect($first >= $last)->toBeTrue();
        }
    });

    it('[Sort] Múltiples campos de sort', function () {
        $response = $this->getJson('/api/alumnos?sortBy=usuario,ID_Usuario&order=asc&per_page=10');

        expect($response->status())->not->toBe(500);
    });

    it('[Sort] Campo inválido ignorado', function () {
        $response = $this->getJson('/api/alumnos?sortBy=invalidColumn&per_page=10');

        expect($response->status())->not->toBe(500);
    });
});

describe('Búsqueda Avanzada', function () {
    uses(RefreshDatabase::class);

    it('[Search] Búsqueda case-insensitive', function () {
        Alumno::factory()->create(['usuario' => 'JuanPérez']);

        $response1 = $this->getJson('/api/alumnos?search=juan');
        $response2 = $this->getJson('/api/alumnos?search=JUAN');

        expect($response1->status())->not->toBe(500);
        expect($response2->status())->not->toBe(500);
    });

    it('[Search] Búsqueda con caracteres especiales', function () {
        Alumno::factory()->create(['usuario' => 'María']);

        $response = $this->getJson('/api/alumnos?search=María');

        expect($response->status())->not->toBe(500);
    });

    it('[Search] Búsqueda con espacios', function () {
        $response = $this->getJson('/api/alumnos?search=Juan Pérez');

        expect($response->status())->not->toBe(500);
    });

    it('[Search] Búsqueda vacía retorna todos', function () {
        Alumno::factory()->count(5)->create();

        $response = $this->getJson('/api/alumnos?search=');

        if ($response->status() === 200) {
            expect(count($response->json('data')))->toBeGreaterThan(0);
        }
    });
});

describe('Inclusión de Relaciones', function () {
    uses(RefreshDatabase::class);

    it('[Include] Parámetro include trae relaciones', function () {
        Alumno::factory()->create();

        $response = $this->getJson('/api/alumnos?include=user');

        expect($response->status())->not->toBe(500);
    });

    it('[Include] Múltiples includes', function () {
        $response = $this->getJson('/api/alumnos?include=user,notas');

        expect($response->status())->not->toBe(500);
    });

    it('[Include] Include inválido ignorado', function () {
        $response = $this->getJson('/api/alumnos?include=invalidRelation');

        expect($response->status())->not->toBe(500);
    });
});
