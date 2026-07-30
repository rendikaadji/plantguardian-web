<?php

namespace App\Services;

use App\Models\CompostProcess;
use App\Models\CompostProgressLog;
use App\Models\InventoryItem;
use App\Models\RealPlanting;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CompostService
{
    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Start a new compost process for the user.
     */
    public function startProcess(User $user, ?int $compostMaterialId = null, int $expReward = 50, ?string $inventoryItemCode = null): CompostProcess
    {
        return DB::transaction(function () use ($user, $compostMaterialId, $expReward, $inventoryItemCode) {
            if ($inventoryItemCode) {
                $item = InventoryItem::where('user_id', $user->id)
                    ->where('item_code', $inventoryItemCode)
                    ->where('quantity', '>', 0)
                    ->first();

                if (! $item) {
                    throw new Exception('Bahan kompos tidak ditemukan di inventaris Anda.');
                }

                $item->decrement('quantity');
                $expReward += 50; // Bonus +50 EXP when using shop compost kit/activator
            }

            $process = CompostProcess::create([
                'user_id' => $user->id,
                'compost_material_id' => $compostMaterialId,
                'status' => 'started',
                'started_at' => Carbon::now(),
            ]);

            $this->rewardService->grantExp($user, $expReward, 'compost_started', $process);

            return $process;
        });
    }

    /**
     * Record a periodic check-in progress log for an active compost process.
     */
    public function recordCheckin(User $user, int $processId, array $data, int $expReward = 20): CompostProgressLog
    {
        return DB::transaction(function () use ($user, $processId, $data, $expReward) {
            $process = $this->getScopedProcess($user, $processId);

            if (! in_array($process->status, ['started', 'in_progress'])) {
                throw new Exception('Proses kompos ini tidak sedang aktif.');
            }

            if ($process->status === 'started') {
                $process->update(['status' => 'in_progress']);
            }

            $log = CompostProgressLog::create([
                'compost_process_id' => $process->id,
                'stage_label' => $data['stage_label'],
                'photo_path' => $data['photo_path'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'note' => $data['note'] ?? null,
                'created_at' => Carbon::now(),
            ]);

            $this->rewardService->grantExp($user, $expReward, 'compost_checkin', $log);

            return $log;
        });
    }

    /**
     * Mark a compost process as matured (ready for planting).
     */
    public function markMatured(User $user, int $processId, int $expReward = 100): CompostProcess
    {
        return DB::transaction(function () use ($user, $processId, $expReward) {
            $process = $this->getScopedProcess($user, $processId);

            if (! in_array($process->status, ['started', 'in_progress'])) {
                throw new Exception('Hanya proses kompos aktif yang dapat ditandai matang.');
            }

            $process->update([
                'status' => 'matured',
                'matured_at' => Carbon::now(),
            ]);

            $this->rewardService->grantExp($user, $expReward, 'compost_matured', $process);

            return $process->fresh();
        });
    }

    /**
     * Record real tree planting proof using compost or independent.
     */
    public function recordRealPlanting(User $user, array $data, int $expReward = 300): RealPlanting
    {
        return DB::transaction(function () use ($user, $data, $expReward) {
            $compostProcessId = $data['compost_process_id'] ?? null;

            if ($compostProcessId) {
                $process = $this->getScopedProcess($user, $compostProcessId);
                $process->update(['status' => 'used_for_planting']);
            }

            $realPlanting = RealPlanting::create([
                'user_id' => $user->id,
                'compost_process_id' => $compostProcessId,
                'plant_species_id' => $data['plant_species_id'] ?? null,
                'photo_path' => $data['photo_path'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'planted_at' => Carbon::now(),
                'verification_status' => 'self_reported',
            ]);

            $this->rewardService->grantExp($user, $expReward, 'real_planting', $realPlanting);

            return $realPlanting;
        });
    }

    /**
     * Scoped process retriever to ensure multi-tenant security.
     */
    protected function getScopedProcess(User $user, int $processId): CompostProcess
    {
        return CompostProcess::where('user_id', $user->id)
            ->where('id', $processId)
            ->firstOrFail();
    }
}
