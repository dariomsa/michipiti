@if($mensajes->isEmpty())
  <div class="text-muted" style="font-size:.9rem;">
    Todavía no hay mensajes para este producto.
  </div>
@else
  @foreach($mensajes as $mensaje)
    <div class="msg-item">
      <div class="d-flex justify-content-between align-items-center">
        <div class="fw-bold" style="font-size:.9rem;">
          {{ $mensaje->autor_nombre ?? $mensaje->autor?->name ?? 'Sistema' }}
        </div>
        <div class="msg-meta">
          {{ optional($mensaje->created_at)->format('d/m/Y H:i') }}
        </div>
      </div>

      <div style="font-size:.92rem;">
        {{ $mensaje->mensaje }}
      </div>
    </div>
  @endforeach
@endif
