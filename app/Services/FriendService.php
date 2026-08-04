<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\InventoryItem;
use App\Models\ItemRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FriendService
{
    /**
     * Search registered users by name or email (excluding current user).
     */
    public function searchUsers(User $currentUser, string $query): Collection
    {
        if (trim($query) === '') {
            return collect();
        }

        return User::where('id', '!=', $currentUser->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get()
            ->map(function ($user) use ($currentUser) {
                $friendship = Friendship::where(function ($q) use ($currentUser, $user) {
                    $q->where('user_id', $currentUser->id)->where('friend_id', $user->id);
                })->orWhere(function ($q) use ($currentUser, $user) {
                    $q->where('user_id', $user->id)->where('friend_id', $currentUser->id);
                })->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'level' => $user->level,
                    'friendship_status' => $friendship ? $friendship->status : 'none',
                    'is_requester' => $friendship ? ($friendship->user_id === $currentUser->id) : false,
                ];
            });
    }

    /**
     * Send friend request to target user.
     */
    public function sendFriendRequest(User $currentUser, int $targetUserId): Friendship
    {
        if ($currentUser->id === $targetUserId) {
            throw new Exception('Anda tidak dapat menambahkan diri sendiri sebagai teman.');
        }

        $targetUser = User::find($targetUserId);
        if (! $targetUser) {
            throw new Exception('Pengguna tujuan tidak ditemukan.');
        }

        $existing = Friendship::where(function ($q) use ($currentUser, $targetUserId) {
            $q->where('user_id', $currentUser->id)->where('friend_id', $targetUserId);
        })->orWhere(function ($q) use ($currentUser, $targetUserId) {
            $q->where('user_id', $targetUserId)->where('friend_id', $currentUser->id);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                throw new Exception('Anda sudah berteman dengan pengguna ini.');
            }
            if ($existing->status === 'pending') {
                throw new Exception('Permintaan pertemanan sudah dikirim dan menunggu konfirmasi.');
            }

            $existing->update([
                'user_id' => $currentUser->id,
                'friend_id' => $targetUserId,
                'status' => 'pending',
            ]);

            return $existing;
        }

        return Friendship::create([
            'user_id' => $currentUser->id,
            'friend_id' => $targetUserId,
            'status' => 'pending',
        ]);
    }

    /**
     * Accept incoming friend request.
     */
    public function acceptFriendRequest(User $currentUser, int $requesterId): Friendship
    {
        $friendship = Friendship::where('user_id', $requesterId)
            ->where('friend_id', $currentUser->id)
            ->where('status', 'pending')
            ->first();

        if (! $friendship) {
            throw new Exception('Permintaan pertemanan tidak ditemukan.');
        }

        $friendship->update(['status' => 'accepted']);

        return $friendship;
    }

    /**
     * Reject incoming friend request or remove friend.
     */
    public function removeFriendship(User $currentUser, int $targetUserId): bool
    {
        return Friendship::where(function ($q) use ($currentUser, $targetUserId) {
            $q->where('user_id', $currentUser->id)->where('friend_id', $targetUserId);
        })->orWhere(function ($q) use ($currentUser, $targetUserId) {
            $q->where('user_id', $targetUserId)->where('friend_id', $currentUser->id);
        })->delete() > 0;
    }

    /**
     * Check if two users are accepted friends.
     */
    public function areFriends(User $user1, User $user2): bool
    {
        return Friendship::where('status', 'accepted')
            ->where(function ($q) use ($user1, $user2) {
                $q->where('user_id', $user1->id)->where('friend_id', $user2->id)
                  ->orWhere(function ($q2) use ($user1, $user2) {
                      $q2->where('user_id', $user2->id)->where('friend_id', $user1->id);
                  });
            })->exists();
    }

    /**
     * Request a shop item from an accepted friend.
     */
    public function requestShopItem(User $currentUser, int $friendId, string $itemCode, ?string $note = null): ItemRequest
    {
        $friend = User::find($friendId);
        if (! $friend) {
            throw new Exception('Teman tidak ditemukan.');
        }

        if (! $this->areFriends($currentUser, $friend)) {
            throw new Exception('Anda hanya dapat meminta barang shop kepada teman dalam Aliansi.');
        }

        return ItemRequest::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $friend->id,
            'item_code' => $itemCode,
            'type' => 'request',
            'status' => 'pending',
            'note' => $note,
        ]);
    }

    /**
     * Fulfill/Give a shop item from inventory to friend (whether fulfilling request or gifting directly).
     */
    public function giftShopItem(User $currentUser, int $friendId, string $itemCode, ?int $requestId = null): ItemRequest
    {
        $friend = User::find($friendId);
        if (! $friend) {
            throw new Exception('Teman tidak ditemukan.');
        }

        if (! $this->areFriends($currentUser, $friend)) {
            throw new Exception('Anda hanya dapat memberikan barang shop kepada teman dalam Aliansi.');
        }

        return DB::transaction(function () use ($currentUser, $friend, $itemCode, $requestId) {
            // Check if sender has the item in inventory with quantity >= 1
            $senderInv = InventoryItem::where('user_id', $currentUser->id)
                ->where('item_code', $itemCode)
                ->where('quantity', '>=', 1)
                ->first();

            if (! $senderInv) {
                throw new Exception('Anda tidak memiliki barang tersebut di inventaris untuk diberikan.');
            }

            // Deduct item from sender's inventory
            $senderInv->decrement('quantity', 1);

            // Add item to receiver's (friend's) inventory
            $receiverInv = InventoryItem::firstOrCreate(
                [
                    'user_id' => $friend->id,
                    'item_code' => $itemCode,
                ],
                [
                    'item_type' => $senderInv->item_type ?? 'tool',
                    'quantity' => 0,
                ]
            );

            $receiverInv->increment('quantity', 1);

            // If linked to an item request, mark request as fulfilled
            if ($requestId) {
                $itemReq = ItemRequest::find($requestId);
                if ($itemReq && $itemReq->receiver_id === $currentUser->id) {
                    $itemReq->update(['status' => 'fulfilled']);
                }
            }

            // Create record for gift transaction
            return ItemRequest::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $friend->id,
                'item_code' => $itemCode,
                'type' => 'gift',
                'status' => 'fulfilled',
                'note' => 'Dikirim sebagai hadiah aliansi',
            ]);
        });
    }
}
