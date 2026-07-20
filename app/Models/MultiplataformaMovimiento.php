<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultiplataformaMovimiento extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'multiplataforma_movimientos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'multiplataforma_producto_id',
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

    public function multiplataformaProducto(): BelongsTo
    {
        return $this->belongsTo(MultiplataformaProducto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
