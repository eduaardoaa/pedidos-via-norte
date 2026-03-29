<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'product_unit_id',
        'uses_variants',
        'current_stock',
        'active',
    ];

    protected $casts = [
        'uses_variants' => 'boolean',
        'current_stock' => 'decimal:3',
        'active' => 'boolean',
    ];

    protected $appends = [
        'total_stock',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(
            StockLocation::class,
            'product_locations',
            'product_id',
            'stock_location_id'
        )->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): string
{
    if ($this->uses_variants) {
        $sum = $this->relationLoaded('variants')
            ? $this->variants->sum(fn ($variant) => (float) $variant->current_stock)
            : $this->variants()->sum('current_stock');

        return $this->formatStockValue($sum);
    }

    return $this->formatStockValue($this->current_stock);
}

private function formatStockValue($value): string
{
    $value = (float) $value;

    if (fmod($value, 1.0) === 0.0) {
        return (string) (int) $value;
    }

    return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
}
public function orderItems(): HasMany
{
    return $this->hasMany(OrderItem::class);
}
}