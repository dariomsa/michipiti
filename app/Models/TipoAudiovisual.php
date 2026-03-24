<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAudiovisual extends Model
{
    use HasFactory;

    public const SLUG_EDICION = 'edicion';
    public const SLUG_GRABACION = 'grabacion';
    public const SLUG_GRABACION_EDICION = 'grabacion_edicion';

    protected $table = 'tipo_audiovisuales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'nombre',
        'slug',
        'descripcion',
        'estado',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function audiovisuales(): HasMany
    {
        return $this->hasMany(Audiovisual::class);
    }
}
