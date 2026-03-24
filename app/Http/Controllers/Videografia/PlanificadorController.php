<?php

namespace App\Http\Controllers\Videografia;

use App\Http\Controllers\Controller;
use App\Models\Audiovisual;
use App\Models\Seccion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlanificadorController extends Controller
{
    public function index()
    {
        return view('videografia.audiovisuales.planificador', [
            'secciones' => Seccion::query()
                ->where('activa', true)
                ->orderBy('id')
                ->pluck('nombre')
                ->all(),
        ]);
    }

    public function week(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'week_start' => ['required', 'date'],
            ],
            [
                'week_start.required' => 'La semana a consultar es obligatoria.',
                'week_start.date' => 'La fecha de inicio de semana no tiene un formato válido.',
            ],
            [
                'week_start' => 'inicio de semana',
            ],
        );

        $start = Carbon::parse($data['week_start'])->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();

        $items = Audiovisual::query()
            ->with(['user:id,name', 'editor:id,name'])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (Audiovisual $audiovisual): array => $this->serializeProducto($audiovisual, $request->user()))
            ->values();

        return response()->json($items);
    }

    public function responsables(): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name')
            ->whereHas('roles', fn ($query) => $query->where('name', 'videografia'))
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'id' => ['nullable', 'integer', 'exists:audiovisuales,id'],
                'fecha' => ['required', 'date'],
                'hora' => ['required', 'date_format:H:i'],
                'seccion' => ['required', 'string', 'max:100'],
                'titulo' => ['required', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string'],
                'estado' => ['nullable', 'string', 'max:30'],
                'origen' => ['required', Rule::in(['propuesta', 'pauta', 'comercial', 'pendiente'])],
                'asignado_a' => ['nullable', 'integer', 'exists:users,id'],
                'link' => ['nullable', 'string', 'max:600'],
            ],
            [
                'id.exists' => 'El audiovisual que intentas editar ya no existe.',
                'fecha.required' => 'Debes seleccionar una fecha.',
                'fecha.date' => 'La fecha seleccionada no es válida.',
                'hora.required' => 'Debes seleccionar una hora.',
                'hora.date_format' => 'La hora seleccionada no es válida.',
                'seccion.required' => 'Debes seleccionar una sección.',
                'seccion.max' => 'La sección no puede superar los 100 caracteres.',
                'titulo.required' => 'Debes ingresar un título.',
                'titulo.max' => 'El título no puede superar los 200 caracteres.',
                'origen.required' => 'Debes seleccionar un origen.',
                'origen.in' => 'El origen seleccionado no es válido.',
                'asignado_a.exists' => 'El responsable seleccionado ya no existe.',
                'link.max' => 'La referencia no puede superar los 600 caracteres.',
            ],
            [
                'asignado_a' => 'responsable',
                'link' => 'referencia',
            ],
        );

        $audiovisual = ($data['id'] ?? null)
            ? Audiovisual::query()->findOrFail($data['id'])
            : new Audiovisual();
        $isNew = ! $audiovisual->exists;
        $estadoAnterior = $audiovisual->estado;
        $origenAnterior = $audiovisual->origen;
        $fechaAnterior = optional($audiovisual->fecha)->format('Y-m-d');
        $horaAnterior = $audiovisual->hora ? Carbon::parse($audiovisual->hora)->format('H:i') : null;

        $isAllowedSchedule = $this->isAllowedSchedule($data['fecha'], $data['hora']);
        $origen = $isAllowedSchedule ? $data['origen'] : 'pendiente';

        if ($this->isPastDateTime($data['fecha'], $data['hora'])) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes crear o editar audiovisuales en fechas u horas anteriores al momento actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slotOcupado = Audiovisual::query()
            ->whereDate('fecha', $data['fecha'])
            ->when($audiovisual->exists, fn ($query) => $query->whereKeyNot($audiovisual->id))
            ->get()
            ->contains(fn (Audiovisual $item): bool => Carbon::parse($item->hora)->format('H:i') === $data['hora']);

        if ($slotOcupado) {
            return response()->json([
                'ok' => false,
                'message' => 'Ese horario ya está ocupado por otro audiovisual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($audiovisual->exists && $audiovisual->origen === 'pauta') {
            $seIntentoEditarCampoBloqueado =
                ($audiovisual->seccion !== $data['seccion']) ||
                ($audiovisual->titulo !== $data['titulo']) ||
                (($audiovisual->copy ?? '') !== ($data['descripcion'] ?? '')) ||
                ($audiovisual->origen !== $origen);

            if ($seIntentoEditarCampoBloqueado) {
                throw new HttpException(422, 'Los audiovisuales que ya están en pauta solo permiten cambiar responsable y referencia.');
            }
        }

        $audiovisual->fill([
            'user_id' => $data['asignado_a'] ?? $audiovisual->user_id ?? $request->user()->id,
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'orden_dia' => $this->orderDiaFromTime($data['hora']),
            'seccion' => $data['seccion'],
            'copy' => $data['descripcion'] ?? null,
            'referencia' => $data['link'] ?? null,
            'estado' => $data['estado'] ?: ($audiovisual->estado ?: 'BORRADOR'),
            'dificultad' => $audiovisual->dificultad ?: 'BASICO',
            'origen' => $origen,
        ]);

        if ($request->user()->hasAnyRole(['editor', 'director'])) {
            $audiovisual->editor_id = $request->user()->id;
        }

        $audiovisual->save();
        $audiovisual->load(['user:id,name', 'editor:id,name']);

        $this->registrarMovimiento(
            audiovisual: $audiovisual,
            user: $request->user(),
            accion: $isNew ? 'PLANIFICADO' : 'EDITADO_PLANIFICADOR',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $audiovisual->estado,
            motivo: $isNew ? 'Audiovisual creado desde el planificador.' : 'Audiovisual actualizado desde el planificador.',
        );

        return response()->json([
            'ok' => true,
            'item' => $this->serializeProducto($audiovisual, $request->user()),
        ]);
    }

    public function destroy(Request $request, Audiovisual $audiovisual): JsonResponse
    {
        if (! $this->canDeleteProducto($request->user(), $audiovisual)) {
            return response()->json([
                'ok' => false,
                'message' => $this->deleteDeniedMessage($request->user(), $audiovisual),
            ], Response::HTTP_FORBIDDEN);
        }

        $this->registrarMovimiento(
            audiovisual: $audiovisual,
            user: $request->user(),
            accion: 'ELIMINADO_PLANIFICADOR',
            estadoAnterior: $audiovisual->estado,
            estadoNuevo: $audiovisual->estado,
            motivo: 'Audiovisual eliminado desde el planificador.',
        );

        $audiovisual->delete();

        return response()->json([
            'ok' => true,
        ]);
    }

    public function move(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'source_key' => ['required', 'string'],
                'target_key' => ['required', 'string'],
            ],
            [
                'source_key.required' => 'No se recibió el slot de origen.',
                'target_key.required' => 'No se recibió el slot de destino.',
            ],
        );

        [$sourceDate, $sourceTime] = $this->parseSlotKey($data['source_key']);
        [$targetDate, $targetTime] = $this->parseSlotKey($data['target_key']);

        if ($this->isPastDateTime($sourceDate, $sourceTime) || $this->isPastDateTime($targetDate, $targetTime)) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes mover audiovisuales desde o hacia horarios anteriores al momento actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $source = Audiovisual::query()
            ->whereDate('fecha', $sourceDate)
            ->whereTime('hora', $sourceTime)
            ->firstOrFail();
        $sourceOriginalDate = optional($source->fecha)->format('Y-m-d');
        $sourceOriginalTime = $source->hora ? Carbon::parse($source->hora)->format('H:i') : null;
        $sourceEstadoAnterior = $source->estado;

        $target = Audiovisual::query()
            ->whereDate('fecha', $targetDate)
            ->whereTime('hora', $targetTime)
            ->first();

        if ($target) {
            $targetOriginalDate = optional($target->fecha)->format('Y-m-d');
            $targetOriginalTime = $target->hora ? Carbon::parse($target->hora)->format('H:i') : null;
            $targetEstadoAnterior = $target->estado;

            $source->update([
                'fecha' => $targetDate,
                'hora' => $targetTime,
                'orden_dia' => $this->orderDiaFromTime($targetTime),
            ]);

            $target->update([
                'fecha' => $sourceOriginalDate,
                'hora' => $sourceOriginalTime,
                'orden_dia' => $this->orderDiaFromTime($sourceOriginalTime),
            ]);

            $this->registrarMovimiento(
                audiovisual: $source->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $sourceEstadoAnterior,
                estadoNuevo: $sourceEstadoAnterior,
                motivo: 'Audiovisual movido desde el planificador.',
            );

            $this->registrarMovimiento(
                audiovisual: $target->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $targetEstadoAnterior,
                estadoNuevo: $targetEstadoAnterior,
                motivo: 'Audiovisual reubicado por intercambio desde el planificador.',
            );

        } else {
            $source->update([
                'fecha' => $targetDate,
                'hora' => $targetTime,
                'orden_dia' => $this->orderDiaFromTime($targetTime),
            ]);

            $this->registrarMovimiento(
                audiovisual: $source->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $sourceEstadoAnterior,
                estadoNuevo: $sourceEstadoAnterior,
                motivo: 'Audiovisual movido desde el planificador.',
            );
        }

        return response()->json(['ok' => true]);
    }

    public function approve(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['editor', 'director'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo los roles editor y director pueden aprobar audiovisuales.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate(
            [
                'id' => ['required', 'integer', 'exists:audiovisuales,id'],
            ],
            [
                'id.required' => 'No se recibió el audiovisual a aprobar.',
                'id.exists' => 'El audiovisual que intentas aprobar ya no existe.',
            ],
        );

        $audiovisual = Audiovisual::query()->findOrFail($data['id']);
        $estadoAnterior = $audiovisual->estado;
        $origenAnterior = $audiovisual->origen;

        if ($audiovisual->estado !== 'PENDIENTE' && $audiovisual->origen !== 'pendiente') {
            return response()->json([
                'ok' => false,
                'message' => 'Solo se pueden aprobar audiovisuales pendientes.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($audiovisual->origen === 'pendiente') {
            $audiovisual->origen = $request->user()->hasRole('director') ? 'pauta' : 'propuesta';
        }
        $audiovisual->editor_id = $request->user()->id;
        $audiovisual->save();

        $this->registrarMovimiento(
            audiovisual: $audiovisual,
            user: $request->user(),
            accion: 'APROBADO_PLANIFICADOR',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $audiovisual->estado,
            motivo: 'Audiovisual aprobado desde el planificador.',
        );

        return response()->json(['ok' => true]);
    }

    public function toPauta(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'propuesta_id' => ['required', 'integer', 'exists:audiovisuales,id'],
                'asignado_a' => ['required', 'integer', 'exists:users,id'],
            ],
            [
                'propuesta_id.required' => 'No se recibió el audiovisual a mover a pauta.',
                'propuesta_id.exists' => 'El audiovisual que intentas mover a pauta ya no existe.',
                'asignado_a.required' => 'Debes seleccionar un responsable antes de enviar a pauta.',
                'asignado_a.exists' => 'El responsable seleccionado ya no existe.',
            ],
        );

        $audiovisual = Audiovisual::query()->findOrFail($data['propuesta_id']);
        $estadoAnterior = $audiovisual->estado;
        $origenAnterior = $audiovisual->origen;

        if ($audiovisual->origen === 'pauta') {
            return response()->json([
                'ok' => false,
                'message' => 'Este audiovisual ya se encuentra en pauta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $audiovisual->user_id = $data['asignado_a'];
        $audiovisual->origen = 'pauta';
        $audiovisual->assigned_at = now();
        $audiovisual->save();

        $this->registrarMovimiento(
            audiovisual: $audiovisual,
            user: $request->user(),
            accion: 'ENVIADO_PAUTA',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $audiovisual->estado,
            motivo: 'Audiovisual enviado a pauta desde el planificador.',
        );

        return response()->json([
            'ok' => true,
            'audiovisual_id' => $audiovisual->id,
        ]);
    }

    private function serializeProducto(Audiovisual $audiovisual, User $user): array
    {
        return [
            'id' => $audiovisual->id,
            'tipo_audiovisual_id' => $audiovisual->tipo_audiovisual_id,
            'fecha' => optional($audiovisual->fecha)->format('Y-m-d'),
            'hora' => $audiovisual->hora ? Carbon::parse($audiovisual->hora)->format('H:i') : null,
            'titulo' => $audiovisual->titulo,
            'descripcion' => $audiovisual->copy,
            'seccion' => $audiovisual->seccion,
            'estado' => $audiovisual->estado,
            'origen' => $audiovisual->origen,
            'asignado_a' => $audiovisual->user_id,
            'responsable_nombre' => $audiovisual->user?->name,
            'link' => $audiovisual->referencia,
            'canva_url' => $audiovisual->canva_url,
            'prioridad' => $audiovisual->prioridad,
            'dificultad' => $audiovisual->dificultad,
            'assigned_at' => optional($audiovisual->assigned_at)->toDateTimeString(),
            'can_delete' => $this->canDeleteProducto($user, $audiovisual),
        ];
    }

    private function canDeleteProducto(User $user, Audiovisual $audiovisual): bool
    {
        if ($user->hasRole('director')) {
            return true;
        }

        if ($user->hasRole('comercial')) {
            return $audiovisual->origen === 'comercial';
        }

        return $audiovisual->estado === 'BORRADOR' && $audiovisual->origen === 'propuesta';
    }

    private function deleteDeniedMessage(User $user, Audiovisual $audiovisual): string
    {
        if ($user->hasRole('comercial')) {
            return 'Los usuarios comerciales solo pueden eliminar audiovisuales con origen comercial.';
        }

        return 'Solo puedes eliminar audiovisuales que estén en estado BORRADOR y con origen propuesta.';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseSlotKey(string $key): array
    {
        return explode('|', $key, 2);
    }

    private function orderDiaFromTime(string $time): int
    {
        [$hour, $minute] = explode(':', $time);

        return ((int) $hour * 100) + (int) $minute;
    }

    private function isAllowedSchedule(string $date, string $time): bool
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
        $weekdaysHours = [
            '06:00', '07:00', '08:15', '09:30', '10:45', '11:30', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30',
            '13:45', '14:00', '14:15', '14:30', '14:45', '15:30', '16:00', '17:15', '18:30', '19:45', '20:15', '21:00',
            '22:15', '22:45',
        ];
        $saturdayHours = [
            '09:00', '10:30', '11:30', '12:00', '13:30', '15:00',
            '15:30', '16:30', '18:00', '19:30', '20:30', '22:00',
        ];
        $sundayHours = [
            '09:30', '10:45', '12:00', '13:30', '15:00',
            '16:30', '18:00', '19:30', '21:00', '22:00',
        ];

        $scheduleByDay = [
            1 => $weekdaysHours,
            2 => $weekdaysHours,
            3 => $weekdaysHours,
            4 => $weekdaysHours,
            5 => $weekdaysHours,
            6 => $saturdayHours,
            7 => $sundayHours,
        ];

        return in_array($time, $scheduleByDay[$dayOfWeek] ?? [], true);
    }

    private function isPastDateTime(string $date, string $time): bool
    {
        return Carbon::parse("{$date} {$time}")->lt(now());
    }

    private function registrarMovimiento(
        Audiovisual $audiovisual,
        User $user,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $motivo,
    ): void {
        $audiovisual->movimientos()->create([
            'user_id' => $user->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
        ]);
    }

}
