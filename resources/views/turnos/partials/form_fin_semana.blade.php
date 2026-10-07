@php
  $mesValue = old('mes', $item?->mes ?? 'ENERO');
  $finSemanaValue = old('fines_de_semana', $item?->fines_de_semana ?? 1);
  $jefeTurnoUserId = old('jefe_turno_user_id', $item?->jefe_turno_user_id);
  $grupoValue = old('grupo', $item?->grupo ?? 'A');
  $deportesUserId = old('deportes_user_id', $item?->deportes_user_id);
@endphp

<div class="js-fin-semana-fieldset" data-year="{{ $anioTurnos }}">
  <div class="mb-3">
    <label class="form-label small fw-bold">Mes</label>
    <select name="mes" class="form-select rounded-0 js-fin-semana-mes" required>
      @foreach($meses as $mes)
        <option value="{{ $mes }}" @selected($mesValue === $mes)>{{ ucfirst(strtolower($mes)) }}</option>
      @endforeach
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label small fw-bold">Fin de semana</label>
    <select name="fines_de_semana" class="form-select rounded-0 js-fin-semana-numero" required>
      @foreach([1, 2, 3, 4, 5] as $finSemana)
        <option value="{{ $finSemana }}" @selected((string) $finSemanaValue === (string) $finSemana)>Fin de semana {{ $finSemana }}</option>
      @endforeach
    </select>
    <div class="form-text js-fin-semana-range">Seleccione mes y fin de semana para ver el rango de fechas.</div>
  </div>
</div>

<div class="mb-3">
  <label class="form-label small fw-bold">Jefe de turno</label>
  <select name="jefe_turno_user_id" class="form-select rounded-0">
    <option value="">Sin asignar</option>
    @foreach($usuariosActivos as $usuario)
      <option value="{{ $usuario->id }}" @selected((string) $jefeTurnoUserId === (string) $usuario->id)>{{ $usuario->name }}</option>
    @endforeach
  </select>
</div>

<div class="row g-3">
  <div class="col-md-5">
    <label class="form-label small fw-bold">Grupo</label>
    <select name="grupo" class="form-select rounded-0" required>
      @foreach($grupos as $grupo)
        <option value="{{ $grupo }}" @selected((string) $grupoValue === (string) $grupo)>Grupo {{ $grupo }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-7">
    <label class="form-label small fw-bold">Deportes</label>
    <select name="deportes_user_id" class="form-select rounded-0">
      <option value="">Sin asignar</option>
      @foreach($usuariosActivos as $usuario)
        <option value="{{ $usuario->id }}" @selected((string) $deportesUserId === (string) $usuario->id)>{{ $usuario->name }}</option>
      @endforeach
    </select>
  </div>
</div>
