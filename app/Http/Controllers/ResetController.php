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
            'message' => 'Data berhasil direset ke nilai default',
        ]);
    }
}
