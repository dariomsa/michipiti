<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiplataformaPrioridad extends Model
{
    protected $table = 'multiplataforma_prioridades';

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
