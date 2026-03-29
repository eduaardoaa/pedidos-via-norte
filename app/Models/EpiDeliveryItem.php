<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpiDeliveryItem extends Model
{
    protected $fillable = [
        'epi_delivery_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'next_expected_date'
    ];

    protected $casts = [
        'next_expected_date' => 'date'
    ];

    public function delivery()
    {
        return $this->belongsTo(EpiDelivery::class, 'epi_delivery_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}