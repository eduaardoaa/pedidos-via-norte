<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'cpf',
        'registration',
        'hired_at',
        'cargo_id',
        'active'
    ];

    protected $casts = [
        'hired_at' => 'date',
        'active' => 'boolean'
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function epiDeliveries()
    {
        return $this->hasMany(EpiDelivery::class);
    }
}