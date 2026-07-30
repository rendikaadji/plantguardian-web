<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\ExpLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get current user wallet balance (EXP & Coin).
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'exp' => (int) $user->exp,
                'coin' => (int) $user->coin,
            ],
        ]);
    }

    /**
     * Get coin transaction audit history scoped to authenticated user.
     */
    public function transactions(Request $request): JsonResponse
    {
        $transactions = CoinTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($transactions);
    }

    /**
     * Get EXP audit log history scoped to authenticated user.
     */
    public function expLogs(Request $request): JsonResponse
    {
        $logs = ExpLog::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($logs);
    }
}
