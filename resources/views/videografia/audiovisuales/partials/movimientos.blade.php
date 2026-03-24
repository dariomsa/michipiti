@if($movimientos->isEmpty())
  <div class="text-muted" style="font-size:.9rem;">
    Aún no hay movimientos registrados para este audiovisual.
  </div>
@else
  <div class="d-grid gap-3">
    @foreach($movimientos as $movimiento)
      <div>
        <div class="fw-semibold" style="font-size:.9rem;">
          {{ $movimiento->accion_label ?? $movimiento->accion ?? 'Movimiento' }}
          @if($movimiento->user?->name)
            <span class="text-muted">· {{ $movimiento->user->name }}</span>
          @endif
        </div>

        @if(!empty($movimiento->motivo))
          <div class="text-muted" style="font-size:.85rem;">
            {{ $movimiento->motivo }}
          </div>
        @endif

        @if($movimiento->estado_anterior || $movimiento->estado_nuevo)
          <div class="text-muted" style="font-size:.82rem;">
            {{ $movimiento->estado_anterior ?: '-' }} -> {{ $movimiento->estado_nuevo ?: '-' }}
          </div>
        @endif

        <div class="msg-meta">
          {{ optional($movimiento->created_at)->format('d/m/Y H:i') }}
        </div>
      </div>
    @endforeach
  </div>
@endif
