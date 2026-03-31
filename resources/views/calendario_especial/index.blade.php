@extends('layouts.app')

@section('title', 'Configuración Feriados')

@push('styles')
<style>
  .holiday-grid {
    display: grid;
    grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
    gap: 1rem;
  }

  .holiday-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(0,0,0,.05);
    overflow: hidden;
  }

  .holiday-card-header {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
    font-weight: 800;
  }

  .holiday-card-body {
    padding: 1rem 1.2rem;
  }

  .holiday-table {
    width: 100%;
    border-collapse: collapse;
  }

  .holiday-table th,
  .holiday-table td {
    border: 1px solid #d1d5db;
    padding: .7rem .8rem;
    vertical-align: middle;
    font-size: .9rem;
  }

  .holiday-table th {
    background: #f8fafc;
    font-weight: 800;
    white-space: nowrap;
  }

  .holiday-form-actions {
    display: flex;
    gap: .65rem;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
  }

  .holiday-row-form {
    display: grid;
    gap: .6rem;
  }

  .holiday-row-actions {
    display: flex;
    gap: .5rem;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: wrap;
  }

  .holiday-delete-form {
    margin: 0;
  }

  @media (max-width: 1100px) {
    .holiday-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Configuración de feriados</h1>
      <div class="text-muted mt-1" style="font-size:.9rem;">
        Define fechas especiales y el tipo de horario feriado que deberán usar.
      </div>
    </div>
  </div>

  <div class="holiday-grid">
    <div class="holiday-card">
      <div class="holiday-card-header">Nuevo feriado</div>
      <div class="holiday-card-body">
        @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            <div class="fw-bold mb-1">No se pudo guardar la configuración.</div>
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('calendario-especial.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" class="form-control rounded-0" name="fecha" value="{{ old('fecha') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" class="form-control rounded-0" name="motivo" value="{{ old('motivo') }}" maxlength="150" placeholder="Ej: Viernes Santo" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Tipo feriado</label>
            <select class="form-select rounded-0" name="tipo_feriado" required>
              <option value="1" {{ old('tipo_feriado', '1') == '1' ? 'selected' : '' }}>Feriado 1</option>
            </select>
          </div>

          <div class="holiday-form-actions">
            <button type="submit" class="btn btn-dark rounded-0">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <div class="holiday-card">
      <div class="holiday-card-header">Feriados registrados</div>
      <div class="holiday-card-body">
        @if($items->isEmpty())
          <div class="text-muted">Todavía no hay feriados configurados.</div>
        @else
          <div class="table-responsive">
            <table class="holiday-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Motivo</th>
                  <th>Tipo</th>
                  <th style="width: 260px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $item)
                  <tr>
                    <td>{{ optional($item->fecha)->format('Y-m-d') }}</td>
                    <td>{{ $item->motivo }}</td>
                    <td>Feriado {{ $item->tipo_feriado }}</td>
                    <td>
                      <div class="holiday-row-form">
                        <form method="POST" action="{{ route('calendario-especial.update', $item) }}" class="holiday-row-form">
                        @csrf
                        @method('PUT')
                        <input type="date" class="form-control rounded-0" name="fecha" value="{{ optional($item->fecha)->format('Y-m-d') }}" required>
                        <input type="text" class="form-control rounded-0" name="motivo" value="{{ $item->motivo }}" maxlength="150" required>
                        <select class="form-select rounded-0" name="tipo_feriado" required>
                          <option value="1" {{ (int) $item->tipo_feriado === 1 ? 'selected' : '' }}>Feriado 1</option>
                        </select>
                        <div class="holiday-row-actions">
                          <button type="submit" class="btn btn-outline-dark btn-sm rounded-0">Actualizar</button>
                        </div>
                        </form>
                        <form method="POST" action="{{ route('calendario-especial.destroy', $item) }}" class="holiday-delete-form" onsubmit="return confirm('¿Eliminar este feriado?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline-danger btn-sm rounded-0">Eliminar</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
