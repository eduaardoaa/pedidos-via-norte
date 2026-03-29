<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'route_id',
        'scope',
        'name',
        'address',
        'latitude',
        'longitude',
        'geocoded_at',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'geocoded_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isRota(): bool
    {
        return $this->scope === 'rota';
    }

    public function isAlmoxarifado(): bool
    {
        return $this->scope === 'almoxarifado';
    }
}