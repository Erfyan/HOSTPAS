<?php

namespace Tests\Feature;

use App\Models\Alternative;
use App\Models\Criterion;
use Database\Seeders\HostingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaspasApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure default data exists
        $this->seed(HostingSeeder::class);
    }

    /**
     * Test perankingan default dengan data seeder dan bobot awal (lambda = 0.5, semua penyedia)
     * Sesuai tabel acuan CLAUDE.md:
     * 1. Rumahweb Corporate / Pro (0.6179)
     * 2. Hostinger Business (0.5902)
     * 3. Hostinger Cloud Startup (0.5887)
     * 4. Niagahoster Personal / Business (0.5746)
     * 5. Niagahoster Cloud Startup (0.4863)
     */
    public function test_waspas_api_ranking_all_providers(): void
    {
        $response = $this->getJson('/api/waspas?lambda=0.5');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('weights_valid', true);

        $data = $response->json();
        $alternatives = $data['alternatives'];

        // Urutkan berdasarkan rank untuk memverifikasi top 5
        usort($alternatives, fn($a, $b) => $a['rank'] <=> $b['rank']);

        $expectedTop5 = [
            ['name' => 'Rumahweb Corporate / Pro', 'q' => 0.6179],
            ['name' => 'Hostinger Business', 'q' => 0.5902],
            ['name' => 'Hostinger Cloud Startup', 'q' => 0.5887],
            ['name' => 'Niagahoster Personal / Business', 'q' => 0.5746],
            ['name' => 'Niagahoster Cloud Startup', 'q' => 0.4863],
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals($expectedTop5[$i]['name'], $alternatives[$i]['name']);
            $this->assertEqualsWithDelta($expectedTop5[$i]['q'], $alternatives[$i]['q'], 0.0001);
            $this->assertEquals($i + 1, $alternatives[$i]['rank']);
        }
    }

    /**
     * Test filter provider = Hostinger, peringkat 1 adalah Hostinger Business dengan Q ≈ 0.6413
     */
    public function test_waspas_api_filtered_by_provider(): void
    {
        $response = $this->getJson('/api/waspas?lambda=0.5&provider=Hostinger');

        $response->assertOk();

        $data = $response->json();
        $alternatives = $data['alternatives'];

        usort($alternatives, fn($a, $b) => $a['rank'] <=> $b['rank']);

        $rank1 = $alternatives[0];
        $this->assertEquals('Hostinger Business', $rank1['name']);
        $this->assertEqualsWithDelta(0.6413, $rank1['q'], 0.0001);
        $this->assertEquals(1, $rank1['rank']);
    }

    /**
     * Test validasi: bobot <= 0 atau > 1 ditolak (HTTP 422)
     */
    public function test_validation_rejects_invalid_weights(): void
    {
        $c1 = Criterion::where('code', 'C1')->first();

        // Bobot <= 0
        $responseZero = $this->putJson('/api/criteria', [
            ['id' => $c1->id, 'weight' => 0, 'type' => 'cost'],
        ]);
        $responseZero->assertStatus(422);

        // Bobot > 1
        $responseOver = $this->putJson('/api/criteria', [
            ['id' => $c1->id, 'weight' => 1.5, 'type' => 'cost'],
        ]);
        $responseOver->assertStatus(422);
    }

    /**
     * Test validasi: atribut selain benefit atau cost ditolak (HTTP 422)
     */
    public function test_validation_rejects_invalid_type(): void
    {
        $c1 = Criterion::where('code', 'C1')->first();

        $response = $this->putJson('/api/criteria', [
            ['id' => $c1->id, 'weight' => 0.3, 'type' => 'neutral'],
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test validasi: nilai negatif ditolak (HTTP 422)
     */
    public function test_validation_rejects_negative_scores(): void
    {
        $alt = Alternative::first();

        $response = $this->putJson("/api/alternatives/{$alt->id}/scores", [
            'C1' => -25000,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test API endpoint GET /api/criteria dan GET /api/alternatives
     */
    public function test_criteria_and_alternatives_endpoints(): void
    {
        $critResponse = $this->getJson('/api/criteria');
        $critResponse->assertOk()
            ->assertJsonCount(4);

        $altResponse = $this->getJson('/api/alternatives');
        $altResponse->assertOk()
            ->assertJsonCount(20);
    }

    /**
     * Test API endpoint POST /api/reset
     */
    public function test_reset_endpoint(): void
    {
        $response = $this->postJson('/api/reset');
        $response->assertOk()
            ->assertJsonPath('ok', true);
    }
}
