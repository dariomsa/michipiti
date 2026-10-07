<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VigiliaIntegrante extends Model
{
    protected $table = 'vigilia_integrantes';

    protected $fillable = [
        'vigilia_id',
        'user_id',
    ];

    public function vigilia(): BelongsTo
    {
        return $this->belongsTo(Vigilia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
