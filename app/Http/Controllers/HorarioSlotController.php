<?php

namespace App\Http\Controllers;

use App\Models\HorarioSlot;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HorarioSlotController extends Controller
{
    /**
     * @return list<string>
     */
    protected function lockedHours(): array
    {
        return [
            '06:00', '07:00', '08:15', '09:00', '09:30', '10:30', '10:45', '11:30', '12:00',
            '12:15', '13:30', '14:45', '15:00', '15:30', '16:00', '16:30', '17:15', '18:00',
            '18:30', '19:30', '19:45', '20:15', '20:30', '21:00', '22:00',
        ];
    }

    public function index(): View
    {
        $slots = HorarioSlot::query()
            ->orderBy('hora')
            ->orderBy('dia_semana')
            ->get();

        $hours = $slots->pluck('hora')->unique()->values();
        $matrix = $slots->groupBy('hora')->map(function ($hourSlots) {
            return $hourSlots->keyBy('dia_semana');
        });

        return view('horario_slots.index', [
            'dayNames' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            'hours' => $hours,
            'matrix' => $matrix,
            'lockedHours' => $this->lockedHours(),
        ]);
    }

    public function update(Request $request, HorarioSlot $horarioSlot): JsonResponse
    {
        $hourHm = substr((string) $horarioSlot->hora, 0, 5);

        if (in_array($hourHm, $this->lockedHours(), true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Este horario no es editable.',
            ], 422);
        }

        $validated = $request->validate([
            'estado_visual' => ['required', 'in:oculto,fuera'],
        ]);

        if (
            $validated['estado_visual'] === 'oculto'
            && HorarioSlot::query()
                ->whereTime('hora', $horarioSlot->hora)
                ->where('visible', true)
                ->exists()
            && $this->hasCurrentWeekPautaForHour($horarioSlot->hora)
        ) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes ocultar el horario '.$hourHm.' porque ya existe una pauta en esa hora durante la semana en curso.',
            ], 422);
        }

        HorarioSlot::query()
            ->whereTime('hora', $horarioSlot->hora)
            ->update([
                'visible' => $validated['estado_visual'] !== 'oculto',
                'fuera_de_pauta' => $validated['estado_visual'] === 'fuera',
                'updated_at' => now(),
            ]);

        $updatedSlots = HorarioSlot::query()
            ->whereTime('hora', $horarioSlot->hora)
            ->orderBy('dia_semana')
            ->get(['id', 'dia_semana', 'hora', 'visible', 'fuera_de_pauta']);

        return response()->json([
            'ok' => true,
            'message' => 'Horario actualizado correctamente.',
            'hour' => $hourHm,
            'estado_visual' => $validated['estado_visual'],
            'slots' => $updatedSlots,
        ]);
    }

    protected function hasCurrentWeekPautaForHour(string $hour): bool
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $end = Carbon::now()->endOfWeek(Carbon::SUNDAY)->toDateString();

        return Producto::query()
            ->whereBetween('fecha', [$start, $end])
            ->whereTime('hora', $hour)
            ->where('origen', 'pauta')
            ->exists();
    }
}
