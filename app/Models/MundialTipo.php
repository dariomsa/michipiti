<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundialTipo extends Model
{
    protected $table = 'mundial_tipos';

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
