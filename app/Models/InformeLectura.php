<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeLectura extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'informe_id',
        'user_id',
        'aperturas',
        'total_seconds',
        'last_ip',
        'last_opened_at',
        'last_seen_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aperturas' => 'integer',
            'total_seconds' => 'integer',
            'last_opened_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function informe(): BelongsTo
    {
        return $this->belongsTo(Informe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
