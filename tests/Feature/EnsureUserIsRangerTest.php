<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsRangerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_is_denied_access_to_ranger_routes(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson('/api/ranger/verifications/pending');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Akses ditolak. Halaman atau endpoint ini khusus untuk pengguna ber-role Ranger.');
    }

    public function test_ranger_is_allowed_access_to_ranger_routes(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $response = $this->actingAs($ranger, 'sanctum')
            ->getJson('/api/ranger/verifications/pending');

        $response->assertStatus(200);
    }
}
