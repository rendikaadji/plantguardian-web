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

    public function test_other_ranger_cannot_update_or_delete_sighting(): void
    {
        $rangerA = User::factory()->create(['role' => 'ranger']);
        $rangerB = User::factory()->create(['role' => 'ranger']);
        $species = PlantSpecies::create(['species_code' => 'SP4', 'common_name' => 'Spesies Empat', 'scientific_name' => 'Species four', 'description' => 'Desc 4', 'created_by' => $rangerA->id]);

        $sighting = PlantSighting::create([
            'ranger_id' => $rangerA->id,
            'plant_species_id' => $species->id,
            'photo_path' => 'sightings/test.jpg',
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
        ]);

        // Ranger B tries to update Ranger A's sighting -> Expect 403
        $resUpdate = $this->actingAs($rangerB, 'sanctum')->putJson("/api/ranger/sightings/{$sighting->id}", [
            'common_name' => 'Nama Baru Oleh B',
        ]);
        $resUpdate->assertStatus(403);

        // Ranger B tries to delete Ranger A's sighting -> Expect 403
        $resDelete = $this->actingAs($rangerB, 'sanctum')->deleteJson("/api/ranger/sightings/{$sighting->id}");
        $resDelete->assertStatus(403);
    }

    public function test_admin_can_update_and_delete_any_ranger_sighting(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $admin = User::factory()->create(['role' => 'admin']);
        $species = PlantSpecies::create(['species_code' => 'SP5', 'common_name' => 'Spesies Lima', 'scientific_name' => 'Species five', 'description' => 'Desc 5', 'created_by' => $ranger->id]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'photo_path' => 'sightings/test.jpg',
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
        ]);

        // Admin updates Ranger's sighting -> Expect 200
        $resUpdate = $this->actingAs($admin, 'sanctum')->putJson("/api/ranger/sightings/{$sighting->id}", [
            'latitude' => -6.2222,
        ]);
        $resUpdate->assertStatus(200);

        // Admin deletes Ranger's sighting -> Expect 200
        $resDelete = $this->actingAs($admin, 'sanctum')->deleteJson("/api/ranger/sightings/{$sighting->id}");
        $resDelete->assertStatus(200);
    }
}
