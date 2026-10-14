<?php

namespace Tests\Unit;

use App\Services\WaspasService;
use PHPUnit\Framework\TestCase;

class WaspasServiceTest extends TestCase
{
    private WaspasService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WaspasService();
    }

    /**
     * Test perhitungan tangan dasar sesuai CLAUDE.md:
     * C1 benefit (0.6), C2 cost (0.4)
     * A = (10, 4), B = (5, 2), lambda = 0.5
     */
    public function test_basic_waspas_calculation(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 0.6],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 0.4],
        ];

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5, 'C2' => 2]],
        ];

        $result = $this->service->calculate($criteria, $alternatives, 0.5);

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['weights_valid']);
        $this->assertEquals(0.5, $result['lambda']);

        // Nilai pembanding (extremes)
        $this->assertEquals(10, $result['extremes']['C1']);
        $this->assertEquals(2, $result['extremes']['C2']);

        // Alternatif A & B
        $altMap = [];
        foreach ($result['alternatives'] as $item) {
            $altMap[$item['code']] = $item;
        }

        // Matriks R
        $this->assertEquals(1.0, $altMap['A']['r']['C1']);
        $this->assertEquals(0.5, $altMap['A']['r']['C2']);
        $this->assertEquals(0.5, $altMap['B']['r']['C1']);
        $this->assertEquals(1.0, $altMap['B']['r']['C2']);

        // SAW
        $this->assertEqualsWithDelta(0.8, $altMap['A']['saw'], 0.0001);
        $this->assertEqualsWithDelta(0.7, $altMap['B']['saw'], 0.0001);

        // WP
        $this->assertEqualsWithDelta(0.7579, $altMap['A']['wp'], 0.0001);
        $this->assertEqualsWithDelta(0.6598, $altMap['B']['wp'], 0.0001);

        // Q (WASPAS)
        $this->assertEqualsWithDelta(0.7789, $altMap['A']['q'], 0.0001);
        $this->assertEqualsWithDelta(0.6799, $altMap['B']['q'], 0.0001);

        // Peringkat
        $this->assertEquals(1, $altMap['A']['rank']);
        $this->assertEquals(2, $altMap['B']['rank']);
    }

    /**
     * Test lambda = 1 menghasilkan nilai Q persis sama dengan skor SAW
     */
    public function test_lambda_one_equals_saw(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 0.6],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 0.4],
        ];

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5, 'C2' => 2]],
        ];

        $result = $this->service->calculate($criteria, $alternatives, 1.0);

        foreach ($result['alternatives'] as $alt) {
            $this->assertEquals($alt['saw'], $alt['q']);
        }
    }

    /**
     * Test lambda = 0 menghasilkan nilai Q persis sama dengan skor WP
     */
    public function test_lambda_zero_equals_wp(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 0.6],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 0.4],
        ];

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5, 'C2' => 2]],
        ];

        $result = $this->service->calculate($criteria, $alternatives, 0.0);

        foreach ($result['alternatives'] as $alt) {
            $this->assertEquals($alt['wp'], $alt['q']);
        }
    }

    /**
     * Test alternatif yang bernilai kosong atau tidak lengkap masuk ke array 'excluded'
     */
    public function test_incomplete_alternative_is_excluded(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 0.5],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 0.5],
        ];

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5, 'C2' => 2]],
            ['id' => 3, 'code' => 'C', 'name' => 'Alternatif C', 'x' => ['C1' => 8]], // C2 kosong
        ];

        $result = $this->service->calculate($criteria, $alternatives, 0.5);

        $this->assertTrue($result['ok']);
        $this->assertCount(2, $result['alternatives']);
        $this->assertCount(1, $result['excluded']);
        $this->assertEquals('Alternatif C', $result['excluded'][0]['name']);
        $this->assertEquals('Nilai belum lengkap', $result['excluded'][0]['reason']);
    }

    /**
     * Test jika alternatif lengkap kurang dari 2, maka ok = false
     */
    public function test_less_than_two_complete_alternatives_fails(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 0.5],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 0.5],
        ];

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5]], // incomplete
        ];

        $result = $this->service->calculate($criteria, $alternatives, 0.5);

        $this->assertFalse($result['ok']);
        $this->assertEmpty($result['alternatives']);
        $this->assertCount(1, $result['excluded']);
    }

    /**
     * Test bobot total tidak sama dengan 1: tetap menghitung dengan bobot normalisasi, weights_valid = false
     */
    public function test_weights_sum_not_one_is_normalized_and_flagged(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Kriteria 1', 'type' => 'benefit', 'weight' => 3.0],
            ['code' => 'C2', 'name' => 'Kriteria 2', 'type' => 'cost', 'weight' => 2.0],
        ]; // Total = 5.0 -> ternormalisasi: C1 = 0.6, C2 = 0.4

        $alternatives = [
            ['id' => 1, 'code' => 'A', 'name' => 'Alternatif A', 'x' => ['C1' => 10, 'C2' => 4]],
            ['id' => 2, 'code' => 'B', 'name' => 'Alternatif B', 'x' => ['C1' => 5, 'C2' => 2]],
        ];

        $result = $this->service->calculate($criteria, $alternatives, 0.5);

        $this->assertTrue($result['ok']);
        $this->assertFalse($result['weights_valid']);

        $altMap = [];
        foreach ($result['alternatives'] as $item) {
            $altMap[$item['code']] = $item;
        }

        // Hasil harus tetap sama dengan rasio 0.6 dan 0.4
        $this->assertEqualsWithDelta(0.7789, $altMap['A']['q'], 0.0001);
        $this->assertEqualsWithDelta(0.6799, $altMap['B']['q'], 0.0001);
    }
}
