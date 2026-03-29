<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceVerificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'reference_photo_path',
        'captured_photo_path',
        'match_distance',
        'status',
        'ip_address',
        'user_agent',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}