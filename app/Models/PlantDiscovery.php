<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantDiscovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plant_sighting_id',
        'discovered_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'discovered_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Viewer user who caught/discovered this plant sighting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The verified plant sighting discovered by the viewer.
     */
    public function plantSighting(): BelongsTo
    {
        return $this->belongsTo(PlantSighting::class, 'plant_sighting_id');
    }
}
