<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_validation_fails_when_image_is_missing(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $response = $this->actingAs($ranger, 'sanctum')
            ->postJson('/api/scan', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image', 'image_base64']);
    }

    public function test_scan_validation_fails_for_invalid_coordinates(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        Storage::fake('public');

        $file = UploadedFile::fake()->image('plant.jpg');

        $response = $this->actingAs($ranger, 'sanctum')
            ->postJson('/api/scan', [
                'image' => $file,
                'latitude' => 120, // out of bounds
                'longitude' => 'not-a-number',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }
}
