<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GardenPlot extends Model
{
    /** @use HasFactory<\Database\Factories\GardenPlotFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slot_number',
        'unlocked',
        'purchase_cost',
    ];

    protected $casts = [
        'slot_number' => 'integer',
        'unlocked' => 'boolean',
        'purchase_cost' => 'integer',
    ];

    /**
     * User who owns this garden plot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * History of plantings on this plot.
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Current active planting on this plot (status 'growing' or 'ready').
     */
    public function currentPlanting(): HasOne
    {
        return $this->hasOne(Planting::class)->whereIn('status', ['growing', 'ready'])->latestOfMany();
    }
}
