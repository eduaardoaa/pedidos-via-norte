<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpiDelivery extends Model
{
    protected $fillable = [
        'employee_id',
        'delivery_date',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'delivery_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(EpiDeliveryItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}