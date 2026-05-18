<?php

namespace App\Models\Concerns;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa_activa', function (Builder $builder): void {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $empresaId = app(EmpresaContext::class)->currentId();

            if ($empresaId) {
                $builder->where($builder->qualifyColumn('empresa_id'), $empresaId);
            }
        });

        static::creating(function ($model): void {
            if (! isset($model->empresa_id) || ! $model->empresa_id) {
                $empresaId = app(EmpresaContext::class)->currentId();

                if ($empresaId) {
                    $model->empresa_id = $empresaId;
                }
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
