<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Planting extends Model
{
    /** @use HasFactory<\Database\Factories\PlantingFactory> */
    use HasFactory;

    protected $fillable = [
        'garden_plot_id',
        'plant_species_id',
        'seed_code',
        'planted_at',
        'ready_at',
        'last_watered_at',
        'status',
        'harvested_at',
    ];

    protected $casts = [
        'planted_at' => 'datetime',
        'ready_at' => 'datetime',
        'last_watered_at' => 'datetime',
        'harvested_at' => 'datetime',
    ];

    /**
     * Garden plot where this planting is located.
     */
    public function gardenPlot(): BelongsTo
    {
        return $this->belongsTo(GardenPlot::class);
    }

    /**
     * Matched plant species catalog item for this seed.
     */
    public function plantSpecies(): BelongsTo
    {
        return $this->belongsTo(PlantSpecies::class, 'plant_species_id');
    }

    /**
     * Coin transactions audit logs linked to this planting (e.g. harvest_reward).
     */
    public function coinTransactions(): MorphMany
    {
        return $this->morphMany(CoinTransaction::class, 'reference');
    }

    /**
     * EXP audit logs linked to this planting (e.g. harvest_reward).
     */
    public function expLogs(): MorphMany
    {
        return $this->morphMany(ExpLog::class, 'reference');
    }
}
