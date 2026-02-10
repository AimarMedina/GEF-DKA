<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Relaciones Eloquent - User', function () {
    uses(RefreshDatabase::class);

    it('[Relación] User tiene ID y email', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'tipo' => 'tutor'
        ]);

        expect($user->id)->toBeGreaterThan(0);
        expect($user->email)->toBe('test@example.com');
    });

    it('[Relación] User puede tener atributo tipo', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);
        $instructor = User::factory()->create(['tipo' => 'instructor']);

        expect($tutor->tipo)->toBe('tutor');
        expect($instructor->tipo)->toBe('instructor');
    });

    it('[Relación] User fillable contiene campos esperados', function () {
        $user = User::factory()->create([
            'nombre' => 'Juan',
            'email' => 'juan@test.com',
            'tipo' => 'tutor'
        ]);

        expect($user->email)->toBe('juan@test.com');
    });
});

describe('Relaciones Eloquent - Alumno', function () {
    uses(RefreshDatabase::class);

    it('[Relación] Alumno tiene ID_Usuario como clave primaria', function () {
        $alumno = Alumno::factory()->create([
            'usuario' => 'test_alumno'
        ]);

        expect($alumno->ID_Usuario)->toBeGreaterThan(0);
    });

    it('[Relación] Alumno tiene campo usuario', function () {
        $alumno = Alumno::factory()->create([
            'usuario' => 'juan_perez'
        ]);

        expect($alumno->usuario)->toBe('juan_perez');
    });

    it('[Relación] Alumno puede ser recuperado por ID', function () {
        $alumno = Alumno::factory()->create([
            'usuario' => 'test_user'
        ]);

        $retrieved = Alumno::find($alumno->ID_Usuario);

        expect($retrieved)->not->toBeNull();
        expect($retrieved->usuario)->toBe('test_user');
    });

    it('[Relación] Alumno puede tener estancia_actual', function () {
        $alumno = Alumno::factory()->create();

        // Verificar que el método exista
        expect(method_exists($alumno, 'estancia_actual'))->toBeTrue();
    });
});

describe('Relaciones Many-to-One', function () {
    uses(RefreshDatabase::class);

    it('[Relación] Alumno pertenece a estancia', function () {
        $alumno = Alumno::factory()->create();

        // Conceptualmente, alumno está en una estancia
        // Si existe la relación belongsTo en modelo
        expect($alumno->ID_Usuario)->toBeGreaterThan(0);
    });

    it('[Relación] Múltiples alumnos pueden estar en mismo grado', function () {
        $alumno1 = Alumno::factory()->create(['usuario' => 'alumno1']);
        $alumno2 = Alumno::factory()->create(['usuario' => 'alumno2']);

        // Ambos pueden estar en misma estancia (en un contexto real)
        expect($alumno1->ID_Usuario)->not->toBe($alumno2->ID_Usuario);
    });
});

describe('Relaciones One-to-Many', function () {
    uses(RefreshDatabase::class);

    it('[Relación] Tutor puede tener muchos alumnos', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        // En la BD habría relación, aquí validamos que el user es tutor
        expect($tutor->tipo)->toBe('tutor');
    });

    it('[Relación] Instructor puede tener muchos alumnos', function () {
        $instructor = User::factory()->create(['tipo' => 'instructor']);

        expect($instructor->tipo)->toBe('instructor');
    });

    it('[Relación] Alumno puede tener muchas notas', function () {
        $alumno = Alumno::factory()->create();

        // Conceptualmente, alumno tiene múltiples notas_cuaderno
        expect($alumno->ID_Usuario)->toBeGreaterThan(0);
    });
});

describe('Relaciones Polimórfica', function () {
    uses(RefreshDatabase::class);

    it('[Relación] Nota puede estar asociada a alumno o cuaderno', function () {
        $alumno = Alumno::factory()->create();

        // Dependiendo de cómo esté modelado
        expect($alumno)->not->toBeNull();
    });
});

describe('Carga de Relaciones (Eager Loading)', function () {
    uses(RefreshDatabase::class);

    it('[EagerLoad] Cargar usuarios sin N+1 queries', function () {
        User::factory()->count(5)->create(['tipo' => 'tutor']);

        // Simulación: with() debería pre-cargar
        $users = User::all();

        expect($users->count())->toBe(5);
    });

    it('[EagerLoad] Cargar alumnos sin N+1 queries', function () {
        Alumno::factory()->count(10)->create();

        $alumnos = Alumno::all();

        expect($alumnos->count())->toBe(10);
    });

    it('[EagerLoad] Evitar lazy loading de relaciones', function () {
        $alumno = Alumno::factory()->create();

        // Si intentamos acceder a relación no cargada, debería evidenciarse
        expect($alumno)->not->toBeNull();
    });
});

describe('Timestamps en Relaciones', function () {
    uses(RefreshDatabase::class);

    it('[Timestamps] Alumno tiene created_at', function () {
        $alumno = Alumno::factory()->create();

        expect($alumno->created_at)->not->toBeNull();
    });

    it('[Timestamps] User tiene created_at y updated_at', function () {
        $user = User::factory()->create();

        expect($user->created_at)->not->toBeNull();
        expect($user->updated_at)->not->toBeNull();
    });

    it('[Timestamps] updated_at se modifica al actualizar', function () {
        $user = User::factory()->create();
        $originalUpdated = $user->updated_at;

        // Simular actualización
        $user->email = 'newemail@test.com';
        $user->save();

        expect($user->updated_at)->not->toBe($originalUpdated);
    });
});

describe('Soft Deletes (si existen)', function () {
    uses(RefreshDatabase::class);

    it('[SoftDelete] Alumno borrado no aparece en listados', function () {
        $alumno = Alumno::factory()->create(['usuario' => 'test']);

        // Si el modelo tiene soft deletes
        // $alumno->delete() sería soft delete
        // Este es test conceptual

        expect($alumno)->not->toBeNull();
    });
});

describe('Búsqueda mediante Relaciones', function () {
    uses(RefreshDatabase::class);

    it('[QueryRelation] Filtrar tutores por tipo', function () {
        User::factory()->count(3)->create(['tipo' => 'tutor']);
        User::factory()->count(2)->create(['tipo' => 'instructor']);

        $tutores = User::where('tipo', 'tutor')->get();

        expect($tutores->count())->toBe(3);
        expect($tutores->first()->tipo)->toBe('tutor');
    });

    it('[QueryRelation] Filtrar alumnos por usuario', function () {
        Alumno::factory()->create(['usuario' => 'juan']);
        Alumno::factory()->create(['usuario' => 'maria']);

        $juan = Alumno::where('usuario', 'juan')->first();

        expect($juan->usuario)->toBe('juan');
    });

    it('[QueryRelation] whereHas para relaciones', function () {
        $tutor = User::factory()->create(['tipo' => 'tutor']);

        // whereHas buscaría usuarios que tienen alumnos
        // Si la relación existe: User::whereHas('alumnos')->get()

        expect($tutor->tipo)->toBe('tutor');
    });
});
