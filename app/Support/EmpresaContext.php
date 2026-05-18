<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Support\Collection;

class EmpresaContext
{
    private const SESSION_KEY = 'empresa_activa_id';

    public function currentId(): ?int
    {
        $empresaId = session(self::SESSION_KEY);

        if ($empresaId !== null) {
            return (int) $empresaId;
        }

        $defaultEmpresaId = Empresa::query()
            ->where('estado', 'activa')
            ->orderBy('id')
            ->value('id');

        if ($defaultEmpresaId) {
            session([self::SESSION_KEY => (int) $defaultEmpresaId]);

            return (int) $defaultEmpresaId;
        }

        return null;
    }

    public function current(): ?Empresa
    {
        $empresaId = $this->currentId();

        if (! $empresaId) {
            return null;
        }

        return Empresa::query()->find($empresaId);
    }

    /**
     * @return Collection<int, Empresa>
     */
    public function available(): Collection
    {
        return Empresa::query()
            ->where('estado', 'activa')
            ->orderBy('nombre')
            ->get();
    }

    public function setCurrentId(int $empresaId): void
    {
        session([self::SESSION_KEY => $empresaId]);
    }
}
