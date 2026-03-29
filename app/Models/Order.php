<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'route_id',
        'location_id',
        'order_date',
        'notes',
        'address_snapshot',
        'excel_generated',
        'pdf_generated',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'excel_generated' => 'boolean',
        'pdf_generated' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    public function getTotalUnitsAttribute(): float
    {
        return (float) $this->items->sum('quantity');
    }
}