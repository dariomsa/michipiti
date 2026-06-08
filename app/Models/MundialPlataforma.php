<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundialPlataforma extends Model
{
    protected $table = 'mundial_plataformas';

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
