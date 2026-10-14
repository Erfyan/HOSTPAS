<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlternativeController extends Controller
{
    /**
     * Menampilkan daftar alternatif beserta nilainya
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alternative::with('scores.criterion');

        if ($request->filled('provider')) {
            $query->where('provider', $request->query('provider'));
        }

        $alternatives = $query->get()->sortBy('code', SORT_NATURAL)->values();

        $data = $alternatives->map(function ($alt) {
            $scoreMap = [];
            foreach ($alt->scores as $score) {
                if ($score->criterion) {
                    $scoreMap[$score->criterion->code] = $score->value !== null ? (float)$score->value : null;
                }
            }

            return [
                'id' => $alt->id,
                'code' => $alt->code,
                'provider' => $alt->provider,
                'name' => $alt->name,
                'category' => $alt->category,
                'spec_raw' => $alt->spec_raw,
                'scores' => $scoreMap,
            ];
        });

        return response()->json($data);
    }
}
