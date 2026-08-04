<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_fetch_shop_catalog(): void
    {
        $viewer = User::factory()->create([
            'role' => 'viewer',
            'coin' => 200,
            'exp' => 500,
        ]);

        $response = $this->actingAs($viewer)
            ->getJson('/api/shop');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'catalog',
                'inventory',
                'user_coin',
                'user_exp',
            ])
            ->assertJson([
                'user_coin' => 200,
                'user_exp' => 500,
            ]);
    }

    public function test_viewer_can_buy_item_with_sufficient_coins(): void
    {
        $viewer = User::factory()->create([
            'role' => 'viewer',
            'coin' => 100,
            'locale' => 'id',
        ]);

        $response = $this->actingAs($viewer)
            ->postJson('/api/shop/buy', [
                'item_code' => 'seed_sunflower', // price: 50
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Berhasil membeli Benih Bunga Matahari!',
                'user_coin' => 50,
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $viewer->id,
            'item_code' => 'seed_sunflower',
            'quantity' => 1,
        ]);
    }

    public function test_viewer_cannot_buy_item_with_insufficient_coins(): void
    {
        $viewer = User::factory()->create([
            'role' => 'viewer',
            'coin' => 10,
        ]);

        $response = $this->actingAs($viewer)
            ->postJson('/api/shop/buy', [
                'item_code' => 'seed_sunflower', // price: 50
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Coin pengguna tidak mencukupi.',
            ]);
    }
}
