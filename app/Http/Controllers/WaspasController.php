<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Services\WaspasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaspasController extends Controller
{
    protected WaspasService $waspasService;

    public function __construct(WaspasService $waspasService)
    {
        $this->waspasService = $waspasService;
    }

    /**
     * Mengembalikan seluruh langkah perhitungan WASPAS
     */
    public function index(Request $request): JsonResponse
    {
        $lambda = $request->has('lambda') ? (float)$request->query('lambda') : 0.5;
        $provider = $request->query('provider');

        // Ambil data kriteria
        $criteria = Criterion::orderBy('code')->get()->toArray();

        // Ambil data alternatif
        $query = Alternative::with('scores.criterion');
        if (!empty($provider)) {
            $query->where('provider', $provider);
        }

        $alternatives = $query->get()->sortBy('code', SORT_NATURAL)->values();

        $alternativesData = $alternatives->map(function ($alt) {
            $x = [];
            foreach ($alt->scores as $score) {
                if ($score->criterion) {
                    $x[$score->criterion->code] = $score->value !== null ? (float)$score->value : null;
                }
            }

            return [
                'id' => $alt->id,
                'code' => $alt->code,
                'provider' => $alt->provider,
                'name' => $alt->name,
                'category' => $alt->category,
                'spec_raw' => $alt->spec_raw,
                'x' => $x,
            ];
        })->toArray();

        $result = $this->waspasService->calculate($criteria, $alternativesData, $lambda);

        return response()->json($result);
    }
}
