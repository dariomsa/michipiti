<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarruselLaminaArchivo extends Model
{
    use HasFactory;

    protected $table = 'carrusel_lamina_archivos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lamina_id',
        'orden',
        'archivo_path',
        'archivo_original',
        'archivo_mime',
        'archivo_size',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'archivo_size' => 'integer',
        ];
    }

    public function lamina(): BelongsTo
    {
        return $this->belongsTo(CarruselLamina::class, 'lamina_id');
    }
}
