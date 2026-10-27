<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vigilia extends Model
{
    protected $table = 'vigilia';

    protected $fillable = [
        'sorteo_fecha',
        'anio',
        'semana',
        'grupo',
        'personas',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'sorteo_fecha' => 'date',
            'anio' => 'integer',
            'semana' => 'integer',
            'personas' => 'array',
            'orden' => 'integer',
        ];
    }

    public function integrantes(): HasMany
    {
        return $this->hasMany(VigiliaIntegrante::class);
    }
}
