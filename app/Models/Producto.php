<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'tipo_producto_id',
        'user_id',
        'responsable2_id',
        'redes_sociales_ids',
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
            'redes_sociales_ids' => 'array',
        ];
    }

    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function responsable2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable2_id');
    }

    public function disenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disenador_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function laminas(): HasMany
    {
        return $this->hasMany(CarruselLamina::class, 'carrusel_id')->orderBy('orden');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(CarruselMensaje::class, 'carrusel_id')->latest('id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CarruselMovimiento::class, 'carrusel_id')->latest('id');
    }

    public function esCarrusel(): bool
    {
        return $this->tipoProducto?->slug === TipoProducto::SLUG_CARRUSEL;
    }
}
