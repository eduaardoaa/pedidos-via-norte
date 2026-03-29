<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = [
        'nome',
        'codigo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}