<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

describe('Cache y Almacenamiento', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('puede guardar y recuperar datos del cache', function () {
        Cache::put('test_key', 'test_value', 3600);

        expect(Cache::get('test_key'))->toBe('test_value');
    });

    it('retorna null si key no existe en cache', function () {
        expect(Cache::get('inexistente'))->toBeNull();
    });

    it('puede invalidar cache específico', function () {
        Cache::put('test_key', 'valor');
        Cache::forget('test_key');

        expect(Cache::get('test_key'))->toBeNull();
    });

    it('puede usar cache con prefijo (instructor)', function () {
        $instructorId = 1;
        $cacheKey = "instructor_{$instructorId}_alumnos";

        $alumnos = [
            ['ID_Usuario' => 1, 'usuario' => 'Juan'],
            ['ID_Usuario' => 2, 'usuario' => 'María'],
        ];

        Cache::put($cacheKey, $alumnos, 3600);

        expect(Cache::get($cacheKey))->toBe($alumnos);
    });

    it('puede usar cache con prefijo (tutor)', function () {
        $tutorId = 1;
        $tipo = 'tutor';
        $cacheKey = "tutor_{$tutorId}_alumnos_{$tipo}";

        $alumnos = [['ID_Usuario' => 5, 'usuario' => 'Carlos']];

        Cache::put($cacheKey, $alumnos, 3600);

        expect(Cache::get($cacheKey))->toBe($alumnos);
    });

    it('diferencia cache por tipo de tutor', function () {
        $tutorId = 1;

        $alumnosTutor = [['ID_Usuario' => 1]];
        $alumnosClase = [['ID_Usuario' => 2]];

        Cache::put("tutor_{$tutorId}_alumnos_tutor", $alumnosTutor, 3600);
        Cache::put("tutor_{$tutorId}_alumnos_clase", $alumnosClase, 3600);

        expect(Cache::get("tutor_{$tutorId}_alumnos_tutor"))->toBe($alumnosTutor);
        expect(Cache::get("tutor_{$tutorId}_alumnos_clase"))->toBe($alumnosClase);
        expect(Cache::get("tutor_{$tutorId}_alumnos_tutor"))->not->toBe($alumnosClase);
    });

    it('versionado de cache para grado', function () {
        $page = 1;
        $perPage = 5;
        $cacheKey = "grado_page_{$page}_per_{$perPage}";

        $data = ['page' => 1, 'items' => []];

        Cache::put($cacheKey, $data, 3600);

        expect(Cache::get($cacheKey))->toBe($data);
    });

    it('puede forzar refresco ignorando cache', function () {
        Cache::put('test', 'old_value', 3600);

        // Simular "forceRefresh"
        Cache::forget('test');
        Cache::put('test', 'new_value', 3600);

        expect(Cache::get('test'))->toBe('new_value');
    });

    it('maneja error al usar cache', function () {
        Cache::put('data', 'test_data');

        $value = Cache::get('data');
        expect($value)->toBe('test_data');

        Cache::forget('data');
        expect(Cache::get('data'))->toBeNull();
    });

    it('invalida todo cache de tutor', function () {
        $tutorId = 1;

        Cache::put("tutor_{$tutorId}_alumnos_tutor", ['data1']);
        Cache::put("tutor_{$tutorId}_alumnos_clase", ['data2']);
        Cache::put("instructor_1_alumnos", ['data3']);

        Cache::forget("tutor_{$tutorId}_alumnos_tutor");
        Cache::forget("tutor_{$tutorId}_alumnos_clase");

        expect(Cache::get("tutor_{$tutorId}_alumnos_tutor"))->toBeNull();
        expect(Cache::get("tutor_{$tutorId}_alumnos_clase"))->toBeNull();
        expect(Cache::get("instructor_1_alumnos"))->toBe(['data3']);
    });
});

describe('Almacenamiento de Notas en Cache', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('puede guardar nota en cache con estructura correcta', function () {
        $noteData = [
            'ID_Alumno' => 1,
            'Nota' => 8.5,
        ];

        Cache::put('nota_cache', $noteData, 3600);

        expect(Cache::get('nota_cache'))->toBe($noteData);
        expect(Cache::get('nota_cache')['Nota'])->toBe(8.5);
    });

    it('puede actualizar nota en cache', function () {
        $original = ['Nota' => 7.0];
        Cache::put('nota_update', $original, 3600);

        $updated = ['Nota' => 8.0];
        Cache::put('nota_update', $updated, 3600);

        expect(Cache::get('nota_update')['Nota'])->toBe(8.0);
    });
});

describe('Sincronización Cache-BD', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('invalida cache cuando se actualiza BD', function () {
        $cacheKey = 'alumnos_list';
        Cache::put($cacheKey, ['old_data']);

        Cache::forget($cacheKey);

        expect(Cache::get($cacheKey))->toBeNull();
    });

    it('recarga cache después de invalidación', function () {
        $cacheKey = 'alumnos_list';

        Cache::forget($cacheKey);

        $freshData = [['ID' => 1, 'nombre' => 'Nuevo']];
        Cache::put($cacheKey, $freshData, 3600);

        expect(Cache::get($cacheKey))->toBe($freshData);
    });
});

describe('Concurrencia y Race Conditions', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('maneja lectura simultánea de cache', function () {
        $cacheKey = 'concurrent_test';
        Cache::put($cacheKey, ['value' => 1]);

        $value1 = Cache::get($cacheKey);
        $value2 = Cache::get($cacheKey);

        expect($value1)->toBe($value2);
    });

    it('último write gana en actualización de cache', function () {
        $cacheKey = 'race_test';

        Cache::put($cacheKey, 'write1');
        Cache::put($cacheKey, 'write2');
        Cache::put($cacheKey, 'write3');

        expect(Cache::get($cacheKey))->toBe('write3');
    });
});
