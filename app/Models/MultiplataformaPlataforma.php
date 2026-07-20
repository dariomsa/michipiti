<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiplataformaPlataforma extends Model
{
    protected $table = 'multiplataforma_plataformas';

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
