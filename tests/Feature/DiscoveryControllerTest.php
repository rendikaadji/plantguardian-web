<?php

namespace Tests\Feature;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_discover_verified_plant_sighting_and_receive_rewards(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 0, 'coin' => 0]);

        $sighting = PlantSighting::factory()->create([
            'ranger_id' => $ranger->id,
            'verification_status' => 'verified',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/plant-discoveries', [
                'plant_sighting_id' => $sighting->id,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Berhasil menemukan tumbuhan!')
            ->assertJsonPath('data.plant_sighting_id', $sighting->id);

        $this->assertDatabaseHas('plant_discoveries', [
            'user_id' => $viewer->id,
            'plant_sighting_id' => $sighting->id,
        ]);

        $this->assertEquals(100, $viewer->fresh()->exp);
        $this->assertEquals(50, $viewer->fresh()->coin);
    }

    public function test_rarer_plants_grant_higher_exp_and_coins(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 0, 'coin' => 0]);

        $species = PlantSpecies::factory()->create([
            'conservation_status' => 'Protected',
        ]);

        $sighting = PlantSighting::factory()->create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'verification_status' => 'verified',
        ]);

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson("/api/map/sightings/{$sighting->id}/claim");

        $response->assertStatus(200)
            ->assertJsonPath('exp_gained', 500)
            ->assertJsonPath('coin_gained', 250);

        $this->assertEquals(500, $viewer->fresh()->exp);
        $this->assertEquals(250, $viewer->fresh()->coin);
    }

    public function test_viewer_cannot_discover_unverified_plant_sighting(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $viewer = User::factory()->create(['role' => 'viewer']);

        $sighting = PlantSighting::factory()->create([
            'ranger_id' => $ranger->id,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/plant-discoveries', [
                'plant_sighting_id' => $sighting->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Hanya temuan tumbuhan yang sudah diverifikasi yang dapat ditemukan.');
    }

    public function test_viewer_cannot_discover_same_sighting_twice(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $viewer = User::factory()->create(['role' => 'viewer']);

        $sighting = PlantSighting::factory()->create([
            'ranger_id' => $ranger->id,
            'verification_status' => 'verified',
        ]);

        $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/plant-discoveries', [
                'plant_sighting_id' => $sighting->id,
            ]);

        $secondResponse = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/plant-discoveries', [
                'plant_sighting_id' => $sighting->id,
            ]);

        $secondResponse->assertStatus(422)
            ->assertJsonPath('message', 'Anda sudah pernah menemukan tumbuhan ini.');
    }
}
