<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class HorarioSlot extends Model
{
    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
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
