<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CalendarioEspecial extends Model
{
    use BelongsToEmpresa;

    protected $table = 'calendario_especial';

    protected $fillable = [
        'empresa_id',
        'fecha',
        'motivo',
        'tipo_feriado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'tipo_feriado' => 'integer',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(CalendarioEspecialSlot::class, 'tipo_feriado', 'tipo_feriado')
            ->orderBy('hora');
    }
}
