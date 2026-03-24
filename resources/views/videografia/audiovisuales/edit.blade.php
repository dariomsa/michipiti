@extends('layouts.app')

@section('title', 'Panel de creación Videografía')

@push('styles')
<style>
  .editor-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(320px, 1fr);
    gap: 1.5rem;
  }

  .editor-panel .card,
  .editor-side .card {
    border-radius: 0;
    border-color: var(--ec-border, #e5e7eb);
  }

  .editor-side .card-header {
    background: #fff;
    border-bottom: 1px solid var(--ec-border, #e5e7eb);
    font-weight: 700;
  }

  .product-meta {
    display: grid;
    gap: .65rem;
  }

  .product-meta-item {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-size: .9rem;
  }

  .product-meta-label {
    color: #6b7280;
  }

  .muted-box {
    border: 1px solid var(--ec-border, #e5e7eb);
    background: #fff;
    padding: 12px;
    font-size: .9rem;
    color: #4b5563;
  }

  .editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .editor-actions .btn {
    font-size: .82rem;
    line-height: 1.2;
    padding: .42rem .7rem;
    white-space: nowrap;
  }

  .btn-action-save {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1d4ed8;
  }

  .btn-action-save:hover {
    background: #bfdbfe;
    border-color: #60a5fa;
    color: #1e40af;
  }

  .hidden-field {
    display: none;
  }

  .check-stack {
    display: grid;
    gap: .45rem;
  }

  .check-stack .form-check {
    margin: 0;
  }

  .preview-badges {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .preview-badges .badge {
    border-radius: 0;
    font-weight: 500;
  }

  @media (max-width: 991.98px) {
    .editor-layout {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
@php
  $selectedTipoAudiovisualId = (string) old('tipo_audiovisual_id', $audiovisual->tipo_audiovisual_id);
  $edicionDetalle = $audiovisual->edicionDetalle;
  $grabacionDetalle = $audiovisual->grabacionDetalle;
  $grabacionEdicionDetalle = $audiovisual->grabacionEdicionDetalle;
  $selectedRequerimientos = old('requerimiento', $audiovisual->requerimientos->pluck('nombre')->all());
  $selectedRedes = old('red_social', $audiovisual->redesSociales->pluck('nombre')->all());
  $briefPath = old('brief_path')
      ?: $grabacionEdicionDetalle?->brief_path
      ?: $grabacionDetalle?->brief_path;
  $briefName = old('brief_original_name')
      ?: $grabacionEdicionDetalle?->brief_original_name
      ?: $grabacionDetalle?->brief_original_name;
@endphp

<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Panel de creación Videografía</h1>
      <div class="text-muted small">Edición audiovisual</div>
    </div>

    <a href="{{ route('videografia.audiovisuales.index') }}" class="btn btn-outline-secondary rounded-0">
      Volver al listado
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger rounded-0">
      <strong>Revisa los campos.</strong>
      <ul class="mb-0 mt-2 ps-3">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="editor-layout">
    <div class="editor-panel">
      <div class="card card-form">
        <div class="card-body">
          <form method="POST"
                action="{{ route('videografia.audiovisuales.update', $audiovisual) }}"
                enctype="multipart/form-data"
                id="videoForm">
            @csrf
            @method('PUT')

            <div class="mb-4 p-3 bg-light border rounded-0">
              <label class="form-label fw-bold">Producto</label>
              <select name="tipo_audiovisual_id" id="tipoProducto" class="form-select border-primary rounded-0">
                <option value="">Seleccione una opción...</option>
                @foreach($tiposAudiovisuales as $tipoAudiovisual)
                  <option
                    value="{{ $tipoAudiovisual->id }}"
                    data-slug="{{ $tipoAudiovisual->slug }}"
                    @selected($selectedTipoAudiovisualId === (string) $tipoAudiovisual->id)
                  >
                    {{ $tipoAudiovisual->nombre }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3 field-tema">
              <label class="form-label">Tema</label>
              <input type="text" name="titulo" class="form-control rounded-0" value="{{ old('titulo', $audiovisual->titulo) }}">
            </div>

            <div class="mb-3 field-descripcion">
              <label class="form-label">Descripción</label>
              <textarea name="descripcion" class="form-control rounded-0" rows="4" placeholder="EXPLICAR EL EJE DEL VIDEO...">{{ old('descripcion', $audiovisual->copy) }}</textarea>
            </div>

            <div class="row mb-3 field-bloque-basico">
              <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control rounded-0" value="{{ old('fecha', optional($audiovisual->fecha)->format('Y-m-d')) }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Hora</label>
                <input type="time" name="hora" class="form-control rounded-0" value="{{ old('hora', $audiovisual->hora ? substr((string) $audiovisual->hora, 0, 5) : '') }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Sección</label>
                <select name="seccion" class="form-select rounded-0">
                  <option value="">Seleccione</option>
                  @foreach($secciones as $seccion)
                    <option value="{{ $seccion->nombre }}" @selected(old('seccion', $audiovisual->seccion) === $seccion->nombre)>{{ $seccion->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Prioridad</label>
                <select name="prioridad" class="form-select rounded-0">
                  <option value="">Seleccione</option>
                  @foreach($prioridades as $prioridad)
                    <option value="{{ $prioridad }}" @selected(old('prioridad', $audiovisual->prioridad) === $prioridad)>{{ $prioridad }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="mb-3 field-producto-digital">
              <label class="form-label">Producto digital</label>
              <select name="producto_digital" class="form-select rounded-0">
                <option value="">Seleccionar</option>
                @foreach($productosDigitales as $productoDigital)
                  <option value="{{ $productoDigital }}" @selected(old('producto_digital', $grabacionEdicionDetalle?->producto_digital ?? $grabacionDetalle?->producto_digital) === $productoDigital)>{{ $productoDigital === 'otro' ? 'Otro' : $productoDigital }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3 field-requerimiento">
              <label class="form-label">Requerimiento</label>
              <div class="check-stack">
                @foreach($requerimientosDisponibles as $requerimiento)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requerimiento[]" value="{{ $requerimiento }}" id="req_{{ md5($requerimiento) }}" @checked(in_array($requerimiento, $selectedRequerimientos, true))>
                    <label class="form-check-label" for="req_{{ md5($requerimiento) }}">{{ $requerimiento }}</label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="mb-3 field-entrevistador">
              <label class="form-label">Entrevistador</label>
              <input type="text" name="entrevistador" class="form-control rounded-0" value="{{ old('entrevistador', $grabacionEdicionDetalle?->entrevistador ?? $edicionDetalle?->entrevistador) }}">
            </div>

            <div class="mb-3 field-entrevistado">
              <label class="form-label">Entrevistado</label>
              <input type="text" name="entrevistado" class="form-control rounded-0" value="{{ old('entrevistado', $grabacionEdicionDetalle?->entrevistado ?? $edicionDetalle?->entrevistado) }}">
            </div>

            <div class="mb-3 field-contacto">
              <label class="form-label">Contacto de cobertura</label>
              <input type="text" name="contacto_cobertura" class="form-control rounded-0" value="{{ old('contacto_cobertura', $grabacionEdicionDetalle?->contacto_cobertura ?? $grabacionDetalle?->contacto_cobertura) }}">
            </div>

            <div class="mb-3 field-redes">
              <label class="form-label">Red social</label>
              <div class="check-stack">
                @foreach($redesDisponibles as $red)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="red_social[]" value="{{ $red }}" id="red_{{ md5($red) }}" @checked(in_array($red, $selectedRedes, true))>
                    <label class="form-check-label" for="red_{{ md5($red) }}">{{ $red }}</label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="mb-3 field-videografo">
              <label class="form-label">Videógrafo</label>
              <select name="videografo" class="form-select rounded-0">
                <option value="">Seleccione</option>
                @foreach($videografos as $videografo)
                  <option value="{{ $videografo->id }}" @selected((string) old('videografo', $audiovisual->disenador_id) === (string) $videografo->id)>{{ $videografo->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3 field-editor">
              <label class="form-label">Editor</label>
              <select name="editor" class="form-select rounded-0">
                <option value="">Seleccione</option>
                @foreach($videografos as $editor)
                  <option value="{{ $editor->id }}" @selected((string) old('editor', $audiovisual->editor_id) === (string) $editor->id)>{{ $editor->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="row mb-3 field-logistica">
              <div class="col-md-4">
                <label class="form-label">Horario grabación</label>
                <input type="time" name="horario_grabacion" class="form-control rounded-0" value="{{ old('horario_grabacion', $grabacionEdicionDetalle?->horario_grabacion ?? $grabacionDetalle?->horario_grabacion) }}">
              </div>
              <div class="col-md-8">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control rounded-0" value="{{ old('ubicacion', $grabacionEdicionDetalle?->ubicacion ?? $grabacionDetalle?->ubicacion) }}">
              </div>
            </div>

            <div class="mb-3 field-guion">
              <label class="form-label">Adjuntar guion, word/pdf</label>
              <input type="file" name="brief" class="form-control rounded-0">
              @if($briefPath && $briefName)
                <div class="mt-2 small">
                  <a href="{{ asset('storage/'.$briefPath) }}" target="_blank" rel="noopener">{{ $briefName }}</a>
                </div>
              @endif
            </div>

            <div class="mb-3 field-referencia">
              <label class="form-label">Referencia (opcional)</label>
              <input type="text" name="referencia" class="form-control rounded-0" value="{{ old('referencia', $audiovisual->referencia) }}">
            </div>

            <div class="mb-3 field-hashtags">
              <label class="form-label">Hashtags</label>
              <input type="text" name="hashtags" class="form-control rounded-0" value="{{ old('hashtags', $audiovisual->hashtags) }}">
            </div>

            <div class="mb-4 field-creditos">
              <label class="form-label">Créditos (opcional)</label>
              <input type="text" name="creditos" class="form-control rounded-0" value="{{ old('creditos', $audiovisual->creditos) }}">
            </div>

            <div class="editor-actions">
              <button type="submit" class="btn btn-action-save rounded-0">Guardar borrador</button>
              <a href="{{ route('videografia.audiovisuales.index') }}" class="btn btn-outline-secondary rounded-0">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <aside class="editor-side">
      <div class="card mb-3">
        <div class="card-header">Audiovisual</div>
        <div class="card-body">
          <div class="product-meta">
            <div class="product-meta-item">
              <span class="product-meta-label">Estado</span>
              <strong>{{ $audiovisual->estado ?: 'BORRADOR' }}</strong>
            </div>
            <div class="product-meta-item">
              <span class="product-meta-label">Responsable</span>
              <strong>{{ $audiovisual->user?->name ?? '-' }}</strong>
            </div>
            <div class="product-meta-item">
              <span class="product-meta-label">Videógrafo</span>
              <strong>{{ $audiovisual->disenador?->name ?? '-' }}</strong>
            </div>
            <div class="product-meta-item">
              <span class="product-meta-label">Editor</span>
              <strong>{{ $audiovisual->editor?->name ?? '-' }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <i class="bi bi-clock-history me-1"></i> Movimientos
        </div>
        <div class="card-body" style="max-height:220px; overflow:auto;">
          @include('videografia.audiovisuales.partials.movimientos', ['movimientos' => $movimientos])
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="bi bi-chat-dots me-1"></i> Mensajes
        </div>
        <div class="card-body" style="max-height:240px; overflow:auto;">
          @include('videografia.audiovisuales.partials.mensajes', ['mensajes' => $mensajes])
        </div>

        <div class="card-footer bg-white border-top">
          <form method="POST" action="{{ route('videografia.audiovisuales.mensajes.store', $audiovisual) }}">
            @csrf
            <div class="input-group">
              <input type="text"
                     name="mensaje"
                     class="form-control rounded-0"
                     placeholder="Escribe un mensaje..."
                     required>
              <button class="btn btn-dark rounded-0" type="submit">
                <i class="bi bi-send"></i>
              </button>
            </div>
            <input type="hidden" name="tipo" value="COMENTARIO">
          </form>
        </div>
      </div>
    </aside>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tipoProducto = document.getElementById('tipoProducto');
  const allFields = document.querySelectorAll('[class*="field-"]');

  function setVisibility() {
    const value = tipoProducto?.selectedOptions?.[0]?.dataset?.slug || '';

    allFields.forEach((element) => element.classList.add('hidden-field'));

    if (value === 'edicion') {
      ['.field-tema', '.field-descripcion', '.field-bloque-basico', '.field-entrevistador', '.field-entrevistado', '.field-editor', '.field-hashtags', '.field-creditos']
        .forEach((selector) => document.querySelector(selector)?.classList.remove('hidden-field'));
      return;
    }

    if (value === 'grabacion') {
      ['.field-tema', '.field-descripcion', '.field-bloque-basico', '.field-entrevistador', '.field-entrevistado', '.field-videografo', '.field-hashtags', '.field-creditos']
        .forEach((selector) => document.querySelector(selector)?.classList.remove('hidden-field'));
      return;
    }

    if (value === 'grabacion_edicion') {
      allFields.forEach((element) => element.classList.remove('hidden-field'));
    }
  }

  tipoProducto?.addEventListener('change', setVisibility);
  setVisibility();
});
</script>
@endpush
