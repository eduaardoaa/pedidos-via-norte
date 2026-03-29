<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'current_stock',
        'sort_order',
        'active',
    ];
    protected $appends = [
    'formatted_stock',
];

public function getFormattedStockAttribute(): string
{
    $value = (float) $this->current_stock;

    if (fmod($value, 1.0) === 0.0) {
        return (string) (int) $value;
    }

    return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
}

    protected $casts = [
        'current_stock' => 'decimal:3',
        'active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }
    public function orderItems(): HasMany
{
    return $this->hasMany(OrderItem::class, 'product_variant_id');
}
}