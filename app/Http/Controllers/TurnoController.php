<?php

namespace App\Http\Controllers;

use App\Models\CalendarioEspecial;
use App\Models\Empresa;
use App\Models\FinSemana;
use App\Models\User;
use App\Models\Vigilia;
use App\Support\EmpresaContext;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TurnoController extends Controller
{
    public function listado(Request $request): View
    {
        $anioTurnos = now()->year;

        return view('turnos.listado', [
            'anioTurnos' => $anioTurnos,
            'meses' => $this->meses(),
            'turnosData' => [
                'fines' => FinSemana::query()
                    ->with(['deportesUser', 'jefeTurnoUser'])
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (FinSemana $item): array => $this->formatFinSemanaForListado($item, $anioTurnos))
                    ->values(),
                'feriados' => CalendarioEspecial::query()
                    ->with('deportesUser')
                    ->orderBy('fecha')
                    ->get()
                    ->map(fn (CalendarioEspecial $item): array => $this->formatFeriadoForListado($item))
                    ->values(),
                'vigilia' => Vigilia::query()
                    ->with('integrantes.usuario')
                    ->orderBy('orden')
                    ->orderBy('semana')
                    ->get()
                    ->map(fn (Vigilia $item): array => $this->formatVigiliaForListado($item, $anioTurnos))
                    ->values(),
            ],
        ]);
    }

    public function administracion(Request $request): View
    {
        $this->authorizeTurnos($request->user());

        $usuariosActivos = User::query()
            ->where('activo', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('turnos.administracion', [
            'finesSemana' => FinSemana::query()
                ->with(['deportesUser', 'jefeTurnoUser'])
                ->orderBy('orden')
                ->orderBy('id')
                ->get(),
            'feriados' => CalendarioEspecial::query()
                ->with('deportesUser')
                ->orderBy('fecha')
                ->get(),
            'vigilias' => Vigilia::query()
                ->with('integrantes.usuario')
                ->orderBy('orden')
                ->orderBy('semana')
                ->get(),
            'meses' => $this->meses(),
            'grupos' => $this->grupos(),
            'gruposVigilia' => $this->gruposVigilia(),
            'usuariosActivos' => $usuariosActivos,
            'anioVigilia' => now()->year,
        ]);
    }

    public function storeFinSemana(Request $request): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $validated = $request->validate($this->finSemanaRules());
        $validated['orden'] = FinSemana::query()->max('orden') + 1;

        FinSemana::query()->create($validated);

        return $this->adminRedirect('fines', 'Fin de semana creado correctamente.');
    }

    public function updateFinSemana(Request $request, FinSemana $finSemana): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $finSemana->update($request->validate($this->finSemanaRules()));

        return $this->adminRedirect('fines', 'Fin de semana actualizado correctamente.');
    }

    public function destroyFinSemana(Request $request, FinSemana $finSemana): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $finSemana->delete();

        return $this->adminRedirect('fines', 'Fin de semana eliminado correctamente.');
    }

    public function storeFeriado(Request $request): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $validated = $request->validate($this->feriadoRules());

        DB::transaction(function () use ($validated): void {
            $this->upsertFeriadoForAllEmpresas($validated);
        });

        return $this->adminRedirect('feriados', 'Feriado creado correctamente.');
    }

    public function updateFeriado(Request $request, CalendarioEspecial $feriado): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $fechaOriginal = $feriado->fecha?->toDateString();
        $validated = $request->validate($this->feriadoRules($feriado));

        DB::transaction(function () use ($fechaOriginal, $validated): void {
            $this->updateFeriadoForAllEmpresas($fechaOriginal, $validated);
        });

        return $this->adminRedirect('feriados', 'Feriado actualizado correctamente.');
    }

    public function destroyFeriado(Request $request, CalendarioEspecial $feriado): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $fecha = $feriado->fecha?->toDateString();

        DB::transaction(function () use ($fecha): void {
            CalendarioEspecial::withoutGlobalScopes()
                ->whereDate('fecha', $fecha)
                ->delete();
        });

        return $this->adminRedirect('feriados', 'Feriado eliminado correctamente.');
    }

    public function storeVigilia(Request $request): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $validated = $this->validatedVigilia($request);
        $integranteIds = $validated['integrante_user_ids'];
        unset($validated['integrante_user_ids']);

        $validated['orden'] = Vigilia::query()->max('orden') + 1;

        DB::transaction(function () use ($validated, $integranteIds): void {
            $vigilia = Vigilia::query()->create($validated);
            $this->syncVigiliaIntegrantes($vigilia, $integranteIds);
        });

        return $this->adminRedirect('vigilia', 'Semana de vigilia creada correctamente.');
    }

    public function updateVigilia(Request $request, Vigilia $vigilia): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $validated = $this->validatedVigilia($request, $vigilia);
        $integranteIds = $validated['integrante_user_ids'];
        unset($validated['integrante_user_ids']);

        DB::transaction(function () use ($vigilia, $validated, $integranteIds): void {
            $vigilia->update($validated);
            $this->syncVigiliaIntegrantes($vigilia, $integranteIds);
        });

        return $this->adminRedirect('vigilia', 'Semana de vigilia actualizada correctamente.');
    }

    public function destroyVigilia(Request $request, Vigilia $vigilia): RedirectResponse
    {
        $this->authorizeTurnos($request->user());

        $vigilia->delete();

        return $this->adminRedirect('vigilia', 'Semana de vigilia eliminada correctamente.');
    }

    private function authorizeTurnos(?User $user): void
    {
        abort_unless($this->canAccessTurnos($user), 403);
    }

    private function canAccessTurnos(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole('director')
            || in_array((int) $user->id, config('constants.turnos.permitidos', []), true);
    }

    /**
     * @return array<string, mixed>
     */
    private function finSemanaRules(): array
    {
        return [
            'mes' => ['required', 'string', 'max:30'],
            'fines_de_semana' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'jefe_turno_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('activo', 1)),
            ],
            'grupo' => ['required', 'string', Rule::in($this->grupos())],
            'deportes_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('activo', 1)),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function feriadoRules(?CalendarioEspecial $feriado = null): array
    {
        $empresaId = app(EmpresaContext::class)->currentId();
        $uniqueFecha = Rule::unique('calendario_especial', 'fecha')
            ->where(fn ($query) => $query->where('empresa_id', $empresaId));

        if ($feriado) {
            $uniqueFecha = $uniqueFecha->ignore($feriado->id);
        }

        return [
            'fecha' => ['required', 'date', $uniqueFecha],
            'motivo' => ['required', 'string', 'max:150'],
            'tipo_feriado' => ['required', 'integer', Rule::in([1, 2])],
            'grupo' => ['nullable', 'string', Rule::in($this->grupos())],
            'deportes_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('activo', 1)),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertFeriadoForAllEmpresas(array $attributes): void
    {
        Empresa::query()
            ->pluck('id')
            ->each(function (int $empresaId) use ($attributes): void {
                CalendarioEspecial::withoutGlobalScopes()->updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'fecha' => $attributes['fecha'],
                    ],
                    $attributes + ['empresa_id' => $empresaId]
                );
            });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function updateFeriadoForAllEmpresas(?string $fechaOriginal, array $attributes): void
    {
        if (! $fechaOriginal) {
            $this->upsertFeriadoForAllEmpresas($attributes);

            return;
        }

        Empresa::query()
            ->pluck('id')
            ->each(function (int $empresaId) use ($fechaOriginal, $attributes): void {
                $feriado = CalendarioEspecial::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->whereDate('fecha', $fechaOriginal)
                    ->first();

                if ($feriado) {
                    $feriado->update($attributes + ['empresa_id' => $empresaId]);

                    return;
                }

                CalendarioEspecial::withoutGlobalScopes()->updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'fecha' => $attributes['fecha'],
                    ],
                    $attributes + ['empresa_id' => $empresaId]
                );
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedVigilia(Request $request, ?Vigilia $vigilia = null): array
    {
        $uniqueSemana = Rule::unique('vigilia', 'semana');

        if ($vigilia) {
            $uniqueSemana = $uniqueSemana->ignore($vigilia->id);
        }

        $validated = $request->validate([
            'semana' => ['required', 'integer', 'min:1', 'max:53', $uniqueSemana],
            'grupo' => ['required', 'integer', Rule::in($this->gruposVigilia())],
            'integrante_user_ids' => ['nullable', 'array'],
            'integrante_user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('activo', 1)),
            ],
        ]);

        $validated['integrante_user_ids'] = collect($validated['integrante_user_ids'] ?? [])
            ->map(fn ($userId): int => (int) $userId)
            ->unique()
            ->values()
            ->all();

        return $validated;
    }

    /**
     * @param  list<int>  $integranteIds
     */
    private function syncVigiliaIntegrantes(Vigilia $vigilia, array $integranteIds): void
    {
        $vigilia->integrantes()->delete();

        $vigilia->integrantes()->createMany(
            collect($integranteIds)
                ->map(fn (int $userId): array => ['user_id' => $userId])
                ->all()
        );
    }

    private function formatFinSemanaForListado(FinSemana $item, int $anio): array
    {
        $range = $this->finSemanaRange($item->mes, (int) $item->fines_de_semana, $anio);

        return [
            'id' => $item->id,
            'mes' => $item->mes,
            'fin_semana_numero' => (int) $item->fines_de_semana,
            'fin_semana_label' => 'Fin de semana ' . $item->fines_de_semana,
            'fecha_inicio' => $range['inicio']?->toDateString(),
            'fecha_fin' => $range['fin']?->toDateString(),
            'fecha_label' => $range['label'],
            'jefe_turno' => $item->jefeTurnoUser?->name ?: ($item->jefe_turno ?: ''),
            'grupo' => $item->grupo,
            'deportes' => $item->deportesUser?->name ?: '',
        ];
    }

    private function formatFeriadoForListado(CalendarioEspecial $item): array
    {
        return [
            'id' => $item->id,
            'feriado' => $item->motivo,
            'fecha' => $item->fecha?->format('d/m/Y') ?: '',
            'fecha_iso' => $item->fecha?->toDateString(),
            'mes' => $item->fecha ? ($this->meses()[((int) $item->fecha->format('n')) - 1] ?? '') : '',
            'anio' => $item->fecha?->format('Y'),
            'grupo' => $item->grupo ?: '',
            'deportes' => $item->deportesUser?->name ?: '',
        ];
    }

    private function formatVigiliaForListado(Vigilia $item, int $anio): array
    {
        $inicio = CarbonImmutable::now()->setISODate($anio, (int) $item->semana)->startOfDay();
        $fin = $inicio->addDays(6);

        return [
            'id' => $item->id,
            'semana' => $item->semana,
            'grupo' => $item->grupo,
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'fecha_label' => $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y'),
            'personas' => $item->integrantes->pluck('usuario.name')->filter()->values(),
        ];
    }

    /**
     * @return array{inicio: ?CarbonImmutable, fin: ?CarbonImmutable, label: string}
     */
    private function finSemanaRange(string $mes, int $finSemana, int $anio): array
    {
        $mesIndex = array_search($mes, $this->meses(), true);

        if ($mesIndex === false || $finSemana < 1 || $finSemana > 5) {
            return ['inicio' => null, 'fin' => null, 'label' => ''];
        }

        $firstDay = CarbonImmutable::create($anio, $mesIndex + 1, 1);
        $firstSaturday = $firstDay->isSaturday()
            ? $firstDay
            : $firstDay->next(CarbonInterface::SATURDAY);
        $inicio = $firstSaturday->addWeeks($finSemana - 1);
        $fin = $inicio->addDay();

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'label' => $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y'),
        ];
    }

    private function adminRedirect(string $tab, string $message): RedirectResponse
    {
        return redirect()
            ->route('turnos.administracion', ['tab' => $tab])
            ->with('status', $message);
    }

    /**
     * @return list<string>
     */
    private function meses(): array
    {
        return [
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE',
        ];
    }

    /**
     * @return list<string>
     */
    private function grupos(): array
    {
        return ['A', 'B', 'C', 'D', 'E', 'F'];
    }

    /**
     * @return list<int>
     */
    private function gruposVigilia(): array
    {
        return [1, 2, 3, 4, 5, 6];
    }
}
