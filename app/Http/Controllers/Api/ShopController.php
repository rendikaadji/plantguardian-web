<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Services\GardenService;
use App\Services\RewardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    protected RewardService $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Get full shop catalog item list.
     */
    protected function getCatalog(): array
    {
        $seedCodes = ['seed_sunflower', 'seed_tomato', 'seed_monstera', 'seed_orchid'];

        $catalog = [];
        foreach ($seedCodes as $code) {
            $config = GardenService::getSeedConfig($code);
            $catalog[] = [
                'item_code' => $code,
                'name' => $config['name'],
                'item_type' => 'seed',
                'price' => $config['price'],
                'icon' => $config['icon'],
                'description' => $config['description'],
                'growth_duration_minutes' => $config['growth_duration_minutes'],
                'exp_reward' => $config['exp_reward'],
                'coin_reward' => $config['coin_reward'],
            ];
        }

        $otherItems = [
            [
                'item_code' => 'tool_fertilizer',
                'name' => 'Pupuk Organik Super',
                'item_type' => 'tool',
                'price' => 30,
                'icon' => '🧪',
                'description' => 'Mempercepat pertumbuhan tanaman di kebun.',
                'time_reduction_minutes' => 5,
                'usage_label' => 'Potong Waktu Tumbuh 5m',
            ],
            [
                'item_code' => 'tool_watering_can',
                'name' => 'Penyiram Otomatis',
                'item_type' => 'tool',
                'price' => 100,
                'icon' => '🚿',
                'description' => 'Alat siram otomatis untuk memotong waktu tunggu tumbuh.',
                'time_reduction_minutes' => 10,
                'usage_label' => 'Potong Waktu Tumbuh 10m',
            ],
        ];

        return array_merge($catalog, $otherItems);
    }

    /**
     * List shop catalog items and user inventory.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $catalog = $this->getCatalog();
        $userInventory = InventoryItem::where('user_id', $user->id)->get();

        return response()->json([
            'catalog' => $catalog,
            'inventory' => $userInventory,
            'user_coin' => $user->coin,
            'user_exp' => $user->exp,
        ]);
    }

    /**
     * Purchase an item from the shop.
     */
    public function buy(Request $request): JsonResponse
    {
        $request->validate([
            'item_code' => 'required|string',
        ]);

        $user = $request->user();
        $itemCode = $request->input('item_code');

        $catalog = collect($this->getCatalog());
        $itemData = $catalog->firstWhere('item_code', $itemCode);

        if (!$itemData) {
            return response()->json(['message' => 'Item tidak ditemukan di katalog.'], 404);
        }

        try {
            $result = DB::transaction(function () use ($user, $itemData) {
                $this->rewardService->deductCoin($user, $itemData['price'], 'buy_shop_item');

                $inventory = InventoryItem::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'item_code' => $itemData['item_code'],
                    ],
                    [
                        'item_type' => $itemData['item_type'],
                        'quantity' => 0,
                    ]
                );

                $inventory->increment('quantity', 1);

                return $inventory;
            });

            return response()->json([
                'message' => 'Berhasil membeli ' . $itemData['name'] . '!',
                'inventory_item' => $result,
                'user_coin' => $user->fresh()->coin,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
