<?php

namespace App\Http\Controllers\Mundial;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\MundialEquipo;
use App\Models\MundialHorarioSlot;
use App\Models\MundialPlataforma;
use App\Models\MundialProducto;
use App\Models\MundialPrioridad;
use App\Models\MundialTipo;
use App\Models\Producto;
use App\Models\RedSocial;
use App\Models\TipoProducto;
use App\Models\User;
use App\Support\EmpresaContext;
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
        $baseAllowedSchedule = $this->scheduleByDayZeroBased();
        $baseVisibleSchedule = $this->visibleHoursZeroBased();
        $empresaActivaId = app(EmpresaContext::class)->currentId();

        return view('mundial.planificador', [
            'tiposProducto' => TipoProducto::query()
                ->whereIn('slug', [TipoProducto::SLUG_CARRUSEL, TipoProducto::SLUG_REEL])
                ->orderByRaw("FIELD(slug, ?, ?)", [TipoProducto::SLUG_CARRUSEL, TipoProducto::SLUG_REEL])
                ->get(['id', 'nombre', 'slug']),
            'mundialPrioridades' => MundialPrioridad::query()
                ->where('activo', true)
                ->orderBy('orden')
                ->get(['id', 'nombre']),
            'mundialPlataformas' => MundialPlataforma::query()
                ->where('activo', true)
                ->orderBy('orden')
                ->get(['id', 'nombre']),
            'mundialEquipos' => MundialEquipo::query()
                ->where('activo', true)
                ->orderBy('orden')
                ->get(['id', 'nombre']),
            'mundialTipos' => MundialTipo::query()
                ->where('activo', true)
                ->orderBy('orden')
                ->get(['id', 'nombre']),
            'redesSociales' => RedSocial::query()
                ->where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'slug']),
            'empresasPublicacion' => Empresa::query()
                ->where('estado', 'activa')
                ->when($empresaActivaId, fn ($query) => $query->where('id', '!=', $empresaActivaId))
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'baseAllowedSchedule' => $baseAllowedSchedule,
            'baseVisibleSchedule' => $baseVisibleSchedule,
            'specialScheduleByDate' => [],
        ]);
    }

    public function horarios()
    {
        $schedule = $this->scheduleByDayZeroBased();
        $allHours = collect($this->visibleHoursZeroBased())
            ->flatten()
            ->unique()
            ->sort(function (string $a, string $b): int {
                [$hourA, $minuteA] = array_map('intval', explode(':', $a));
                [$hourB, $minuteB] = array_map('intval', explode(':', $b));

                return ($hourA * 60 + $minuteA) <=> ($hourB * 60 + $minuteB);
            })
            ->values();

        return view('planificador_horarios', [
            'dayNames' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            'schedule' => $schedule,
            'allHours' => $allHours,
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

        $items = MundialProducto::query()
            ->with([
                'user:id,name',
                'editor:id,name',
                'responsable2:id,name',
                'manager:id,name',
                'tipoProducto:id,nombre,slug',
                'mundialPrioridad:id,nombre',
                'mundialPlataforma:id,nombre',
                'mundialEquipo:id,nombre',
                'mundialTipo:id,nombre',
            ])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (MundialProducto $producto): array => $this->serializeProducto($producto, $request->user()))
            ->values();

        $michipitiItems = Producto::query()
            ->with([
                'user:id,name',
                'editor:id,name',
                'responsable2:id,name',
                'tipoProducto:id,nombre,slug',
            ])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (Producto $producto): array => $this->serializeMichipitiProducto($producto))
            ->values();

        return response()->json($items->concat($michipitiItems)->values());
    }

    public function periodistas(): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name')
            ->whereHas('roles')
            ->where('email', 'not like', '%@admin.com')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function videografos(): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['videografia', 'video_manager']))
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'id' => ['nullable', 'integer', 'exists:mundial_productos,id'],
                'fecha' => ['required', 'date'],
                'hora' => ['required', 'date_format:H:i'],
                'mundial_prioridad_id' => ['required', 'integer', Rule::exists('mundial_prioridades', 'id')->where(fn ($query) => $query->where('activo', true))],
                'mundial_plataformas_ids' => ['required', 'array', 'min:1'],
                'mundial_plataformas_ids.*' => ['integer', Rule::exists('mundial_plataformas', 'id')->where(fn ($query) => $query->where('activo', true))],
                'mundial_equipo_id' => ['required', 'integer', Rule::exists('mundial_equipos', 'id')->where(fn ($query) => $query->where('activo', true))],
                'mundial_tipo_id' => ['required', 'integer', Rule::exists('mundial_tipos', 'id')->where(fn ($query) => $query->where('activo', true))],
                'titulo' => ['required', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string'],
                'auspicio' => ['nullable', 'string', 'max:600'],
                'estado' => ['nullable', 'string', 'max:30'],
                'asignado_a' => ['nullable', 'integer', 'exists:users,id'],
                'responsable2_id' => ['required', 'integer', 'exists:users,id'],
                'edicion_id' => ['nullable', 'integer', 'exists:users,id'],
                'tipo_producto_id' => [
                    'required',
                    'integer',
                    Rule::exists('tipo_productos', 'id')->where(
                        fn ($query) => $query->where('empresa_id', app(EmpresaContext::class)->currentId())
                    ),
                ],
                'redes_sociales_ids' => ['nullable', 'array'],
                'redes_sociales_ids.*' => ['integer', Rule::exists('redes_sociales', 'id')],
                'etapa' => ['nullable', 'string', Rule::in(['Borrador', 'En proceso', 'Terminado', 'Por cerrar'])],
            ],
            [
                'id.exists' => 'El producto que intentas editar ya no existe.',
                'fecha.required' => 'Debes seleccionar una fecha.',
                'fecha.date' => 'La fecha seleccionada no es válida.',
                'hora.required' => 'Debes seleccionar una hora.',
                'hora.date_format' => 'La hora seleccionada no es válida.',
                'mundial_prioridad_id.required' => 'Debes seleccionar una prioridad.',
                'mundial_prioridad_id.exists' => 'La prioridad seleccionada no es válida.',
                'mundial_plataformas_ids.required' => 'Debes seleccionar al menos una plataforma.',
                'mundial_plataformas_ids.array' => 'Las plataformas seleccionadas no tienen un formato válido.',
                'mundial_plataformas_ids.min' => 'Debes seleccionar al menos una plataforma.',
                'mundial_plataformas_ids.*.exists' => 'Una de las plataformas seleccionadas no es válida.',
                'mundial_equipo_id.required' => 'Debes seleccionar un equipo.',
                'mundial_equipo_id.exists' => 'El equipo seleccionado no es válido.',
                'mundial_tipo_id.required' => 'Debes seleccionar un tipo.',
                'mundial_tipo_id.exists' => 'El tipo seleccionado no es válido.',
                'titulo.required' => 'Debes ingresar un título.',
                'titulo.max' => 'El título no puede superar los 200 caracteres.',
                'auspicio.max' => 'El auspicio no puede superar los 600 caracteres.',
                'asignado_a.exists' => 'El líder seleccionado ya no existe.',
                'responsable2_id.required' => 'Debes seleccionar un responsable.',
                'responsable2_id.exists' => 'El responsable seleccionado ya no existe.',
                'edicion_id.exists' => 'El usuario de edición seleccionado ya no existe.',
                'tipo_producto_id.required' => 'Debes seleccionar un tipo de producto.',
                'tipo_producto_id.exists' => 'El tipo de producto seleccionado no es válido para esta empresa.',
                'redes_sociales_ids.array' => 'Las redes sociales seleccionadas no tienen un formato válido.',
                'redes_sociales_ids.*.exists' => 'Una de las redes sociales seleccionadas ya no existe.',
                'etapa.in' => 'La etapa seleccionada no es válida.',
            ],
            [
                'asignado_a' => 'líder',
                'responsable2_id' => 'responsable',
                'edicion_id' => 'edición',
                'mundial_prioridad_id' => 'prioridad',
                'mundial_plataformas_ids' => 'plataformas',
                'mundial_equipo_id' => 'equipo',
                'mundial_tipo_id' => 'tipo',
                'tipo_producto_id' => 'tipo de producto',
                'redes_sociales_ids' => 'redes sociales',
                'etapa' => 'etapa',
                'auspicio' => 'auspicio',
            ],
        );

        $producto = $data['id']
            ? MundialProducto::query()->findOrFail($data['id'])
            : new MundialProducto();
        $isNew = ! $producto->exists;
        $estadoAnterior = $producto->estado;
        $origenAnterior = $producto->origen;
        $fechaAnterior = optional($producto->fecha)->format('Y-m-d');
        $horaAnterior = $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null;

        if ($producto->exists && ! $request->user()->hasRole('director')) {
            $data['fecha'] = optional($producto->fecha)->format('Y-m-d') ?: $data['fecha'];
            $data['hora'] = $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : $data['hora'];
        }

        $isAllowedSchedule = $this->isAllowedSchedule($data['fecha'], $data['hora']);
        $origen = $isAllowedSchedule ? 'propuesta' : 'pendiente';

        $mundialPrioridad = MundialPrioridad::query()->findOrFail($data['mundial_prioridad_id']);
        $mundialPlataformas = MundialPlataforma::query()
            ->whereIn('id', collect($data['mundial_plataformas_ids'])->map(fn ($id) => (int) $id)->unique()->values())
            ->orderBy('orden')
            ->get();
        $mundialPlataforma = $mundialPlataformas->first();
        $mundialEquipo = MundialEquipo::query()->findOrFail($data['mundial_equipo_id']);
        $mundialTipo = MundialTipo::query()->findOrFail($data['mundial_tipo_id']);
        $isComercial = strcasecmp($mundialTipo->nombre, 'Comercial') === 0;
        $auspicio = $isComercial ? ($data['auspicio'] ?? null) : null;

        if ($producto->exists && $producto->origen === 'pauta') {
            $seIntentoEditarCampoBloqueado =
                ((int) $producto->mundial_prioridad_id !== (int) $data['mundial_prioridad_id']) ||
                (collect($producto->mundial_plataformas_ids ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all() !== collect($data['mundial_plataformas_ids'])->map(fn ($id) => (int) $id)->unique()->sort()->values()->all()) ||
                ((int) $producto->mundial_equipo_id !== (int) $data['mundial_equipo_id']) ||
                ((int) $producto->mundial_tipo_id !== (int) $data['mundial_tipo_id']) ||
                ($producto->titulo !== $data['titulo']) ||
                (($producto->copy ?? '') !== ($data['descripcion'] ?? '')) ||
                (($producto->creditos ?? '') !== ($auspicio ?? '')) ||
                ((int) $producto->tipo_producto_id !== (int) $data['tipo_producto_id']) ||
                ($producto->origen !== $origen);

            if ($seIntentoEditarCampoBloqueado) {
                throw new HttpException(422, 'Los productos que ya están en pauta solo permiten cambiar responsable y referencia.');
            }
        }

        $producto->fill([
            'tipo_producto_id' => $this->resolveTipoProductoId((int) $data['tipo_producto_id']),
            'mundial_prioridad_id' => $mundialPrioridad->id,
            'mundial_plataforma_id' => $mundialPlataforma?->id,
            'mundial_plataformas_ids' => $mundialPlataformas->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'mundial_equipo_id' => $mundialEquipo->id,
            'mundial_tipo_id' => $mundialTipo->id,
            'user_id' => $data['asignado_a'] ?? $producto->user_id ?? $request->user()->id,
            'responsable2_id' => $data['responsable2_id'],
            'manager_id' => $data['edicion_id'] ?? null,
            'redes_sociales_ids' => collect($data['redes_sociales_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'orden_dia' => $this->orderDiaFromTime($data['hora']),
            'seccion' => $mundialEquipo->nombre,
            'copy' => $data['descripcion'] ?? null,
            'referencia' => $data['etapa'] ?? 'Borrador',
            'creditos' => $auspicio,
            'estado' => $data['estado'] ?: ($producto->estado ?: 'BORRADOR'),
            'prioridad' => $mundialPrioridad->nombre,
            'dificultad' => $producto->dificultad ?: 'BASICO',
            'origen' => $origen,
        ]);

        if ($request->user()->hasAnyRole(['editor', 'director'])) {
            $producto->editor_id = $request->user()->id;
        }

        $producto->save();
        $producto->load([
            'user:id,name',
            'editor:id,name',
            'responsable2:id,name',
            'manager:id,name',
            'tipoProducto:id,nombre,slug',
            'mundialPrioridad:id,nombre',
            'mundialPlataforma:id,nombre',
            'mundialEquipo:id,nombre',
            'mundialTipo:id,nombre',
        ]);
        $publicacionEnOtrasEmpresas = ['creadas' => [], 'conflictos' => []];

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

        if ($isNew && ! $isAllowedSchedule) {
            $directorIds = User::role('director')
                ->pluck('id')
                ->map(fn ($value) => (int) $value)
                ->toArray();

            if ($directorIds !== []) {
                $texto =
                    "────────────────────────\n".
                    $this->formatMundialHeader($producto)."\n".
                    "⚠️ Por aprobar fuera de pauta\n".
                    "Horario: {$data['fecha']} {$data['hora']}\n".
                    "────────────────────────\n";

                app(\App\Services\Carrusel\CarruselSlackNotifier::class)->notifyUsersByIds(
                    $directorIds,
                    $texto,
                    (int) $request->user()->id,
                );
            }
        }

        return response()->json([
            'ok' => true,
            'item' => $this->serializeProducto($producto, $request->user()),
            'replicadas' => $publicacionEnOtrasEmpresas['creadas'],
            'replica_conflictos' => $publicacionEnOtrasEmpresas['conflictos'],
        ]);
    }

    public function destroy(Request $request, MundialProducto $producto): JsonResponse
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
        if (! $request->user()->hasAnyRole(['editor', 'director'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo los roles editor y director pueden mover productos desde el planificador.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate(
            [
                'producto_id' => ['required', 'integer', 'exists:mundial_productos,id'],
                'source_key' => ['required', 'string'],
                'target_key' => ['required', 'string'],
            ],
            [
                'producto_id.required' => 'No se recibió el producto a mover.',
                'producto_id.exists' => 'El producto que intentas mover ya no existe.',
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

        $source = MundialProducto::query()->findOrFail($data['producto_id']);
        $sourceOriginalDate = optional($source->fecha)->format('Y-m-d');
        $sourceOriginalTime = $source->hora ? Carbon::parse($source->hora)->format('H:i') : null;
        $sourceEstadoAnterior = $source->estado;
        $sourceCanMoveToTarget = $this->isAllowedSchedule($targetDate, $targetTime) || $source->origen === 'pauta';

        if ($sourceOriginalDate !== $sourceDate || $sourceOriginalTime !== $sourceTime) {
            return response()->json([
                'ok' => false,
                'message' => 'El producto ya no está en el horario de origen. Recarga el planificador e intenta nuevamente.',
            ], Response::HTTP_CONFLICT);
        }

        if (! $sourceCanMoveToTarget) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo los productos que ya están en pauta pueden moverse a horarios fuera de pauta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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
                'id' => ['required', 'integer', 'exists:mundial_productos,id'],
            ],
            [
                'id.required' => 'No se recibió el producto a aprobar.',
                'id.exists' => 'El producto que intentas aprobar ya no existe.',
            ],
        );

        $producto = MundialProducto::query()->findOrFail($data['id']);
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
                'propuesta_id' => ['required', 'integer', 'exists:mundial_productos,id'],
                'asignado_a' => ['required', 'integer', 'exists:users,id'],
            ],
            [
                'propuesta_id.required' => 'No se recibió el producto a mover a pauta.',
                'propuesta_id.exists' => 'El producto que intentas mover a pauta ya no existe.',
                'asignado_a.required' => 'Debes seleccionar un responsable antes de enviar a pauta.',
                'asignado_a.exists' => 'El responsable seleccionado ya no existe.',
            ],
        );

        $producto = MundialProducto::query()->findOrFail($data['propuesta_id']);
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

    private function serializeProducto(MundialProducto $producto, User $user): array
    {
        return [
            'source' => 'mundial',
            'uid' => 'mundial:'.$producto->id,
            'id' => $producto->id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'tipo_producto_nombre' => $producto->tipoProducto?->nombre,
            'tipo_producto_slug' => $producto->tipoProducto?->slug,
            'mundial_prioridad_id' => $producto->mundial_prioridad_id,
            'mundial_prioridad_nombre' => $producto->mundialPrioridad?->nombre,
            'mundial_plataforma_id' => $producto->mundial_plataforma_id,
            'mundial_plataformas_ids' => collect($producto->mundial_plataformas_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'mundial_plataforma_nombre' => $this->mundialPlataformasLabel($producto),
            'mundial_equipo_id' => $producto->mundial_equipo_id,
            'mundial_equipo_nombre' => $producto->mundialEquipo?->nombre,
            'mundial_tipo_id' => $producto->mundial_tipo_id,
            'mundial_tipo_nombre' => $producto->mundialTipo?->nombre,
            'redes_sociales_ids' => collect($producto->redes_sociales_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'fecha' => optional($producto->fecha)->format('Y-m-d'),
            'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            'titulo' => $producto->titulo,
            'descripcion' => $producto->copy,
            'auspicio' => $producto->creditos,
            'creditos' => $producto->creditos,
            'seccion' => $producto->seccion,
            'estado' => $producto->estado,
            'origen' => $producto->origen,
            'asignado_a' => $producto->user_id,
            'responsable_nombre' => $producto->user?->name,
            'responsable2_id' => $producto->responsable2_id,
            'responsable2_nombre' => $producto->responsable2?->name,
            'edicion_id' => $producto->manager_id,
            'edicion_nombre' => $producto->manager?->name,
            'etapa' => $producto->referencia ?: 'Borrador',
            'link' => $producto->referencia,
            'canva_url' => $producto->canva_url,
            'prioridad' => $producto->prioridad,
            'dificultad' => $producto->dificultad,
            'assigned_at' => optional($producto->assigned_at)->toDateTimeString(),
            'updated_at' => optional($producto->updated_at)->toDateTimeString(),
            'can_delete' => $this->canDeleteProducto($user, $producto),
        ];
    }

    private function serializeMichipitiProducto(Producto $producto): array
    {
        return [
            'source' => 'michipiti',
            'uid' => 'michipiti:'.$producto->id,
            'id' => $producto->id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'tipo_producto_nombre' => $producto->tipoProducto?->nombre,
            'tipo_producto_slug' => $producto->tipoProducto?->slug,
            'redes_sociales_ids' => collect($producto->redes_sociales_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'fecha' => optional($producto->fecha)->format('Y-m-d'),
            'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            'titulo' => $producto->titulo,
            'descripcion' => $producto->copy,
            'seccion' => $producto->seccion,
            'estado' => $producto->estado,
            'origen' => $producto->origen,
            'asignado_a' => $producto->user_id,
            'responsable_nombre' => $producto->user?->name,
            'responsable2_id' => $producto->responsable2_id,
            'responsable2_nombre' => $producto->responsable2?->name,
            'link' => $producto->referencia,
            'canva_url' => $producto->canva_url,
            'prioridad' => $producto->prioridad,
            'dificultad' => $producto->dificultad,
            'assigned_at' => optional($producto->assigned_at)->toDateTimeString(),
            'updated_at' => optional($producto->updated_at)->toDateTimeString(),
            'can_delete' => false,
        ];
    }

    private function canDeleteProducto(User $user, MundialProducto $producto): bool
    {
        return $user->hasRole('director');
    }

    private function deleteDeniedMessage(User $user, MundialProducto $producto): string
    {
        return 'Solo el rol director puede eliminar productos.';
    }

    private function resolveTipoProductoId(?int $tipoProductoId = null): int
    {
        if ($tipoProductoId) {
            $tipoProducto = TipoProducto::query()->find($tipoProductoId);

            if (! $tipoProducto) {
                throw new HttpException(422, 'El tipo de producto seleccionado no existe para la empresa activa.');
            }

            return $tipoProducto->id;
        }

        $tipoProducto = TipoProducto::query()
            ->where('slug', TipoProducto::SLUG_CARRUSEL)
            ->first();

        if (! $tipoProducto) {
            throw new HttpException(422, 'No existe el tipo de producto carrusel configurado para el planificador.');
        }

        return $tipoProducto->id;
    }

    /**
     * @param  array<string, mixed>  $data CONTROL GIT
     * @return array{creadas: list<string>, conflictos: list<string>}
     */
    private function replicarProductoEnEmpresas(MundialProducto $producto, array $data, Request $request): array
    {
        $empresaIds = collect($data['publicar_tambien_en'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($empresaIds->isEmpty()) {
            return ['creadas' => [], 'conflictos' => []];
        }

        $tipoSlug = $producto->tipoProducto?->slug;
        if (! $tipoSlug) {
            return ['creadas' => [], 'conflictos' => []];
        }

        $empresas = Empresa::query()
            ->whereIn('id', $empresaIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $creadas = [];
        $conflictos = [];

        foreach ($empresas as $empresa) {
            $ocupado = MundialProducto::withoutGlobalScope('empresa_activa')
                ->where('empresa_id', $empresa->id)
                ->whereDate('fecha', $data['fecha'])
                ->whereTime('hora', $data['hora'])
                ->exists();

            if ($ocupado) {
                $conflictos[] = $empresa->nombre;
                continue;
            }

            $tipoDestino = TipoProducto::withoutGlobalScope('empresa_activa')
                ->where('empresa_id', $empresa->id)
                ->where('slug', $tipoSlug)
                ->first();

            if (! $tipoDestino) {
                $conflictos[] = $empresa->nombre;
                continue;
            }

            $clon = new MundialProducto();
            $clon->empresa_id = $empresa->id;
            $clon->tipo_producto_id = $tipoDestino->id;
            $clon->mundial_prioridad_id = $producto->mundial_prioridad_id;
            $clon->mundial_plataforma_id = $producto->mundial_plataforma_id;
            $clon->mundial_plataformas_ids = $producto->mundial_plataformas_ids;
            $clon->mundial_equipo_id = $producto->mundial_equipo_id;
            $clon->mundial_tipo_id = $producto->mundial_tipo_id;
            $clon->user_id = $producto->user_id;
            $clon->responsable2_id = $producto->responsable2_id;
            $clon->redes_sociales_ids = $producto->redes_sociales_ids;
            $clon->editor_id = $request->user()->hasAnyRole(['editor', 'director']) ? $request->user()->id : null;
            $clon->titulo = $producto->titulo;
            $clon->fecha = $producto->fecha;
            $clon->hora = $producto->hora;
            $clon->orden_dia = $producto->orden_dia;
            $clon->seccion = $producto->seccion;
            $clon->copy = $producto->copy;
            $clon->referencia = $producto->referencia;
            $clon->creditos = $producto->creditos;
            $clon->estado = $producto->estado;
            $clon->prioridad = $producto->prioridad;
            $clon->dificultad = $producto->dificultad;
            $clon->origen = $producto->origen;
            $clon->save();

            $creadas[] = $empresa->nombre;
        }

        return [
            'creadas' => $creadas,
            'conflictos' => $conflictos,
        ];
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
        return in_array($time, $this->allowedHoursForDate($date), true);
    }

    /**
     * @return array<int, list<string>>
     */
    private function visibleHoursZeroBased(): array
    {
        $dbSchedule = MundialHorarioSlot::query()
            ->where('visible', true)
            ->orderBy('hora')
            ->get(['dia_semana', 'hora'])
            ->groupBy('dia_semana')
            ->map(fn ($slots) => $slots->pluck('hora')
                ->map(fn ($hour) => substr((string) $hour, 0, 5))
                ->values()
                ->all())
            ->all();

        if ($dbSchedule !== []) {
            return [
                0 => $dbSchedule[0] ?? [],
                1 => $dbSchedule[1] ?? [],
                2 => $dbSchedule[2] ?? [],
                3 => $dbSchedule[3] ?? [],
                4 => $dbSchedule[4] ?? [],
                5 => $dbSchedule[5] ?? [],
                6 => $dbSchedule[6] ?? [],
            ];
        }

        return $this->fallbackAllowedScheduleZeroBased();
    }

    /**
     * @return array<int, list<string>>
     */
    private function scheduleByDayZeroBased(): array
    {
        $dbSchedule = MundialHorarioSlot::query()
            ->where('visible', true)
            ->where('fuera_de_pauta', false)
            ->orderBy('hora')
            ->get(['dia_semana', 'hora'])
            ->groupBy('dia_semana')
            ->map(fn ($slots) => $slots->pluck('hora')
                ->map(fn ($hour) => substr((string) $hour, 0, 5))
                ->values()
                ->all())
            ->all();

        if ($dbSchedule !== []) {
            return [
                0 => $dbSchedule[0] ?? [],
                1 => $dbSchedule[1] ?? [],
                2 => $dbSchedule[2] ?? [],
                3 => $dbSchedule[3] ?? [],
                4 => $dbSchedule[4] ?? [],
                5 => $dbSchedule[5] ?? [],
                6 => $dbSchedule[6] ?? [],
            ];
        }

        return $this->fallbackAllowedScheduleZeroBased();
    }

    /**
     * @return array<int, list<string>>
     */
    private function fallbackAllowedScheduleZeroBased(): array
    {
        $hours = [];

        for ($minutes = 6 * 60; $minutes <= 23 * 60; $minutes += 15) {
            $hours[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        return [
            0 => $hours,
            1 => $hours,
            2 => $hours,
            3 => $hours,
            4 => $hours,
            5 => $hours,
            6 => $hours,
        ];
    }

    /**
     * @return array<int, list<string>>
     */
    private function scheduleByDayIso(): array
    {
        $zeroBased = $this->scheduleByDayZeroBased();

        return [
            1 => $zeroBased[0],
            2 => $zeroBased[1],
            3 => $zeroBased[2],
            4 => $zeroBased[3],
            5 => $zeroBased[4],
            6 => $zeroBased[5],
            7 => $zeroBased[6],
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedHoursForDate(string $date): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
        $scheduleByDay = $this->scheduleByDayIso();

        return $scheduleByDay[$dayOfWeek] ?? [];
    }

    private function isPastDateTime(string $date, string $time): bool
    {
        return Carbon::parse("{$date} {$time}")->lt(now());
    }

    private function formatMundialHeader(MundialProducto $producto): string
    {
        $titulo = $producto->titulo ?: ('Mundial #'.$producto->id);
        $responsable = $producto->user?->name ?? 'Sin responsable';

        return "🏆 *{$titulo}* (ID: {$producto->id})\n👤 Responsable: {$responsable}";
    }

    private function mundialPlataformasLabel(MundialProducto $producto): ?string
    {
        $ids = collect($producto->mundial_plataformas_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty() && $producto->mundialPlataforma) {
            return $producto->mundialPlataforma->nombre;
        }

        if ($ids->isEmpty()) {
            return null;
        }

        return MundialPlataforma::query()
            ->whereIn('id', $ids)
            ->orderBy('orden')
            ->pluck('nombre')
            ->join(', ');
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private function registrarMovimiento(
        MundialProducto $producto,
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
