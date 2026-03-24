<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarruselMensaje extends Model
{
    use HasFactory;

    protected $table = 'carrusel_mensajes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'carrusel_id',
        'user_id',
        'reply_to_id',
        'tipo',
        'mensaje',
        'read_at',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function carrusel(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'carrusel_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }
}
