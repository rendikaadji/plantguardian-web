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
    ];

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
}
