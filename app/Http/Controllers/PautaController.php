<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PautaController extends Controller
{
    public function index(): View
    {
        return view('pauta.index');
    }

    public function items(Request $request): JsonResponse
    {
        $fecha = $request->string('fecha')->toString();

        $items = Producto::query()
            ->where('origen', '!=', 'propuesta')
            ->with(['user:id,name', 'disenador:id,name'])
            ->when($fecha !== '', fn ($query) => $query->whereDate('fecha', $fecha))
            ->orderByRaw('fecha is null, fecha asc')
            ->orderByRaw('hora is null, hora asc')
            ->orderBy('id')
            ->get()
            ->map(function (Producto $producto): array {
                return [
                    'id' => $producto->id,
                    'fecha' => optional($producto->fecha)?->format('Y-m-d'),
                    'hora' => $producto->hora ? substr((string) $producto->hora, 0, 5) : null,
                    'titulo' => $producto->titulo,
                    'contenido' => $producto->titulo,
                    'autor' => $producto->user?->name,
                    'disenador' => $producto->disenador?->name,
                    'estado' => $producto->estado,
                    'copy' => $producto->copy,
                    'hashtags' => $producto->hashtags,
                    'creditos' => $producto->creditos,
                    'canva_url' => $producto->canva_url,
                    'origen' => $producto->origen,
                ];
            });

        return response()->json([
            'items' => $items,
            'fecha' => $fecha,
        ]);
    }

    public function programar(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        $producto = Producto::query()
            ->whereKey($id)
            ->where('origen', '!=', 'propuesta')
            ->firstOrFail();

        $producto->update([
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
        ]);

        return response()->json([
            'ok' => true,
            'id' => $producto->id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'message' => 'Programación guardada.',
        ]);
    }
}
