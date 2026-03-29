<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'visited_at',
        'latitude',
        'longitude',
        'address',
        'display_name',
        'service_report',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getPlaceNameAttribute(): string
    {
        if (!empty($this->location?->name)) {
            return $this->location->name;
        }

        if (!empty($this->display_name)) {
            return $this->display_name;
        }

        return $this->address ?: 'Endereço não identificado';
    }
}