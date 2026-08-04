<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\ExpLog;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RewardService
{
    /**
     * Grant EXP to user and record audit log in exp_logs table.
     */
    public function grantExp(User $user, int $amount, string $reason, ?Model $reference = null): ExpLog
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah EXP harus lebih dari 0.');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $reference) {
            $user->increment('exp', $amount);

            return ExpLog::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
            ]);
        });
    }

    /**
     * Add Coin to user and record audit log in coin_transactions table.
     */
    public function addCoin(User $user, int $amount, string $reason, ?Model $reference = null): CoinTransaction
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah Coin harus lebih dari 0.');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $reference) {
            $user->increment('coin', $amount);

            return CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
            ]);
        });
    }

    /**
     * Deduct Coin from user and record audit log with negative amount.
     */
    public function deductCoin(User $user, int $amount, string $reason, ?Model $reference = null): CoinTransaction
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah Coin yang dikurangi harus lebih dari 0.');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $reference) {
            if ($user->coin < $amount) {
                throw new Exception('Coin pengguna tidak mencukupi.');
            }

            $user->decrement('coin', $amount);

            return CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'reason' => $reason,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
            ]);
        });
    }

    /**
     * Convenience method to grant scan rewards (EXP + Coin) scaled by conservation status / rarity.
     */
    public function grantScanReward(User $user, Model $reference, ?string $conservationStatus = 'Common'): array
    {
        [$expAmount, $coinAmount] = match (strtolower((string) $conservationStatus)) {
            'vulnerable' => [200, 100],
            'endangered' => [350, 175],
            'protected'  => [500, 250],
            default      => [100, 50],
        };

        return DB::transaction(function () use ($user, $reference, $expAmount, $coinAmount) {
            $expLog = $this->grantExp($user, $expAmount, 'scan_reward', $reference);
            $coinTx = $this->addCoin($user, $coinAmount, 'scan_reward', $reference);

            return [
                'exp_gained' => $expAmount,
                'coin_gained' => $coinAmount,
                'exp_log' => $expLog,
                'coin_transaction' => $coinTx,
            ];
        });
    }

    /**
     * Convenience method to grant harvest rewards (EXP + Coin).
     */
    public function grantHarvestReward(User $user, Model $planting, int $expAmount = 50, int $coinAmount = 20): array
    {
        return DB::transaction(function () use ($user, $planting, $expAmount, $coinAmount) {
            $expLog = $this->grantExp($user, $expAmount, 'harvest_reward', $planting);
            $coinTx = $this->addCoin($user, $coinAmount, 'harvest_reward', $planting);

            return [
                'exp_log' => $expLog,
                'coin_transaction' => $coinTx,
            ];
        });
    }
}
