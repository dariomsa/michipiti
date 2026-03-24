<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Audiovisual extends Model
{
    use HasFactory;

    protected $table = 'audiovisuales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo_audiovisual_id',
        'user_id',
        'editor_id',
        'disenador_id',
        'manager_id',
        'assigned_at',
        'titulo',
        'fecha',
        'hora',
        'orden_dia',
        'seccion',
        'copy',
        'referencia',
        'hashtags',
        'canva_url',
        'creditos',
        'estado',
        'prioridad',
        'dificultad',
        'origen',
        'pauta_comercial',
        'programado_metricool',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'fecha' => 'date',
            'orden_dia' => 'integer',
            'pauta_comercial' => 'boolean',
            'programado_metricool' => 'boolean',
        ];
    }

    public function tipoAudiovisual(): BelongsTo
    {
        return $this->belongsTo(TipoAudiovisual::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function disenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disenador_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function edicionDetalle(): HasOne
    {
        return $this->hasOne(AudiovisualEdicion::class);
    }

    public function grabacionDetalle(): HasOne
    {
        return $this->hasOne(AudiovisualGrabacion::class);
    }

    public function grabacionEdicionDetalle(): HasOne
    {
        return $this->hasOne(AudiovisualGrabacionEdicion::class);
    }

    public function requerimientos(): HasMany
    {
        return $this->hasMany(AudiovisualRequerimiento::class)->orderBy('id');
    }

    public function redesSociales(): HasMany
    {
        return $this->hasMany(AudiovisualRedSocial::class)->orderBy('id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(AudiovisualMensaje::class)->latest('id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(AudiovisualMovimiento::class)->latest('id');
    }
}
