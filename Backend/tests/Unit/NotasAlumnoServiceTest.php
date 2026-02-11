<?php

namespace Tests\Unit;

use App\Http\Services\NotasAlumnoService;
use Tests\TestCase;

class NotasAlumnoServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotasAlumnoService();
    }

    /**
     * Test: calcularNotaFinalEmpresa con fórmula ponderada correcta
     * Fórmula: (Cuaderno * 0.20) + (Transversal * 0.20) + (Técnica * 0.60)
     */
    public function test_calcular_nota_final_empresa_formula_ponderada()
    {
        $notaCuaderno = 8.0;    // 20%
        $notaTransversal = 7.0; // 20%
        $notasTecnicas = [
            1 => 9.0, // 60%
            2 => 6.0  // 60%
        ];

        $notasFinales = $this->service->calcularNotaFinalEmpresa(
            $notaCuaderno,
            $notaTransversal,
            $notasTecnicas
        );

        // Asignatura 1: (8 * 0.20) + (7 * 0.20) + (9 * 0.60) = 1.6 + 1.4 + 5.4 = 8.4
        $this->assertEquals(8.4, $notasFinales[1]);
        
        // Asignatura 2: (8 * 0.20) + (7 * 0.20) + (6 * 0.60) = 1.6 + 1.4 + 3.6 = 6.6
        $this->assertEquals(6.6, $notasFinales[2]);
    }

    /**
     * Test: calcularNotaFinalEmpresa con notas bajas
     */
    public function test_calcular_nota_final_empresa_notas_bajas()
    {
        $notaCuaderno = 2.0;
        $notaTransversal = 3.0;
        $notasTecnicas = [1 => 1.0];

        $notasFinales = $this->service->calcularNotaFinalEmpresa(
            $notaCuaderno,
            $notaTransversal,
            $notasTecnicas
        );

        // (2 * 0.20) + (3 * 0.20) + (1 * 0.60) = 0.4 + 0.6 + 0.6 = 1.6
        $this->assertEquals(1.6, $notasFinales[1]);
    }

    /**
     * Test: calcularNotaFinalEmpresa con notas máximas
     */
    public function test_calcular_nota_final_empresa_notas_maximas()
    {
        $notaCuaderno = 10.0;
        $notaTransversal = 10.0;
        $notasTecnicas = [1 => 10.0];

        $notasFinales = $this->service->calcularNotaFinalEmpresa(
            $notaCuaderno,
            $notaTransversal,
            $notasTecnicas
        );

        // (10 * 0.20) + (10 * 0.20) + (10 * 0.60) = 2 + 2 + 6 = 10
        $this->assertEquals(10.0, $notasFinales[1]);
    }

    /**
     * Test: calcularNotaFinalEmpresa redondea a 2 decimales
     */
    public function test_calcular_nota_final_empresa_redondea_decimales()
    {
        $notaCuaderno = 7.33;
        $notaTransversal = 8.45;
        $notasTecnicas = [1 => 9.12];

        $notasFinales = $this->service->calcularNotaFinalEmpresa(
            $notaCuaderno,
            $notaTransversal,
            $notasTecnicas
        );

        // Verificar que tiene máximo 2 decimales
        $this->assertIsFloat($notasFinales[1]);
        $decimales = strlen(substr(strrchr($notasFinales[1], '.'), 1));
        $this->assertLessThanOrEqual(2, $decimales);
    }

    /**
     * Test: calcularNotasFinalesPorAsignatura con fórmula 20-80
     * Fórmula: (NotaEmpresa * 0.20) + (NotaTutor * 0.80)
     */
    public function test_calcular_notas_finales_por_asignatura_formula_20_80()
    {
        $notasEmpresa = [
            1 => 8.0,
            2 => 6.0
        ];

        $notasEgibide = [
            1 => 9.0,
            2 => 7.0
        ];

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // Asignatura 1: (8 * 0.20) + (9 * 0.80) = 1.6 + 7.2 = 8.8
        $this->assertEquals(8.8, $notasFinales[1]);

        // Asignatura 2: (6 * 0.20) + (7 * 0.80) = 1.2 + 5.6 = 6.8
        $this->assertEquals(6.8, $notasFinales[2]);
    }

    /**
     * Test: calcularNotasFinalesPorAsignatura con nota Egibide extrema
     */
    public function test_calcular_notas_finales_nota_egibide_extrema()
    {
        $notasEmpresa = [1 => 2.0]; // Muy baja en empresa
        $notasEgibide = [1 => 10.0]; // Muy alta en Egibide

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // (2 * 0.20) + (10 * 0.80) = 0.4 + 8.0 = 8.4
        // El 80% de Egibide domina la nota final
        $this->assertEquals(8.4, $notasFinales[1]);
    }

    /**
     * Test: calcularNotasFinalesPorAsignatura sin nota Egibide retorna null
     */
    public function test_calcular_notas_finales_sin_egibide_retorna_null()
    {
        $notasEmpresa = [
            1 => 8.0,
            2 => 6.0
        ];

        $notasEgibide = [
            1 => 9.0
            // Asignatura 2 no tiene nota Egibide
        ];

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // Asignatura 1 tiene nota calculada
        $this->assertArrayHasKey(1, $notasFinales);
        $this->assertEquals(8.8, $notasFinales[1]);
        
        // Asignatura 2 está presente pero con null (sin nota Egibide)
        $this->assertArrayHasKey(2, $notasFinales);
        $this->assertNull($notasFinales[2]);
    }

    /**
     * Test: calcularNotasFinalesPorAsignatura con arrays vacíos
     */
    public function test_calcular_notas_finales_arrays_vacios()
    {
        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura([], []);

        $this->assertIsArray($notasFinales);
        $this->assertEmpty($notasFinales);
    }

    /**
     * Test: calcularNotasFinalesPorAsignatura conjunto vacío de Egibide retorna nulls
     */
    public function test_calcular_notas_finales_egibide_vacio()
    {
        $notasEmpresa = [1 => 8.0, 2 => 7.0];
        $notasEgibide = [];

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // Sin notas Egibide, todas las notas deben ser null
        $this->assertCount(2, $notasFinales);
        $this->assertNull($notasFinales[1]);
        $this->assertNull($notasFinales[2]);
    }

    /**
     * Test: peso correcto de Empresa vs Egibide en fórmula final
     * Egibide tiene peso 80%, debe dominar la nota
     */
    public function test_formula_finales_peso_correcto_egibide()
    {
        $notasEmpresa = [1 => 1.0]; // Nota muy baja
        $notasEgibide = [1 => 9.0]; // Nota muy alta

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // (1 * 0.20) + (9 * 0.80) = 0.2 + 7.2 = 7.4
        // La nota DEBE ser cercana a 9.0 (80% de Egibide domina)
        // Mayor que 7 (domina sobre empresa), menor que 9.0
        $this->assertGreaterThan(7.0, $notasFinales[1]);
        $this->assertLessThanOrEqual(7.5, $notasFinales[1]);
    }

    /**
     * Test: peso correcto de los componentes en fórmula empresa
     * Técnicas tienen peso 60%, deben dominar
     */
    public function test_formula_empresa_peso_correcto_tecnicas()
    {
        // Cuaderno y Transversal bajos, Técnicas altas
        $notaCuaderno = 1.0;
        $notaTransversal = 1.0;
        $notasTecnicas = [1 => 10.0];

        $notasFinales = $this->service->calcularNotaFinalEmpresa(
            $notaCuaderno,
            $notaTransversal,
            $notasTecnicas
        );

        // (1 * 0.20) + (1 * 0.20) + (10 * 0.60) = 0.2 + 0.2 + 6 = 6.4
        // La nota DEBE ser cercana a 6.4, lejos de 1 (técnicas dominan)
        $this->assertEquals(6.4, $notasFinales[1]);
    }

    /**
     * Test: múltiples asignaturas varias notas
     */
    public function test_calcular_notas_finales_multiples_asignaturas()
    {
        $notasEmpresa = [
            1 => 8.0,
            2 => 6.0,
            3 => 9.0,
            4 => 5.0
        ];

        $notasEgibide = [
            1 => 7.0,
            2 => 8.0,
            3 => 10.0,
            4 => 6.0
        ];

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // Verificar estructura y conteo
        $this->assertCount(4, $notasFinales);
        $this->assertIsFloat($notasFinales[1]);
        $this->assertIsFloat($notasFinales[2]);
        $this->assertIsFloat($notasFinales[3]);
        $this->assertIsFloat($notasFinales[4]);

        // Verificar orden correcto de aplicación de fórmula
        // Asig 1: (8*0.2) + (7*0.8) = 1.6 + 5.6 = 7.2
        $this->assertEquals(7.2, $notasFinales[1]);
    }

    /**
     * Test: redondeo correcto a 2 decimales en fórmula final
     */
    public function test_redondeo_notas_finales_decimales()
    {
        // Números que generan muchos decimales
        $notasEmpresa = [1 => 7.33];
        $notasEgibide = [1 => 8.67];

        $notasFinales = $this->service->calcularNotasFinalesPorAsignatura(
            $notasEmpresa,
            $notasEgibide
        );

        // Verificar que no tiene más de 2 decimales
        $valor = $notasFinales[1];
        $partes = explode('.', (string)$valor);
        if (isset($partes[1])) {
            $this->assertLessThanOrEqual(2, strlen($partes[1]));
        }
    }
}

