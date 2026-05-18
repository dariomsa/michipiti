<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoProducto extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    public const SLUG_CARRUSEL = 'carrusel';
    public const SLUG_REEL = 'reel';

    protected $table = 'tipo_productos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'nombre',
        'slug',
        'descripcion',
        'estado',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function audiovisuales(): HasMany
    {
        return $this->hasMany(Audiovisual::class);
    }
}
