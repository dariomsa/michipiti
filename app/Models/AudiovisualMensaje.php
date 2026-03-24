<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualMensaje extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_mensajes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audiovisual_id',
        'user_id',
        'reply_to_id',
        'tipo',
        'mensaje',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
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
