<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\InventoryItem;
use App\Models\ItemRequest;

use App\Services\FriendService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function __construct(
        protected FriendService $friendService
    ) {}

    /**
     * Get friends data list, pending incoming friend requests, and item requests.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get accepted friends
        $friends = $user->friends->map(function ($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'email' => $f->email,
                'role' => $f->role,
                'level' => $f->level,
            ];
        });

        // Get incoming friend requests
        $incomingRequests = Friendship::with('user')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get()
            ->map(function ($fr) {
                return [
                    'id' => $fr->id,
                    'requester_id' => $fr->user->id,
                    'name' => $fr->user->name,
                    'email' => $fr->user->email,
                    'level' => $fr->user->level,
                    'created_at' => $fr->created_at->diffForHumans(),
                ];
            });

        // Get incoming item requests from friends
        $itemRequests = ItemRequest::with('sender')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'request')
            ->get()
            ->map(function ($ir) {
                return [
                    'id' => $ir->id,
                    'sender_id' => $ir->sender->id,
                    'sender_name' => $ir->sender->name,
                    'item_code' => $ir->item_code,
                    'note' => $ir->note,
                    'created_at' => $ir->created_at->diffForHumans(),
                ];
            });

        // Get user's current inventory for gifting
        $inventory = InventoryItem::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->get();

        return response()->json([
            'success' => true,
            'friends' => $friends,
            'incoming_requests' => $incomingRequests,
            'item_requests' => $itemRequests,
            'inventory' => $inventory,
        ]);
    }

    /**
     * Search users by name or email.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $results = $this->friendService->searchUsers($request->user(), $query);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Send friend request to user.
     */
    public function addFriend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $friendship = $this->friendService->sendFriendRequest($request->user(), $validated['friend_id']);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan pertemanan berhasil dikirim!',
                'friendship' => $friendship,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Accept incoming friend request.
     */
    public function acceptFriend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requester_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $friendship = $this->friendService->acceptFriendRequest($request->user(), $validated['requester_id']);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan pertemanan diterima! Anda sekarang berteman.',
                'friendship' => $friendship,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject or remove friend.
     */
    public function removeFriend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $this->friendService->removeFriendship($request->user(), $validated['friend_id']);

            return response()->json([
                'success' => true,
                'message' => 'Hubungan pertemanan berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Request a shop item from a friend.
     */
    public function requestItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
            'item_code' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $itemReq = $this->friendService->requestShopItem(
                $request->user(),
                $validated['friend_id'],
                $validated['item_code'],
                $validated['note'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Permintaan barang shop berhasil dikirim ke teman!',
                'request' => $itemReq,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Gift/Transfer a shop item to a friend.
     */
    public function giftItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'friend_id' => 'required|integer|exists:users,id',
            'item_code' => 'required|string',
            'request_id' => 'nullable|integer|exists:item_requests,id',
        ]);

        try {
            $gift = $this->friendService->giftShopItem(
                $request->user(),
                $validated['friend_id'],
                $validated['item_code'],
                $validated['request_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Barang shop berhasil dikirimkan ke inventaris teman!',
                'gift' => $gift,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
