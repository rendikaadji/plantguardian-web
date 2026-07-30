<?php

namespace Tests\Feature;

use App\Models\ExpLog;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_current_leaderboard_endpoint(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        ExpLog::create(['user_id' => $user1->id, 'amount' => 150, 'reason' => 'scan_reward']);
        ExpLog::create(['user_id' => $user2->id, 'amount' => 300, 'reason' => 'scan_reward']);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson('/api/leaderboard/current');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.user_name', 'Bob')
            ->assertJsonPath('data.0.exp_earned', 300)
            ->assertJsonPath('data.1.user_name', 'Alice')
            ->assertJsonPath('data.1.exp_earned', 150);
    }

    public function test_calculate_weekly_artisan_command(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        $lastWeekDate = Carbon::now()->subWeek();

        ExpLog::create([
            'user_id' => $user1->id,
            'amount' => 200,
            'reason' => 'scan_reward',
            'created_at' => $lastWeekDate,
        ]);
        ExpLog::create([
            'user_id' => $user2->id,
            'amount' => 500,
            'reason' => 'scan_reward',
            'created_at' => $lastWeekDate,
        ]);

        $this->artisan('leaderboard:calculate-weekly')
            ->assertExitCode(0);

        $this->assertDatabaseHas('weekly_rewards', [
            'user_id' => $user2->id,
            'rank' => 1,
            'exp_earned' => 500,
        ]);

        $this->assertDatabaseHas('weekly_rewards', [
            'user_id' => $user1->id,
            'rank' => 2,
            'exp_earned' => 200,
        ]);
    }

    public function test_get_history_leaderboard_endpoint(): void
    {
        $user = User::factory()->create();

        $leaderboardService = new LeaderboardService();
        ExpLog::create([
            'user_id' => $user->id,
            'amount' => 250,
            'reason' => 'scan_reward',
            'created_at' => Carbon::now()->subWeek(),
        ]);
        $leaderboardService->calculateAndSnapshotForWeek();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/leaderboard/history');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $user->id)
            ->assertJsonPath('data.0.exp_earned', 250);
    }
}
