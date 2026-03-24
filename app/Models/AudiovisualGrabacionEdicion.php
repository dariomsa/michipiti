<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualGrabacionEdicion extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_grabacion_ediciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audiovisual_id',
        'producto_digital',
        'entrevistador',
        'entrevistado',
        'contacto_cobertura',
        'horario_grabacion',
        'ubicacion',
        'brief_path',
        'brief_original_name',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }
}
