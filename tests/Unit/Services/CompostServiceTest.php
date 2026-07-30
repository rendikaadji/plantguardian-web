<?php

namespace Tests\Unit\Services;

use App\Models\CompostMaterial;
use App\Models\CompostProcess;
use App\Models\User;
use App\Services\CompostService;
use App\Services\RewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompostService $compostService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compostService = new CompostService(new RewardService());
    }

    public function test_start_compost_process_grants_exp(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 0]);

        $material = CompostMaterial::create([
            'material_code' => 'DAUN_KERING',
            'name' => 'Daun Kering Kebun',
            'description' => 'Daun gugur organik.',
            'instructions' => 'Cacah dan basahi.',
            'created_by' => $ranger->id,
        ]);

        $process = $this->compostService->startProcess($viewer, $material->id, 50);

        $this->assertEquals('started', $process->status);
        $this->assertEquals(50, $viewer->fresh()->exp);
        $this->assertDatabaseHas('exp_logs', [
            'user_id' => $viewer->id,
            'amount' => 50,
            'reason' => 'compost_started',
        ]);
    }

    public function test_record_checkin_creates_log_and_grants_exp(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 50]);
        $process = CompostProcess::create([
            'user_id' => $viewer->id,
            'status' => 'started',
            'started_at' => now(),
        ]);

        $log = $this->compostService->recordCheckin($viewer, $process->id, [
            'stage_label' => 'Fermentasi Awal',
            'photo_path' => 'compost/progress1.jpg',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ], 20);

        $this->assertEquals('in_progress', $process->fresh()->status);
        $this->assertEquals(70, $viewer->fresh()->exp);
        $this->assertDatabaseHas('compost_progress_logs', [
            'compost_process_id' => $process->id,
            'stage_label' => 'Fermentasi Awal',
        ]);
    }

    public function test_mark_matured_updates_status_and_grants_exp(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 70]);
        $process = CompostProcess::create([
            'user_id' => $viewer->id,
            'status' => 'in_progress',
            'started_at' => now()->subDays(7),
        ]);

        $maturedProcess = $this->compostService->markMatured($viewer, $process->id, 100);

        $this->assertEquals('matured', $maturedProcess->status);
        $this->assertNotNull($maturedProcess->matured_at);
        $this->assertEquals(170, $viewer->fresh()->exp);
    }

    public function test_record_real_planting_updates_compost_status_and_grants_exp(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'exp' => 170]);
        $process = CompostProcess::create([
            'user_id' => $viewer->id,
            'status' => 'matured',
            'started_at' => now()->subDays(10),
            'matured_at' => now()->subDay(),
        ]);

        $realPlanting = $this->compostService->recordRealPlanting($viewer, [
            'compost_process_id' => $process->id,
            'photo_path' => 'real_plantings/tree1.jpg',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ], 300);

        $this->assertEquals('used_for_planting', $process->fresh()->status);
        $this->assertEquals('self_reported', $realPlanting->verification_status);
        $this->assertEquals(470, $viewer->fresh()->exp);
        $this->assertDatabaseHas('real_plantings', [
            'user_id' => $viewer->id,
            'compost_process_id' => $process->id,
        ]);
    }
}
