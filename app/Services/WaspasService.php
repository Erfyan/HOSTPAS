<?php

namespace App\Services;

class WaspasService
{
    /**
     * Menghitung metode WASPAS (Weight Aggregated Sum Product Assessment)
     *
     * @param array $criteria Kriteria [['code' => 'C1', 'name' => '...', 'unit' => '...', 'type' => 'benefit|cost', 'weight' => 0.3], ...]
     * @param array $alternatives Alternatif dengan skor [['code' => 'A1', 'name' => '...', 'x' => ['C1' => 21400, ...]], ...]
     * @param float $lambda Parameter kombinasi SAW dan WP (0.0 s.d 1.0, bawaan 0.5)
     * @return array Hasil kalkulasi bertahap
     */
    public function calculate(array $criteria, array $alternatives, float $lambda = 0.5): array
    {
        $lambda = max(0.0, min(1.0, (float)$lambda));

        // 1. Normalisasi Bobot Kriteria
        $rawWeightSum = 0.0;
        foreach ($criteria as $c) {
            $rawWeightSum += (float)($c['weight'] ?? 0);
        }

        $weightsValid = abs($rawWeightSum - 1.0) <= 0.0001;

        $processedCriteria = [];
        $normalizedWeights = [];
        $criteriaCount = count($criteria);

        foreach ($criteria as $c) {
            $code = $c['code'];
            $w = (float)($c['weight'] ?? 0);
            $normalizedW = $rawWeightSum > 0 ? ($w / $rawWeightSum) : ($criteriaCount > 0 ? 1.0 / $criteriaCount : 0);
            
            $normalizedWeights[$code] = $normalizedW;

            $processedCriteria[] = [
                'id' => $c['id'] ?? null,
                'code' => $code,
                'name' => $c['name'] ?? $code,
                'unit' => $c['unit'] ?? '',
                'type' => strtolower($c['type'] ?? 'benefit'),
                'weight' => $w,
                'normalized_weight' => round($normalizedW, 4),
            ];
        }

        $criterionCodes = array_column($processedCriteria, 'code');

        // 2. Filter Alternatif Lengkap & Excluded
        $validAlternatives = [];
        $excluded = [];

        foreach ($alternatives as $alt) {
            $scores = $alt['x'] ?? $alt['scores'] ?? [];
            $isIncomplete = false;
            $cleanedX = [];

            foreach ($criterionCodes as $code) {
                if (!isset($scores[$code]) || $scores[$code] === null || $scores[$code] === '' || !is_numeric($scores[$code])) {
                    $isIncomplete = true;
                    break;
                }
                $cleanedX[$code] = (float)$scores[$code];
            }

            if ($isIncomplete) {
                $excluded[] = [
                    'id' => $alt['id'] ?? null,
                    'code' => $alt['code'] ?? null,
                    'name' => $alt['name'] ?? '',
                    'reason' => 'Nilai belum lengkap',
                ];
            } else {
                $validAlternatives[] = [
                    'id' => $alt['id'] ?? null,
                    'code' => $alt['code'],
                    'provider' => $alt['provider'] ?? '',
                    'name' => $alt['name'] ?? '',
                    'category' => $alt['category'] ?? '',
                    'spec_raw' => $alt['spec_raw'] ?? '',
                    'x' => $cleanedX,
                ];
            }
        }

        // 3. Validasi Minimal 2 Alternatif
        if (count($validAlternatives) < 2) {
            return [
                'ok' => false,
                'lambda' => $lambda,
                'weights_valid' => $weightsValid,
                'message' => 'Minimal 2 alternatif lengkap untuk melakukan perhitungan',
                'criteria' => $processedCriteria,
                'extremes' => (object)[],
                'alternatives' => [],
                'excluded' => $excluded,
            ];
        }

        // 4. Hitung Nilai Ekstrem (max untuk benefit, min untuk cost)
        $extremes = [];
        foreach ($processedCriteria as $crit) {
            $code = $crit['code'];
            $type = $crit['type'];
            $colValues = array_column(array_column($validAlternatives, 'x'), $code);

            if ($type === 'cost') {
                $extremes[$code] = count($colValues) > 0 ? min($colValues) : 0;
            } else {
                $extremes[$code] = count($colValues) > 0 ? max($colValues) : 0;
            }
        }

        // 5. Normalisasi Matriks R, SAW, WP, dan WASPAS (Q)
        foreach ($validAlternatives as &$alt) {
            $rValues = [];
            $sawScore = 0.0;
            $wpScore = 1.0;

            foreach ($processedCriteria as $crit) {
                $code = $crit['code'];
                $type = $crit['type'];
                $val = $alt['x'][$code];
                $extreme = $extremes[$code];
                $w = $normalizedWeights[$code];

                // Aturan pembagian
                if ($type === 'cost') {
                    if ($val == 0) {
                        $r = ($extreme == 0) ? 1.0 : 0.0;
                    } else {
                        $r = $extreme / $val;
                    }
                } else {
                    if ($extreme == 0) {
                        $r = 0.0;
                    } else {
                        $r = $val / $extreme;
                    }
                }

                $rValues[$code] = round($r, 4);

                // SAW: akumulasi w * r
                $sawScore += ($w * $r);

                // WP: perkalian r^w
                $wpScore *= pow($r, $w);
            }

            // WASPAS Q = lambda * SAW + (1 - lambda) * WP
            $qScore = ($lambda * $sawScore) + ((1.0 - $lambda) * $wpScore);

            $alt['r'] = $rValues;
            $alt['saw'] = round($sawScore, 4);
            $alt['wp'] = round($wpScore, 4);
            $alt['q'] = round($qScore, 4);
        }
        unset($alt);

        // 6. Tentukan Peringkat (Rank) Berdasarkan Q Tertinggi
        $ranked = $validAlternatives;
        usort($ranked, function ($a, $b) {
            if ($b['q'] <=> $a['q']) {
                return $b['q'] <=> $a['q'];
            }
            return strnatcmp($a['code'], $b['code']);
        });

        $rankMap = [];
        $rank = 1;
        foreach ($ranked as $item) {
            $rankMap[$item['code']] = $rank++;
        }

        foreach ($validAlternatives as &$alt) {
            $alt['rank'] = $rankMap[$alt['code']];
        }
        unset($alt);

        // 7. Urutkan Alternatif Berdasarkan Kode Sesuai Spesifikasi CLAUDE.md
        usort($validAlternatives, fn($a, $b) => strnatcmp($a['code'], $b['code']));

        return [
            'ok' => true,
            'lambda' => $lambda,
            'weights_valid' => $weightsValid,
            'criteria' => $processedCriteria,
            'extremes' => $extremes,
            'alternatives' => $validAlternatives,
            'excluded' => $excluded,
        ];
    }
}
