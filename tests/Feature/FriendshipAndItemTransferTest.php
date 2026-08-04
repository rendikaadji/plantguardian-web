<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipAndItemTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_and_send_friend_request(): void
    {
        $user1 = User::factory()->create(['name' => 'User One', 'role' => 'viewer']);
        $user2 = User::factory()->create(['name' => 'User Two', 'role' => 'viewer']);

        $response = $this->actingAs($user1)->getJson('/api/friends/search?q=User Two');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $addResponse = $this->actingAs($user1)->postJson('/api/friends/add', [
            'friend_id' => $user2->id,
        ]);

        $addResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $user1->id,
            'friend_id' => $user2->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_accept_friend_request(): void
    {
        $user1 = User::factory()->create(['role' => 'viewer']);
        $user2 = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user1)->postJson('/api/friends/add', ['friend_id' => $user2->id]);

        $acceptResponse = $this->actingAs($user2)->postJson('/api/friends/accept', [
            'requester_id' => $user1->id,
        ]);

        $acceptResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $user1->id,
            'friend_id' => $user2->id,
            'status' => 'accepted',
        ]);
    }

    public function test_friend_can_request_and_gift_shop_item(): void
    {
        $user1 = User::factory()->create(['role' => 'viewer']);
        $user2 = User::factory()->create(['role' => 'viewer']);

        // Establish accepted friendship
        $this->actingAs($user1)->postJson('/api/friends/add', ['friend_id' => $user2->id]);
        $this->actingAs($user2)->postJson('/api/friends/accept', ['requester_id' => $user1->id]);

        // User1 gives User2 1 Pupuk Organik in inventory
        InventoryItem::create([
            'user_id' => $user1->id,
            'item_code' => 'tool_fertilizer',
            'item_type' => 'tool',
            'quantity' => 2,
        ]);

        // User2 requests tool_fertilizer from User1
        $reqResponse = $this->actingAs($user2)->postJson('/api/friends/request-item', [
            'friend_id' => $user1->id,
            'item_code' => 'tool_fertilizer',
            'note' => 'Minta pupuk dong',
        ]);

        $reqResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $requestId = $reqResponse->json('request.id');

        // User1 fulfills the request and gifts the item
        $giftResponse = $this->actingAs($user1)->postJson('/api/friends/gift-item', [
            'friend_id' => $user2->id,
            'item_code' => 'tool_fertilizer',
            'request_id' => $requestId,
        ]);

        $giftResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert item was deducted from user1 and added to user2
        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $user1->id,
            'item_code' => 'tool_fertilizer',
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $user2->id,
            'item_code' => 'tool_fertilizer',
            'quantity' => 1,
        ]);
    }
}
