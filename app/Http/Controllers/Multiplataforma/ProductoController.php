<?php

namespace App\Http\Controllers\Multiplataforma;

use App\Http\Controllers\Controller;
use App\Models\MultiplataformaEquipo;
use App\Models\MultiplataformaPlataforma;
use App\Models\MultiplataformaPrioridad;
use App\Models\MultiplataformaProducto;
use App\Models\MultiplataformaTipo;
use App\Models\TipoProducto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ProductoController extends Controller
{
    private const START_DATE = '2026-06-08';

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'prioridad' => (int) $request->query('prioridad', 0),
            'plataforma' => (int) $request->query('plataforma', 0),
            'equipo' => (int) $request->query('equipo', 0),
            'tipo' => (int) $request->query('tipo', 0),
            'fecha' => $request->filled('fecha') ? Carbon::parse($request->query('fecha'))->toDateString() : '',
        ];

        $tipos = MultiplataformaTipo::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']);
        $tipoEditorialId = $tipos->first(fn (MultiplataformaTipo $tipo) => strcasecmp($tipo->nombre, 'Editorial') === 0)?->id;
        $tipoComercialId = $tipos->first(fn (MultiplataformaTipo $tipo) => strcasecmp($tipo->nombre, 'Comercial') === 0)?->id;
        $tipoRadioId = $tipos->first(fn (MultiplataformaTipo $tipo) => strcasecmp($tipo->nombre, 'Radio') === 0)?->id;

        $baseQuery = MultiplataformaProducto::query()
            ->with([
                'user:id,name',
                'responsable2:id,name',
                'manager:id,name',
                'multiplataformaPrioridad:id,nombre',
                'multiplataformaPlataforma:id,nombre',
                'multiplataformaEquipo:id,nombre',
                'multiplataformaTipo:id,nombre',
                'productoConvertido:id,multiplataforma_id,estado,origen,programado_metricool',
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $q = $filters['q'];

                $query->where(function (Builder $inner) use ($q): void {
                    $inner
                        ->where('titulo', 'like', "%{$q}%")
                        ->orWhere('copy', 'like', "%{$q}%")
                        ->orWhere('creditos', 'like', "%{$q}%")
                        ->orWhere('seccion', 'like', "%{$q}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('responsable2', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($filters['prioridad'] > 0, fn (Builder $query) => $query->where('multiplataforma_prioridad_id', $filters['prioridad']))
            ->when($filters['plataforma'] > 0, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $inner) use ($filters): void {
                    $inner
                        ->whereJsonContains('multiplataforma_plataformas_ids', $filters['plataforma'])
                        ->orWhere('multiplataforma_plataforma_id', $filters['plataforma']);
                });
            })
            ->when($filters['equipo'] > 0, fn (Builder $query) => $query->where('multiplataforma_equipo_id', $filters['equipo']))
            ->when($filters['tipo'] > 0, fn (Builder $query) => $query->where('multiplataforma_tipo_id', $filters['tipo']))
            ->whereDate('fecha', '>=', self::START_DATE)
            ->when($filters['fecha'] !== '', fn (Builder $query) => $query->whereDate('fecha', $filters['fecha']));

        $statsQuery = clone $baseQuery;
        $productosQuery = clone $baseQuery;

        if ($filters['fecha'] === '') {
            $productosQuery->whereDate('fecha', '>=', today()->toDateString());
        }

        $productos = $productosQuery
            ->orderByRaw('fecha IS NULL')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->orderBy('id')
            ->paginate(60)
            ->withQueryString();

        $plataformasById = MultiplataformaPlataforma::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get(['id', 'nombre'])
            ->keyBy('id');

        $statsItems = (clone $statsQuery)->get(['id', 'multiplataforma_tipo_id']);

        return view('multiplataforma.index', [
            'productos' => $productos,
            'filters' => $filters,
            'prioridades' => MultiplataformaPrioridad::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']),
            'plataformas' => $plataformasById->values(),
            'equipos' => MultiplataformaEquipo::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']),
            'tipos' => $tipos,
            'plataformasById' => $plataformasById,
            'defaultTipoProductoId' => TipoProducto::query()->orderBy('id')->value('id'),
            'puedeEditarMultiplataforma' => ! $this->isMultiplataformaReadOnlyUser($request),
            'stats' => [
                'total' => $statsItems->count(),
                'editorial' => $tipoEditorialId ? $statsItems->where('multiplataforma_tipo_id', $tipoEditorialId)->count() : 0,
                'comercial' => $tipoComercialId ? $statsItems->where('multiplataforma_tipo_id', $tipoComercialId)->count() : 0,
                'radio' => $tipoRadioId ? $statsItems->where('multiplataforma_tipo_id', $tipoRadioId)->count() : 0,
            ],
        ]);
    }

    public function metricool(Request $request, MultiplataformaProducto $producto): JsonResponse
    {
        if ($this->isMultiplataformaReadOnlyUser($request)) {
            return response()->json([
                'ok' => false,
                'message' => 'Este rol solo puede ver el Multiplataforma.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($producto->productoConvertido()->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Los productos pasados a Michipiti no se pueden modificar desde el listado Multiplataforma.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $producto->programado_metricool) {
            $producto->forceFill([
                'programado_metricool' => true,
            ])->save();
        }

        return response()->json([
            'ok' => true,
            'programado_metricool' => true,
        ]);
    }

    private function isMultiplataformaReadOnlyUser(Request $request): bool
    {
        $user = $request->user();

        return $user && $user->hasRole('multiplataforma_lectura') && $user->getRoleNames()->count() === 1;
    }
}
