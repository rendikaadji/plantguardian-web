<?php

namespace Tests\Feature;

use App\Models\PlantSighting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranger_can_fetch_pending_queue_and_verify_sighting(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $fieldRanger = User::factory()->create(['role' => 'ranger']);

        $sighting = PlantSighting::factory()->create([
            'ranger_id' => $fieldRanger->id,
            'photo_path' => 'sightings/mangga.jpg',
            'verification_status' => 'pending',
        ]);

        // 1. Get pending queue
        $queueResponse = $this->actingAs($ranger, 'sanctum')
            ->getJson('/api/ranger/verifications/pending');

        $queueResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.pending_sightings');

        // 2. Verify sighting
        $verifySightingResponse = $this->actingAs($ranger, 'sanctum')
            ->postJson("/api/ranger/verifications/sightings/{$sighting->id}", [
                'status' => 'verified',
            ]);

        $verifySightingResponse->assertStatus(200)
            ->assertJsonPath('data.verification_status', 'verified')
            ->assertJsonPath('data.verified_by', $ranger->id);

        $this->assertDatabaseHas('plant_sightings', [
            'id' => $sighting->id,
            'verification_status' => 'verified',
            'verified_by' => $ranger->id,
        ]);
    }

    public function test_viewer_is_denied_access_to_verification_endpoints(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson('/api/ranger/verifications/pending');

        $response->assertStatus(403);
    }
}
