<?php

namespace App\Http\Controllers;

use Database\Seeders\HostingSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ResetController extends Controller
{
    /**
     * Menjalankan ulang HostingSeeder untuk Reset Default
     */
    public function reset(): JsonResponse
    {
        Artisan::call('db:seed', [
            '--class' => HostingSeeder::class,
            '--force' => true,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Data berhasil direset ke nilai default (20 Paket Hosting)',
        ]);
    }

    /**
     * Memuat dataset kasus uji penelitian jurnal Bab 3.5 (5 Hosting A-E)
     */
    public function loadJournalCase(): JsonResponse
    {
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\JournalCaseSeeder::class,
            '--force' => true,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Dataset penelitian jurnal (Hosting A-E Bab 3.5) berhasil dimuat',
        ]);
    }
}
