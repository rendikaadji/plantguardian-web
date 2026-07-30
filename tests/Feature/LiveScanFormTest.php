<?php

namespace Tests\Feature;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveScanFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranger_can_scan_photo_and_fill_plant_form_on_the_spot(): void
    {
        Storage::fake('public');

        $ranger = User::factory()->create(['role' => 'ranger']);
        $file = UploadedFile::fake()->image('live_plant.jpg');

        $response = $this->actingAs($ranger, 'sanctum')->postJson('/api/scan', [
            'image' => $file,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'common_name' => 'Pohon Ketapang Kencana',
            'scientific_name' => 'Terminalia neotaliala',
            'conservation_status' => 'Common',
            'description' => 'Pohon peneduh bertingkat yang sering ditanam di taman sekolah.',
            'care_instructions' => 'Siram 1-2 kali sehari, pemangkasan ranting mati berkala.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Pemindaian tumbuhan berhasil.')
            ->assertJsonPath('data.species.common_name', 'Pohon Ketapang Kencana')
            ->assertJsonPath('data.species.scientific_name', 'Terminalia neotaliala')
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('plant_species', [
            'common_name' => 'Pohon Ketapang Kencana',
            'scientific_name' => 'Terminalia neotaliala',
            'conservation_status' => 'Common',
        ]);

        $this->assertDatabaseHas('plant_sightings', [
            'ranger_id' => $ranger->id,
            'verification_status' => 'verified',
        ]);
    }
}
