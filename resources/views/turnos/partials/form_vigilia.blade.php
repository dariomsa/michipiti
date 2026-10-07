@php
  $integrantesSeleccionados = collect(old('integrante_user_ids', $item?->integrantes?->pluck('user_id')->all() ?? []))
      ->map(fn ($userId) => (string) $userId)
      ->all();
  $grupoValue = old('grupo', $item?->grupo ?? 1);
@endphp

<div class="row g-3">
  <div class="col-md-6 js-week-field">
    <label class="form-label small fw-bold">Semana</label>
    <input
      type="number"
      name="semana"
      class="form-control rounded-0 js-vigilia-semana"
      value="{{ old('semana', $item?->semana) }}"
      min="1"
      max="53"
      data-year="{{ $anioVigilia }}"
      required
    >
    <div class="form-text js-week-range">Seleccione una semana para ver el rango de fechas.</div>
  </div>
  <div class="col-md-6">
    <label class="form-label small fw-bold">Grupo</label>
    <select name="grupo" class="form-select rounded-0" required>
      @foreach($gruposVigilia as $grupo)
        <option value="{{ $grupo }}" @selected((string) $grupoValue === (string) $grupo)>Grupo {{ $grupo }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="mt-3">
  <label class="form-label small fw-bold">Integrantes de vigilia</label>
  <select name="integrante_user_ids[]" class="form-select rounded-0" multiple size="9">
    @foreach($usuariosActivos as $usuario)
      <option value="{{ $usuario->id }}" @selected(in_array((string) $usuario->id, $integrantesSeleccionados, true))>
        {{ $usuario->name }}
      </option>
    @endforeach
  </select>
  <div class="form-text">Mantenga presionado Ctrl o Cmd para seleccionar varios usuarios.</div>
</div>
