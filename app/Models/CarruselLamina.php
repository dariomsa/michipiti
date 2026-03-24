<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarruselLamina extends Model
{
    use HasFactory;

    protected $table = 'carrusel_laminas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'carrusel_id',
        'orden',
        'titulo',
        'descripcion',
        'archivo_path',
        'archivo_original',
        'archivo_mime',
        'archivo_size',
        'url_externa',
        'sin_foto',
        'motivo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'archivo_size' => 'integer',
            'sin_foto' => 'boolean',
        ];
    }

    public function carrusel(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'carrusel_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(CarruselLaminaArchivo::class, 'lamina_id')->orderBy('orden');
    }
}
