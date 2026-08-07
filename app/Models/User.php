<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'exp',
        'coin',
        'locale',
        'avatar',
    ];

    /**
     * Get user avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            $filename = str_ends_with($this->avatar, '.png') || str_ends_with($this->avatar, '.jpeg') || str_ends_with($this->avatar, '.jpg')
                ? $this->avatar
                : $this->avatar . '.png';

            if (file_exists(public_path("images/{$filename}"))) {
                return asset("images/{$filename}");
            }
        }

        return asset('images/guardian_avatar.png');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'exp' => 'integer',
            'coin' => 'integer',
        ];
    }

    /**
     * Get user level based on progressive EXP thresholds:
     * Lvl 1: 0 - 999
     * Lvl 2: 1000 - 1999
     * Lvl 3: 2000 - 3499
     * Lvl 4: 3500 - 5499
     * Lvl 5: 5500 - 7999
     * Lvl 6: 8000+
     */
    public function getLevelAttribute(): int
    {
        $exp = $this->exp ?? 0;

        if ($exp < 1000) return 1;
        if ($exp < 2000) return 2;
        if ($exp < 3500) return 3;
        if ($exp < 5500) return 4;
        if ($exp < 8000) return 5;
        if ($exp < 11000) return 6;
        if ($exp < 14500) return 7;
        if ($exp < 18500) return 8;
        if ($exp < 23000) return 9;

        return 10 + (int) floor(($exp - 23000) / 5000);
    }

    /**
     * Check if user is an Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a Ranger.
     */
    public function isRanger(): bool
    {
        return $this->role === 'ranger';
    }

    /**
     * Check if user is a Viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Plant species catalog entries created by this Ranger user.
     */
    public function createdPlantSpecies(): HasMany
    {
        return $this->hasMany(PlantSpecies::class, 'created_by');
    }

    /**
     * Plant sightings scanned by this Viewer user.
     */
    public function plantSightings(): HasMany
    {
        return $this->hasMany(PlantSighting::class);
    }

    /**
     * Garden plots owned by this user.
     */
    public function gardenPlots(): HasMany
    {
        return $this->hasMany(GardenPlot::class);
    }

    /**
     * Inventory items owned by this user.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Coin transactions audit log history for this user.
     */
    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    /**
     * EXP audit log history for this user.
     */
    public function expLogs(): HasMany
    {
        return $this->hasMany(ExpLog::class);
    }

    /**
     * Friendships initiated by this user.
     */
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Friendships targeting this user.
     */
    public function friendOf(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Get list of accepted User models for this user.
     */
    public function getFriendsAttribute()
    {
        $sentFriendIds = Friendship::where('user_id', $this->id)->where('status', 'accepted')->pluck('friend_id');
        $receivedFriendIds = Friendship::where('friend_id', $this->id)->where('status', 'accepted')->pluck('user_id');

        return User::whereIn('id', $sentFriendIds->merge($receivedFriendIds))->get();
    }
}
