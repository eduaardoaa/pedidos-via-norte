<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $fillable = [
        'user_id',
        'route_id',
        'location_id',
        'requester_role',
        'scope',
        'status',
        'notes',
        'admin_notes',
        'approved_by',
        'approved_at',

        'request_latitude',
        'request_longitude',
        'request_location_accuracy',
        'request_street',
        'request_number',
        'request_neighborhood',
        'request_city',
        'request_state',
        'request_zipcode',
        'request_full_address',
        'request_location_captured_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'request_location_captured_at' => 'datetime',
        'request_latitude' => 'decimal:7',
        'request_longitude' => 'decimal:7',
        'request_location_accuracy' => 'decimal:2',
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
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}