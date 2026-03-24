<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualGrabacion extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_grabaciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audiovisual_id',
        'producto_digital',
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
