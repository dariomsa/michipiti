<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\CarruselMensaje;
use App\Models\CarruselMovimiento;
use App\Models\AudiovisualMensaje;
use App\Models\AudiovisualMovimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function productosComoEditor(): HasMany
    {
        return $this->hasMany(Producto::class, 'editor_id');
    }

    public function productosComoDisenador(): HasMany
    {
        return $this->hasMany(Producto::class, 'disenador_id');
    }

    public function productosComoManager(): HasMany
    {
        return $this->hasMany(Producto::class, 'manager_id');
    }

    public function audiovisuales(): HasMany
    {
        return $this->hasMany(Audiovisual::class);
    }

    public function audiovisualesComoEditor(): HasMany
    {
        return $this->hasMany(Audiovisual::class, 'editor_id');
    }

    public function audiovisualesComoDisenador(): HasMany
    {
        return $this->hasMany(Audiovisual::class, 'disenador_id');
    }

    public function audiovisualesComoManager(): HasMany
    {
        return $this->hasMany(Audiovisual::class, 'manager_id');
    }

    public function mensajesCarrusel(): HasMany
    {
        return $this->hasMany(CarruselMensaje::class);
    }

    public function movimientosCarrusel(): HasMany
    {
        return $this->hasMany(CarruselMovimiento::class);
    }

    public function mensajesAudiovisual(): HasMany
    {
        return $this->hasMany(AudiovisualMensaje::class);
    }

    public function movimientosAudiovisual(): HasMany
    {
        return $this->hasMany(AudiovisualMovimiento::class);
    }
}
