<?php

namespace App\Http\Controllers\Disenador;

use App\Http\Controllers\Periodista\ProductoController as BaseProductoController;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductoController extends BaseProductoController
{
    /**
     * @return list<string>
     */
    protected function estadosEditables(): array
    {
        return ['ASIGNADO', 'FINALIZADO', 'APROBADO'];
    }

    public function edit(Producto $producto): View
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto(request()->user(), $producto), 403);
        abort_unless(in_array($producto->estado, $this->estadosEditables(), true), 403);

        $producto->load([
            'tipoProducto:id,nombre,slug',
            'user:id,name',
            'disenador:id,name',
            'laminas.archivos',
            'mensajes.autor:id,name',
            'movimientos.user:id,name',
        ]);

        return view('disenador.productos.edit', [
            'producto' => $producto,
            'laminasData' => $this->laminasFormData($producto),
            'movimientos' => $producto->movimientos,
            'mensajes' => $producto->mensajes()->with('autor:id,name')->orderBy('id')->get(),
            'routeBase' => 'disenador.productos',
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);
        abort_unless(in_array($producto->estado, $this->estadosEditables(), true), 403);

        $validated = $request->validate([
            'programado_metricool' => ['nullable', 'boolean'],
            'accion' => ['required', 'in:guardar,finalizar'],
            'canva_url' => ['nullable', 'string', 'max:600'],
        ], [
            'canva_url.max' => 'La URL de Canva es demasiado larga.',
        ]);

        if ($validated['accion'] === 'finalizar' && empty($validated['canva_url'])) {
            return back()
                ->withErrors(['canva_url' => 'La URL de Canva es obligatoria para finalizar.'])
                ->withInput();
        }

        if (! empty($validated['canva_url']) && ! str_starts_with((string) $validated['canva_url'], 'https://')) {
            return back()
                ->withErrors(['canva_url' => 'La URL de Canva debe comenzar con https://'])
                ->withInput();
        }

        if ($producto->programado_metricool) {
            $validated['programado_metricool'] = true;
        }

        if ($producto->canva_url) {
            $incomingCanva = trim((string) ($validated['canva_url'] ?? ''));

            if ($incomingCanva !== '' && $incomingCanva !== (string) $producto->canva_url) {
                return back()
                    ->withErrors(['canva_url' => 'La URL de Canva ya fue registrada y no se puede cambiar.'])
                    ->withInput();
            }

            $validated['canva_url'] = $producto->canva_url;
        }

        $estadoAnterior = $producto->estado;
        $estadoNuevo = match ($validated['accion']) {
            'guardar' => $producto->estado,
            'finalizar' => 'FINALIZADO',
            default => $producto->estado,
        };

        $producto->fill([
            'programado_metricool' => (bool) ($validated['programado_metricool'] ?? false),
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
                default => 'EDITADO',
            },
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $estadoNuevo,
            motivo: match ($validated['accion']) {
                'finalizar' => 'Producto finalizado por disenador.',
                default => 'Producto actualizado por disenador.',
            },
            meta: [
                'programado_metricool' => $producto->programado_metricool,
            ],
        );

        return redirect()
            ->route('disenador.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function autosave(Request $request, Producto $producto): JsonResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);
        abort_unless(in_array($producto->estado, $this->estadosEditables(), true), 403);

        $validated = $request->validate([
            'programado_metricool' => ['nullable', 'boolean'],
        ]);

        $producto->fill([
            'programado_metricool' => $producto->programado_metricool
                ? true
                : (bool) ($validated['programado_metricool'] ?? false),
        ]);
        $producto->save();

        return response()->json([
            'ok' => true,
            'updated_at' => optional($producto->updated_at)->toISOString(),
            'message' => 'Guardado automáticamente.',
        ]);
    }
}
