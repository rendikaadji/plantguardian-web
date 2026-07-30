<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompostProgressLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'compost_process_id',
        'stage_label',
        'photo_path',
        'latitude',
        'longitude',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function compostProcess(): BelongsTo
    {
        return $this->belongsTo(CompostProcess::class);
    }
}
