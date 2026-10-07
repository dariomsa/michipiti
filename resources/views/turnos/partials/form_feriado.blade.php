@php
  $fechaValue = old('fecha', $item?->fecha?->format('Y-m-d'));
  $tipoValue = (int) old('tipo_feriado', $item?->tipo_feriado ?? 1);
  $grupoValue = old('grupo', $item?->grupo);
  $deportesUserId = old('deportes_user_id', $item?->deportes_user_id);
@endphp

<div class="mb-3">
  <label class="form-label small fw-bold">Feriado</label>
  <input type="text" name="motivo" class="form-control rounded-0" value="{{ old('motivo', $item?->motivo) }}" maxlength="150" required>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <label class="form-label small fw-bold">Fecha</label>
    <input type="date" name="fecha" class="form-control rounded-0" value="{{ $fechaValue }}" required>
  </div>
  <div class="col-md-5">
    <label class="form-label small fw-bold">Tipo de Feriado</label>
    <select name="tipo_feriado" class="form-select rounded-0" required>
      <option value="1" @selected($tipoValue === 1)>Feriado 1</option>
      <option value="2" @selected($tipoValue === 2)>Feriado 2</option>
    </select>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-md-5">
    <label class="form-label small fw-bold">Grupo</label>
    <select name="grupo" class="form-select rounded-0">
      <option value="">Sin grupo</option>
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
