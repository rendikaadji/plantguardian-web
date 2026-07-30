<?php

namespace Tests\Feature;

use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_endpoint_returns_plant_sighting_resource_by_ranger(): void
    {
        Storage::fake('public');
        Http::fake([
            '*/classify' => Http::response([
                'success' => true,
                'predicted_species_code' => 'MANGIFERA_INDICA',
                'confidence' => 0.95,
            ], 200),
        ]);

        $ranger = User::factory()->create(['role' => 'ranger']);

        $species = PlantSpecies::factory()->create([
            'species_code' => 'MANGIFERA_INDICA',
            'common_name' => 'Pohon Mangga',
            'description' => 'Pohon buah mangga manis lokal.',
            'created_by' => $ranger->id,
        ]);

        $file = UploadedFile::fake()->image('mangga.jpg');

        $response = $this->actingAs($ranger, 'sanctum')
            ->postJson('/api/scan', [
                'image' => $file,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Pemindaian tumbuhan berhasil.')
            ->assertJsonPath('data.plant_species_id', $species->id)
            ->assertJsonPath('data.species.species_code', 'MANGIFERA_INDICA')
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('plant_sightings', [
            'ranger_id' => $ranger->id,
            'plant_species_id' => $species->id,
            'verification_status' => 'verified',
        ]);
    }
}
