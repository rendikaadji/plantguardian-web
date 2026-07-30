<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
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
     * List shop catalog items and user inventory.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $catalog = [
            [
                'item_code' => 'seed_sunflower',
                'name' => 'Benih Bunga Matahari',
                'item_type' => 'seed',
                'price' => 50,
                'icon' => '🌻',
                'description' => 'Benih bunga matahari hias berkualitas tinggi.',
            ],
            [
                'item_code' => 'seed_tomato',
                'name' => 'Benih Tomat Organik',
                'item_type' => 'seed',
                'price' => 75,
                'icon' => '🍅',
                'description' => 'Benih buah tomat cepat tumbuh dan manis.',
            ],
            [
                'item_code' => 'seed_monstera',
                'name' => 'Benih Monstera Deliciosa',
                'item_type' => 'seed',
                'price' => 120,
                'icon' => '🌿',
                'description' => 'Benih tanaman hias indoor eksotis favorit.',
            ],
            [
                'item_code' => 'tool_fertilizer',
                'name' => 'Pupuk Organik Super',
                'item_type' => 'tool',
                'price' => 30,
                'icon' => '🧪',
                'description' => 'Mempercepat pertumbuhan tanaman di kebun.',
            ],
            [
                'item_code' => 'tool_watering_can',
                'name' => 'Penyiram Otomatis',
                'item_type' => 'tool',
                'price' => 100,
                'icon' => '🚿',
                'description' => 'Menjaga kelembapan lahan secara otomatis.',
            ],
            [
                'item_code' => 'tool_shovel',
                'name' => 'Sekop Kebun Emas',
                'item_type' => 'tool',
                'price' => 150,
                'icon' => '⛏️',
                'description' => 'Alat olah tanah premium untuk efisiensi tinggi.',
            ],
            [
                'item_code' => 'material_compost_kit',
                'name' => 'Starter Kit Kompos',
                'item_type' => 'material',
                'price' => 80,
                'icon' => '📦',
                'description' => 'Wadah dan akselerator pengolah sampah dapur.',
            ],
            [
                'item_code' => 'material_bio_activator',
                'name' => 'Bio-Akselerator Mikroba',
                'item_type' => 'material',
                'price' => 60,
                'icon' => '🧫',
                'description' => 'Bahan pengurai organik untuk mempercepat kompos.',
            ],
        ];

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

        $catalog = collect([
            ['item_code' => 'seed_sunflower', 'name' => 'Benih Bunga Matahari', 'item_type' => 'seed', 'price' => 50],
            ['item_code' => 'seed_tomato', 'name' => 'Benih Tomat Organik', 'item_type' => 'seed', 'price' => 75],
            ['item_code' => 'seed_monstera', 'name' => 'Benih Monstera Deliciosa', 'item_type' => 'seed', 'price' => 120],
            ['item_code' => 'tool_fertilizer', 'name' => 'Pupuk Organik Super', 'item_type' => 'tool', 'price' => 30],
            ['item_code' => 'tool_watering_can', 'name' => 'Penyiram Otomatis', 'item_type' => 'tool', 'price' => 100],
            ['item_code' => 'tool_shovel', 'name' => 'Sekop Kebun Emas', 'item_type' => 'tool', 'price' => 150],
            ['item_code' => 'material_compost_kit', 'name' => 'Starter Kit Kompos', 'item_type' => 'material', 'price' => 80],
            ['item_code' => 'material_bio_activator', 'name' => 'Bio-Akselerator Mikroba', 'item_type' => 'material', 'price' => 60],
        ]);

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
