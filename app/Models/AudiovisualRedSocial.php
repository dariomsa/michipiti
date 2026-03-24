<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiovisualRedSocial extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_redes_sociales';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audiovisual_id',
        'nombre',
    ];

    public function audiovisual(): BelongsTo
    {
        return $this->belongsTo(Audiovisual::class);
    }
}
