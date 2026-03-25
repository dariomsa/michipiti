<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Seccion;
use App\Models\TipoProducto;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth()->user()?->hasRole('director') ?? false, 403);

        $tipoProductoId = $request->integer('tipo_producto_id') ?: null;
        $seccionesSeleccionadas = collect((array) $request->input('secciones', []))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        $productos = $this->baseQuery($tipoProductoId, $seccionesSeleccionadas)
            ->with(['movimientos', 'tipoProducto'])
            ->orderByDesc('created_at')
            ->get();

        $chartDia = $this->chartPorDia($tipoProductoId, $seccionesSeleccionadas);
        $chartSemana = $this->chartPorSemana($tipoProductoId, $seccionesSeleccionadas);
        $categorias = $this->categorias($tipoProductoId, $seccionesSeleccionadas);
        $ultimosCarruseles = $productos->take(20)->map(function (Producto $producto) {
            $producto->tiempo_diseno = $this->tiempoDiseno($producto);

            return $producto;
        });

        $payload = [
            'kpi_total_promedio' => $this->formatDuration($this->averageMinutes(
                $productos->map(fn (Producto $producto) => $this->diffMinutes($producto->created_at, $producto->updated_at))
            )),
            'kpi_diseno_promedio' => $this->formatDuration($this->averageMinutes(
                $productos->map(fn (Producto $producto) => $this->minutesFromActionToEnd($producto, 'ASIGNADO_DISENADOR'))
            )),
            'kpi_revision_promedio' => $this->formatDuration($this->averageMinutes(
                $productos->map(fn (Producto $producto) => $this->minutesBetweenActions($producto, 'ENVIADO_REVISION', 'ENVIADO_DISENO'))
            )),
            'kpi_total_carruseles' => $productos->count(),
            'chartDiaLabels' => $chartDia['labels'],
            'chartDiaData' => $chartDia['data'],
            'chartSemanaLabels' => $chartSemana['labels'],
            'chartSemanaData' => $chartSemana['data'],
            'chartCatLabels' => $categorias['labels'],
            'chartCatData' => $categorias['data'],
            'categoriasTabla' => $categorias['table'],
            'ultimosCarruseles' => $ultimosCarruseles->map(function (Producto $producto): array {
                return [
                    'titulo' => $producto->titulo,
                    'estado' => $producto->estado,
                    'seccion' => $producto->seccion,
                    'created_at' => $producto->created_at?->format('d M Y H:i'),
                    'updated_at' => $producto->updated_at?->format('d M Y H:i'),
                    'tiempo_diseno' => $producto->tiempo_diseno,
                ];
            })->values()->all(),
            'tipoProductoId' => $tipoProductoId,
            'seccionesSeleccionadas' => $seccionesSeleccionadas,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return view('director.dashboard', [
            ...$payload,
            'ultimosCarruseles' => $ultimosCarruseles,
            'tiposProducto' => TipoProducto::query()->orderBy('nombre')->get(['id', 'nombre', 'slug']),
            'secciones' => Seccion::query()->where('activa', true)->orderBy('nombre')->pluck('nombre'),
            'tipoProductoId' => $tipoProductoId,
            'seccionesSeleccionadas' => $seccionesSeleccionadas,
        ]);
    }

    protected function baseQuery(?int $tipoProductoId = null, array $seccionesSeleccionadas = [])
    {
        return Producto::query()
            ->when(
                $tipoProductoId,
                fn ($query) => $query->where('tipo_producto_id', $tipoProductoId),
                fn ($query) => $query->whereHas('tipoProducto', fn ($tipoQuery) => $tipoQuery->where('slug', 'tipo_carrusel'))
            )
            ->when(
                $seccionesSeleccionadas !== [],
                fn ($query) => $query->whereIn('seccion', $seccionesSeleccionadas)
            );
    }

    protected function chartPorDia(?int $tipoProductoId = null, array $seccionesSeleccionadas = []): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = $this->baseQuery($tipoProductoId, $seccionesSeleccionadas)
                ->whereDate('created_at', $date->toDateString())
                ->count();
        }

        return compact('labels', 'data');
    }

    protected function chartPorSemana(?int $tipoProductoId = null, array $seccionesSeleccionadas = []): array
    {
        $labels = [];
        $data = [];

        for ($i = 3; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $end = (clone $start)->endOfWeek();
            $labels[] = 'Sem '.($start->weekOfYear);
            $data[] = $this->baseQuery($tipoProductoId, $seccionesSeleccionadas)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return compact('labels', 'data');
    }

    protected function categorias(?int $tipoProductoId = null, array $seccionesSeleccionadas = []): array
    {
        $rows = $this->baseQuery($tipoProductoId, $seccionesSeleccionadas)
            ->selectRaw('COALESCE(seccion, ?) as seccion, COUNT(*) as total', ['Sin sección'])
            ->groupBy('seccion')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('seccion')->values()->all(),
            'data' => $rows->pluck('total')->map(fn ($value) => (int) $value)->values()->all(),
            'table' => $rows->map(fn ($row) => [
                'seccion' => $row->seccion,
                'total' => (int) $row->total,
            ])->values()->all(),
        ];
    }

    protected function minutesBetweenActions(Producto $producto, string $fromAction, string $toAction): ?int
    {
        $from = $producto->movimientos->firstWhere('accion', $fromAction)?->created_at;
        $to = $producto->movimientos->firstWhere('accion', $toAction)?->created_at;

        return $this->diffMinutes($from, $to);
    }

    protected function minutesFromActionToEnd(Producto $producto, string $fromAction): ?int
    {
        $from = $producto->movimientos->firstWhere('accion', $fromAction)?->created_at;
        $to = $producto->movimientos
            ->first(fn ($movimiento) => in_array($movimiento->accion, ['FINALIZADO', 'APROBADO'], true))
            ?->created_at ?? $producto->updated_at;

        return $this->diffMinutes($from, $to);
    }

    protected function tiempoDiseno(Producto $producto): string
    {
        return $this->formatDuration($this->minutesFromActionToEnd($producto, 'ASIGNADO_DISENADOR'));
    }

    protected function diffMinutes($from, $to): ?int
    {
        if (! $from || ! $to) {
            return null;
        }

        $from = $from instanceof Carbon ? $from : Carbon::parse($from);
        $to = $to instanceof Carbon ? $to : Carbon::parse($to);

        return $from->diffInMinutes($to);
    }

    protected function averageMinutes(Collection $minutes): ?int
    {
        $valid = $minutes->filter(fn ($value) => is_numeric($value));

        if ($valid->isEmpty()) {
            return null;
        }

        return (int) round($valid->avg());
    }

    protected function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return "{$remainingMinutes}m";
        }

        if ($remainingMinutes === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$remainingMinutes}m";
    }
}
