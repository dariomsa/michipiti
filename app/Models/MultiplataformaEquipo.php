<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiplataformaEquipo extends Model
{
    protected $table = 'multiplataforma_equipos';

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
