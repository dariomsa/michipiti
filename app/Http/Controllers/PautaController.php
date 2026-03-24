<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Carbon\Carbon;
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

        if (! $this->isAllowedHourForDate($validated['fecha'], $validated['hora'])) {
            return response()->json([
                'message' => 'La hora seleccionada está fuera de pauta para esa fecha.',
            ], 422);
        }

        $slotDateTime = Carbon::parse("{$validated['fecha']} {$validated['hora']}:00");
        if ($slotDateTime->lt(now())) {
            return response()->json([
                'message' => 'No puedes programar en una fecha u hora pasada.',
            ], 422);
        }

        $isOccupied = Producto::query()
            ->where('origen', '!=', 'propuesta')
            ->whereDate('fecha', $validated['fecha'])
            ->where('hora', $validated['hora'])
            ->whereKeyNot($producto->id)
            ->exists();

        if ($isOccupied) {
            return response()->json([
                'message' => 'Ese horario ya está ocupado.',
            ], 422);
        }

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

    protected function isAllowedHourForDate(string $date, string $hour): bool
    {
        $dayIndex = (int) Carbon::parse($date)->dayOfWeekIso - 1;

        return in_array($hour, $this->scheduleByDay()[$dayIndex] ?? [], true);
    }

    /**
     * @return array<int, list<string>>
     */
    protected function scheduleByDay(): array
    {
        $weekdaysHours = [
            '06:00', '07:00', '08:15', '09:30', '10:45', '11:30', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30',
            '13:45', '14:00', '14:15', '14:30', '14:45', '15:30', '16:00', '17:15', '18:30', '19:45', '20:15', '21:00',
            '22:15', '22:45',
        ];

        return [
            0 => $weekdaysHours,
            1 => $weekdaysHours,
            2 => $weekdaysHours,
            3 => $weekdaysHours,
            4 => $weekdaysHours,
            5 => [
                '09:00', '10:30', '11:30', '12:00', '13:30', '15:00',
                '15:30', '16:30', '18:00', '19:30', '20:30', '22:00',
            ],
            6 => [
                '09:30', '10:45', '12:00', '13:30', '15:00',
                '16:30', '18:00', '19:30', '21:00', '22:00',
            ],
        ];
    }
}
