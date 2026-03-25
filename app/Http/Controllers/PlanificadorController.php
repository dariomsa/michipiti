<?php

namespace App\Http\Controllers;

use App\Models\CarruselMovimiento;
use App\Models\Producto;
use App\Models\Seccion;
use App\Models\TipoProducto;
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
        return view('planificador', [
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

        $items = Producto::query()
            ->with(['user:id,name', 'editor:id,name'])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (Producto $producto): array => $this->serializeProducto($producto, $request->user()))
            ->values();

        return response()->json($items);
    }

    public function periodistas(): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name')
            ->whereHas('roles', fn ($query) => $query->where('name', 'periodista'))
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'id' => ['nullable', 'integer', 'exists:productos,id'],
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
                'id.exists' => 'El producto que intentas editar ya no existe.',
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

        $producto = $data['id']
            ? Producto::query()->findOrFail($data['id'])
            : new Producto();
        $isNew = ! $producto->exists;
        $estadoAnterior = $producto->estado;
        $origenAnterior = $producto->origen;
        $fechaAnterior = optional($producto->fecha)->format('Y-m-d');
        $horaAnterior = $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null;

        $isAllowedSchedule = $this->isAllowedSchedule($data['fecha'], $data['hora']);
        $origen = $isAllowedSchedule ? $data['origen'] : 'pendiente';

        if ($this->isPastDateTime($data['fecha'], $data['hora'])) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes crear o editar productos en fechas u horas anteriores al momento actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($producto->exists && $producto->origen === 'pauta') {
            $seIntentoEditarCampoBloqueado =
                ($producto->seccion !== $data['seccion']) ||
                ($producto->titulo !== $data['titulo']) ||
                (($producto->copy ?? '') !== ($data['descripcion'] ?? '')) ||
                ($producto->origen !== $origen);

            if ($seIntentoEditarCampoBloqueado) {
                throw new HttpException(422, 'Los productos que ya están en pauta solo permiten cambiar responsable y referencia.');
            }
        }

        $producto->fill([
            'tipo_producto_id' => $this->resolveTipoProductoId(),
            'user_id' => $data['asignado_a'] ?? $producto->user_id ?? $request->user()->id,
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'orden_dia' => $this->orderDiaFromTime($data['hora']),
            'seccion' => $data['seccion'],
            'copy' => $data['descripcion'] ?? null,
            'referencia' => $data['link'] ?? null,
            'estado' => $data['estado'] ?: ($producto->estado ?: 'BORRADOR'),
            'dificultad' => $producto->dificultad ?: 'BASICO',
            'origen' => $origen,
        ]);

        if ($request->user()->hasAnyRole(['editor', 'director'])) {
            $producto->editor_id = $request->user()->id;
        }

        $producto->save();
        $producto->load(['user:id,name', 'editor:id,name']);

        $this->registrarMovimiento(
            producto: $producto,
            user: $request->user(),
            accion: $isNew ? 'PLANIFICADO' : 'EDITADO_PLANIFICADOR',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $producto->estado,
            motivo: $isNew ? 'Producto creado desde el planificador.' : 'Producto actualizado desde el planificador.',
            meta: [
                'origen_anterior' => $origenAnterior,
                'origen_nuevo' => $producto->origen,
                'fecha_anterior' => $fechaAnterior,
                'hora_anterior' => $horaAnterior,
                'fecha_nueva' => optional($producto->fecha)->format('Y-m-d'),
                'hora_nueva' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            ],
        );

        return response()->json([
            'ok' => true,
            'item' => $this->serializeProducto($producto, $request->user()),
        ]);
    }

    public function destroy(Request $request, Producto $producto): JsonResponse
    {
        if (! $this->canDeleteProducto($request->user(), $producto)) {
            return response()->json([
                'ok' => false,
                'message' => $this->deleteDeniedMessage($request->user(), $producto),
            ], Response::HTTP_FORBIDDEN);
        }

        $this->registrarMovimiento(
            producto: $producto,
            user: $request->user(),
            accion: 'ELIMINADO_PLANIFICADOR',
            estadoAnterior: $producto->estado,
            estadoNuevo: $producto->estado,
            motivo: 'Producto eliminado desde el planificador.',
            meta: [
                'origen' => $producto->origen,
                'fecha' => optional($producto->fecha)->format('Y-m-d'),
                'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            ],
        );

        $producto->delete();

        return response()->json([
            'ok' => true,
        ]);
    }

    public function move(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('director')) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo el director puede mover productos desde el planificador.',
            ], Response::HTTP_FORBIDDEN);
        }

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
                'message' => 'No puedes mover productos desde o hacia horarios anteriores al momento actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $source = Producto::query()
            ->whereDate('fecha', $sourceDate)
            ->whereTime('hora', $sourceTime)
            ->firstOrFail();
        $sourceOriginalDate = optional($source->fecha)->format('Y-m-d');
        $sourceOriginalTime = $source->hora ? Carbon::parse($source->hora)->format('H:i') : null;
        $sourceEstadoAnterior = $source->estado;

        $target = Producto::query()
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
                producto: $source->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $sourceEstadoAnterior,
                estadoNuevo: $sourceEstadoAnterior,
                motivo: 'Producto movido desde el planificador.',
                meta: [
                    'fecha_anterior' => $sourceOriginalDate,
                    'hora_anterior' => $sourceOriginalTime,
                    'fecha_nueva' => $targetDate,
                    'hora_nueva' => $targetTime,
                    'intercambio' => true,
                ],
            );

            $this->registrarMovimiento(
                producto: $target->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $targetEstadoAnterior,
                estadoNuevo: $targetEstadoAnterior,
                motivo: 'Producto reubicado por intercambio desde el planificador.',
                meta: [
                    'fecha_anterior' => $targetOriginalDate,
                    'hora_anterior' => $targetOriginalTime,
                    'fecha_nueva' => $sourceOriginalDate,
                    'hora_nueva' => $sourceOriginalTime,
                    'intercambio' => true,
                ],
            );
        } else {
            $source->update([
                'fecha' => $targetDate,
                'hora' => $targetTime,
                'orden_dia' => $this->orderDiaFromTime($targetTime),
            ]);

            $this->registrarMovimiento(
                producto: $source->fresh(),
                user: $request->user(),
                accion: 'MOVIDO_PLANIFICADOR',
                estadoAnterior: $sourceEstadoAnterior,
                estadoNuevo: $sourceEstadoAnterior,
                motivo: 'Producto movido desde el planificador.',
                meta: [
                    'fecha_anterior' => $sourceOriginalDate,
                    'hora_anterior' => $sourceOriginalTime,
                    'fecha_nueva' => $targetDate,
                    'hora_nueva' => $targetTime,
                    'intercambio' => false,
                ],
            );
        }

        return response()->json(['ok' => true]);
    }

    public function approve(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['editor', 'director'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo los roles editor y director pueden aprobar productos.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate(
            [
                'id' => ['required', 'integer', 'exists:productos,id'],
            ],
            [
                'id.required' => 'No se recibió el producto a aprobar.',
                'id.exists' => 'El producto que intentas aprobar ya no existe.',
            ],
        );

        $producto = Producto::query()->findOrFail($data['id']);
        $estadoAnterior = $producto->estado;
        $origenAnterior = $producto->origen;

        if ($producto->estado !== 'PENDIENTE' && $producto->origen !== 'pendiente') {
            return response()->json([
                'ok' => false,
                'message' => 'Solo se pueden aprobar productos pendientes.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($producto->origen === 'pendiente') {
            $producto->origen = $request->user()->hasRole('director') ? 'pauta' : 'propuesta';
        }
        $producto->editor_id = $request->user()->id;
        $producto->save();

        $this->registrarMovimiento(
            producto: $producto,
            user: $request->user(),
            accion: 'APROBADO_PLANIFICADOR',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $producto->estado,
            motivo: 'Producto aprobado desde el planificador.',
            meta: [
                'origen_anterior' => $origenAnterior,
                'origen_nuevo' => $producto->origen,
            ],
        );

        return response()->json(['ok' => true]);
    }

    public function toPauta(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'propuesta_id' => ['required', 'integer', 'exists:productos,id'],
                'asignado_a' => ['required', 'integer', 'exists:users,id'],
            ],
            [
                'propuesta_id.required' => 'No se recibió el producto a mover a pauta.',
                'propuesta_id.exists' => 'El producto que intentas mover a pauta ya no existe.',
                'asignado_a.required' => 'Debes seleccionar un responsable antes de enviar a pauta.',
                'asignado_a.exists' => 'El responsable seleccionado ya no existe.',
            ],
        );

        $producto = Producto::query()->findOrFail($data['propuesta_id']);
        $estadoAnterior = $producto->estado;
        $origenAnterior = $producto->origen;

        if ($producto->origen === 'pauta') {
            return response()->json([
                'ok' => false,
                'message' => 'Este producto ya se encuentra en pauta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $producto->user_id = $data['asignado_a'];
        $producto->origen = 'pauta';
        $producto->assigned_at = now();
        $producto->save();

        $this->registrarMovimiento(
            producto: $producto,
            user: $request->user(),
            accion: 'ENVIADO_PAUTA',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $producto->estado,
            motivo: 'Producto enviado a pauta desde el planificador.',
            meta: [
                'origen_anterior' => $origenAnterior,
                'origen_nuevo' => $producto->origen,
                'asignado_a' => $producto->user_id,
            ],
        );

        return response()->json([
            'ok' => true,
            'carrusel_id' => $producto->id,
        ]);
    }

    private function serializeProducto(Producto $producto, User $user): array
    {
        return [
            'id' => $producto->id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'fecha' => optional($producto->fecha)->format('Y-m-d'),
            'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            'titulo' => $producto->titulo,
            'descripcion' => $producto->copy,
            'seccion' => $producto->seccion,
            'estado' => $producto->estado,
            'origen' => $producto->origen,
            'asignado_a' => $producto->user_id,
            'responsable_nombre' => $producto->user?->name,
            'link' => $producto->referencia,
            'canva_url' => $producto->canva_url,
            'prioridad' => $producto->prioridad,
            'dificultad' => $producto->dificultad,
            'assigned_at' => optional($producto->assigned_at)->toDateTimeString(),
            'can_delete' => $this->canDeleteProducto($user, $producto),
        ];
    }

    private function canDeleteProducto(User $user, Producto $producto): bool
    {
        if ($user->hasRole('director')) {
            return true;
        }

        if ($user->hasRole('comercial')) {
            return $producto->origen === 'comercial';
        }

        return $producto->estado === 'BORRADOR' && $producto->origen === 'propuesta';
    }

    private function deleteDeniedMessage(User $user, Producto $producto): string
    {
        if ($user->hasRole('comercial')) {
            return 'Los usuarios comerciales solo pueden eliminar productos con origen comercial.';
        }

        return 'Solo puedes eliminar productos que estén en estado BORRADOR y con origen propuesta.';
    }

    private function resolveTipoProductoId(): int
    {
        $tipoProducto = TipoProducto::query()
            ->where('slug', TipoProducto::SLUG_CARRUSEL)
            ->first();

        if (! $tipoProducto) {
            throw new HttpException(422, 'No existe el tipo de producto tipo_carrusel configurado para el planificador.');
        }

        return $tipoProducto->id;
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

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private function registrarMovimiento(
        Producto $producto,
        User $user,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $motivo,
        ?array $meta = null,
    ): void {
        $producto->movimientos()->create([
            'user_id' => $user->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'meta' => $meta,
        ]);
    }
}
