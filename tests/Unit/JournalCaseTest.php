<?php

namespace Tests\Unit;

use App\Services\WaspasService;
use PHPUnit\Framework\TestCase;

class JournalCaseTest extends TestCase
{
    /**
     * Menguji kasus uji penelitian Jurnal Bab 3.5:
     * - Alternatif: Hosting A, B, C, D, E
     * - Hosting B unggul (Storage 50 GB terbesar, Respon 380 ms tercepat, Harga Rp28.000)
     * - Hosting D harga termurah namun peringkat terakhir (uptime, respon, dukungan teknis terendah)
     * - Urutan peringkat stabil di λ = 0 (WPM), λ = 0.5 (WASPAS), dan λ = 1 (WSM): B, E, A, C, D
     */
    public function test_journal_case_waspas_stability_and_ranking(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Harga per Bulan', 'unit' => 'Rp', 'type' => 'cost', 'weight' => 0.25],
            ['code' => 'C2', 'name' => 'Kapasitas Penyimpanan', 'unit' => 'GB', 'type' => 'benefit', 'weight' => 0.20],
            ['code' => 'C3', 'name' => 'Waktu Respon', 'unit' => 'ms', 'type' => 'cost', 'weight' => 0.20],
            ['code' => 'C4', 'name' => 'Uptime', 'unit' => '%', 'type' => 'benefit', 'weight' => 0.20],
            ['code' => 'C5', 'name' => 'Dukungan Teknis', 'unit' => 'Skala', 'type' => 'benefit', 'weight' => 0.15],
        ];

        $alternatives = [
            [
                'code' => 'A1',
                'name' => 'Hosting A',
                'provider' => 'Penyedia A',
                'x' => ['C1' => 35000, 'C2' => 20, 'C3' => 520, 'C4' => 99.5, 'C5' => 80],
            ],
            [
                'code' => 'A2',
                'name' => 'Hosting B',
                'provider' => 'Penyedia B',
                'x' => ['C1' => 28000, 'C2' => 50, 'C3' => 380, 'C4' => 99.8, 'C5' => 85],
            ],
            [
                'code' => 'A3',
                'name' => 'Hosting C',
                'provider' => 'Penyedia C',
                'x' => ['C1' => 45000, 'C2' => 25, 'C3' => 600, 'C4' => 99.2, 'C5' => 75],
            ],
            [
                'code' => 'A4',
                'name' => 'Hosting D',
                'provider' => 'Penyedia D',
                'x' => ['C1' => 22000, 'C2' => 10, 'C3' => 850, 'C4' => 97.5, 'C5' => 55],
            ],
            [
                'code' => 'A5',
                'name' => 'Hosting E',
                'provider' => 'Penyedia E',
                'x' => ['C1' => 30000, 'C2' => 40, 'C3' => 420, 'C4' => 99.6, 'C5' => 85],
            ],
        ];

        $service = new WaspasService();
        $resDefault = $service->calculate($criteria, $alternatives, 0.5);
        $this->assertTrue($resDefault['ok']);
        $this->assertTrue($resDefault['weights_valid']);

        $rankMapDefault = [];
        foreach ($resDefault['alternatives'] as $alt) {
            $rankMapDefault[$alt['name']] = $alt['rank'];
        }

        $this->assertEquals(1, $rankMapDefault['Hosting B'], 'Hosting B harus peringkat 1 pada lambda 0.5');
        $this->assertEquals(2, $rankMapDefault['Hosting E'], 'Hosting E harus peringkat 2 pada lambda 0.5');
        $this->assertEquals(3, $rankMapDefault['Hosting A'], 'Hosting A harus peringkat 3 pada lambda 0.5');
        $this->assertEquals(4, $rankMapDefault['Hosting C'], 'Hosting C harus peringkat 4 pada lambda 0.5');
        $this->assertEquals(5, $rankMapDefault['Hosting D'], 'Hosting D harus peringkat 5 pada lambda 0.5');

        // 2. Uji Sensitivitas pada λ = 1.0 (Hanya WSM / Q1)
        $resWsm = $service->calculate($criteria, $alternatives, 1.0);
        $rankMapWsm = [];
        foreach ($resWsm['alternatives'] as $alt) {
            $rankMapWsm[$alt['name']] = $alt['rank'];
        }
        $this->assertEquals(1, $rankMapWsm['Hosting B'], 'Hosting B harus peringkat 1 pada WSM (lambda 1)');
        $this->assertEquals(2, $rankMapWsm['Hosting E'], 'Hosting E harus peringkat 2 pada WSM (lambda 1)');
        $this->assertEquals(3, $rankMapWsm['Hosting A'], 'Hosting A harus peringkat 3 pada WSM (lambda 1)');
        $this->assertEquals(4, $rankMapWsm['Hosting C'], 'Hosting C harus peringkat 4 pada WSM (lambda 1)');
        $this->assertEquals(5, $rankMapWsm['Hosting D'], 'Hosting D harus peringkat 5 pada WSM (lambda 1)');

        // 3. Uji Sensitivitas pada λ = 0.0 (Hanya WPM / Q2)
        $resWpm = $service->calculate($criteria, $alternatives, 0.0);
        $rankMapWpm = [];
        foreach ($resWpm['alternatives'] as $alt) {
            $rankMapWpm[$alt['name']] = $alt['rank'];
        }
        $this->assertEquals(1, $rankMapWpm['Hosting B'], 'Hosting B harus peringkat 1 pada WPM (lambda 0)');
        $this->assertEquals(2, $rankMapWpm['Hosting E'], 'Hosting E harus peringkat 2 pada WPM (lambda 0)');
        $this->assertEquals(3, $rankMapWpm['Hosting A'], 'Hosting A harus peringkat 3 pada WPM (lambda 0)');
        $this->assertEquals(4, $rankMapWpm['Hosting C'], 'Hosting C harus peringkat 4 pada WPM (lambda 0)');
        $this->assertEquals(5, $rankMapWpm['Hosting D'], 'Hosting D harus peringkat 5 pada WPM (lambda 0)');
    }
}
