@extends('layouts.app')

@section('title', 'Ajuste Horarios')

@push('styles')
<style>
  .schedule-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(0,0,0,.05);
    overflow: hidden;
  }

  .schedule-table-wrap {
    overflow: auto;
  }

  .schedule-table {
    min-width: 980px;
    width: 100%;
    border-collapse: collapse;
  }

  .schedule-table col.schedule-hour-col {
    width: 150px;
  }

  .schedule-table th,
  .schedule-table td {
    border: 1px solid #111827;
    height: 42px;
    padding: .3rem .5rem;
    text-align: center;
    vertical-align: middle;
    font-size: .88rem;
    white-space: nowrap;
  }

  .schedule-table thead th {
    background: #f8fafc;
    font-weight: 800;
    position: sticky;
    top: 0;
    z-index: 2;
    border-bottom: 2px solid #111827;
  }

  .schedule-hour {
    background: #e5e7eb;
    font-weight: 800;
    position: sticky;
    left: 0;
    z-index: 1;
    border-right: 2px solid #111827;
    padding-left: .45rem;
    padding-right: .45rem;
  }

  .schedule-hour-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .35rem;
  }

  .slot-cell-normal {
    background: #dcfce7;
  }

  .slot-cell-outside {
    background: #dbeafe;
  }

  .slot-cell-hidden {
    background: #d1d5db;
  }

  .slot-cell-locked {
    background: #ede9fe;
    color: #5b21b6;
    font-weight: 700;
  }

  .slot-select {
    min-width: 120px;
    max-width: 120px;
    padding-top: .25rem;
    padding-bottom: .25rem;
    padding-left: .5rem;
    padding-right: 1.8rem;
    border-radius: 0;
    border-color: #cbd5e1;
    font-size: .78rem;
  }

  .schedule-note {
    color: #6b7280;
    font-size: .88rem;
  }
</style>
@endpush

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Ajuste de horarios</h1>
      <div class="text-muted mt-1" style="font-size:.9rem;">
        Matriz semanal de 06:00 a 22:00 en saltos de 15 minutos.
      </div>
    </div>
  </div>

  <div class="schedule-card">
    <div class="schedule-table-wrap">
      <table class="schedule-table">
        <colgroup>
          <col class="schedule-hour-col">
          @foreach($dayNames as $dayName)
            <col>
          @endforeach
        </colgroup>
        <thead>
          <tr>
            <th class="schedule-hour">Hora</th>
            @foreach($dayNames as $dayName)
              <th>{{ $dayName }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($hours as $hour)
            @php
              $hourHm = substr((string) $hour, 0, 5);
              $isLockedHour = in_array($hourHm, $lockedHours ?? [], true);
              $hourSlots = $matrix->get($hour);
              $hourState = 'oculto';

              if ($hourSlots && $hourSlots->contains(fn ($slot) => $slot && $slot->visible && $slot->fuera_de_pauta)) {
                  $hourState = 'fuera';
              }
            @endphp
            <tr>
              <td class="schedule-hour">
                <div class="schedule-hour-inner">
                  <span>{{ $hourHm }}</span>
                  @if(! $isLockedHour && $hourSlots && $hourSlots->first())
                    <select class="form-select slot-select"
                            data-hour-select
                            data-update-url="{{ route('horario-slots.update', $hourSlots->first()) }}">
                      <option value="oculto" {{ $hourState === 'oculto' ? 'selected' : '' }}>Oculto</option>
                      <option value="fuera" {{ $hourState === 'fuera' ? 'selected' : '' }}>Fuera de pauta</option>
                    </select>
                  @endif
                </div>
              </td>
              @foreach($dayNames as $dayIndex => $dayName)
                @php
                  $slot = $matrix->get($hour)?->get($dayIndex);
                  $estadoVisual = ! $slot || ! $slot->visible
                      ? 'oculto'
                      : ($slot->fuera_de_pauta ? 'fuera' : 'normal');

                  $cellClass = match ($estadoVisual) {
                      'normal' => 'slot-cell-normal',
                      'fuera' => 'slot-cell-outside',
                      default => 'slot-cell-hidden',
                  };

                  if ($isLockedHour && $estadoVisual === 'normal') {
                      $cellClass .= ' slot-cell-locked';
                  }
                @endphp
                <td class="{{ $cellClass }}" data-slot-cell data-hour="{{ $hourHm }}">
                  @if($isLockedHour && $estadoVisual === 'normal')
                    <i class="bi bi-calendar-event"></i>
                  @endif
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3 d-flex flex-wrap gap-3 schedule-note">
    <span><span class="badge" style="background:#dbeafe;color:#1d4ed8;">Fuera de pauta</span></span>
    <span><span class="badge" style="background:#f3f4f6;color:#374151;">Oculto</span></span>
    <span><span class="badge" style="background:#ede9fe;color:#5b21b6;">Bloqueado en planificador</span></span>
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function applyCellState(cell, state, locked = false) {
    cell.classList.remove('slot-cell-normal', 'slot-cell-outside', 'slot-cell-hidden');
    cell.classList.toggle('slot-cell-locked', locked);

    if (state === 'fuera') {
      cell.classList.add('slot-cell-outside');
      return;
    }

    cell.classList.add('slot-cell-hidden');
  }

  document.querySelectorAll('[data-hour-select]').forEach((select) => {
    select.addEventListener('change', async () => {
      const previous = select.dataset.previous ?? select.value;
      const next = select.value;
      const row = select.closest('tr');

      select.disabled = true;

      try {
        const response = await fetch(select.dataset.updateUrl, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
          },
          body: JSON.stringify({
            estado_visual: next,
          }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || !data.ok) {
          throw new Error(data.message || 'No se pudo actualizar el horario.');
        }

        select.dataset.previous = next;
        row?.querySelectorAll('[data-slot-cell]').forEach((cell) => {
          applyCellState(cell, next, false);
        });
      } catch (error) {
        select.value = previous;
        if (window.Swal) {
          window.Swal.fire({
            icon: 'warning',
            title: 'No se pudo actualizar',
            text: error?.message || 'No se pudo actualizar el horario.',
            confirmButtonText: 'Entendido',
          });
        } else {
          window.alert(error?.message || 'No se pudo actualizar el horario.');
        }
      } finally {
        select.disabled = false;
      }
    });

    select.dataset.previous = select.value;
  });
})();
</script>
@endpush
