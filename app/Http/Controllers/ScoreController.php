<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateScoresRequest;
use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\Score;
use Illuminate\Http\JsonResponse;

class ScoreController extends Controller
{
    /**
     * Memperbarui nilai skor suatu alternatif
     */
    public function update(UpdateScoresRequest $request, int $id): JsonResponse
    {
        $alternative = Alternative::findOrFail($id);
        $scoresData = $request->validated();

        $criteria = Criterion::all()->keyBy('code');

        foreach ($scoresData as $code => $value) {
            if (isset($criteria[$code])) {
                Score::updateOrCreate(
                    [
                        'alternative_id' => $alternative->id,
                        'criterion_id' => $criteria[$code]->id,
                    ],
                    [
                        'value' => $value !== null && $value !== '' ? (float)$value : null,
                    ]
                );
            }
        }

        // Ambil data skor terbaru
        $alternative->load('scores.criterion');
        $updatedScores = [];
        foreach ($alternative->scores as $score) {
            if ($score->criterion) {
                $updatedScores[$score->criterion->code] = $score->value !== null ? (float)$score->value : null;
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Nilai alternatif berhasil diperbarui',
            'scores' => $updatedScores,
        ]);
    }
}
