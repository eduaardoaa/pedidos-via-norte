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
        'location_id',
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function epiDeliveries()
    {
        return $this->hasMany(EpiDelivery::class);
    }
    public function employees()
{
    return $this->hasMany(Employee::class);
}
}