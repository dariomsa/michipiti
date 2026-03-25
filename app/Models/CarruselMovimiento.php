<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarruselMovimiento extends Model
{
    use HasFactory;

    protected $table = 'carrusel_movimientos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'carrusel_id',
        'user_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function carrusel(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'carrusel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAccionLabelAttribute(): string
    {
        return match ($this->accion) {
            'APROBADO' => 'Aprobado',
            'ENVIADO_REVISION' => 'Enviado a revision',
            'ENVIADO_DISENO' => 'Enviado a diseno',
            'ASIGNADO_DISENADOR' => 'Asignado a disenador',
            'DEVUELTO_EDITOR' => 'Devuelto al editor',
            'DEVUELTO_PERIODISTA' => 'Devuelto al periodista',
            'FINALIZADO' => 'Finalizado',
            'EDITADO' => 'Editado',
            'COMENTARIO' => 'Comentario',
            default => str_replace('_', ' ', ucfirst(strtolower($this->accion))),
        };
    }
}
