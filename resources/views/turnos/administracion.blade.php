@extends('layouts.app')

@section('title', 'Administración de turnos')

@php
  $activeTab = request('tab', 'fines');
  $activeTab = in_array($activeTab, ['fines', 'feriados', 'vigilia'], true) ? $activeTab : 'fines';
  $monthName = fn ($fecha) => $fecha ? ($meses[((int) $fecha->format('n')) - 1] ?? '-') : '-';
  $mesNumero = fn ($mes) => array_search($mes, $meses, true) === false ? null : array_search($mes, $meses, true) + 1;
  $weekendRange = function ($mes, $weekend) use ($anioVigilia, $mesNumero) {
      $weekend = (int) $weekend;
      $month = $mesNumero($mes);

      if (! $month || $weekend < 1 || $weekend > 5) {
          return null;
      }

      $firstDay = \Carbon\CarbonImmutable::create($anioVigilia, $month, 1);
      $firstSaturday = $firstDay->isSaturday() ? $firstDay : $firstDay->next(\Carbon\CarbonInterface::SATURDAY);
      $start = $firstSaturday->addWeeks($weekend - 1);
      $end = $start->addDay();

      return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
  };
  $weekRange = function ($week) use ($anioVigilia) {
      if (! $week) {
          return '-';
      }

      $start = \Carbon\CarbonImmutable::now()->setISODate($anioVigilia, (int) $week)->startOfDay();
      $end = $start->addDays(6);

      return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
  };
@endphp

@push('styles')
<style>
  .turnos-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 10px 24px rgba(0,0,0,.05);
    overflow: hidden;
  }

  .turnos-tabs {
    border-bottom: 1px solid #e5e7eb;
    gap: .25rem;
  }

  .turnos-tabs .nav-link {
    border: 0;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    color: #64748b;
    font-weight: 700;
    padding: .75rem 1rem;
  }

  .turnos-tabs .nav-link.active {
    background: transparent;
    border-bottom-color: #111827;
    color: #111827;
  }

  .turnos-table {
    width: 100%;
    border-collapse: collapse;
  }

  .turnos-table th,
  .turnos-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: .85rem .9rem;
    vertical-align: middle;
    font-size: .92rem;
  }

  .turnos-table th {
    background: #f8fafc;
    font-weight: 800;
    white-space: nowrap;
    text-transform: uppercase;
    font-size: .75rem;
    color: #475569;
  }

  .turnos-personas {
    max-width: 520px;
    white-space: normal;
  }

  @media (max-width: 980px) {
    .turnos-table {
      min-width: 860px;
    }
  }
