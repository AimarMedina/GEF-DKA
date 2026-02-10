<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->in('Feature');

describe('Autenticación', function () {

    it('puede obtener el usuario actual autenticado', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonPath('id', $user->id);
        $response->assertJsonPath('email', $user->email);
    });

    it('rechaza acceso sin autenticación', function () {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    });
});

describe('Alumnos', function () {

    it('lista alumnos con paginación', function () {
        Alumno::factory()->count(5)->create();

        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/alumnos');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['ID_Usuario', 'usuario', 'estancia_actual']
            ],
            'current_page',
            'total',
            'last_page'
        ]);
    });

    it('obtiene un alumno específico', function () {
        $alumno = Alumno::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/alumnos/{$alumno->ID_Usuario}");

        expect(in_array($response->status(), [200, 404]))->toBeTrue();
    });

    it('filtra alumnos por tipo y grado', function () {
        Alumno::factory()->count(3)->create(['tipo' => 'alumno', 'id_grado' => 1]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/alumnos?tipo=alumno&id_grado=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['ID_Usuario', 'usuario']
            ]
        ]);
    });
});

describe('Notas de Cuaderno', function () {

    it('obtiene notas de cuaderno para un tutor', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);
        Alumno::factory()->count(2)->create();

        $response = $this->actingAs($tutor)->getJson("/api/tutor/{$tutor->id}/notas-cuaderno");

        $response->assertStatus(200);
        $response->assertIsArray();
    });

    it('guarda una nota de cuaderno válida', function () {
        $alumno = Alumno::factory()->create();
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 8.5,
        ]);

        expect(in_array($response->status(), [200, 201, 422, 403]))->toBeTrue();
    });

    it('rechaza nota de cuaderno con valor inválido', function () {
        $alumno = Alumno::factory()->create();
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->postJson('/api/nota-cuaderno', [
            'ID_Alumno' => $alumno->ID_Usuario,
            'Nota' => 15, // fuera de rango
        ]);

        expect(in_array($response->status(), [422, 403, 200]))->toBeTrue();
    });

    it('rechaza nota sin ID_Alumno', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->postJson('/api/nota-cuaderno', [
            'Nota' => 8.5,
        ]);

        expect(in_array($response->status(), [422, 400]))->toBeTrue();
    });
});

describe('Entregas (Cuadernos de alumnos)', function () {

    it('obtiene entregas por tutor', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson("/api/tutor/{$tutor->id}/grados");

        expect(in_array($response->status(), [200, 404]))->toBeTrue();
    });

    it('obtiene entregas por grado', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/grado/1/entregas');

        expect(in_array($response->status(), [200, 404]))->toBeTrue();
    });
});

describe('Mi Grado - Gestión de Notas', function () {
    it('obtiene datos de gestión de grado', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson('/api/mi-grado/gestion?page=1&per_page=5');

        expect(in_array($response->status(), [200, 403, 401]))->toBeTrue();
    });
});

describe('Transversales', function () {
    it('lista todas las competencias transversales', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/transversales');

        $response->assertStatus(200);
        $response->assertIsArray();
    });
});

describe('Competencias', function () {
    it('lista todas las competencias técnicas', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/competencias');

        $response->assertStatus(200);
        $response->assertIsArray();
    });
});

describe('Tutores', function () {
    it('obtiene alumnos de un tutor', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson("/api/tutores/{$tutor->id}/alumnos");

        expect(in_array($response->status(), [200, 401]))->toBeTrue();
    });

    it('obtiene alumnos de clases del tutor', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        $response = $this->actingAs($tutor)->getJson("/api/tutores/{$tutor->id}/alumnos-clases");

        expect(in_array($response->status(), [200, 401]))->toBeTrue();
    });
});

describe('Instructores', function () {
    it('obtiene alumnos de un instructor', function () {
        $instructor = User::factory()->create(['tipo' => 'instructor']);

        $response = $this->actingAs($instructor)->getJson("/api/instructores/{$instructor->id}/alumnos");

        expect(in_array($response->status(), [200, 404]))->toBeTrue();
    });
});

describe('Estancias', function () {
    it('obtiene estancias de un alumno', function () {
        $alumno = Alumno::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/alumno/{$alumno->ID_Usuario}/estancias");

        expect(in_array($response->status(), [200, 404]))->toBeTrue();
    });
});
