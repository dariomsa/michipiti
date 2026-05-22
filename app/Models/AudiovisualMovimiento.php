<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualMovimiento extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'audiovisual_movimientos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'audiovisual_id',
        'user_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAccionLabelAttribute(): string
    {
        return match ($this->accion) {
            'CREADO' => 'Creado',
            'EDITADO' => 'Editado',
            'COMENTARIO' => 'Comentario',
            'ENVIADO_REVISION' => 'Enviado a revisión',
            'ASIGNADO' => 'Asignado',
            'FINALIZADO' => 'Finalizado',
            'ARCHIVO_SLACK_ELIMINADO' => 'Archivo de Slack eliminado',
            default => str_replace('_', ' ', ucfirst(strtolower($this->accion))),
        };
    }
}