</style>
@endpush

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Administración de turnos</h1>
      <div class="text-muted mt-1" style="font-size:.9rem;">
        Gestión de fines de semana, feriados y vigilia.
      </div>
    </div>

    <button type="button" class="btn btn-dark rounded-0" data-bs-toggle="modal" data-bs-target="#modalCreate{{ ucfirst($activeTab) }}">
      <i class="bi bi-plus-circle"></i> Añadir registro
    </button>
  </div>

  @if(session('status'))
    <div class="alert alert-success rounded-0">
      {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger rounded-0">
      <div class="fw-bold mb-1">No se pudo guardar el registro.</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <ul class="nav turnos-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link {{ $activeTab === 'fines' ? 'active' : '' }}" href="{{ route('turnos.administracion', ['tab' => 'fines']) }}">
        <i class="bi bi-calendar-week me-1"></i> Fines de Semana
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $activeTab === 'feriados' ? 'active' : '' }}" href="{{ route('turnos.administracion', ['tab' => 'feriados']) }}">
        <i class="bi bi-umbrella me-1"></i> Feriados
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $activeTab === 'vigilia' ? 'active' : '' }}" href="{{ route('turnos.administracion', ['tab' => 'vigilia']) }}">
        <i class="bi bi-shield-check me-1"></i> Vigilia
      </a>
    </li>
  </ul>

  <div class="turnos-card">
    @if($activeTab === 'fines')
      <div class="table-responsive">
        <table class="turnos-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Mes</th>
              <th>Fin de semana</th>
              <th>Fechas</th>
              <th>Jefe de turno</th>
              <th>Grupo</th>
              <th>Deportes</th>
              <th class="text-end" style="width:170px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($finesSemana as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td class="fw-bold">{{ $item->mes }}</td>
                <td>{{ in_array((int) $item->fines_de_semana, [1, 2, 3, 4, 5], true) ? 'Fin de semana ' . $item->fines_de_semana : $item->fines_de_semana }}</td>
                <td>{{ $weekendRange($item->mes, $item->fines_de_semana) ?: '-' }}</td>
                <td>{{ $item->jefeTurnoUser?->name ?: ($item->jefe_turno ?: '-') }}</td>
                <td><span class="badge bg-secondary rounded-0">{{ $item->grupo }}</span></td>
                <td>{{ $item->deportesUser?->name ?: '-' }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-dark rounded-0" data-bs-toggle="modal" data-bs-target="#modalFin{{ $item->id }}" title="Editar" aria-label="Editar">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form method="POST" action="{{ route('turnos.fin-semana.destroy', $item) }}" class="d-inline js-delete-turno no-loading" data-label="este fin de semana">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0" title="Eliminar" aria-label="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-muted p-4">Todavía no hay fines de semana configurados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @elseif($activeTab === 'feriados')
      <div class="table-responsive">
        <table class="turnos-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Feriado</th>
              <th>Mes</th>
              <th>Año</th>
              <th>Fecha</th>
              <th>Grupo</th>
              <th>Deportes</th>
              <th class="text-end" style="width:170px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($feriados as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td class="fw-bold">{{ $item->motivo }}</td>
                <td>{{ $monthName($item->fecha) }}</td>
                <td>{{ $item->fecha?->format('Y') ?: '-' }}</td>
                <td>{{ $item->fecha?->format('d/m/Y') ?: '-' }}</td>
                <td><span class="badge bg-secondary rounded-0">{{ $item->grupo ?: '-' }}</span></td>
                <td>{{ $item->deportesUser?->name ?: '-' }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-dark rounded-0" data-bs-toggle="modal" data-bs-target="#modalFeriado{{ $item->id }}" title="Editar" aria-label="Editar">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form method="POST" action="{{ route('turnos.feriados.destroy', $item) }}" class="d-inline js-delete-turno no-loading" data-label="este feriado">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0" title="Eliminar" aria-label="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-muted p-4">Todavía no hay feriados configurados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @else
      <div class="table-responsive">
        <table class="turnos-table">
          <thead>
            <tr>
              <th>Semana</th>
              <th>Fechas</th>
              <th>Grupo</th>
              <th>Integrantes de vigilia</th>
              <th class="text-end" style="width:170px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vigilias as $item)
              <tr>
                <td class="fw-bold">Semana {{ $item->semana }}</td>
                <td>{{ $weekRange($item->semana) }}</td>
                <td><span class="badge bg-secondary rounded-0">Grupo {{ $item->grupo }}</span></td>
                <td class="turnos-personas">{{ $item->integrantes->pluck('usuario.name')->filter()->join(', ') ?: '-' }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-dark rounded-0" data-bs-toggle="modal" data-bs-target="#modalVigilia{{ $item->id }}" title="Editar" aria-label="Editar">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form method="POST" action="{{ route('turnos.vigilia.destroy', $item) }}" class="d-inline js-delete-turno no-loading" data-label="esta semana de vigilia">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0" title="Eliminar" aria-label="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-muted p-4">Todavía no hay semanas de vigilia configuradas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @endif
  </div>

  @include('turnos.partials.admin_modals')
</section>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-delete-turno').forEach((form) => {
      form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const label = form.dataset.label || 'este registro';

        if (!window.Swal) {
          if (confirm('¿Eliminar ' + label + '?')) {
            form.submit();
          }

          return;
        }

        const result = await Swal.fire({
          title: '¿Eliminar registro?',
          text: 'Se eliminará ' + label + ' y no se podrá recuperar.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          reverseButtons: true,
        });

        if (result.isConfirmed) {
          window.showBlockingLoader?.('Eliminando registro...');
          form.submit();
        }
      });
    });

    const formatter = new Intl.DateTimeFormat('es-EC', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone: 'UTC',
    });

    const isoWeekRange = (year, week) => {
      const base = new Date(Date.UTC(year, 0, 4));
      const day = base.getUTCDay() || 7;
      const mondayWeekOne = new Date(base);
      mondayWeekOne.setUTCDate(base.getUTCDate() - day + 1);

      const start = new Date(mondayWeekOne);
      start.setUTCDate(mondayWeekOne.getUTCDate() + ((week - 1) * 7));

      const end = new Date(start);
      end.setUTCDate(start.getUTCDate() + 6);

      return [start, end];
    };

    document.querySelectorAll('.js-vigilia-semana').forEach((input) => {
      const wrapper = input.closest('.js-week-field');
      const output = wrapper?.querySelector('.js-week-range');

      const refreshRange = () => {
        const week = Number(input.value);
        const year = Number(input.dataset.year);

        if (!output || !week || week < 1 || week > 53 || !year) {
          if (output) {
            output.textContent = 'Seleccione una semana para ver el rango de fechas.';
          }

          return;
        }

        const [start, end] = isoWeekRange(year, week);
        output.textContent = formatter.format(start) + ' - ' + formatter.format(end);
      };

      input.addEventListener('input', refreshRange);
      refreshRange();
    });

    const monthMap = @json(array_combine($meses, range(1, count($meses))));

    const weekendRange = (year, month, weekend) => {
      if (!year || !month || !weekend || weekend < 1 || weekend > 4) {
        return null;
      }

      const firstDay = new Date(Date.UTC(year, month - 1, 1));
      const day = firstDay.getUTCDay();
      const daysUntilSaturday = (6 - day + 7) % 7;
      const start = new Date(firstDay);
      start.setUTCDate(firstDay.getUTCDate() + daysUntilSaturday + ((weekend - 1) * 7));

      const end = new Date(start);
      end.setUTCDate(start.getUTCDate() + 1);

      return formatter.format(start) + ' - ' + formatter.format(end);
    };

    document.querySelectorAll('.js-fin-semana-fieldset').forEach((fieldset) => {
      const monthInput = fieldset.querySelector('.js-fin-semana-mes');
      const weekendInput = fieldset.querySelector('.js-fin-semana-numero');
      const output = fieldset.querySelector('.js-fin-semana-range');

      const refreshRange = () => {
        const month = monthMap[monthInput?.value] || null;
        const weekend = Number(weekendInput?.value);
        const year = Number(fieldset.dataset.year);
        const range = weekendRange(year, month, weekend);

        if (output) {
          output.textContent = range || 'Seleccione mes y fin de semana para ver el rango de fechas.';
        }
      };

      monthInput?.addEventListener('change', refreshRange);
      weekendInput?.addEventListener('change', refreshRange);
      refreshRange();
    });
  });
</script>
@endpush
