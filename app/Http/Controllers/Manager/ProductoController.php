<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Periodista\ProductoController as BaseProductoController;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends BaseProductoController
{
    public function edit(Producto $producto): View
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto(request()->user(), $producto), 403);

        $producto->load([
            'tipoProducto:id,nombre,slug',
            'user:id,name',
            'disenador:id,name',
            'laminas.archivos',
            'mensajes.autor:id,name',
            'movimientos.user:id,name',
        ]);

        return view('manager.productos.edit', [
            'producto' => $producto,
            'laminasData' => $this->laminasFormData($producto),
            'movimientos' => $producto->movimientos,
            'mensajes' => $producto->mensajes()->with('autor:id,name')->orderBy('id')->get(),
            'disenadores' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', 'disenador'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'routeBase' => 'manager.productos',
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);

        $validated = $request->validate([
            'disenador_id' => ['required', 'integer', 'exists:users,id'],
            'dificultad' => ['required', Rule::in(['BAJA', 'ALTA'])],
            'pauta_comercial' => ['nullable', 'boolean'],
            'motivo' => ['nullable', 'string', 'max:600'],
            'accion' => ['required', Rule::in(['guardar', 'finalizar', 'devolver_editor'])],
            'canva_url' => ['nullable', 'url', 'max:600'],
        ], [
            'disenador_id.required' => 'Debes asignar un diseñador.',
            'disenador_id.exists' => 'El diseñador seleccionado ya no existe.',
            'dificultad.required' => 'Debes indicar la dificultad.',
            'dificultad.in' => 'La dificultad seleccionada no es válida.',
            'canva_url.url' => 'La URL de Canva no es válida.',
        ]);

        if ($validated['accion'] === 'finalizar' && empty($validated['canva_url'])) {
            return back()
                ->withErrors(['canva_url' => 'La URL de Canva es obligatoria para finalizar.'])
                ->withInput();
        }

        if ($validated['accion'] === 'devolver_editor' && empty(trim((string) ($validated['motivo'] ?? '')))) {
            return back()
                ->withErrors(['motivo' => 'Debes indicar el motivo para devolver al editor.'])
                ->withInput();
        }

        $estadoAnterior = $producto->estado;
        $estadoNuevo = match ($validated['accion']) {
            'guardar' => $producto->estado === 'EN_DISENO' ? 'ASIGNADO' : $producto->estado,
            'finalizar' => 'FINALIZADO',
            'devolver_editor' => 'DEVUELTO',
            default => $producto->estado,
        };

        $producto->fill([
            'disenador_id' => (int) $validated['disenador_id'],
            'manager_id' => $request->user()?->id,
            'dificultad' => $validated['dificultad'],
            'pauta_comercial' => (bool) ($validated['pauta_comercial'] ?? false),
            'canva_url' => $validated['accion'] === 'finalizar'
                ? $validated['canva_url']
                : $producto->canva_url,
            'estado' => $estadoNuevo,
        ]);
        $producto->save();

        $this->registrarMovimiento(
            producto: $producto,
            accion: match ($validated['accion']) {
                'finalizar' => 'FINALIZADO',
                'devolver_editor' => 'DEVUELTO_EDITOR',
                default => 'ASIGNADO_DISENADOR',
            },
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $estadoNuevo,
            motivo: match ($validated['accion']) {
                'finalizar' => 'Producto finalizado por manager.',
                'devolver_editor' => trim((string) $validated['motivo']),
                default => 'Diseñador asignado por manager.',
            },
            meta: [
                'disenador_id' => $producto->disenador_id,
                'manager_id' => $producto->manager_id,
                'dificultad' => $producto->dificultad,
                'pauta_comercial' => $producto->pauta_comercial,
            ],
        );

        return redirect()
            ->route('manager.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }
}
