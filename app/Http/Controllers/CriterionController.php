<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCriteriaRequest;
use App\Models\Criterion;
use Illuminate\Http\JsonResponse;

class CriterionController extends Controller
{
    /**
     * Menampilkan daftar kriteria
     */
    public function index(): JsonResponse
    {
        $criteria = Criterion::orderBy('code')->get();

        return response()->json($criteria);
    }

    /**
     * Memperbarui bobot dan tipe (atribut) kriteria sekaligus
     */
    public function update(UpdateCriteriaRequest $request): JsonResponse
    {
        $items = $request->has('criteria') ? $request->input('criteria') : $request->all();

        foreach ($items as $item) {
            $criterion = Criterion::find($item['id']);
            if ($criterion) {
                $criterion->update([
                    'weight' => $item['weight'],
                    'type' => $item['type'],
                ]);
            }
        }

        $updatedCriteria = Criterion::orderBy('code')->get();

        return response()->json([
            'ok' => true,
            'message' => 'Kriteria berhasil diperbarui',
            'data' => $updatedCriteria,
        ]);
    }
}
