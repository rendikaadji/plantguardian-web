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

        $isEn = app()->getLocale() === 'en';

        $otherItems = [
            [
                'item_code' => 'tool_fertilizer',
                'name' => $isEn ? 'Super Organic Fertilizer' : 'Pupuk Organik Super',
                'item_type' => 'tool',
                'price' => 30,
                'icon' => '🧪',
                'description' => $isEn ? 'Accelerates plant growth in garden.' : 'Mempercepat pertumbuhan tanaman di kebun.',
                'time_reduction_minutes' => 10,
                'usage_label' => $isEn ? 'Speedup Growth 10m' : 'Potong Waktu Tumbuh 10m',
            ],
            [
                'item_code' => 'tool_watering_can',
                'name' => $isEn ? 'Automatic Watering Can' : 'Penyiram Otomatis',
                'item_type' => 'tool',
                'price' => 100,
                'icon' => '🚿',
                'description' => $isEn ? 'Automatic watering tool to cut growth wait time.' : 'Alat siram otomatis untuk memotong waktu tunggu tumbuh.',
                'time_reduction_minutes' => 20,
                'usage_label' => $isEn ? 'Speedup Growth 20m' : 'Potong Waktu Tumbuh 20m',
            ],
        ];

        $avatarItems = [
            [
                'item_code' => 'avatar_profile1',
                'name' => $isEn ? 'Profile Avatar #1' : 'Foto Profil #1',
                'item_type' => 'avatar',
                'price' => 3100,
                'icon' => '🖼️',
                'image' => asset('images/avatars/profile1.png'),
                'avatar_key' => 'profile1',
                'description' => $isEn ? 'Exclusive profile picture edition #1.' : 'Foto profil edisi eksklusif #1.',
            ],
            [
                'item_code' => 'avatar_profile2',
                'name' => $isEn ? 'Profile Avatar #2' : 'Foto Profil #2',
                'item_type' => 'avatar',
                'price' => 3100,
                'icon' => '🖼️',
                'image' => asset('images/avatars/profile2.png'),
                'avatar_key' => 'profile2',
                'description' => $isEn ? 'Exclusive profile picture edition #2.' : 'Foto profil edisi eksklusif #2.',
            ],
            [
                'item_code' => 'avatar_profile3',
                'name' => $isEn ? 'Profile Avatar #3' : 'Foto Profil #3',
                'item_type' => 'avatar',
                'price' => 3100,
                'icon' => '🖼️',
                'image' => asset('images/avatars/profile3.png'),
                'avatar_key' => 'profile3',
                'description' => $isEn ? 'Exclusive profile picture edition #3.' : 'Foto profil edisi eksklusif #3.',
            ],
        ];

        return array_merge($catalog, $otherItems, $avatarItems);
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
            'current_avatar' => $user->avatar ?? 'default',
            'avatar_url' => $user->avatar_url,
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
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage() ?: 'Gagal membeli item.'], 400);
        }
    }

    /**
     * Equip or change profile avatar.
     */
    public function equipAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar_code' => 'required|string',
            ]);

            $user = $request->user();
            $avatarCode = $request->input('avatar_code');

            if ($avatarCode === 'default' || $avatarCode === 'guardian_avatar') {
                $user->update(['avatar' => null]);
                return response()->json([
                    'message' => 'Foto profil diubah ke default.',
                    'avatar_url' => $user->fresh()->avatar_url,
                    'current_avatar' => 'default',
                ]);
            }

            // Clean up code format if item_code is avatar_profile1 or profile1
            $cleanKey = str_replace('avatar_', '', $avatarCode);
            $itemCode = str_starts_with($avatarCode, 'avatar_') ? $avatarCode : 'avatar_' . $avatarCode;

            // Check if user owns the avatar item in inventory
            $hasItem = InventoryItem::where('user_id', $user->id)
                ->where(function ($q) use ($itemCode, $cleanKey) {
                    $q->where('item_code', $itemCode)
                      ->orWhere('item_code', $cleanKey);
                })
                ->where('quantity', '>', 0)
                ->exists();

            if (!$hasItem) {
                return response()->json(['message' => 'Anda belum memiliki foto profil ini. Silakan beli di Toko.'], 403);
            }

            $user->update(['avatar' => $cleanKey]);

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui!',
                'avatar_url' => $user->fresh()->avatar_url,
                'current_avatar' => $cleanKey,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage() ?: 'Gagal memperbarui foto profil.'], 400);
        }
    }
}
