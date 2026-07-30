<?php

namespace Tests\Feature;

use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeciesCatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranger_can_create_list_update_and_delete_species(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        // 1. Create species
        $createResponse = $this->actingAs($ranger, 'sanctum')
            ->postJson('/api/ranger/species', [
                'species_code' => 'ORCHIDACEAE_SPECIES',
                'common_name' => 'Anggrek Bulan',
                'scientific_name' => 'Phalaenopsis amabilis',
                'description' => 'Tumbuhan anggrek indah khas Indonesia.',
                'conservation_status' => 'Protected',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.species_code', 'ORCHIDACEAE_SPECIES')
            ->assertJsonPath('data.common_name', 'Anggrek Bulan');

        $speciesId = $createResponse->json('data.id');

        // 2. List species (Verify read query is NOT scoped by created_by)
        $listResponse = $this->actingAs($ranger, 'sanctum')
            ->getJson('/api/ranger/species');

        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // 3. Update species
        $updateResponse = $this->actingAs($ranger, 'sanctum')
            ->putJson("/api/ranger/species/{$speciesId}", [
                'common_name' => 'Anggrek Bulan Putih',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.common_name', 'Anggrek Bulan Putih');

        // 4. Delete species
        $deleteResponse = $this->actingAs($ranger, 'sanctum')
            ->deleteJson("/api/ranger/species/{$speciesId}");

        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('plant_species', ['id' => $speciesId]);
    }

    public function test_viewer_cannot_manage_species_catalog(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/ranger/species', [
                'species_code' => 'ROSA_RUBIGINOSA',
                'common_name' => 'Bunga Mawar',
                'description' => 'Mawar liar.',
            ]);

        $response->assertStatus(403);
    }
}
