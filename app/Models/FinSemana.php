<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinSemana extends Model
{
    protected $table = 'fin_semana';

    protected $fillable = [
        'mes',
        'fines_de_semana',
        'jefe_turno',
        'jefe_turno_user_id',
        'grupo',
        'deportes_user_id',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function deportesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deportes_user_id');
    }

    public function jefeTurnoUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_turno_user_id');
    }
}
