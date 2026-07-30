<?php

namespace Tests\Feature;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SightingEditControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranger_can_list_and_update_plant_sighting(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $species1 = PlantSpecies::create(['species_code' => 'SP1', 'common_name' => 'Spesies Salah', 'scientific_name' => 'Species one', 'description' => 'Desc 1', 'created_by' => $ranger->id]);
        $species2 = PlantSpecies::create(['species_code' => 'SP2', 'common_name' => 'Spesies Benar', 'scientific_name' => 'Species two', 'description' => 'Desc 2', 'created_by' => $ranger->id]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species1->id,
            'photo_path' => 'sightings/test.jpg',
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
        ]);

        // 1. List sightings
        $responseList = $this->actingAs($ranger, 'sanctum')->getJson('/api/ranger/sightings');
        $responseList->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // 2. Update sighting to species2
        $responseUpdate = $this->actingAs($ranger, 'sanctum')->putJson("/api/ranger/sightings/{$sighting->id}", [
            'plant_species_id' => $species2->id,
            'latitude' => -6.2100,
            'longitude' => 106.8100,
        ]);

        $responseUpdate->assertStatus(200)
            ->assertJsonPath('message', 'Data temuan tumbuhan berhasil diperbarui.')
            ->assertJsonPath('data.plant_species_id', $species2->id);

        $this->assertDatabaseHas('plant_sightings', [
            'id' => $sighting->id,
            'plant_species_id' => $species2->id,
            'latitude' => -6.2100,
            'longitude' => 106.8100,
        ]);
    }

    public function test_ranger_can_delete_plant_sighting(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $species = PlantSpecies::create(['species_code' => 'SP3', 'common_name' => 'Spesies Tiga', 'scientific_name' => 'Species three', 'description' => 'Desc 3', 'created_by' => $ranger->id]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'photo_path' => 'sightings/test.jpg',
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
        ]);

        $responseDelete = $this->actingAs($ranger, 'sanctum')->deleteJson("/api/ranger/sightings/{$sighting->id}");
        $responseDelete->assertStatus(200)
            ->assertJsonPath('message', 'Data temuan tumbuhan berhasil dihapus dari peta.');

        $this->assertDatabaseMissing('plant_sightings', [
            'id' => $sighting->id,
        ]);
    }
}
