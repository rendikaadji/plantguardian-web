<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CoinTransaction extends Model
{
    use HasFactory;

    /**
     * Disable updated_at column since audit log only records creation.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'amount',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * User associated with this coin transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic source reference (e.g. PlantSighting or Planting).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
