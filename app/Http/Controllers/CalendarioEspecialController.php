<?php

namespace App\Http\Controllers;

use App\Models\CalendarioEspecial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarioEspecialController extends Controller
{
    public function index(): View
    {
        return view('calendario_especial.index', [
            'items' => CalendarioEspecial::query()
                ->orderBy('fecha')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date', 'unique:calendario_especial,fecha'],
            'motivo' => ['required', 'string', 'max:150'],
            'tipo_feriado' => ['required', 'integer', Rule::in([1,2])],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no es válida.',
            'fecha.unique' => 'Ya existe un feriado configurado para esa fecha.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.max' => 'El motivo no puede superar los 150 caracteres.',
            'tipo_feriado.required' => 'Debes seleccionar un tipo de feriado.',
            'tipo_feriado.in' => 'El tipo de feriado no es válido.',
        ]);

        CalendarioEspecial::query()->create($validated);

        return redirect()
            ->route('calendario-especial.index')
            ->with('success', 'Feriado creado correctamente.');
    }

    public function update(Request $request, CalendarioEspecial $calendarioEspecial): RedirectResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date', Rule::unique('calendario_especial', 'fecha')->ignore($calendarioEspecial->id)],
            'motivo' => ['required', 'string', 'max:150'],
            'tipo_feriado' => ['required', 'integer', Rule::in([1])],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no es válida.',
            'fecha.unique' => 'Ya existe un feriado configurado para esa fecha.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.max' => 'El motivo no puede superar los 150 caracteres.',
            'tipo_feriado.required' => 'Debes seleccionar un tipo de feriado.',
            'tipo_feriado.in' => 'El tipo de feriado no es válido.',
        ]);

        $calendarioEspecial->update($validated);

        return redirect()
            ->route('calendario-especial.index')
            ->with('success', 'Feriado actualizado correctamente.');
    }

    public function destroy(CalendarioEspecial $calendarioEspecial): RedirectResponse
    {
        $calendarioEspecial->delete();

        return redirect()
            ->route('calendario-especial.index')
            ->with('success', 'Feriado eliminado correctamente.');
    }
}
