<?php

namespace Tests\Unit\Services;

use App\Models\CoinTransaction;
use App\Models\ExpLog;
use App\Models\GardenPlot;
use App\Models\InventoryItem;
use App\Models\Planting;
use App\Models\User;
use App\Services\GardenService;
use App\Services\RewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GardenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GardenService $gardenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gardenService = new GardenService(new RewardService());
    }

    public function test_unlock_plot_success(): void
    {
        $user = User::factory()->create(['coin' => 200]);
        $plot = GardenPlot::factory()->create(['user_id' => $user->id, 'slot_number' => 1, 'unlocked' => false]);

        $unlockedPlot = $this->gardenService->unlockPlot($user, $plot->id, 100);

        $this->assertTrue($unlockedPlot->unlocked);
        $this->assertEquals(100, $user->fresh()->coin);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'amount' => -100,
            'reason' => 'buy_plot',
        ]);
    }

    public function test_plant_seed_success(): void
    {
        $user = User::factory()->create();
        $plot = GardenPlot::factory()->create(['user_id' => $user->id, 'slot_number' => 1, 'unlocked' => true]);

        InventoryItem::factory()->create([
            'user_id' => $user->id,
            'item_type' => 'seed',
            'item_code' => 'SEED_MANGGA',
            'quantity' => 2,
        ]);

        $planting = $this->gardenService->plantSeed($user, $plot->id, 'SEED_MANGGA', null, 30);

        $this->assertEquals('growing', $planting->status);
        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $user->id,
            'item_code' => 'SEED_MANGGA',
            'quantity' => 1,
        ]);
    }

    public function test_water_plant_reduces_ready_time(): void
    {
        $user = User::factory()->create();
        $plot = GardenPlot::factory()->create(['user_id' => $user->id, 'slot_number' => 1, 'unlocked' => true]);
        $planting = Planting::factory()->create([
            'garden_plot_id' => $plot->id,
            'planted_at' => Carbon::now(),
            'ready_at' => Carbon::now()->addMinutes(30),
            'status' => 'growing',
        ]);

        $updatedPlanting = $this->gardenService->waterPlant($user, $planting->id, 10);

        $this->assertNotNull($updatedPlanting->last_watered_at);
        $this->assertTrue(Carbon::parse($updatedPlanting->ready_at)->lt(Carbon::now()->addMinutes(30)));
    }

    public function test_harvest_plant_grants_rewards(): void
    {
        $user = User::factory()->create(['exp' => 0, 'coin' => 0]);
        $plot = GardenPlot::factory()->create(['user_id' => $user->id, 'slot_number' => 1, 'unlocked' => true]);
        $planting = Planting::factory()->create([
            'garden_plot_id' => $plot->id,
            'planted_at' => Carbon::now()->subMinutes(60),
            'ready_at' => Carbon::now()->subMinutes(10),
            'status' => 'growing',
        ]);

        $result = $this->gardenService->harvestPlant($user, $planting->id, 50, 20);

        $this->assertEquals('harvested', $result['planting']->status);
        $this->assertEquals(50, $user->fresh()->exp);
        $this->assertEquals(20, $user->fresh()->coin);

        $this->assertDatabaseHas('exp_logs', [
            'user_id' => $user->id,
            'amount' => 50,
            'reason' => 'harvest_reward',
        ]);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'amount' => 20,
            'reason' => 'harvest_reward',
        ]);
    }
}
