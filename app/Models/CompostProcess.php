<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompostProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'compost_material_id',
        'status',
        'started_at',
        'matured_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'matured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(CompostMaterial::class, 'compost_material_id');
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(CompostProgressLog::class);
    }

    public function realPlantings(): HasMany
    {
        return $this->hasMany(RealPlanting::class);
    }
}
