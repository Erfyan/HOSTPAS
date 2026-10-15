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

    /**
     * Menambahkan alternatif hosting baru untuk simulasi
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'spec_raw' => 'nullable|string',
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0',
        ]);

        // Tentukan kode unik berikutnya
        $count = Alternative::count();
        $code = 'A' . ($count + 1);
        while (Alternative::where('code', $code)->exists()) {
            $count++;
            $code = 'A' . ($count + 1);
        }

        $alternative = Alternative::create([
            'code' => $code,
            'provider' => $validated['provider'],
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'Custom Package',
            'spec_raw' => $validated['spec_raw'] ?? '-',
        ]);

        $criteria = \App\Models\Criterion::all()->keyBy('code');

        foreach ($validated['scores'] as $codeKey => $scoreVal) {
            if (isset($criteria[$codeKey])) {
                \App\Models\Score::create([
                    'alternative_id' => $alternative->id,
                    'criterion_id' => $criteria[$codeKey]->id,
                    'value' => (float)$scoreVal,
                ]);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Paket hosting alternatif berhasil ditambahkan ke simulasi',
            'data' => $alternative,
        ], 201);
    }

    /**
     * Memperbarui informasi data alternatif hosting
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $alternative = Alternative::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'spec_raw' => 'nullable|string',
        ]);

        $alternative->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Data alternatif berhasil diperbarui',
            'data' => $alternative,
        ]);
    }

    /**
     * Menghapus alternatif hosting
     */
    public function destroy(int $id): JsonResponse
    {
        $alternative = Alternative::findOrFail($id);
        $alternative->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Alternatif berhasil dihapus dari simulasi',
        ]);
    }
}
