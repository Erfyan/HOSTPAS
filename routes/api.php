<?php

use App\Http\Controllers\AlternativeController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\WaspasController;
use Illuminate\Support\Facades\Route;

// Kriteria
Route::get('/criteria', [CriterionController::class, 'index']);
Route::put('/criteria', [CriterionController::class, 'update']);

// Alternatif & Nilai
Route::get('/alternatives', [AlternativeController::class, 'index']);
Route::post('/alternatives', [AlternativeController::class, 'store']);
Route::delete('/alternatives/{id}', [AlternativeController::class, 'destroy']);
Route::put('/alternatives/{id}/scores', [ScoreController::class, 'update']);

// Perhitungan WASPAS
Route::get('/waspas', [WaspasController::class, 'index']);

// Reset Default
Route::post('/reset', [ResetController::class, 'reset']);
