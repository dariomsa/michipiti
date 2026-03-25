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
    border: 1px solid #e5e7eb;
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
  }

  .schedule-hour {
    background: #f3f4f6;
    font-weight: 800;
    position: sticky;
    left: 0;
    z-index: 1;
  }

  .schedule-slot-active {
    background: #fce7f3;
    color: #9d174d;
    font-weight: 700;
  }

  .schedule-slot-empty {
    background: #fff;
    color: #cbd5e1;
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
          @foreach($allHours as $hour)
            <tr>
              <td class="schedule-hour">{{ $hour }}</td>
              @foreach($dayNames as $index => $dayName)
                @php
                  $active = in_array($hour, $schedule[$index] ?? [], true);
                @endphp
                <td class="{{ $active ? 'schedule-slot-active' : 'schedule-slot-empty' }}">
                  {{ $active ? $hour : '—' }}
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
