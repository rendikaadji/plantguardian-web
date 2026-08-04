<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PlantSighting extends Model
{
    /** @use HasFactory<\Database\Factories\PlantSightingFactory> */
    use HasFactory;

    protected $fillable = [
        'ranger_id',
        'plant_species_id',
        'photo_path',
        'confidence_score',
        'latitude',
        'longitude',
        'saved_to_gallery',
        'verification_status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'saved_to_gallery' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Ranger who scanned this plant sighting in the field.
     */
    public function ranger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ranger_id');
    }

    /**
     * Discoveries (catches) by Viewers linked to this sighting.
     */
    public function discoveries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlantDiscovery::class, 'plant_sighting_id');
    }

    /**
     * Matched plant species catalog item (nullable if unrecognized).
     */
    public function plantSpecies(): BelongsTo
    {
        return $this->belongsTo(PlantSpecies::class, 'plant_species_id');
    }

    /**
     * Alias for plantSpecies relationship.
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(PlantSpecies::class, 'plant_species_id');
    }

    /**
     * Coin transaction audit logs linked to this sighting scan reward.
     */
    public function coinTransactions(): MorphMany
    {
        return $this->morphMany(CoinTransaction::class, 'reference');
    }

    /**
     * EXP audit logs linked to this sighting scan reward.
     */
    public function expLogs(): MorphMany
    {
        return $this->morphMany(ExpLog::class, 'reference');
    }

    /**
     * Ranger user who verified or rejected this sighting.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
