<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MultiplataformaProducto extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'multiplataforma_productos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'tipo_producto_id',
        'multiplataforma_prioridad_id',
        'multiplataforma_plataforma_id',
        'multiplataforma_plataformas_ids',
        'multiplataforma_equipo_id',
        'multiplataforma_tipo_id',
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
            'multiplataforma_plataformas_ids' => 'array',
            'redes_sociales_ids' => 'array',
        ];
    }

    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class);
    }

    public function multiplataformaPrioridad(): BelongsTo
    {
        return $this->belongsTo(MultiplataformaPrioridad::class);
    }

    public function multiplataformaPlataforma(): BelongsTo
    {
        return $this->belongsTo(MultiplataformaPlataforma::class);
    }

    public function multiplataformaEquipo(): BelongsTo
    {
        return $this->belongsTo(MultiplataformaEquipo::class);
    }

    public function multiplataformaTipo(): BelongsTo
    {
        return $this->belongsTo(MultiplataformaTipo::class);
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
        return $this->hasMany(MultiplataformaMovimiento::class, 'multiplataforma_producto_id')->latest('id');
    }

    public function productoConvertido(): HasOne
    {
        return $this->hasOne(Producto::class, 'multiplataforma_id');
    }
}
