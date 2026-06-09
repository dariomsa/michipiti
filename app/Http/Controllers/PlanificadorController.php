<?php

namespace App\Http\Controllers;

use App\Models\CarruselMovimiento;
use App\Models\CalendarioEspecial;
use App\Models\CalendarioEspecialSlot;
use App\Models\Empresa;
use App\Models\HorarioSlot;
use App\Models\MundialPlataforma;
use App\Models\MundialProducto;
use App\Models\Producto;
use App\Models\RedSocial;
use App\Models\Seccion;
use App\Models\TipoProducto;
use App\Models\User;
use App\Services\Carrusel\CarruselSlackNotifier;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlanificadorController extends Controller
{
    public function index()
    {
        $baseAllowedSchedule = $this->scheduleByDayZeroBased();
        $baseVisibleSchedule = $this->visibleHoursZeroBased();
        $empresaActivaId = app(EmpresaContext::class)->currentId();

        return view('planificador', [
            'secciones' => Seccion::query()
                ->where('activa', true)
                ->orderBy('id')
                ->pluck('nombre')
                ->all(),
            'tiposProducto' => TipoProducto::query()
                ->whereIn('slug', [TipoProducto::SLUG_CARRUSEL, TipoProducto::SLUG_REEL])
                ->orderByRaw("FIELD(slug, ?, ?)", [TipoProducto::SLUG_CARRUSEL, TipoProducto::SLUG_REEL])
                ->get(['id', 'nombre', 'slug']),
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
            'specialScheduleByDate' => $this->specialScheduleByDate(),
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

        $items = Producto::query()
            ->with(['user:id,name', 'editor:id,name', 'responsable2:id,name', 'tipoProducto:id,nombre,slug'])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (Producto $producto): array => $this->serializeProducto($producto, $request->user()))
            ->values();

        $instagramPlataformaId = MundialPlataforma::query()
            ->where('nombre', 'Instagram')
            ->value('id');
        $instagramRedSocialId = RedSocial::query()
            ->where('slug', 'instagram')
            ->value('id');

        $mundialItems = collect();

        if ($instagramPlataformaId) {
            $mundialItems = MundialProducto::query()
                ->with([
                    'user:id,name',
                    'responsable2:id,name',
                    'manager:id,name',
                    'tipoProducto:id,nombre,slug',
                    'mundialPrioridad:id,nombre',
                    'mundialEquipo:id,nombre',
                    'mundialTipo:id,nombre',
                ])
                ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
                ->where('visible', true)
                ->where(function ($query) use ($instagramPlataformaId): void {
                    $query
                        ->whereJsonContains('mundial_plataformas_ids', (int) $instagramPlataformaId)
                        ->orWhere('mundial_plataforma_id', (int) $instagramPlataformaId);
                })
                ->orderBy('fecha')
                ->orderBy('hora')
                ->get()
                ->map(fn (MundialProducto $producto): array => $this->serializeMundialProducto($producto, $instagramRedSocialId ? (int) $instagramRedSocialId : null))
                ->values();
        }

        return response()->json($items->concat($mundialItems)->values());
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
                'responsable2_id' => ['nullable', 'integer', 'exists:users,id'],
                'tipo_producto_id' => [
                    'required',
                    'integer',
                    Rule::exists('tipo_productos', 'id')->where(
                        fn ($query) => $query->where('empresa_id', app(EmpresaContext::class)->currentId())
                    ),
                ],
                'redes_sociales_ids' => ['nullable', 'array'],
                'redes_sociales_ids.*' => ['integer', Rule::exists('redes_sociales', 'id')],
                'publicar_tambien_en' => ['nullable', 'array'],
                'publicar_tambien_en.*' => [
                    'integer',
                    Rule::exists('empresas', 'id')->where(
                        fn ($query) => $query
                            ->where('estado', 'activa')
                            ->where('id', '!=', app(EmpresaContext::class)->currentId())
                    ),
                ],
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
                'responsable2_id.exists' => 'El responsable 2 seleccionado ya no existe.',
                'tipo_producto_id.required' => 'Debes seleccionar un tipo de producto.',
                'tipo_producto_id.exists' => 'El tipo de producto seleccionado no es válido para esta empresa.',
                'redes_sociales_ids.array' => 'Las redes sociales seleccionadas no tienen un formato válido.',
                'redes_sociales_ids.*.exists' => 'Una de las redes sociales seleccionadas ya no existe.',
                'publicar_tambien_en.array' => 'Las empresas seleccionadas no tienen un formato válido.',
                'publicar_tambien_en.*.exists' => 'Una de las empresas destino ya no es válida.',
                'link.max' => 'La referencia no puede superar los 600 caracteres.',
            ],
            [
                'asignado_a' => 'responsable',
                'responsable2_id' => 'responsable 2',
                'tipo_producto_id' => 'tipo de producto',
                'redes_sociales_ids' => 'redes sociales',
                'publicar_tambien_en' => 'publicar también en',
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
                ((int) $producto->tipo_producto_id !== (int) $data['tipo_producto_id']) ||
                ($producto->origen !== $origen);

            if ($seIntentoEditarCampoBloqueado) {
                throw new HttpException(422, 'Los productos que ya están en pauta solo permiten cambiar responsable y referencia.');
            }
        }

        $producto->fill([
            'tipo_producto_id' => $this->resolveTipoProductoId((int) $data['tipo_producto_id']),
            'user_id' => $data['asignado_a'] ?? $producto->user_id ?? $request->user()->id,
            'responsable2_id' => $data['responsable2_id'] ?? null,
            'redes_sociales_ids' => collect($data['redes_sociales_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
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
        $producto->load(['user:id,name', 'editor:id,name', 'responsable2:id,name', 'tipoProducto:id,nombre,slug']);
        $publicacionEnOtrasEmpresas = $isNew
            ? $this->replicarProductoEnEmpresas($producto, $data, $request)
            : ['creadas' => [], 'conflictos' => []];

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
                    app(CarruselSlackNotifier::class)->formatHeader($producto)."\n".
                    "⚠️ Por aprobar fuera de pauta\n".
                    "Horario: {$data['fecha']} {$data['hora']}\n".
                    "────────────────────────\n";

                app(CarruselSlackNotifier::class)->notifyUsersByIds(
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
        $sourceCanMoveToTarget = $this->isAllowedSchedule($targetDate, $targetTime) || $source->origen === 'pauta';

        $target = Producto::query()
            ->whereDate('fecha', $targetDate)
            ->whereTime('hora', $targetTime)
            ->first();

        if (! $sourceCanMoveToTarget) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo los productos que ya están en pauta pueden moverse a horarios fuera de pauta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($target) {
            $targetOriginalDate = optional($target->fecha)->format('Y-m-d');
            $targetOriginalTime = $target->hora ? Carbon::parse($target->hora)->format('H:i') : null;
            $targetEstadoAnterior = $target->estado;
            $targetCanMoveToSource = $this->isAllowedSchedule($sourceOriginalDate, $sourceOriginalTime) || $target->origen === 'pauta';

            if (! $targetCanMoveToSource) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El intercambio no es válido porque uno de los productos quedaría fuera de pauta sin estar en pauta.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $source->update([
                'fecha' => $targetDate,
                'hora' => $targetTime,
                'orden_dia' => $this->orderDiaFromTime($targetTime),
            ]);
            $this->syncMundialScheduleFromProducto($source->fresh());

            $target->update([
                'fecha' => $sourceOriginalDate,
                'hora' => $sourceOriginalTime,
                'orden_dia' => $this->orderDiaFromTime($sourceOriginalTime),
            ]);
            $this->syncMundialScheduleFromProducto($target->fresh());

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
            $this->syncMundialScheduleFromProducto($source->fresh());

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

    public function mundialToPauta(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'mundial_producto_id' => ['required', 'integer', 'exists:mundial_productos,id'],
                'asignado_a' => ['required', 'integer', 'exists:users,id'],
                'responsable2_id' => ['nullable', 'integer', 'exists:users,id'],
                'fecha' => ['required', 'date'],
                'hora' => ['required', 'date_format:H:i'],
                'seccion' => ['required', 'string', 'max:100'],
                'titulo' => ['required', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string'],
                'tipo_producto_id' => [
                    'required',
                    'integer',
                    Rule::exists('tipo_productos', 'id')->where(
                        fn ($query) => $query->where('empresa_id', app(EmpresaContext::class)->currentId())
                    ),
                ],
                'redes_sociales_ids' => ['nullable', 'array'],
                'redes_sociales_ids.*' => ['integer', Rule::exists('redes_sociales', 'id')],
                'link' => ['nullable', 'string', 'max:600'],
            ],
            [
                'mundial_producto_id.required' => 'No se recibió el producto Mundial a mover a pauta.',
                'mundial_producto_id.exists' => 'El producto Mundial que intentas mover ya no existe.',
                'asignado_a.required' => 'Debes seleccionar un responsable antes de enviar a pauta.',
                'asignado_a.exists' => 'El responsable seleccionado ya no existe.',
                'responsable2_id.exists' => 'El responsable 2 seleccionado ya no existe.',
                'fecha.required' => 'Debes seleccionar una fecha.',
                'hora.required' => 'Debes seleccionar una hora.',
                'seccion.required' => 'Debes seleccionar una sección.',
                'titulo.required' => 'Debes ingresar un título.',
                'tipo_producto_id.required' => 'Debes seleccionar un tipo de producto.',
                'tipo_producto_id.exists' => 'El tipo de producto seleccionado no es válido para esta empresa.',
                'redes_sociales_ids.array' => 'Las redes sociales seleccionadas no tienen un formato válido.',
                'redes_sociales_ids.*.exists' => 'Una de las redes sociales seleccionadas ya no existe.',
                'link.max' => 'La referencia no puede superar los 600 caracteres.',
            ],
        );

        $mundialProducto = MundialProducto::query()->findOrFail($data['mundial_producto_id']);

        if (! $mundialProducto->visible) {
            return response()->json([
                'ok' => false,
                'message' => 'Este producto Mundial ya fue movido u ocultado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isAllowedSchedule = $this->isAllowedSchedule($data['fecha'], $data['hora']);

        $producto = DB::transaction(function () use ($data, $mundialProducto, $request): Producto {
            $producto = new Producto();
            $producto->empresa_id = $mundialProducto->empresa_id;
            $producto->mundial_id = $mundialProducto->id;
            $producto->tipo_producto_id = $this->resolveTipoProductoId((int) $data['tipo_producto_id']);
            $producto->user_id = (int) $data['asignado_a'];
            $producto->responsable2_id = $data['responsable2_id'] ?? $mundialProducto->responsable2_id;
            $producto->redes_sociales_ids = collect($data['redes_sociales_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $producto->editor_id = $request->user()->hasAnyRole(['editor', 'director']) ? $request->user()->id : null;
            $producto->titulo = $data['titulo'];
            $producto->fecha = $data['fecha'];
            $producto->hora = $data['hora'];
            $producto->orden_dia = $this->orderDiaFromTime($data['hora']);
            $producto->seccion = $data['seccion'];
            $producto->copy = $data['descripcion'] ?? null;
            $producto->referencia = $data['link'] ?? $mundialProducto->referencia;
            $producto->creditos = $mundialProducto->creditos;
            $producto->estado = 'BORRADOR';
            $producto->prioridad = $mundialProducto->prioridad ?: 'Día';
            $producto->dificultad = $mundialProducto->dificultad ?: 'BASICO';
            $producto->origen = $this->isAllowedSchedule($data['fecha'], $data['hora']) ? 'pauta' : 'pendiente';
            $producto->assigned_at = $producto->origen === 'pauta' ? now() : null;
            $producto->save();

            $mundialProducto->visible = false;
            $mundialProducto->save();

            $this->registrarMovimiento(
                producto: $producto,
                user: $request->user(),
                accion: 'ENVIADO_PAUTA',
                estadoAnterior: null,
                estadoNuevo: $producto->estado,
                motivo: 'Producto creado en pauta desde Especial Mundial.',
                meta: [
                    'origen_anterior' => 'mundial',
                    'origen_nuevo' => $producto->origen,
                    'mundial_producto_id' => $mundialProducto->id,
                    'asignado_a' => $producto->user_id,
                ],
            );

            $mundialProducto->movimientos()->create([
                'user_id' => $request->user()->id,
                'accion' => 'MOVIDO_PLANIFICADOR_NORMAL',
                'estado_anterior' => $mundialProducto->estado,
                'estado_nuevo' => $mundialProducto->estado,
                'motivo' => 'Producto Mundial movido al planificador.',
                'meta' => [
                    'producto_id' => $producto->id,
                    'visible_anterior' => true,
                    'visible_nuevo' => false,
                ],
            ]);

            return $producto->load(['user:id,name', 'editor:id,name', 'responsable2:id,name', 'tipoProducto:id,nombre,slug']);
        });

        if (! $isAllowedSchedule) {
            $directorIds = User::role('director')
                ->pluck('id')
                ->map(fn ($value) => (int) $value)
                ->toArray();

            if ($directorIds !== []) {
                $texto =
                    "────────────────────────\n".
                    app(CarruselSlackNotifier::class)->formatHeader($producto)."\n".
                    "⚠️ Por aprobar fuera de pauta\n".
                    "Origen: Especial Mundial\n".
                    "Horario: {$data['fecha']} {$data['hora']}\n".
                    "────────────────────────\n";

                app(CarruselSlackNotifier::class)->notifyUsersByIds(
                    $directorIds,
                    $texto,
                    (int) $request->user()->id,
                );
            }
        }

        return response()->json([
            'ok' => true,
            'item' => $this->serializeProducto($producto, $request->user()),
            'carrusel_id' => $producto->id,
        ]);
    }

    private function serializeProducto(Producto $producto, User $user): array
    {
        return [
            'source' => 'producto',
            'uid' => 'producto:'.$producto->id,
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
            'can_delete' => $this->canDeleteProducto($user, $producto),
        ];
    }

    private function syncMundialScheduleFromProducto(Producto $producto): void
    {
        if (! $producto->mundial_id) {
            return;
        }

        MundialProducto::query()
            ->whereKey($producto->mundial_id)
            ->update([
                'fecha' => optional($producto->fecha)->format('Y-m-d'),
                'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
                'orden_dia' => $producto->hora ? $this->orderDiaFromTime(Carbon::parse($producto->hora)->format('H:i')) : null,
            ]);
    }

    private function serializeMundialProducto(MundialProducto $producto, ?int $instagramRedSocialId = null): array
    {
        return [
            'source' => 'mundial',
            'uid' => 'mundial:'.$producto->id,
            'id' => $producto->id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'tipo_producto_nombre' => $producto->tipoProducto?->nombre,
            'tipo_producto_slug' => $producto->tipoProducto?->slug,
            'redes_sociales_ids' => $instagramRedSocialId ? [$instagramRedSocialId] : [],
            'fecha' => optional($producto->fecha)->format('Y-m-d'),
            'hora' => $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : null,
            'titulo' => $producto->titulo,
            'descripcion' => $producto->copy,
            'seccion' => $producto->mundialEquipo?->nombre ?: $producto->seccion,
            'estado' => $producto->estado,
            'origen' => 'mundial',
            'asignado_a' => $producto->user_id,
            'responsable_nombre' => $producto->user?->name,
            'responsable2_id' => $producto->responsable2_id,
            'responsable2_nombre' => $producto->responsable2?->name,
            'edicion_nombre' => $producto->manager?->name,
            'link' => $producto->referencia,
            'canva_url' => $producto->canva_url,
            'prioridad' => $producto->mundialPrioridad?->nombre ?: $producto->prioridad,
            'dificultad' => $producto->dificultad,
            'mundial_tipo_nombre' => $producto->mundialTipo?->nombre,
            'etapa' => $producto->referencia ?: 'Borrador',
            'assigned_at' => optional($producto->assigned_at)->toDateTimeString(),
            'updated_at' => optional($producto->updated_at)->toDateTimeString(),
            'can_delete' => false,
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
    private function replicarProductoEnEmpresas(Producto $producto, array $data, Request $request): array
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
            $ocupado = Producto::withoutGlobalScope('empresa_activa')
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

            $clon = new Producto();
            $clon->empresa_id = $empresa->id;
            $clon->tipo_producto_id = $tipoDestino->id;
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
            $clon->estado = $producto->estado;
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
        $dbSchedule = HorarioSlot::query()
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

        $allowed = $this->fallbackAllowedScheduleZeroBased();
        $visible = $allowed;

        foreach ($visible as $dayIndex => $hours) {
            if (! in_array('14:00', $hours, true)) {
                $visible[$dayIndex][] = '14:00';
                sort($visible[$dayIndex]);
            }
        }

        return $visible;
    }

    /**
     * @return array<int, list<string>>
     */
    private function scheduleByDayZeroBased(): array
    {
        $dbSchedule = HorarioSlot::query()
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
        $weekdaysHours = [
            '06:00', '07:00', '08:15', '09:30', '10:45', '11:30', '12:15', '13:30',
            '14:45', '15:30', '16:00', '17:15', '18:30', '19:45', '20:15', '21:00',
            '22:15', '22:45',
        ];

        return [
            0 => $weekdaysHours,
            1 => $weekdaysHours,
            2 => $weekdaysHours,
            3 => $weekdaysHours,
            4 => $weekdaysHours,
            5 => [
                '09:00', '10:30', '12:00', '13:30', '15:00',
                '16:30', '18:00', '19:30', '20:30', '22:00',
            ],
            6 => [
                '09:30', '10:45', '12:00', '13:30', '15:00',
                '16:30', '18:00', '19:30', '21:00', '22:00',
            ],
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
        $special = $this->specialScheduleForDate($date);

        if ($special !== null) {
            return $special['allowed'];
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
        $scheduleByDay = $this->scheduleByDayIso();

        return $scheduleByDay[$dayOfWeek] ?? [];
    }

    /**
     * @return array<string, array{visible: list<string>, allowed: list<string>}>
     */
    private function specialScheduleByDate(): array
    {
        return CalendarioEspecial::query()
            ->with(['slots' => fn ($query) => $query->where('visible', true)->orderBy('hora')])
            ->orderBy('fecha')
            ->get()
            ->mapWithKeys(function (CalendarioEspecial $item): array {
                $visible = $item->slots
                    ->pluck('hora')
                    ->map(fn ($hour) => substr((string) $hour, 0, 5))
                    ->values()
                    ->all();

                $allowed = $item->slots
                    ->where('fuera_de_pauta', false)
                    ->pluck('hora')
                    ->map(fn ($hour) => substr((string) $hour, 0, 5))
                    ->values()
                    ->all();

                return [
                    $item->fecha->format('Y-m-d') => [
                        'visible' => $visible,
                        'allowed' => $allowed,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @return array{visible: list<string>, allowed: list<string>}|null
     */
    private function specialScheduleForDate(string $date): ?array
    {
        $special = CalendarioEspecial::query()
            ->whereDate('fecha', $date)
            ->first();

        if (! $special) {
            return null;
        }

        $visibleSlots = CalendarioEspecialSlot::query()
            ->where('tipo_feriado', $special->tipo_feriado)
            ->where('visible', true)
            ->orderBy('hora')
            ->get(['hora', 'fuera_de_pauta']);

        return [
            'visible' => $visibleSlots
                ->pluck('hora')
                ->map(fn ($hour) => substr((string) $hour, 0, 5))
                ->values()
                ->all(),
            'allowed' => $visibleSlots
                ->where('fuera_de_pauta', false)
                ->pluck('hora')
                ->map(fn ($hour) => substr((string) $hour, 0, 5))
                ->values()
                ->all(),
        ];
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
