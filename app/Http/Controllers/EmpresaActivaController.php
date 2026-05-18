<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpresaActivaController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'redirect_to' => ['nullable', 'string', 'max:600'],
        ]);

        $empresa = Empresa::query()
            ->where('estado', 'activa')
            ->findOrFail($validated['empresa_id']);

        app(EmpresaContext::class)->setCurrentId($empresa->id);

        $redirectTo = (string) ($validated['redirect_to'] ?? '');

        if ($redirectTo !== '' && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo);
        }

        return back();
    }
}
