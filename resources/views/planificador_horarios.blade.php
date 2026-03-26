@extends('layouts.app')

@section('title', 'Horarios')

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

  .schedule-table th,
  .schedule-table td {
    border: 1px solid #111827;
    height: 46px;
    padding: .45rem .6rem;
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
    background: #f3f4f6;
    font-weight: 800;
    position: sticky;
    left: 0;
    z-index: 1;
    border-right: 2px solid #111827;
  }

  .schedule-slot-active {
    background: #dcfce7;
    color: #166534;
    font-weight: 700;
  }

  .schedule-slot-outside {
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 700;
  }

  .schedule-note {
    color: #6b7280;
    font-size: .88rem;
  }
</style>
@endpush

@section('content')
@php
  $availableWeekdaysHours = [
    '06:00', '07:00', '08:15', '09:30', '10:45', '11:30', '12:15', '13:30',
    '14:45', '15:30', '16:00', '17:15', '18:30', '19:45', '20:15', '21:00',
    '22:15', '22:45',
  ];

  $availableSaturdayHours = [
    '09:00', '10:30', '12:00', '13:30', '15:00',
    '16:30', '18:00', '19:30', '20:30', '22:00',
  ];

  $availableSundayHours = [
    '09:30', '10:45', '12:00', '13:30', '15:00',
    '16:30', '18:00', '19:30', '21:00', '22:00',
  ];

  $availableSchedule = [
    0 => $availableWeekdaysHours,
    1 => $availableWeekdaysHours,
    2 => $availableWeekdaysHours,
    3 => $availableWeekdaysHours,
    4 => $availableWeekdaysHours,
    5 => $availableSaturdayHours,
    6 => $availableSundayHours,
  ];

  $availableSundayLabels = [
    '09:30' => 'Softnews',
    '10:45' => 'Reel',
    '12:00' => 'Deportes',
    '13:30' => 'Noticias del dia',
    '15:00' => 'Reel',
    '16:30' => 'Carrusel premium',
    '18:00' => 'Tendencias',
    '19:30' => 'Deportes',
    '21:00' => 'Reel',
    '22:00' => 'Tendencias',
  ];

  $availableAllHours = collect($availableSchedule)
      ->flatten()
      ->unique()
      ->sort()
      ->values();
@endphp

<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Horarios disponibles</h1>
    </div>
  </div>

  <div class="schedule-card">
    <div class="schedule-table-wrap">
      <table class="schedule-table">
        <thead>
          <tr>
            <th class="schedule-hour">Hora</th>
            @foreach($dayNames as $dayName)
              <th>{{ $dayName }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($availableAllHours as $hour)
            <tr>
              <td class="schedule-hour">{{ $hour }}</td>
              @foreach($dayNames as $index => $dayName)
                @php
                  $isAvailable = in_array($hour, $availableSchedule[$index] ?? [], true);
                  $slotClass = $isAvailable ? 'schedule-slot-active' : 'schedule-slot-outside';
                  $slotLabel = $isAvailable ? 'Permitido' : 'Fuera de pauta';
                  $slotTitle = $index === 6 && $isAvailable && isset($availableSundayLabels[$hour])
                      ? 'Domingo: '.$availableSundayLabels[$hour]
                      : '';
                @endphp
                <td class="{{ $slotClass }}" title="{{ $slotTitle }}">
                  {{ $slotLabel }}
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3 d-flex flex-wrap gap-3 schedule-note">
    <span><span class="badge" style="background:#dcfce7;color:#166534;">Permitido</span></span>
    <span><span class="badge" style="background:#dbeafe;color:#1d4ed8;">Fuera de pauta</span></span>
  </div>
</section>
@endsection
