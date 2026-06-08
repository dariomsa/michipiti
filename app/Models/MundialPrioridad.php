<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundialPrioridad extends Model
{
    protected $table = 'mundial_prioridades';

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
