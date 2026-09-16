<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Models\InformeLectura;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InformeController extends Controller
{
    public function index(): View
    {
        $informes = Informe::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('informes.index', compact('informes'));
    }

    public function create(): View
    {
        return view('informes.create');
    }

    public function reportes(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'titulo' => trim((string) $request->query('titulo', '')),
            'periodista_id' => (int) $request->query('periodista_id', 0),
            'disenador_id' => (int) $request->query('disenador_id', 0),
            'fecha' => trim((string) $request->query('fecha', '')),
        ];

        $dashboardInformes = Informe::query()
            ->with(['lecturas.user'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function (Informe $informe): array {
                $lecturasInforme = $informe->lecturas
                    ->sortByDesc('total_seconds')
                    ->values();
                $topViewers = $lecturasInforme
                    ->take(10)
                    ->map(fn (InformeLectura $lectura): array => [
                        'name' => $lectura->user?->name ?? 'Usuario eliminado',
                        'seconds' => (int) $lectura->total_seconds,
                        'aperturas' => (int) $lectura->aperturas,
                    ])
                    ->values();
                $totalSeconds = (int) $lecturasInforme->sum('total_seconds');
                $topSeconds = (int) $topViewers->sum('seconds');
                $chartSegments = $topViewers->filter(fn (array $viewer): bool => $viewer['seconds'] > 0)->values();

                if ($totalSeconds > $topSeconds) {
                    $chartSegments->push([
                        'name' => 'Otros',
                        'seconds' => $totalSeconds - $topSeconds,
                        'aperturas' => 0,
                    ]);
                }

                return [
                    'id' => $informe->id,
                    'nombre' => $informe->nombre,
                    'created_at' => $informe->created_at?->format('d/m/Y'),
                    'total_seconds' => $totalSeconds,
                    'top_viewers' => $topViewers->all(),
                    'chart_segments' => $chartSegments->all(),
                ];
            });

        $lecturas = InformeLectura::query()
            ->with(['informe', 'user.roles'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $query->where(function ($inner) use ($filters): void {
                    $inner
                        ->whereHas('informe', fn ($informe) => $informe->where('nombre', 'like', '%'.$filters['q'].'%'))
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when($filters['titulo'] !== '', function ($query) use ($filters): void {
                $query->whereHas('informe', fn ($informe) => $informe->where('nombre', 'like', '%'.$filters['titulo'].'%'));
            })
            ->when($filters['periodista_id'] > 0, fn ($query) => $query->where('user_id', $filters['periodista_id']))
            ->when($filters['disenador_id'] > 0, fn ($query) => $query->where('user_id', $filters['disenador_id']))
            ->when($filters['fecha'] !== '', fn ($query) => $query->whereDate('last_seen_at', $filters['fecha']))
            ->orderByDesc('last_seen_at')
            ->paginate(20)
            ->withQueryString();

        $periodistas = User::role('periodista')
            ->where('activo', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $disenadores = User::role('disenador')
            ->where('activo', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('informes.reportes', compact('lecturas', 'filters', 'periodistas', 'disenadores', 'dashboardInformes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'archivo' => ['required', 'file', 'max:10240'],
        ]);

        $archivo = $request->file('archivo');
        $extension = strtolower((string) $archivo->getClientOriginalExtension());

        if (! in_array($extension, ['html', 'htm'], true)) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo debe tener extension .html o .htm.',
            ]);
        }

        $path = $archivo->storeAs(
            'informes',
            Str::uuid()->toString().'.'.$extension,
            'local'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'archivo' => 'No se pudo guardar el archivo. Intenta nuevamente.',
            ]);
        }

        $informe = Informe::create([
            'user_id' => $request->user()->id,
            'nombre' => $validated['nombre'],
            'archivo_original' => $archivo->getClientOriginalName(),
            'archivo_path' => $path,
            'mime_type' => $archivo->getClientMimeType(),
            'size' => $archivo->getSize(),
        ]);

        return redirect()
            ->route('informes.index')
            ->with('status', 'Informe subido correctamente.')
            ->with('uploaded_url', route('informes.show', $informe))
            ->with('uploaded_name', $informe->nombre);
    }

    public function destroy(Informe $informe): RedirectResponse
    {
        if (Storage::disk('local')->exists($informe->archivo_path)) {
            Storage::disk('local')->delete($informe->archivo_path);
        }

        $informe->delete();

        return redirect()
            ->route('informes.index')
            ->with('status', 'Informe eliminado correctamente.');
    }

    public function show(Informe $informe): View
    {
        abort_unless(Storage::disk('local')->exists($informe->archivo_path), 404);

        $lectura = InformeLectura::firstOrCreate(
            [
                'informe_id' => $informe->id,
                'user_id' => auth()->id(),
            ],
            [
                'aperturas' => 0,
                'total_seconds' => 0,
                'last_ip' => request()->ip(),
            ]
        );

        $lectura->increment('aperturas', 1, [
            'last_ip' => request()->ip(),
            'last_opened_at' => now(),
            'last_seen_at' => now(),
        ]);

        $html = Storage::disk('local')->get($informe->archivo_path);

        return view('informes.show', compact('informe', 'html'));
    }

    public function track(Request $request, Informe $informe): Response
    {
        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $lectura = InformeLectura::firstOrCreate(
            [
                'informe_id' => $informe->id,
                'user_id' => $request->user()->id,
            ],
            [
                'aperturas' => 0,
                'total_seconds' => 0,
                'last_ip' => $request->ip(),
                'last_opened_at' => now(),
            ]
        );

        $lectura->increment('total_seconds', (int) $validated['seconds'], [
            'last_ip' => $request->ip(),
            'last_seen_at' => now(),
        ]);

        return response()->noContent();
    }
}
