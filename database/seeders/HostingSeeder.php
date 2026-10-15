<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\Score;
use Illuminate\Database\Seeder;

class HostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Kriteria Default
        $criteriaData = [
            [
                'code' => 'C1',
                'name' => 'Harga per bulan',
                'unit' => 'Rp',
                'type' => 'cost',
                'weight' => 0.30,
            ],
            [
                'code' => 'C2',
                'name' => 'Storage',
                'unit' => 'GB',
                'type' => 'benefit',
                'weight' => 0.25,
            ],
            [
                'code' => 'C3',
                'name' => 'RAM',
                'unit' => 'GB',
                'type' => 'benefit',
                'weight' => 0.25,
            ],
            [
                'code' => 'C4',
                'name' => 'CPU',
                'unit' => 'vCPU',
                'type' => 'benefit',
                'weight' => 0.20,
            ],
        ];

        $criteriaMap = [];
        foreach ($criteriaData as $item) {
            $criterion = Criterion::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'unit' => $item['unit'],
                    'type' => $item['type'],
                    'weight' => $item['weight'],
                ]
            );
            $criteriaMap[$item['code']] = $criterion->id;
        }

        // 2. Data Alternatif & Skor Konversi
        $alternativesData = [
            [
                'code' => 'A1',
                'provider' => 'Hostinger',
                'name' => 'Hostinger Single',
                'category' => 'Shared Hosting',
                'spec_raw' => '50 GB / 1 GB / Shared / Rp12.900–29.900',
                'scores' => ['C1' => 21400, 'C2' => 50, 'C3' => 1, 'C4' => 0.5],
            ],
            [
                'code' => 'A2',
                'provider' => 'Hostinger',
                'name' => 'Hostinger Premium',
                'category' => 'Shared Hosting',
                'spec_raw' => '100 GB / 1,5 GB / Shared / Rp24.900–39.900',
                'scores' => ['C1' => 32400, 'C2' => 100, 'C3' => 1.5, 'C4' => 0.5],
            ],
            [
                'code' => 'A3',
                'provider' => 'Hostinger',
                'name' => 'Hostinger Business',
                'category' => 'Advanced Web',
                'spec_raw' => '200 GB / 1,5 GB / 2 vCPU / Rp38.900–59.900',
                'scores' => ['C1' => 49400, 'C2' => 200, 'C3' => 1.5, 'C4' => 2],
            ],
            [
                'code' => 'A4',
                'provider' => 'Hostinger',
                'name' => 'Hostinger Cloud Startup',
                'category' => 'Cloud Hosting',
                'spec_raw' => '200 GB / 3 GB / 2 Cores / Rp116.900',
                'scores' => ['C1' => 116900, 'C2' => 200, 'C3' => 3, 'C4' => 2],
            ],
            [
                'code' => 'A5',
                'provider' => 'Hostinger',
                'name' => 'Hostinger KVM 1 (VPS)',
                'category' => 'Virtual Private Server',
                'spec_raw' => '50 GB / 4 GB / 1 Core / Rp116.900',
                'scores' => ['C1' => 116900, 'C2' => 50, 'C3' => 4, 'C4' => 1],
            ],
            [
                'code' => 'A6',
                'provider' => 'Niagahoster',
                'name' => 'Niagahoster Bayi / Single',
                'category' => 'Shared Hosting',
                'spec_raw' => '500 MB–10 GB / 1 GB / Shared / Rp10.000–19.800',
                'scores' => ['C1' => 14900, 'C2' => 5.25, 'C3' => 1, 'C4' => 0.5],
            ],
            [
                'code' => 'A7',
                'provider' => 'Niagahoster',
                'name' => 'Niagahoster Pelajar / Premium',
                'category' => 'Shared Hosting',
                'spec_raw' => '100 GB / 1,5 GB / Shared / Rp24.900–40.200',
                'scores' => ['C1' => 32550, 'C2' => 100, 'C3' => 1.5, 'C4' => 0.5],
            ],
            [
                'code' => 'A8',
                'provider' => 'Niagahoster',
                'name' => 'Niagahoster Personal / Business',
                'category' => 'Unlimited / Advanced',
                'spec_raw' => '100 GB–Unlimited / 2 GB / 2 vCPU / Rp38.900–59.900',
                'scores' => ['C1' => 49400, 'C2' => 150, 'C3' => 2, 'C4' => 2],
            ],
            [
                'code' => 'A9',
                'provider' => 'Niagahoster',
                'name' => 'Niagahoster Cloud Startup',
                'category' => 'Cloud Hosting',
                'spec_raw' => '100 GB / 3 GB / 2 Cores / Rp116.900',
                'scores' => ['C1' => 116900, 'C2' => 100, 'C3' => 3, 'C4' => 2],
            ],
            [
                'code' => 'A10',
                'provider' => 'Niagahoster',
                'name' => 'Niagahoster Cloud VPS KVM 1',
                'category' => 'Virtual Private Server',
                'spec_raw' => '50 GB / 4 GB / 1 Core / Rp116.900',
                'scores' => ['C1' => 116900, 'C2' => 50, 'C3' => 4, 'C4' => 1],
            ],
            [
                'code' => 'A11',
                'provider' => 'Rumahweb',
                'name' => 'Rumahweb Entry / Small',
                'category' => 'Shared Hosting',
                'spec_raw' => '1–5 GB / 1 GB / Shared / Rp15.000–25.000',
                'scores' => ['C1' => 20000, 'C2' => 3, 'C3' => 1, 'C4' => 0.5],
            ],
            [
                'code' => 'A12',
                'provider' => 'Rumahweb',
                'name' => 'Rumahweb Medium / Unlimited',
                'category' => 'Shared Hosting',
                'spec_raw' => '10 GB–Unlimited / 2 GB / Shared / Rp30.000–50.000',
                'scores' => ['C1' => 40000, 'C2' => 105, 'C3' => 2, 'C4' => 0.5],
            ],
            [
                'code' => 'A13',
                'provider' => 'Rumahweb',
                'name' => 'Rumahweb Corporate / Pro',
                'category' => 'Unlimited Business',
                'spec_raw' => 'Unlimited / 3 GB / 2 vCPU / Rp75.000–100.000',
                'scores' => ['C1' => 87500, 'C2' => 200, 'C3' => 3, 'C4' => 2],
            ],
            [
                'code' => 'A14',
                'provider' => 'Rumahweb',
                'name' => 'Rumahweb Cloud Hosting',
                'category' => 'Cloud Hosting',
                'spec_raw' => '20–50 GB / 4 GB / 2 Cores / Rp120.000–150.000',
                'scores' => ['C1' => 135000, 'C2' => 35, 'C3' => 4, 'C4' => 2],
            ],
            [
                'code' => 'A15',
                'provider' => 'Rumahweb',
                'name' => 'Rumahweb VPS Linux',
                'category' => 'Virtual Private Server',
                'spec_raw' => '30–60 GB / 2–4 GB / 1–2 vCPU / Rp150.000–200.000',
                'scores' => ['C1' => 175000, 'C2' => 45, 'C3' => 3, 'C4' => 1.5],
            ],
            [
                'code' => 'A16',
                'provider' => 'IDCloudHost',
                'name' => 'IDCloudHost Cloud Hosting Starter',
                'category' => 'Shared Hosting',
                'spec_raw' => '10 GB–Unlimited / 1 GB / Shared / Rp15.000–30.000',
                'scores' => ['C1' => 22500, 'C2' => 105, 'C3' => 1, 'C4' => 0.5],
            ],
            [
                'code' => 'A17',
                'provider' => 'IDCloudHost',
                'name' => 'IDCloudHost Cloud Hosting Pro',
                'category' => 'Advanced Shared',
                'spec_raw' => 'Unlimited / 2 GB / Shared / Rp40.000–70.000',
                'scores' => ['C1' => 55000, 'C2' => 200, 'C3' => 2, 'C4' => 0.5],
            ],
            [
                'code' => 'A18',
                'provider' => 'IDCloudHost',
                'name' => 'IDCloudHost Server VPS Basic',
                'category' => 'VPS Standard',
                'spec_raw' => '20–40 GB / 1–2 GB / 1 vCore / Rp40.000–75.000',
                'scores' => ['C1' => 57500, 'C2' => 30, 'C3' => 1.5, 'C4' => 1],
            ],
            [
                'code' => 'A19',
                'provider' => 'IDCloudHost',
                'name' => 'IDCloudHost Cloud VPS eXtreme',
                'category' => 'Cloud VPS',
                'spec_raw' => '20–60 GB / 2–4 GB / 2 vCore / Rp87.000–149.000',
                'scores' => ['C1' => 118000, 'C2' => 40, 'C3' => 3, 'C4' => 2],
            ],
            [
                'code' => 'A20',
                'provider' => 'IDCloudHost',
                'name' => 'IDCloudHost Managed VPS',
                'category' => 'Managed WHM/cPanel',
                'spec_raw' => '50 GB / 4 GB / 2 Core / Rp680.000',
                'scores' => ['C1' => 680000, 'C2' => 50, 'C3' => 4, 'C4' => 2],
            ],
        ];

        foreach ($alternativesData as $altData) {
            $alternative = Alternative::updateOrCreate(
                ['code' => $altData['code']],
                [
                    'provider' => $altData['provider'],
                    'name' => $altData['name'],
                    'category' => $altData['category'],
                    'spec_raw' => $altData['spec_raw'],
                ]
            );

            foreach ($altData['scores'] as $critCode => $val) {
                if (isset($criteriaMap[$critCode])) {
                    Score::updateOrCreate(
                        [
                            'alternative_id' => $alternative->id,
                            'criterion_id' => $criteriaMap[$critCode],
                        ],
                        [
                            'value' => $val,
                        ]
                    );
                }
            }
        }

        // Hapus kriteria atau alternatif tambahan jika sebelumnya menggunakan preset lain
        $seededAltCodes = array_column($alternativesData, 'code');
        Alternative::whereNotIn('code', $seededAltCodes)->delete();
        Criterion::whereNotIn('code', ['C1', 'C2', 'C3', 'C4'])->delete();
    }
}
