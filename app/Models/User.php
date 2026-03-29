<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'cargo_id',
        'name',
        'cpf',
        'usuario',
        'numero',
        'email',
        'password',
        'must_change_password',
        'active',
        'face_photo_path',
        'face_descriptor',
        'face_registered_at',
        'must_register_face',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'must_change_password' => 'boolean',
            'face_registered_at' => 'datetime',
            'must_register_face' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    public function isAdmin(): bool
    {
        return optional($this->cargo)->codigo === 'admin';
    }
    public function materialRequests()
{
    return $this->hasMany(\App\Models\MaterialRequest::class);
}
public function approvedMaterialRequests()
{
    return $this->hasMany(\App\Models\MaterialRequest::class, 'approved_by');
}
}