<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\Score;
use Illuminate\Database\Seeder;

class JournalCaseSeeder extends Seeder
{
    /**
     * Run the database seeds for Research Journal Chapter 3.5 Case Study.
     */
    public function run(): void
    {
        // 1. Data 5 Kriteria Kasus Uji Bab 3.5
        $criteriaData = [
            [
                'code' => 'C1',
                'name' => 'Harga per Bulan',
                'unit' => 'Rp',
                'type' => 'cost',
                'weight' => 0.25,
            ],
            [
                'code' => 'C2',
                'name' => 'Kapasitas Penyimpanan',
                'unit' => 'GB',
                'type' => 'benefit',
                'weight' => 0.20,
            ],
            [
                'code' => 'C3',
                'name' => 'Waktu Respon',
                'unit' => 'ms',
                'type' => 'cost',
                'weight' => 0.20,
            ],
            [
                'code' => 'C4',
                'name' => 'Uptime',
                'unit' => '%',
                'type' => 'benefit',
                'weight' => 0.20,
            ],
            [
                'code' => 'C5',
                'name' => 'Dukungan Teknis',
                'unit' => 'Skala',
                'type' => 'benefit',
                'weight' => 0.15,
            ],
        ];

        // Bersihkan data lama agar sinkron
        Score::query()->delete();
        Alternative::query()->delete();
        Criterion::query()->delete();

        $criteriaMap = [];
        foreach ($criteriaData as $item) {
            $criterion = Criterion::create($item);
            $criteriaMap[$item['code']] = $criterion->id;
        }

        // 2. Data 5 Alternatif Hosting (Hosting A s/d Hosting E)
        $alternativesData = [
            [
                'code' => 'A1',
                'provider' => 'Penyedia A',
                'name' => 'Hosting A',
                'category' => 'Shared Hosting',
                'spec_raw' => '20 GB SSD / 520 ms / 99.5% Uptime / Dukungan 80 / Rp35.000',
                'scores' => ['C1' => 35000, 'C2' => 20, 'C3' => 520, 'C4' => 99.5, 'C5' => 80],
            ],
            [
                'code' => 'A2',
                'provider' => 'Penyedia B',
                'name' => 'Hosting B',
                'category' => 'Cloud Hosting',
                'spec_raw' => '50 GB SSD / 380 ms / 99.8% Uptime / Dukungan 85 / Rp28.000',
                'scores' => ['C1' => 28000, 'C2' => 50, 'C3' => 380, 'C4' => 99.8, 'C5' => 85],
            ],
            [
                'code' => 'A3',
                'provider' => 'Penyedia C',
                'name' => 'Hosting C',
                'category' => 'Shared Hosting',
                'spec_raw' => '30 GB SSD / 540 ms / 99.4% Uptime / Dukungan 80 / Rp38.000',
                'scores' => ['C1' => 38000, 'C2' => 30, 'C3' => 540, 'C4' => 99.4, 'C5' => 80],
            ],
            [
                'code' => 'A4',
                'provider' => 'Penyedia D',
                'name' => 'Hosting D',
                'category' => 'Budget Hosting',
                'spec_raw' => '10 GB SSD / 850 ms / 97.5% Uptime / Dukungan 55 / Rp22.000',
                'scores' => ['C1' => 22000, 'C2' => 10, 'C3' => 850, 'C4' => 97.5, 'C5' => 55],
            ],
            [
                'code' => 'A5',
                'provider' => 'Penyedia E',
                'name' => 'Hosting E',
                'category' => 'Business Hosting',
                'spec_raw' => '40 GB SSD / 420 ms / 99.6% Uptime / Dukungan 85 / Rp30.000',
                'scores' => ['C1' => 30000, 'C2' => 40, 'C3' => 420, 'C4' => 99.6, 'C5' => 85],
            ],
        ];

        foreach ($alternativesData as $item) {
            $alt = Alternative::create([
                'code' => $item['code'],
                'provider' => $item['provider'],
                'name' => $item['name'],
                'category' => $item['category'],
                'spec_raw' => $item['spec_raw'],
            ]);

            foreach ($item['scores'] as $critCode => $val) {
                if (isset($criteriaMap[$critCode])) {
                    Score::create([
                        'alternative_id' => $alt->id,
                        'criterion_id' => $criteriaMap[$critCode],
                        'value' => $val,
                    ]);
                }
            }
        }
    }
}
