<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_type',
        'item_code',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * User who owns this inventory item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
