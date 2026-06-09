<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MundialProducto extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'mundial_productos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'tipo_producto_id',
        'mundial_prioridad_id',
        'mundial_plataforma_id',
        'mundial_plataformas_ids',
        'mundial_equipo_id',
        'mundial_tipo_id',
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
        'visible',
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
            'visible' => 'boolean',
            'mundial_plataformas_ids' => 'array',
            'redes_sociales_ids' => 'array',
        ];
    }

    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class);
    }

    public function mundialPrioridad(): BelongsTo
    {
        return $this->belongsTo(MundialPrioridad::class);
    }

    public function mundialPlataforma(): BelongsTo
    {
        return $this->belongsTo(MundialPlataforma::class);
    }

    public function mundialEquipo(): BelongsTo
    {
        return $this->belongsTo(MundialEquipo::class);
    }

    public function mundialTipo(): BelongsTo
    {
        return $this->belongsTo(MundialTipo::class);
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

    public function movimientos(): HasMany
    {
        return $this->hasMany(MundialMovimiento::class, 'mundial_producto_id')->latest('id');
    }

    public function productoConvertido(): HasOne
    {
        return $this->hasOne(Producto::class, 'mundial_id');
    }
}
