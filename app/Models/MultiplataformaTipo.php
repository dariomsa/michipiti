<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiplataformaTipo extends Model
{
    protected $table = 'multiplataforma_tipos';

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
