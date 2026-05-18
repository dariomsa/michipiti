<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualRedSocial extends Model
{
    use BelongsToEmpresa;
    use HasFactory;

    protected $table = 'audiovisual_redes_sociales';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'audiovisual_id',
        'nombre',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }
}
