<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioSlot extends Model
{
    protected $fillable = [
        'dia_semana',
        'hora',
        'visible',
        'fuera_de_pauta',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'visible' => 'boolean',
            'fuera_de_pauta' => 'boolean',
        ];
    }
}
