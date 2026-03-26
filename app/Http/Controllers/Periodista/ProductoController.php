<?php

namespace App\Http\Controllers\Periodista;

use App\Http\Controllers\Controller;
use App\Models\CarruselLamina;
use App\Models\CarruselLaminaArchivo;
use App\Models\CarruselMensaje;
use App\Models\CarruselMovimiento;
use App\Models\Producto;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $q = trim((string) $request->string('q'));
        $seccionFiltro = $request->string('seccion')->toString();
        $periodistaFiltro = $request->string('periodista')->toString();
        $disenadorFiltro = $request->string('disenador')->toString();
        $estado = $request->string('estado')->toString();
        $fecha = $request->string('fecha')->toString();

        $productos = Producto::query()
            ->where(fn (Builder $query) => $this->scopeOrigenVisible($query))
            ->where(fn (Builder $query) => $this->scopeProductosAccesibles($query, $user))
            ->with(['user:id,name', 'disenador:id,name'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($innerQuery) use ($q): void {
                    $innerQuery
                        ->where('titulo', 'like', "%{$q}%")
                        ->orWhere('copy', 'like', "%{$q}%")
                        ->orWhere('hashtags', 'like', "%{$q}%");
                });
            })
            ->when($seccionFiltro !== '', fn ($query) => $query->where('seccion', $seccionFiltro))
            ->when($periodistaFiltro !== '', fn ($query) => $query->where('user_id', $periodistaFiltro))
            ->when($disenadorFiltro !== '', fn ($query) => $query->where('disenador_id', $disenadorFiltro))
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($fecha !== '', fn ($query) => $query->whereDate('created_at', $fecha))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view($this->viewPath($request, 'index'), [
            'productos' => $productos,
            'q' => $q,
            'seccionFiltro' => $seccionFiltro,
            'periodistaFiltro' => $periodistaFiltro,
            'disenadorFiltro' => $disenadorFiltro,
            'estado' => $estado,
            'fecha' => $fecha,
            'secciones' => Seccion::query()->where('activa', true)->orderBy('nombre')->get(),
            'periodistas' => $this->periodistasDisponibles($user),
            'disenadores' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', 'disenador'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'estados' => Producto::query()
                ->where(fn (Builder $query) => $this->scopeOrigenVisible($query))
                ->where(fn (Builder $query) => $this->scopeProductosAccesibles($query, $user))
                ->select('estado')
                ->distinct()
                ->orderBy('estado')
                ->pluck('estado'),
            'routeBase' => $this->routeBaseFromRequest($request),
            'canFilterPeriodista' => $this->puedeVerTodosLosProductos($user),
        ]);
    }

    public function create(): View
    {
        return view($this->viewPath(request(), 'create'));
    }

    public function edit(Producto $producto): View
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto(request()->user(), $producto), 403);

        $producto->load([
            'tipoProducto:id,nombre,slug',
            'user:id,name',
            'laminas.archivos',
            'mensajes.autor:id,name',
            'movimientos.user:id,name',
        ]);

        return view($this->viewPath(request(), 'edit'), [
            'producto' => $producto,
            'secciones' => $this->activeSections(),
            'prioridades' => $this->prioridades(),
            'laminasData' => $this->laminasFormData($producto),
            'movimientos' => $producto->movimientos,
            'mensajes' => $producto->mensajes()->with('autor:id,name')->orderBy('id')->get(),
            'canEditLaminas' => $producto->esCarrusel(),
            'readOnly' => ! $this->puedeEditarProducto($producto),
            'routeBase' => $this->routeBaseFromRequest(request()),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);
        abort_unless($this->puedeEditarProducto($producto), 403);

        $producto->load('tipoProducto', 'laminas.archivos');

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'seccion' => [
                'required',
                'string',
                Rule::exists('secciones', 'nombre')->where('activa', true),
            ],
            'prioridad' => ['required', 'string', Rule::in($this->prioridades())],
            'copy' => ['nullable', 'string'],
            'hashtags' => ['required', 'string', 'max:600'],
            'creditos' => ['nullable', 'string', 'max:600'],
            'canva_url' => ['nullable', 'url', 'max:600'],
            'accion' => ['required', Rule::in(array_keys($this->accionesDisponibles()))],
            'motivo' => ['nullable', 'string', 'max:600'],
            'laminas' => [$producto->esCarrusel() ? 'required' : 'nullable', 'array', 'min:1'],
            'laminas.*.id' => ['nullable', 'integer'],
            'laminas.*.titulo' => [$producto->esCarrusel() ? 'required' : 'nullable', 'string', 'max:200'],
            'laminas.*.descripcion' => [$producto->esCarrusel() ? 'required' : 'nullable', 'string', 'max:600'],
            'laminas.*.archivos' => ['nullable', 'array'],
            'laminas.*.archivos.*' => ['nullable', 'file', 'max:30720'],
            'laminas.*.delete_archivos' => ['nullable', 'array'],
            'laminas.*.delete_archivos.*' => ['nullable', 'integer'],
            'laminas.*.replace_archivos' => ['nullable', 'array'],
            'laminas.*.replace_archivos.*' => ['nullable', 'file', 'max:30720'],
            'laminas.*.url_externa' => ['nullable', 'url', 'max:600'],
            'laminas.*.sin_foto' => ['nullable', 'boolean'],
            'laminas.*.motivo' => ['nullable', 'string', 'max:255'],
        ]);

        if ($producto->esCarrusel()) {
            foreach (array_values($validated['laminas'] ?? []) as $index => $laminaData) {
                $laminaId = isset($laminaData['id']) ? (int) $laminaData['id'] : null;
                $lamina = $laminaId ? $producto->laminas->firstWhere('id', $laminaId) : null;
                $files = $request->file("laminas.{$index}.archivos", []);
                $replaceFiles = $request->file("laminas.{$index}.replace_archivos", []);
                $deleteIds = collect($laminaData['delete_archivos'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter();
                $maxFiles = $index === 0 ? 3 : 1;
                $existingCount = $lamina instanceof CarruselLamina ? $lamina->archivos->count() : 0;
                $remainingCount = max(0, $existingCount - $deleteIds->count());
                $finalCount = $remainingCount + count($files);

                if (count($files) > $maxFiles) {
                    return back()
                        ->withErrors([
                            "laminas.{$index}.archivos" => $index === 0
                                ? 'La portada permite hasta 3 archivos.'
                                : 'Cada lámina solo permite 1 archivo.',
                        ])
                        ->withInput();
                }

                if (count($replaceFiles) > $maxFiles) {
                    return back()
                        ->withErrors([
                            "laminas.{$index}.replace_archivos" => 'La cantidad de reemplazos no es válida para esta lámina.',
                        ])
                        ->withInput();
                }

                if ($finalCount > $maxFiles) {
                    return back()
                        ->withErrors([
                            "laminas.{$index}.archivos" => $index === 0
                                ? 'La portada no puede superar 3 archivos entre actuales y nuevos.'
                                : 'Esta lámina solo puede conservar o agregar 1 archivo.',
                        ])
                        ->withInput();
                }
            }
        }

        if ($validated['accion'] === 'finalizar' && empty($validated['canva_url'])) {
            return back()
                ->withErrors(['canva_url' => 'El enlace de Canva es obligatorio para finalizar.'])
                ->withInput();
        }

        if ($validated['accion'] === 'devolver_periodista' && blank($validated['motivo'] ?? null)) {
            return back()
                ->withErrors(['motivo' => 'Debes indicar el motivo para devolver al periodista.'])
                ->withInput();
        }

        $estadoAnterior = $producto->estado;
        $estadoNuevo = $this->accionesDisponibles()[$validated['accion']] === '__KEEP_STATE__'
            ? $producto->estado
            : $this->accionesDisponibles()[$validated['accion']];

        $producto->fill([
            'titulo' => $validated['titulo'],
            'seccion' => $validated['seccion'],
            'prioridad' => $validated['prioridad'],
            'copy' => $validated['copy'] ?? null,
            'hashtags' => $validated['hashtags'],
            'creditos' => $validated['creditos'] ?? null,
            'canva_url' => $validated['canva_url'] ?? null,
            'estado' => $estadoNuevo,
        ]);
        $producto->save();

        if ($producto->esCarrusel()) {
            $this->syncLaminas($request, $producto, $validated['laminas'] ?? []);
        }

        $this->registrarMovimiento(
            producto: $producto,
            accion: match ($validated['accion']) {
                'guardar' => 'EDITADO',
                'revision' => $estadoNuevo === 'EN_DISENO' ? 'ENVIADO_DISENO' : 'ENVIADO_REVISION',
                'devolver_periodista' => 'DEVUELTO_PERIODISTA',
                default => 'FINALIZADO',
            },
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $estadoNuevo,
            motivo: $validated['accion'] === 'devolver_periodista'
                ? ($validated['motivo'] ?? null)
                : null,
            meta: [
                'tipo_producto' => $producto->tipoProducto?->slug,
            ],
        );

        return redirect()
            ->route($this->routeBaseFromRequest($request).'.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function autosave(Request $request, Producto $producto): JsonResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);
        abort_unless($this->puedeEditarProducto($producto), 403);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'seccion' => [
                'required',
                'string',
                Rule::exists('secciones', 'nombre')->where('activa', true),
            ],
            'prioridad' => ['required', 'string', Rule::in($this->prioridades())],
            'copy' => ['nullable', 'string'],
            'hashtags' => ['nullable', 'string', 'max:600'],
            'creditos' => ['nullable', 'string', 'max:600'],
            'laminas' => ['nullable', 'array'],
            'laminas.*.id' => ['nullable', 'integer'],
            'laminas.*.titulo' => ['nullable', 'string', 'max:200'],
            'laminas.*.descripcion' => ['nullable', 'string', 'max:600'],
        ]);

        $producto->fill([
            'titulo' => $validated['titulo'],
            'seccion' => $validated['seccion'],
            'prioridad' => $validated['prioridad'],
            'copy' => $validated['copy'] ?? null,
            'hashtags' => $validated['hashtags'] ?? null,
            'creditos' => $validated['creditos'] ?? null,
        ]);
        $producto->save();

        if ($producto->esCarrusel() && !empty($validated['laminas']) && is_array($validated['laminas'])) {
            $this->autosaveLaminas($producto, $validated['laminas']);
        }

        return response()->json([
            'ok' => true,
            'updated_at' => optional($producto->updated_at)->toISOString(),
            'message' => 'Guardado automáticamente.',
        ]);
    }

    public function storeMessage(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);

        $validated = $request->validate([
            'mensaje' => ['required', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'reply_to_id' => ['nullable', 'integer', 'exists:carrusel_mensajes,id'],
        ]);

        $mensaje = $producto->mensajes()->create([
            'user_id' => $request->user()?->id,
            'reply_to_id' => $validated['reply_to_id'] ?? null,
            'tipo' => Str::upper($validated['tipo']),
            'mensaje' => $validated['mensaje'],
        ]);

        $this->registrarMovimiento(
            producto: $producto,
            accion: 'COMENTARIO',
            estadoAnterior: $producto->estado,
            estadoNuevo: $producto->estado,
            motivo: Str::limit($mensaje->mensaje, 200),
            meta: [
                'mensaje_id' => $mensaje->id,
                'tipo' => $mensaje->tipo,
            ],
        );

        return redirect()
            ->route($this->routeBaseFromRequest($request).'.edit', $producto)
            ->with('success', 'Mensaje enviado correctamente.');
    }

    public function approve(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless($this->puedeEntrarAlEditor($producto), 404);
        abort_unless($this->puedeGestionarProducto($request->user(), $producto), 403);
        abort_unless($this->puedeAprobarProducto($producto), 403);

        $validated = $request->validate([
            'canva_url' => ['required', 'url', 'max:600'],
        ]);

        $estadoAnterior = $producto->estado;

        $producto->update([
            'canva_url' => $validated['canva_url'],
            'estado' => 'APROBADO',
        ]);

        $this->registrarMovimiento(
            producto: $producto,
            accion: 'APROBADO',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: 'APROBADO',
            motivo: 'Producto aprobado desde el listado.',
            meta: [
                'canva_url' => $validated['canva_url'],
                'tipo_producto' => $producto->tipoProducto?->slug,
            ],
        );

        return redirect()
            ->route($this->routeBaseFromRequest($request).'.index')
            ->with('success', 'Producto aprobado correctamente.');
    }

    /**
     * @return Collection<int, Seccion>
     */
    protected function activeSections(): Collection
    {
        return Seccion::query()
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @return list<string>
     */
    protected function prioridades(): array
    {
        return ['Urgente', 'Día', 'Semana', 'Mes'];
    }

    /**
     * @return array<string, string>
     */
    protected function accionesDisponibles(): array
    {
        $producto = request()->route('producto');
        $revisionTarget = $producto instanceof Producto
            && $producto->estado === 'EN_REVISION'
            ? 'EN_DISENO'
            : 'EN_REVISION';

        return [
            'guardar' => '__KEEP_STATE__',
            'revision' => $revisionTarget,
            'devolver_periodista' => 'DEVUELTO',
            'finalizar' => 'FINALIZADO',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function laminasFormData(Producto $producto): array
    {
        $laminas = $producto->laminas
            ->map(function (CarruselLamina $lamina): array {
                return [
                    'id' => $lamina->id,
                    'titulo' => $lamina->titulo ?? '',
                    'descripcion' => $lamina->descripcion ?? '',
                    'url_externa' => $lamina->url_externa ?? '',
                    'sin_foto' => (bool) $lamina->sin_foto,
                    'motivo' => $lamina->motivo ?? '',
                    'archivos' => $lamina->archivos->map(function (CarruselLaminaArchivo $archivo): array {
                        return [
                            'id' => $archivo->id,
                            'archivo_path' => $archivo->archivo_path,
                            'archivo_original' => $archivo->archivo_original,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        if ($laminas !== []) {
            return $laminas;
        }

        return [[
            'titulo' => '',
            'descripcion' => '',
            'url_externa' => '',
            'sin_foto' => false,
            'motivo' => '',
            'archivos' => [],
        ]];
    }

    /**
     * @param  list<array<string, mixed>>  $laminas
     */
    protected function syncLaminas(Request $request, Producto $producto, array $laminas): void
    {
        $existing = $producto->laminas->keyBy('id');
        $keptIds = [];

        foreach (array_values($laminas) as $index => $laminaData) {
            $laminaId = isset($laminaData['id']) ? (int) $laminaData['id'] : null;
            $lamina = $laminaId ? $existing->get($laminaId) : new CarruselLamina(['carrusel_id' => $producto->id]);

            if (! $lamina instanceof CarruselLamina) {
                continue;
            }

            $lamina->fill([
                'orden' => $index + 1,
                'titulo' => $laminaData['titulo'] ?? null,
                'descripcion' => $laminaData['descripcion'] ?? null,
                'url_externa' => $laminaData['url_externa'] ?? null,
                'sin_foto' => (bool) ($laminaData['sin_foto'] ?? false),
                'motivo' => $laminaData['motivo'] ?? null,
            ]);

            $lamina->carrusel_id = $producto->id;
            $lamina->save();
            $this->syncLaminaArchivos($request, $producto, $lamina, $index);
            $keptIds[] = $lamina->id;
        }

        $producto->laminas()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function (CarruselLamina $lamina): void {
                $this->deleteLaminaFiles($lamina);

                $lamina->delete();
            });
    }

    /**
     * @param  list<array<string, mixed>>  $laminas
     */
    protected function autosaveLaminas(Producto $producto, array $laminas): void
    {
        $existing = $producto->laminas()->get()->keyBy('id');

        foreach (array_values($laminas) as $index => $laminaData) {
            $titulo = trim((string) ($laminaData['titulo'] ?? ''));
            $descripcion = trim((string) ($laminaData['descripcion'] ?? ''));

            if ($titulo === '' && $descripcion === '') {
                continue;
            }

            $laminaId = isset($laminaData['id']) ? (int) $laminaData['id'] : null;
            $lamina = $laminaId ? $existing->get($laminaId) : null;

            if (! $lamina instanceof CarruselLamina) {
                $lamina = new CarruselLamina([
                    'carrusel_id' => $producto->id,
                ]);
            }

            $lamina->fill([
                'orden' => $index + 1,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
            ]);
            $lamina->carrusel_id = $producto->id;
            $lamina->save();
        }
    }

    protected function syncLaminaArchivos(Request $request, Producto $producto, CarruselLamina $lamina, int $index): void
    {
        $files = $request->file("laminas.{$index}.archivos", []);
        $replaceFiles = $request->file("laminas.{$index}.replace_archivos", []);
        $deleteIds = collect($request->input("laminas.{$index}.delete_archivos", []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $lamina->loadMissing('archivos');

        if ($deleteIds !== []) {
            $lamina->archivos
                ->whereIn('id', $deleteIds)
                ->each(function (CarruselLaminaArchivo $archivo): void {
                    Storage::disk('public')->delete($archivo->archivo_path);
                    $archivo->delete();
                });
        }

        if (is_array($replaceFiles) && $replaceFiles !== []) {
            foreach ($replaceFiles as $archivoId => $file) {
                $archivo = $lamina->archivos->firstWhere('id', (int) $archivoId);

                if (! $archivo instanceof CarruselLaminaArchivo) {
                    continue;
                }

                Storage::disk('public')->delete($archivo->archivo_path);

                $storedPath = $file->store("productos/{$producto->id}/laminas", 'public');
                $archivo->update([
                    'archivo_path' => $storedPath,
                    'archivo_original' => $file->getClientOriginalName(),
                    'archivo_mime' => $file->getMimeType(),
                    'archivo_size' => $file->getSize(),
                ]);
            }
        }

        $nextOrder = (int) $lamina->archivos()->max('orden');

        foreach (array_values($files) as $file) {
            $storedPath = $file->store("productos/{$producto->id}/laminas", 'public');

            $lamina->archivos()->create([
                'orden' => ++$nextOrder,
                'archivo_path' => $storedPath,
                'archivo_original' => $file->getClientOriginalName(),
                'archivo_mime' => $file->getMimeType(),
                'archivo_size' => $file->getSize(),
            ]);
        }

        $firstFile = $lamina->archivos()->orderBy('orden')->first();

        $lamina->forceFill([
            'archivo_path' => $firstFile?->archivo_path,
            'archivo_original' => $firstFile?->archivo_original,
            'archivo_mime' => $firstFile?->archivo_mime,
            'archivo_size' => $firstFile?->archivo_size,
        ])->save();
    }

    protected function deleteLaminaFiles(CarruselLamina $lamina): void
    {
        $lamina->loadMissing('archivos');

        $lamina->archivos->each(function (CarruselLaminaArchivo $archivo): void {
            Storage::disk('public')->delete($archivo->archivo_path);
            $archivo->delete();
        });

        if ($lamina->archivo_path) {
            Storage::disk('public')->delete($lamina->archivo_path);
        }

        $lamina->forceFill([
            'archivo_path' => null,
            'archivo_original' => null,
            'archivo_mime' => null,
            'archivo_size' => null,
        ])->save();
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    protected function registrarMovimiento(
        Producto $producto,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $motivo,
        ?array $meta = null,
    ): void {
        $producto->movimientos()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'meta' => $meta,
        ]);
    }

    protected function puedeEntrarAlEditor(Producto $producto): bool
    {
        return in_array($producto->origen, ['pauta', 'comercial'], true);
    }

    protected function puedeEditarProducto(Producto $producto): bool
    {
        $user = request()->user();

        if ($user?->hasAnyRole(['editor', 'director'])) {
            return in_array($producto->estado, ['BORRADOR', 'EN_REVISION', 'DEVUELTO'], true);
        }

        if ($user?->hasAnyRole(['disenador', 'disenador_manager'])) {
            return $producto->estado === 'EN_DISENO';
        }

        return in_array($producto->estado, ['BORRADOR', 'DEVUELTO'], true);
    }

    protected function puedeGestionarProducto(?User $user, Producto $producto): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->puedeVerTodosLosProductos($user)) {
            return true;
        }

        return (int) $producto->user_id === (int) $user->id;
    }

    protected function puedeAprobarProducto(Producto $producto): bool
    {
        $user = request()->user();

        return ($user?->hasAnyRole(['editor', 'director']) ?? false)
            && $producto->estado === 'FINALIZADO';
    }

    protected function puedeVerTodosLosProductos(?User $user): bool
    {
        return $user?->hasAnyRole(['editor', 'director', 'disenador', 'disenador_manager']) ?? false;
    }

    /**
     * @return Collection<int, User>
     */
    protected function periodistasDisponibles(?User $user): Collection
    {
        $query = User::query()
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['periodista', 'editor']))
            ->orderBy('name');

        if (! $this->puedeVerTodosLosProductos($user) && $user) {
            $query->where('id', $user->id);
        }

        return $query->get(['id', 'name']);
    }

    protected function scopeOrigenVisible(Builder $query): void
    {
        $query->whereIn('origen', ['pauta', 'comercial']);
    }

    protected function scopeProductosAccesibles(Builder $query, ?User $user): void
    {
        if (! $this->puedeVerTodosLosProductos($user) && $user) {
            $query->where('user_id', $user->id);
        }
    }

    protected function routeBaseFromRequest(Request $request): string
    {
        $routeName = (string) optional($request->route())->getName();
        $user = $request->user();

        if (str_starts_with($routeName, 'editor.')) {
            return 'editor.productos';
        }

        if (str_starts_with($routeName, 'director.')) {
            return 'director.productos';
        }

        if (str_starts_with($routeName, 'disenador.')) {
            return 'disenador.productos';
        }

        if (str_starts_with($routeName, 'manager.')) {
            return 'manager.productos';
        }

        if ($user?->hasRole('director')) {
            return 'director.productos';
        }

        if ($user?->hasRole('editor')) {
            return 'editor.productos';
        }

        if ($user?->hasRole('disenador_manager')) {
            return 'manager.productos';
        }

        if ($user?->hasRole('disenador')) {
            return 'disenador.productos';
        }

        return 'periodista.productos';
    }

    protected function viewPath(Request $request, string $view): string
    {
        $routeBase = $this->routeBaseFromRequest($request);

        return match ($routeBase) {
            'editor.productos' => "editor.productos.{$view}",
            'director.productos' => "director.productos.{$view}",
            'disenador.productos' => "disenador.productos.{$view}",
            'manager.productos' => "manager.productos.{$view}",
            default => "periodista.productos.{$view}",
        };
    }
}
