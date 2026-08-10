<?php

namespace Tests\Feature;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\SightingReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SightingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_report_for_plant_sighting(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $ranger = User::factory()->create(['role' => 'ranger']);
        $species = PlantSpecies::create([
            'species_code' => 'TEST_PLANT',
            'common_name' => 'Tanaman Tes',
            'scientific_name' => 'Testus plantus',
            'description' => 'Deskripsi tes',
            'created_by' => $ranger->id,
        ]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
            'photo_path' => 'sightings/test.jpg',
        ]);

        $response = $this->actingAs($user)->postJson("/api/map/sightings/{$sighting->id}/report", [
            'reason' => 'plant_missing_or_dead',
            'notes' => 'Pohon di lokasi ini sudah mati/ditebang.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Laporan temuan tumbuhan berhasil dikirim ke Admin. Terima kasih atas kontribusimu!',
        ]);

        $this->assertDatabaseHas('sighting_reports', [
            'user_id' => $user->id,
            'plant_sighting_id' => $sighting->id,
            'reason' => 'plant_missing_or_dead',
            'notes' => 'Pohon di lokasi ini sudah mati/ditebang.',
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_pending_reports_are_rejected(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $ranger = User::factory()->create(['role' => 'ranger']);
        $species = PlantSpecies::create([
            'species_code' => 'TEST_PLANT2',
            'common_name' => 'Tanaman Tes 2',
            'description' => 'Deskripsi tes 2',
            'created_by' => $ranger->id,
        ]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
            'photo_path' => 'sightings/test.jpg',
        ]);

        SightingReport::create([
            'user_id' => $user->id,
            'plant_sighting_id' => $sighting->id,
            'reason' => 'fake_specimen',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson("/api/map/sightings/{$sighting->id}/report", [
            'reason' => 'fake_specimen',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_resolve_report_and_delete_sighting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'viewer']);
        $ranger = User::factory()->create(['role' => 'ranger']);
        $species = PlantSpecies::create([
            'species_code' => 'TEST_PLANT3',
            'common_name' => 'Tanaman Tes 3',
            'description' => 'Deskripsi tes 3',
            'created_by' => $ranger->id,
        ]);

        $sighting = PlantSighting::create([
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'verification_status' => 'verified',
            'photo_path' => 'sightings/test.jpg',
        ]);

        $report = SightingReport::create([
            'user_id' => $user->id,
            'plant_sighting_id' => $sighting->id,
            'reason' => 'fake_specimen',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/reports/{$report->id}/resolve", [
            'action' => 'delete_sighting',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('plant_sightings', ['id' => $sighting->id]);
        $this->assertDatabaseHas('sighting_reports', [
            'id' => $report->id,
            'status' => 'resolved_deleted',
            'resolved_by' => $admin->id,
        ]);
    }
}
