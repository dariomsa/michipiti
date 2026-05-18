<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class CalendarioEspecialSlot extends Model
{
    use BelongsToEmpresa;

    protected $table = 'calendario_especial_slots';

    protected $fillable = [
        'empresa_id',
        'tipo_feriado',
        'hora',
        'visible',
        'fuera_de_pauta',
    ];

    protected function casts(): array
    {
        return [
            'tipo_feriado' => 'integer',
            'visible' => 'boolean',
            'fuera_de_pauta' => 'boolean',
        ];
    }
}
