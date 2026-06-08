<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundialEquipo extends Model
{
    protected $table = 'mundial_equipos';

    protected $fillable = [
        'nombre',
        'slug',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
