<div class="modal fade" id="modalCreateFines" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('turnos.fin-semana.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Añadir fin de semana</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        @include('turnos.partials.form_fin_semana', ['item' => null, 'meses' => $meses, 'grupos' => $grupos, 'usuariosActivos' => $usuariosActivos, 'anioTurnos' => $anioVigilia])
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalCreateFeriados" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('turnos.feriados.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Añadir feriado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        @include('turnos.partials.form_feriado', ['item' => null, 'grupos' => $grupos, 'usuariosActivos' => $usuariosActivos])
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalCreateVigilia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('turnos.vigilia.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Añadir vigilia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        @include('turnos.partials.form_vigilia', ['item' => null, 'gruposVigilia' => $gruposVigilia, 'usuariosActivos' => $usuariosActivos, 'anioVigilia' => $anioVigilia])
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
      </div>
    </form>
  </div>
</div>

@foreach($finesSemana as $item)
  <div class="modal fade" id="modalFin{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('turnos.fin-semana.update', $item) }}" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Editar fin de semana</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          @include('turnos.partials.form_fin_semana', ['item' => $item, 'meses' => $meses, 'grupos' => $grupos, 'usuariosActivos' => $usuariosActivos, 'anioTurnos' => $anioVigilia])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
        </div>
      </form>
    </div>
  </div>
@endforeach

@foreach($feriados as $item)
  <div class="modal fade" id="modalFeriado{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('turnos.feriados.update', $item) }}" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Editar feriado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          @include('turnos.partials.form_feriado', ['item' => $item, 'grupos' => $grupos, 'usuariosActivos' => $usuariosActivos])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
        </div>
      </form>
    </div>
  </div>
@endforeach

@foreach($vigilias as $item)
  <div class="modal fade" id="modalVigilia{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('turnos.vigilia.update', $item) }}" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Editar vigilia</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          @include('turnos.partials.form_vigilia', ['item' => $item, 'gruposVigilia' => $gruposVigilia, 'usuariosActivos' => $usuariosActivos, 'anioVigilia' => $anioVigilia])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-0" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark btn-sm rounded-0">Guardar</button>
        </div>
      </form>
    </div>
  </div>
@endforeach
