<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompostMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_code',
        'name',
        'description',
        'instructions',
        'created_by',
    ];

    /**
     * Ranger user who created this compost material guide.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Active compost processes using this material.
     */
    public function processes(): HasMany
    {
        return $this->hasMany(CompostProcess::class);
    }
}
