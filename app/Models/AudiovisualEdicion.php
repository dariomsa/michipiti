<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualEdicion extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_ediciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audiovisual_id',
        'entrevistador',
        'entrevistado',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }
}
