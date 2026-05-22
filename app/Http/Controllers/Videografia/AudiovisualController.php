<?php

namespace App\Http\Controllers\Videografia;

use App\Http\Controllers\Controller;
use App\Models\Audiovisual;
use App\Models\AudiovisualEdicion;
use App\Models\AudiovisualGrabacion;
use App\Models\AudiovisualGrabacionEdicion;
use App\Models\Seccion;
use App\Models\TipoAudiovisual;
use App\Models\User;
use App\Services\SlackFileUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AudiovisualController extends Controller
{
    /**
     * @return list<string>
     */
    protected function audiovisualStaffRoles(): array
    {
        return ['videografia', 'video_manager'];
    }

    protected function buildFormViewData(Audiovisual $audiovisual): array
    {
        $audiovisual->loadMissing([
            'user:id,name',
            'editor:id,name',
            'disenador:id,name',
            'tipoAudiovisual:id,nombre,slug',
            'edicionDetalle',
            'grabacionDetalle',
            'grabacionEdicionDetalle',
            'requerimientos',
            'redesSociales',
            'mensajes.autor:id,name',
            'movimientos.user:id,name',
        ]);

        return [
            'audiovisual' => $audiovisual,
            'tiposAudiovisuales' => TipoAudiovisual::query()->where('estado', 'activo')->orderBy('id')->get(['id', 'nombre', 'slug']),
            'secciones' => Seccion::query()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
            'prioridades' => $this->prioridades(),
            'videografos' => User::query()
                ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $this->audiovisualStaffRoles()))
                ->orderBy('name')
                ->get(['id', 'name']),
            'productosDigitales' => $this->productosDigitalesDisponibles(),
            'requerimientosDisponibles' => $this->requerimientosDisponibles(),
            'redesDisponibles' => $this->redesDisponibles(),
            'mensajes' => $audiovisual->exists
                ? $audiovisual->mensajes()->with('autor:id,name')->orderBy('id')->get()
                : collect(),
            'movimientos' => $audiovisual->exists ? $audiovisual->movimientos : collect(),
            'isCreate' => ! $audiovisual->exists,
        ];
    }

    /**
     * @return list<string>
     */
    protected function requerimientosDisponibles(): array
    {
        return ['Fotos', 'Video', 'Edición', 'Live', 'Podcast'];
    }

    /**
     * @return list<string>
     */
    protected function redesDisponibles(): array
    {
        return ['Instagram', 'Facebook', 'TikTok', 'YouTube', 'WhatsApp', 'X'];
    }

    /**
     * @return list<string>
     */
    protected function productosDigitalesDisponibles(): array
    {
        return [
            'Reporte Callejero',
            'Los Vargas',
            'El Sillón',
            'Garabot',
            'Vox Populi',
            'El Señor del sombrero',
            'Resulta pasa y acontece',
            'Animalitos',
            'Reportajes',
            'La Pizzara',
            'Video Noticias',
            'Debate',
            'otro',
        ];
    }

    /**
     * @return list<string>
     */
    protected function prioridades(): array
    {
        return ['Urgente', 'Día', 'Semana', 'Mes'];
    }

    protected function canSendToRevision(?User $user): bool
    {
        return $user?->hasAnyRole(['videografia', 'video_manager']) ?? false;
    }

    protected function canAssignFromRevision(?User $user): bool
    {
        return $user?->hasRole('video_manager') ?? false;
    }

    protected function canFinalizeAssigned(?User $user): bool
    {
        return $user?->hasAnyRole(['videografia', 'video_manager']) ?? false;
    }

    protected function resolveWorkflowAction(Request $request): string
    {
        $action = (string) $request->input('workflow_action', 'save_draft');

        return in_array($action, ['save_draft', 'send_revision', 'assign', 'finalize'], true)
            ? $action
            : 'save_draft';
    }

    /**
     * @return array{estado:string, accion:string, motivo:string}
     */
    protected function resolveWorkflowTransition(
        Request $request,
        Audiovisual $audiovisual,
        string $workflowAction,
        ?int $videografoId,
        ?string $canvaUrl,
        bool $hasUploadedFinalFile,
        bool $hasExistingSlackFile,
    ): array {
        $user = $request->user();
        $estadoActual = (string) ($audiovisual->estado ?: 'BORRADOR');

        if (! $audiovisual->exists && $workflowAction !== 'save_draft') {
            throw ValidationException::withMessages([
                'workflow_action' => 'Primero guarda el audiovisual en borrador antes de cambiar su estado.',
            ]);
        }

        return match ($workflowAction) {
            'send_revision' => $this->transitionToRevision($user, $estadoActual),
            'assign' => $this->transitionToAssigned($user, $estadoActual, $videografoId),
            'finalize' => $this->transitionToFinalized($user, $estadoActual, $canvaUrl, $hasUploadedFinalFile, $hasExistingSlackFile),
            default => [
                'estado' => $audiovisual->exists ? $estadoActual : 'BORRADOR',
                'accion' => $audiovisual->exists ? 'EDITADO' : 'CREADO',
                'motivo' => $audiovisual->exists
                    ? 'Audiovisual actualizado desde el editor.'
                    : 'Audiovisual creado.',
            ],
        };
    }

    /**
     * @return array{estado:string, accion:string, motivo:string}
     */
    protected function transitionToRevision(?User $user, string $estadoActual): array
    {
        if (! $this->canSendToRevision($user)) {
            throw ValidationException::withMessages([
                'workflow_action' => 'No tienes permisos para enviar este audiovisual a revisión.',
            ]);
        }

        if ($estadoActual !== 'BORRADOR') {
            throw ValidationException::withMessages([
                'workflow_action' => 'Solo los audiovisuales en borrador pueden enviarse a revisión.',
            ]);
        }

        return [
            'estado' => 'EN_REVISION',
            'accion' => 'ENVIADO_REVISION',
            'motivo' => 'Audiovisual enviado a revisión.',
        ];
    }

    /**
     * @return array{estado:string, accion:string, motivo:string}
     */
    protected function transitionToAssigned(?User $user, string $estadoActual, ?int $videografoId): array
    {
        if (! $this->canAssignFromRevision($user)) {
            throw ValidationException::withMessages([
                'workflow_action' => 'Solo un video manager puede asignar este audiovisual.',
            ]);
        }

        if ($estadoActual !== 'EN_REVISION') {
            throw ValidationException::withMessages([
                'workflow_action' => 'Solo los audiovisuales en revisión pueden pasar a asignado.',
            ]);
        }

        if (! $videografoId) {
            throw ValidationException::withMessages([
                'videografo' => 'Debes asignar un videógrafo antes de pasar a ASIGNADO.',
            ]);
        }

        return [
            'estado' => 'ASIGNADO',
            'accion' => 'ASIGNADO',
            'motivo' => 'Audiovisual asignado a videógrafo.',
        ];
    }

    /**
     * @return array{estado:string, accion:string, motivo:string}
     */
    protected function transitionToFinalized(
        ?User $user,
        string $estadoActual,
        ?string $canvaUrl,
        bool $hasUploadedFinalFile,
        bool $hasExistingSlackFile,
    ): array
    {
        if (! $this->canFinalizeAssigned($user)) {
            throw ValidationException::withMessages([
                'workflow_action' => 'No tienes permisos para finalizar este audiovisual.',
            ]);
        }

        if ($estadoActual !== 'ASIGNADO') {
            throw ValidationException::withMessages([
                'workflow_action' => 'Solo los audiovisuales asignados pueden pasar a FINALIZADO.',
            ]);
        }

        if (! filled($canvaUrl) && ! $hasUploadedFinalFile && ! $hasExistingSlackFile) {
            throw ValidationException::withMessages([
                'canva_url' => 'Debes pegar un enlace o subir un archivo antes de pasar a FINALIZADO.',
            ]);
        }

        return [
            'estado' => 'FINALIZADO',
            'accion' => 'FINALIZADO',
            'motivo' => 'Audiovisual finalizado con enlace adjunto.',
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $q = trim((string) $request->string('q'));
        $seccionFiltro = $request->string('seccion')->toString();
        $responsableFiltro = $request->string('responsable')->toString();
        $videografoFiltro = $request->string('videografo')->toString();
        $estado = $request->string('estado')->toString();
        $fecha = $request->string('fecha')->toString();

        $audiovisuales = Audiovisual::query()
            ->with(['user:id,name', 'editor:id,name', 'disenador:id,name'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($innerQuery) use ($q): void {
                    $innerQuery
                        ->where('titulo', 'like', "%{$q}%")
                        ->orWhere('copy', 'like', "%{$q}%")
                        ->orWhere('hashtags', 'like', "%{$q}%");
                });
            })
            ->when($seccionFiltro !== '', fn ($query) => $query->where('seccion', $seccionFiltro))
            ->when($responsableFiltro !== '', fn ($query) => $query->where('user_id', $responsableFiltro))
            ->when($videografoFiltro !== '', fn ($query) => $query->where('disenador_id', $videografoFiltro))
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($fecha !== '', fn ($query) => $query->whereDate('created_at', $fecha))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('videografia.audiovisuales.index', [
            'audiovisuales' => $audiovisuales,
            'q' => $q,
            'seccionFiltro' => $seccionFiltro,
            'responsableFiltro' => $responsableFiltro,
            'videografoFiltro' => $videografoFiltro,
            'estado' => $estado,
            'fecha' => $fecha,
            'secciones' => Seccion::query()->where('activa', true)->orderBy('nombre')->get(),
            'responsables' => User::query()
                ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $this->audiovisualStaffRoles()))
                ->orderBy('name')
                ->get(['id', 'name']),
            'videografos' => User::query()
                ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $this->audiovisualStaffRoles()))
                ->orderBy('name')
                ->get(['id', 'name']),
            'estados' => Audiovisual::query()
                ->select('estado')
                ->whereNotNull('estado')
                ->distinct()
                ->orderBy('estado')
                ->pluck('estado'),
            'routeBase' => 'videografia.audiovisuales',
            'canFilterResponsable' => $user?->hasAnyRole(['editor', 'director']) ?? false,
        ]);
    }

    public function create(): View
    {
        $audiovisual = new Audiovisual([
            'estado' => 'BORRADOR',
            'dificultad' => 'BASICO',
            'origen' => 'propuesta',
        ]);

        return view('videografia.audiovisuales.edit', $this->buildFormViewData($audiovisual));
    }

    public function edit(Audiovisual $audiovisual): View
    {
        return view('videografia.audiovisuales.edit', $this->buildFormViewData($audiovisual));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_audiovisual_id' => ['nullable', 'integer', 'exists:tipo_audiovisuales,id'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'seccion' => ['nullable', 'string', Rule::exists('secciones', 'nombre')->where('activa', true)],
            'prioridad' => ['nullable', 'string', Rule::in($this->prioridades())],
            'producto_digital' => ['nullable', 'string', Rule::in($this->productosDigitalesDisponibles())],
            'requerimiento' => ['nullable', 'array'],
            'requerimiento.*' => ['string', Rule::in($this->requerimientosDisponibles())],
            'entrevistador' => ['nullable', 'string', 'max:255'],
            'entrevistado' => ['nullable', 'string', 'max:255'],
            'contacto_cobertura' => ['nullable', 'string', 'max:255'],
            'red_social' => ['nullable', 'array'],
            'red_social.*' => ['string', Rule::in($this->redesDisponibles())],
            'videografo' => ['nullable', 'integer', 'exists:users,id'],
            'editor' => ['nullable', 'integer', 'exists:users,id'],
            'horario_grabacion' => ['nullable', 'date_format:H:i'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'brief' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'referencia' => ['nullable', 'string', 'max:600'],
            'canva_url' => ['nullable', 'string', 'max:600'],
            'archivo_final' => ['nullable', 'file', 'max:30720'],
            'hashtags' => ['nullable', 'string', 'max:600'],
            'creditos' => ['nullable', 'string', 'max:600'],
        ], [
            'titulo.required' => 'Debes ingresar un tema.',
            'brief.mimes' => 'El brief debe ser un archivo PDF o Word.',
            'brief.max' => 'El brief no puede superar los 10 MB.',
            'archivo_final.max' => 'El archivo final no puede superar los 30 MB.',
        ]);

        $tipoAudiovisual = $this->resolveTipoAudiovisual($validated['tipo_audiovisual_id'] ?? null);

        $audiovisual = DB::transaction(function () use ($request, $validated, $tipoAudiovisual): Audiovisual {
            $audiovisual = new Audiovisual();
            $tipoSlug = $tipoAudiovisual?->slug;

            $audiovisual->fill([
                'tipo_audiovisual_id' => $tipoAudiovisual?->id,
                'user_id' => $request->user()?->id,
                'titulo' => $validated['titulo'],
                'copy' => $validated['descripcion'] ?? null,
                'fecha' => $validated['fecha'] ?? null,
                'hora' => $validated['hora'] ?? null,
                'orden_dia' => filled($validated['hora'] ?? null)
                    ? ((int) substr($validated['hora'], 0, 2) * 100) + (int) substr($validated['hora'], 3, 2)
                    : null,
                'seccion' => $validated['seccion'] ?? null,
                'prioridad' => $validated['prioridad'] ?? null,
                'referencia' => $validated['referencia'] ?? null,
                'canva_url' => $validated['canva_url'] ?? null,
                'hashtags' => $validated['hashtags'] ?? null,
                'creditos' => $validated['creditos'] ?? null,
                'disenador_id' => $validated['videografo'] ?? null,
                'editor_id' => $validated['editor'] ?? null,
                'estado' => 'BORRADOR',
                'dificultad' => 'BASICO',
                'origen' => 'propuesta',
            ]);
            $audiovisual->save();

            $briefData = $this->storeBriefIfPresent($request, $audiovisual);
            $this->syncTipoDetalles($audiovisual, $validated, $tipoSlug, $briefData);
            $this->syncSimpleRows(
                $audiovisual,
                'requerimientos',
                in_array($tipoSlug, [TipoAudiovisual::SLUG_GRABACION, TipoAudiovisual::SLUG_GRABACION_EDICION], true)
                    ? ($validated['requerimiento'] ?? [])
                    : [],
            );
            $this->syncSimpleRows(
                $audiovisual,
                'redesSociales',
                in_array($tipoSlug, [TipoAudiovisual::SLUG_GRABACION, TipoAudiovisual::SLUG_GRABACION_EDICION], true)
                    ? ($validated['red_social'] ?? [])
                    : [],
            );

            $this->registrarMovimiento(
                $audiovisual,
                'CREADO',
                null,
                $audiovisual->estado,
                'Audiovisual creado.',
            );

            return $audiovisual;
        });

        return redirect()
            ->route('videografia.audiovisuales.edit', $audiovisual)
            ->with('success', 'Audiovisual creado correctamente.');
    }

    public function update(Request $request, Audiovisual $audiovisual, SlackFileUploader $slackFileUploader): RedirectResponse
    {
        $workflowAction = $this->resolveWorkflowAction($request);
        $validated = $request->validate([
            'tipo_audiovisual_id' => ['nullable', 'integer', 'exists:tipo_audiovisuales,id'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'seccion' => ['nullable', 'string', Rule::exists('secciones', 'nombre')->where('activa', true)],
            'prioridad' => ['nullable', 'string', Rule::in($this->prioridades())],
            'producto_digital' => ['nullable', 'string', Rule::in($this->productosDigitalesDisponibles())],
            'requerimiento' => ['nullable', 'array'],
            'requerimiento.*' => ['string', Rule::in($this->requerimientosDisponibles())],
            'entrevistador' => ['nullable', 'string', 'max:255'],
            'entrevistado' => ['nullable', 'string', 'max:255'],
            'contacto_cobertura' => ['nullable', 'string', 'max:255'],
            'red_social' => ['nullable', 'array'],
            'red_social.*' => ['string', Rule::in($this->redesDisponibles())],
            'videografo' => ['nullable', 'integer', 'exists:users,id'],
            'editor' => ['nullable', 'integer', 'exists:users,id'],
            'horario_grabacion' => ['nullable', 'date_format:H:i'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'brief' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'referencia' => ['nullable', 'string', 'max:600'],
            'canva_url' => ['nullable', 'string', 'max:600'],
            'archivo_final' => ['nullable', 'file', 'max:30720'],
            'hashtags' => ['nullable', 'string', 'max:600'],
            'creditos' => ['nullable', 'string', 'max:600'],
        ], [
            'titulo.required' => 'Debes ingresar un tema.',
            'brief.mimes' => 'El brief debe ser un archivo PDF o Word.',
            'brief.max' => 'El brief no puede superar los 10 MB.',
            'archivo_final.max' => 'El archivo final no puede superar los 30 MB.',
        ]);

        $tipoAudiovisual = $this->resolveTipoAudiovisual($validated['tipo_audiovisual_id'] ?? null);
        $transition = $this->resolveWorkflowTransition(
            request: $request,
            audiovisual: $audiovisual,
            workflowAction: $workflowAction,
            videografoId: isset($validated['videografo']) ? (int) $validated['videografo'] : (int) ($audiovisual->disenador_id ?? 0),
            canvaUrl: $validated['canva_url'] ?? $audiovisual->canva_url,
            hasUploadedFinalFile: $request->hasFile('archivo_final'),
            hasExistingSlackFile: filled($audiovisual->slack_file_id),
        );

        $slackFileData = null;

        if ($workflowAction === 'finalize' && $request->hasFile('archivo_final')) {
            try {
                $slackUpload = $slackFileUploader->upload(
                    $request->file('archivo_final'),
                    $validated['titulo'],
                    "Audiovisual finalizado: {$validated['titulo']}"
                );
            } catch (Throwable $exception) {
                Log::error('Audiovisual Slack upload failed', [
                    'message' => $exception->getMessage(),
                    'audiovisual_id' => $audiovisual->id,
                    'user_id' => $request->user()?->id,
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['archivo_final' => 'No se pudo subir el archivo a Slack. Revisa la configuración del bot y el canal.']);
            }

            $file = $request->file('archivo_final');
            $slackFileData = [
                'archivo_final_path' => null,
                'archivo_final_original_name' => $file->getClientOriginalName(),
                'archivo_final_mime' => $file->getClientMimeType(),
                'archivo_final_size' => (int) $file->getSize(),
                'slack_file_id' => $slackUpload['file_id'],
                'slack_permalink' => $slackUpload['permalink'],
                'slack_private_url' => $slackUpload['private_url'],
            ];
        }

        DB::transaction(function () use ($request, $audiovisual, $validated, $tipoAudiovisual, $transition, $slackFileData): void {
            $tipoSlug = $tipoAudiovisual?->slug;
            $estadoAnterior = $audiovisual->estado;

            $audiovisual->fill([
                'tipo_audiovisual_id' => $tipoAudiovisual?->id,
                'titulo' => $validated['titulo'],
                'copy' => $validated['descripcion'] ?? null,
                'fecha' => $validated['fecha'] ?? null,
                'hora' => $validated['hora'] ?? null,
                'orden_dia' => filled($validated['hora'] ?? null)
                    ? ((int) substr($validated['hora'], 0, 2) * 100) + (int) substr($validated['hora'], 3, 2)
                    : null,
                'seccion' => $validated['seccion'] ?? null,
                'prioridad' => $validated['prioridad'] ?? null,
                'referencia' => $validated['referencia'] ?? null,
                'canva_url' => $validated['canva_url'] ?? null,
                'hashtags' => $validated['hashtags'] ?? null,
                'creditos' => $validated['creditos'] ?? null,
                'disenador_id' => $validated['videografo'] ?? null,
                'editor_id' => $validated['editor'] ?? $audiovisual->editor_id,
                'estado' => $transition['estado'],
            ]);

            if ($transition['estado'] === 'ASIGNADO') {
                $audiovisual->manager_id = $request->user()?->id;
                $audiovisual->assigned_at = now();
            }

            $audiovisual->save();

            $briefData = $this->storeBriefIfPresent($request, $audiovisual);

            $this->syncTipoDetalles($audiovisual, $validated, $tipoSlug, $briefData);
            $this->syncSimpleRows(
                $audiovisual,
                'requerimientos',
                in_array($tipoSlug, [TipoAudiovisual::SLUG_GRABACION, TipoAudiovisual::SLUG_GRABACION_EDICION], true)
                    ? ($validated['requerimiento'] ?? [])
                    : [],
            );
            $this->syncSimpleRows(
                $audiovisual,
                'redesSociales',
                in_array($tipoSlug, [TipoAudiovisual::SLUG_GRABACION, TipoAudiovisual::SLUG_GRABACION_EDICION], true)
                    ? ($validated['red_social'] ?? [])
                    : [],
            );

            if ($slackFileData) {
                $audiovisual->forceFill($slackFileData)->save();
            }

            $this->registrarMovimiento(
                $audiovisual,
                $transition['accion'],
                $estadoAnterior,
                $audiovisual->estado,
                $transition['motivo'],
            );
        });

        return redirect()
            ->route('videografia.audiovisuales.edit', $audiovisual)
            ->with('success', match ($transition['estado']) {
                'EN_REVISION' => 'Audiovisual enviado a revisión correctamente.',
                'ASIGNADO' => 'Audiovisual asignado correctamente.',
                'FINALIZADO' => 'Audiovisual finalizado correctamente.',
                default => 'Audiovisual actualizado correctamente.',
            });
    }

    public function storeMessage(Request $request, Audiovisual $audiovisual): RedirectResponse
    {
        $validated = $request->validate([
            'mensaje' => ['required', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'reply_to_id' => ['nullable', 'integer', 'exists:audiovisual_mensajes,id'],
        ]);

        $mensaje = $audiovisual->mensajes()->create([
            'user_id' => $request->user()?->id,
            'reply_to_id' => $validated['reply_to_id'] ?? null,
            'tipo' => Str::upper($validated['tipo']),
            'mensaje' => $validated['mensaje'],
        ]);

        $this->registrarMovimiento(
            $audiovisual,
            'COMENTARIO',
            $audiovisual->estado,
            $audiovisual->estado,
            Str::limit($mensaje->mensaje, 200),
        );

        return redirect()
            ->route('videografia.audiovisuales.edit', $audiovisual)
            ->with('success', 'Mensaje enviado correctamente.');
    }

    public function planificacion(): View
    {
        $proximos = Audiovisual::query()
            ->with(['user:id,name'])
            ->orderByRaw('fecha is null, fecha asc')
            ->orderByRaw('hora is null, hora asc')
            ->limit(10)
            ->get();

        return view('videografia.audiovisuales.planificacion', [
            'totalAudiovisuales' => Audiovisual::query()->count(),
            'totalPendientes' => Audiovisual::query()->where('estado', 'PENDIENTE')->count(),
            'totalFinalizados' => Audiovisual::query()->where('estado', 'FINALIZADO')->count(),
            'proximosAudiovisuales' => $proximos,
            'videografos' => User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('name', $this->audiovisualStaffRoles()))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function multimedia(Request $request): View
    {
        $q = trim((string) $request->string('q'));

        $audiovisuales = Audiovisual::query()
            ->with(['user:id,name', 'disenador:id,name'])
            ->whereNotNull('slack_file_id')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($innerQuery) use ($q): void {
                    $innerQuery
                        ->where('titulo', 'like', "%{$q}%")
                        ->orWhere('seccion', 'like', "%{$q}%")
                        ->orWhere('slack_file_id', 'like', "%{$q}%");
                });
            })
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('videografia.audiovisuales.multimedia', [
            'audiovisuales' => $audiovisuales,
            'q' => $q,
        ]);
    }

    public function destroySlackMedia(Request $request, Audiovisual $audiovisual, SlackFileUploader $slackFileUploader): RedirectResponse
    {
        if (! $request->user()?->hasAnyRole(['videografia', 'video_manager', 'editor', 'director'])) {
            abort(403);
        }

        if (! filled($audiovisual->slack_file_id)) {
            return redirect()
                ->route('videografia.audiovisuales.multimedia')
                ->with('error', 'Ese audiovisual no tiene un archivo de Slack asociado.');
        }

        try {
            $slackFileUploader->delete($audiovisual->slack_file_id);
        } catch (Throwable $exception) {
            Log::error('Audiovisual Slack delete failed', [
                'message' => $exception->getMessage(),
                'audiovisual_id' => $audiovisual->id,
                'user_id' => $request->user()?->id,
                'slack_file_id' => $audiovisual->slack_file_id,
            ]);

            return back()->with('error', 'No se pudo eliminar el archivo en Slack.');
        }

        $estadoAnterior = $audiovisual->estado;

        $audiovisual->forceFill([
            'archivo_final_path' => null,
            'archivo_final_original_name' => null,
            'archivo_final_mime' => null,
            'archivo_final_size' => null,
            'slack_file_id' => null,
            'slack_permalink' => null,
            'slack_private_url' => null,
        ])->save();

        $this->registrarMovimiento(
            $audiovisual,
            'ARCHIVO_SLACK_ELIMINADO',
            $estadoAnterior,
            $audiovisual->estado,
            'Archivo final eliminado de Slack y desvinculado del audiovisual.',
        );

        return redirect()
            ->route('videografia.audiovisuales.multimedia')
            ->with('success', 'Archivo eliminado de Slack correctamente.');
    }

    protected function resolveTipoAudiovisual(?int $tipoAudiovisualId): ?TipoAudiovisual
    {
        if (! $tipoAudiovisualId) {
            return null;
        }

        return TipoAudiovisual::query()->find($tipoAudiovisualId);
    }

    /**
     * @return array{brief_path: string, brief_original_name: string}|null
     */
    protected function storeBriefIfPresent(Request $request, Audiovisual $audiovisual): ?array
    {
        if (! $request->hasFile('brief')) {
            return null;
        }

        $archivo = $request->file('brief');

        return [
            'brief_path' => $archivo->store("audiovisuales/{$audiovisual->id}", 'public'),
            'brief_original_name' => $archivo->getClientOriginalName(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array{brief_path: string, brief_original_name: string}|null  $briefData
     */
    protected function syncTipoDetalles(Audiovisual $audiovisual, array $validated, ?string $slug, ?array $briefData): void
    {
        $audiovisual->loadMissing('edicionDetalle', 'grabacionDetalle', 'grabacionEdicionDetalle');

        if ($slug === TipoAudiovisual::SLUG_EDICION) {
            $audiovisual->edicionDetalle()->updateOrCreate(
                [],
                [
                    'entrevistador' => $validated['entrevistador'] ?? null,
                    'entrevistado' => $validated['entrevistado'] ?? null,
                ],
            );
            $this->deleteGrabacionRelations($audiovisual);
            return;
        }

        if ($slug === TipoAudiovisual::SLUG_GRABACION) {
            $payload = [
                'producto_digital' => $validated['producto_digital'] ?? null,
                'contacto_cobertura' => $validated['contacto_cobertura'] ?? null,
                'horario_grabacion' => $validated['horario_grabacion'] ?? null,
                'ubicacion' => $validated['ubicacion'] ?? null,
            ];

            if ($briefData) {
                $this->deleteStoredBrief($audiovisual->grabacionDetalle?->brief_path);
                $payload = array_merge($payload, $briefData);
            }

            $audiovisual->grabacionDetalle()->updateOrCreate([], $payload);
            $this->deleteEdicionRelations($audiovisual);
            return;
        }

        if ($slug === TipoAudiovisual::SLUG_GRABACION_EDICION) {
            $payload = [
                'producto_digital' => $validated['producto_digital'] ?? null,
                'entrevistador' => $validated['entrevistador'] ?? null,
                'entrevistado' => $validated['entrevistado'] ?? null,
                'contacto_cobertura' => $validated['contacto_cobertura'] ?? null,
                'horario_grabacion' => $validated['horario_grabacion'] ?? null,
                'ubicacion' => $validated['ubicacion'] ?? null,
            ];

            if ($briefData) {
                $this->deleteStoredBrief($audiovisual->grabacionEdicionDetalle?->brief_path);
                $payload = array_merge($payload, $briefData);
            }

            $audiovisual->grabacionEdicionDetalle()->updateOrCreate([], $payload);
            $this->deleteStandaloneRelations($audiovisual);
            return;
        }

        $this->deleteEdicionRelations($audiovisual);
        $this->deleteGrabacionRelations($audiovisual);
        $audiovisual->requerimientos()->delete();
        $audiovisual->redesSociales()->delete();
    }

    protected function deleteEdicionRelations(Audiovisual $audiovisual): void
    {
        if ($audiovisual->edicionDetalle) {
            $audiovisual->edicionDetalle->delete();
        }
    }

    protected function deleteGrabacionRelations(Audiovisual $audiovisual): void
    {
        if ($audiovisual->grabacionDetalle) {
            $this->deleteStoredBrief($audiovisual->grabacionDetalle->brief_path);
            $audiovisual->grabacionDetalle->delete();
        }

        if ($audiovisual->grabacionEdicionDetalle) {
            $this->deleteStoredBrief($audiovisual->grabacionEdicionDetalle->brief_path);
            $audiovisual->grabacionEdicionDetalle->delete();
        }
    }

    protected function deleteStandaloneRelations(Audiovisual $audiovisual): void
    {
        if ($audiovisual->edicionDetalle) {
            $audiovisual->edicionDetalle->delete();
        }

        if ($audiovisual->grabacionDetalle) {
            $this->deleteStoredBrief($audiovisual->grabacionDetalle->brief_path);
            $audiovisual->grabacionDetalle->delete();
        }
    }

    protected function deleteStoredBrief(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @param  list<string>  $values
     */
    protected function syncSimpleRows(Audiovisual $audiovisual, string $relation, array $values): void
    {
        $audiovisual->{$relation}()->delete();

        foreach (array_values(array_unique($values)) as $value) {
            $audiovisual->{$relation}()->create([
                'nombre' => $value,
            ]);
        }
    }

    protected function registrarMovimiento(
        Audiovisual $audiovisual,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $motivo,
    ): void {
        $audiovisual->movimientos()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
        ]);
    }
}
