<?php

namespace App\Http\Controllers\Mundial;

use App\Http\Controllers\Controller;
use App\Models\MundialEquipo;
use App\Models\MundialPlataforma;
use App\Models\MundialPrioridad;
use App\Models\MundialProducto;
use App\Models\MundialTipo;
use App\Models\TipoProducto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductoController extends Controller
{
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

        $tipos = MundialTipo::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']);
        $tipoEditorialId = $tipos->first(fn (MundialTipo $tipo) => strcasecmp($tipo->nombre, 'Editorial') === 0)?->id;
        $tipoComercialId = $tipos->first(fn (MundialTipo $tipo) => strcasecmp($tipo->nombre, 'Comercial') === 0)?->id;
        $tipoRadioId = $tipos->first(fn (MundialTipo $tipo) => strcasecmp($tipo->nombre, 'Radio') === 0)?->id;

        $baseQuery = MundialProducto::query()
            ->with([
                'user:id,name',
                'responsable2:id,name',
                'manager:id,name',
                'mundialPrioridad:id,nombre',
                'mundialPlataforma:id,nombre',
                'mundialEquipo:id,nombre',
                'mundialTipo:id,nombre',
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
            ->when($filters['prioridad'] > 0, fn (Builder $query) => $query->where('mundial_prioridad_id', $filters['prioridad']))
            ->when($filters['plataforma'] > 0, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $inner) use ($filters): void {
                    $inner
                        ->whereJsonContains('mundial_plataformas_ids', $filters['plataforma'])
                        ->orWhere('mundial_plataforma_id', $filters['plataforma']);
                });
            })
            ->when($filters['equipo'] > 0, fn (Builder $query) => $query->where('mundial_equipo_id', $filters['equipo']))
            ->when($filters['tipo'] > 0, fn (Builder $query) => $query->where('mundial_tipo_id', $filters['tipo']))
            ->when($filters['fecha'] !== '', fn (Builder $query) => $query->whereDate('fecha', $filters['fecha']));

        $statsQuery = clone $baseQuery;
        $productos = $baseQuery
            ->orderByRaw('fecha IS NULL')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->orderBy('id')
            ->paginate(60)
            ->withQueryString();

        $plataformasById = MundialPlataforma::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get(['id', 'nombre'])
            ->keyBy('id');

        $statsItems = (clone $statsQuery)->get(['id', 'mundial_tipo_id']);

        return view('mundial.index', [
            'productos' => $productos,
            'filters' => $filters,
            'prioridades' => MundialPrioridad::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']),
            'plataformas' => $plataformasById->values(),
            'equipos' => MundialEquipo::query()->where('activo', true)->orderBy('orden')->get(['id', 'nombre']),
            'tipos' => $tipos,
            'plataformasById' => $plataformasById,
            'defaultTipoProductoId' => TipoProducto::query()->orderBy('id')->value('id'),
            'stats' => [
                'total' => $statsItems->count(),
                'editorial' => $tipoEditorialId ? $statsItems->where('mundial_tipo_id', $tipoEditorialId)->count() : 0,
                'comercial' => $tipoComercialId ? $statsItems->where('mundial_tipo_id', $tipoComercialId)->count() : 0,
                'radio' => $tipoRadioId ? $statsItems->where('mundial_tipo_id', $tipoRadioId)->count() : 0,
            ],
        ]);
    }
}
