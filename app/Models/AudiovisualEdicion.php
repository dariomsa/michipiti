<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualEdicion extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'audiovisual_ediciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'audiovisual_id',
        'entrevistador',
        'entrevistado',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }
}
