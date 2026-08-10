<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SightingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plant_sighting_id',
        'reason',
        'notes',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * User who submitted this report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Reported plant sighting.
     */
    public function sighting(): BelongsTo
    {
        return $this->belongsTo(PlantSighting::class, 'plant_sighting_id');
    }

    /**
     * Admin user who resolved or dismissed this report.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
