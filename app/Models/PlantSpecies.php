<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantSpecies extends Model
{
    /** @use HasFactory<\Database\Factories\PlantSpeciesFactory> */
    use HasFactory;

    protected $fillable = [
        'species_code',
        'common_name',
        'scientific_name',
        'description',
        'care_instructions',
        'conservation_status',
        'reference_image_path',
        'created_by',
    ];

    /**
     * Ranger user who created/registered this species catalog item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Plant sightings matched with this species.
     */
    public function sightings(): HasMany
    {
        return $this->hasMany(PlantSighting::class);
    }

    /**
     * Plantings using this species in minigame.
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Real plantings using this species.
     */
    public function realPlantings(): HasMany
    {
        return $this->hasMany(RealPlanting::class);
    }
}
