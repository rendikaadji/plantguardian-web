<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealPlanting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'compost_process_id',
        'plant_species_id',
        'photo_path',
        'latitude',
        'longitude',
        'planted_at',
        'verification_status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'planted_at' => 'datetime',
        'verified_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function compostProcess(): BelongsTo
    {
        return $this->belongsTo(CompostProcess::class);
    }

    public function plantSpecies(): BelongsTo
    {
        return $this->belongsTo(PlantSpecies::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
