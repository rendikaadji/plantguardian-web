<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_claim_achievement_reward_and_persists_in_database(): void
    {
        $user = User::factory()->create([
            'role' => 'viewer',
            'exp' => 100,
            'coin' => 50,
        ]);

        $response = $this->actingAs($user)->postJson('/api/achievements/claim', [
            'achievement_code' => 'flora_explorer',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user_exp', 200)
            ->assertJsonPath('user_coin', 100);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_code' => 'flora_explorer',
            'status' => 'claimed',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'exp' => 200,
            'coin' => 100,
        ]);

        // Attempting to claim the same achievement again should fail
        $secondResponse = $this->actingAs($user)->postJson('/api/achievements/claim', [
            'achievement_code' => 'flora_explorer',
        ]);

        $secondResponse->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Hadiah achievement ini sudah pernah diklaim.');
    }

    public function test_user_cannot_claim_uncompleted_achievement(): void
    {
        $user = User::factory()->create(['exp' => 0]);

        $response = $this->actingAs($user)->postJson('/api/achievements/claim', [
            'achievement_code' => 'ecosystem_master',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}
