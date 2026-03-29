<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLocation extends Model
{
    protected $table = 'stock_locations';

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_locations',
            'stock_location_id',
            'product_id'
        )->withTimestamps();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_location_id');
    }
}