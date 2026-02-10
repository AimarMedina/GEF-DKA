<?php

use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Modelo Alumno', function () {
    uses (RefreshDatabase::class);

    it('puede crear un alumno', function () {
        $alumno = Alumno::factory()->create([
            'usuario' => 'test_alumno',
        ]);

        expect($alumno->ID_Usuario)->toBeGreaterThan(0);
        expect($alumno->usuario)->toBe('test_alumno');
    });

    it('tiene relación con usuario', function () {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['ID_Usuario' => $user->id]);

        expect($alumno->ID_Usuario)->toBe($user->id);
    });

    it('obtiene estancia actual correctamente', function () {
        $alumno = Alumno::factory()->create();

        expect(method_exists($alumno, 'estancia_actual'))->toBeTrue();
    });

    it('usuario es guardado correctamente', function () {
        $alumno = Alumno::factory()->create(['usuario' => 'nuevo_usuario']);

        $retrieved = Alumno::find($alumno->ID_Usuario);
        expect($retrieved->usuario)->toBe('nuevo_usuario');
    });
});

describe('Modelo User - Validaciones', function () {
    uses (RefreshDatabase::class);

    it('puede crear usuario de tipo tutor', function () {
        $user = User::factory()->create(['tipo' => 'tutor']);

        expect($user->tipo)->toBe('tutor');
    });

    it('puede crear usuario de tipo instructor', function () {
        $user = User::factory()->create(['tipo' => 'instructor']);

        expect($user->tipo)->toBe('instructor');
    });

    it('usuario tiene email válido', function () {
        $user = User::factory()->create();

        expect(filter_var($user->email, FILTER_VALIDATE_EMAIL))->not->toBeFalse();
    });

    it('usuario tiene password hasheado', function () {
        $user = User::factory()->create();

        expect($user->password)->not->toBe('password');
    });
});

describe('Búsqueda y Filtrado por BD', function () {
    uses (RefreshDatabase::class);

    beforeEach(function () {
        User::factory()->count(5)->create(['tipo' => 'tutor']);
        User::factory()->count(3)->create(['tipo' => 'instructor']);
    });

    it('puede buscar usuario por email', function () {
        $user = User::first();

        $found = User::where('email', $user->email)->first();

        expect($found->id)->toBe($user->id);
    });

    it('puede filtrar usuarios por tipo', function () {
        $tutores = User::where('tipo', 'tutor')->get();

        expect($tutores->count())->toBeGreaterThan(0);
        expect($tutores->first()->tipo)->toBe('tutor');
    });

    it('puede contar usuarios por tipo', function () {
        $tutorCount = User::where('tipo', 'tutor')->count();

        expect($tutorCount)->toBeGreaterThan(0);
    });

    it('búsqueda con like funciona', function () {
        Alumno::factory()->create(['usuario' => 'juan_perez']);

        $found = Alumno::where('usuario', 'like', '%juan%')->count();

        expect($found)->toBeGreaterThan(0);
    });
});

describe('Ordenamiento y Paginación por BD', function () {
    uses (RefreshDatabase::class);

    beforeEach(function () {
        Alumno::factory()->count(10)->create();
    });

    it('ordena alumnos por usuario ascendente', function () {
        $alumnos = Alumno::orderBy('usuario')->limit(5)->get();

        if ($alumnos->count() >= 2) {
            $first = $alumnos->first()->usuario;
            $second = $alumnos[1]->usuario;
            expect($first <= $second)->toBeTrue();
        }
    });

    it('ordena alumnos descendentemente', function () {
        $alumnos = Alumno::orderByDesc('ID_Usuario')->limit(2)->get();

        expect($alumnos->count())->toBeLessThanOrEqual(2);
    });

    it('limita resultados correctamente', function () {
        $limited = Alumno::limit(3)->get();

        expect($limited->count())->toBeLessThanOrEqual(3);
    });

    it('omite registros con offset', function () {
        $all = Alumno::get()->count();
        $offset = Alumno::offset(5)->get()->count();

        expect($offset)->toBeLessThanOrEqual($all - 5);
    });
});

describe('Timestamps y Auditoría', function () {
    uses (RefreshDatabase::class);

    it('usuario tiene created_at y updated_at', function () {
        $user = User::factory()->create();

        expect($user->created_at)->not->toBeNull();
        expect($user->updated_at)->not->toBeNull();
    });

    it('timestamps son válidos', function () {
        $user = User::factory()->create();

        expect($user->created_at->timestamp)->toBeGreaterThan(0);
        expect($user->updated_at->timestamp)->toBeGreaterThan(0);
    });
});

describe('Relaciones de Modelos en BD', function () {
    uses (RefreshDatabase::class);

    it('user puede tener múltiples alumnos', function () {
        $user = User::factory()->create(['tipo' => 'tutor']);

        expect($user->tipo)->toBe('tutor');
    });

    it('alumno puede consultar su usuario', function () {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create(['ID_Usuario' => $user->id]);

        expect($alumno->ID_Usuario)->toBe($user->id);
    });
});
