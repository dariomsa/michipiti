@extends('layouts.app')

@section('title', 'Detalle '.$producto->titulo)

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

  .lamina-box {
    border: 1px solid var(--ec-border, #e5e7eb);
    background: #fafafa;
    padding: 14px;
  }

  .muted-box {
    border: 1px solid var(--ec-border, #e5e7eb);
    background: #fff;
    padding: 12px;
    font-size: .9rem;
    color: #4b5563;
  }

  .count-small,
  .msg-meta {
    color: #6b7280;
    font-size: .78rem;
  }

  .msg-item {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
  }

  .msg-item:last-child {
    border-bottom: 0;
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

  .readonly-zone input,
  .readonly-zone textarea,
  .readonly-zone select {
    background: #f5f6f7 !important;
  }

  .copy-group {
    display: flex;
    gap: 8px;
    align-items: stretch;
  }

  .copy-group .form-control {
    flex: 1 1 auto;
  }

  .btn-copy {
    flex: 0 0 42px;
    width: 42px;
    min-width: 42px;
    border-radius: 0;
    border: 1px solid #ced4da;
    background: #fff;
    color: #333;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .15s ease-in-out;
  }

  .btn-copy:hover {
    background: #f1f3f5;
    color: #000;
  }

  .btn-copy.copied {
    background: #198754;
    border-color: #198754;
    color: #fff;
  }

  .archivo-link-box {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .archivo-link-box a {
    flex: 1 1 auto;
    min-width: 0;
  }

  .copy-toast {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 1080;
    background: #111;
    color: #fff;
    padding: 10px 14px;
    font-size: .85rem;
    border-radius: 0;
    opacity: 0;
    transform: translateY(10px);
    transition: all .18s ease;
    pointer-events: none;
  }

  .copy-toast.show {
    opacity: 1;
    transform: translateY(0);
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

  .btn-action-send {
    background: #dcfce7;
    border-color: #86efac;
    color: #166534;
  }

  .btn-action-send:hover {
    background: #bbf7d0;
    border-color: #4ade80;
    color: #166534;
  }

  .btn-action-return {
    background: #fef3c7;
    border-color: #fcd34d;
    color: #92400e;
  }

  .btn-action-return:hover {
    background: #fde68a;
    border-color: #fbbf24;
    color: #78350f;
  }

  .preview-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
    gap: .65rem;
  }

  .preview-media-item {
    border: 1px solid #e5e7eb;
    background: #fff;
    min-height: 92px;
    overflow: hidden;
  }

  .preview-media-thumb {
    display: block;
    width: 100%;
    height: 72px;
    object-fit: cover;
    background: #f3f4f6;
  }

  .preview-media-file {
    align-items: center;
    background: #f9fafb;
    color: #374151;
    display: flex;
    font-size: .72rem;
    font-weight: 700;
    height: 72px;
    justify-content: center;
    padding: .5rem;
    text-align: center;
  }

  .preview-media-name {
    border-top: 1px solid #f3f4f6;
    font-size: .7rem;
    overflow: hidden;
    padding: .35rem .4rem;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  @media (max-width: 991.98px) {
    .editor-layout {
      grid-template-columns: 1fr;
    }

    .editor-actions .btn {
      font-size: .78rem;
      padding: .4rem .6rem;
    }
  }
</style>
@endpush

@section('content')
@php
  $laminasRows = old('laminas', $laminasData);
  $programadoMetricool = (bool) old('programado_metricool', $producto->programado_metricool);
  $metricoolBloqueado = (bool) $producto->programado_metricool;
  $canvaBloqueado = filled($producto->canva_url);
@endphp

<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Detalle del producto</h1>
      <div class="text-muted small">Diseño</div>
    </div>

    <a href="{{ route('disenador.productos.index') }}" class="btn btn-outline-secondary rounded-0">
      Volver al listado
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
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
          <form method="POST" id="formMain" action="{{ route('disenador.productos.update', $producto) }}">
            @csrf
            @method('PUT')

            <div class="readonly-zone">
              <div class="mb-3">
                <label class="form-label">Título</label>
                <div class="copy-group">
                  <input type="text" class="form-control js-copy-source" value="{{ $producto->titulo }}" readonly>
                  <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                  </button>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-3">
                  <label class="form-label">Fecha</label>
                  <input type="date" class="form-control js-copy-source" value="{{ optional($producto->fecha)->format('Y-m-d') }}" readonly>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Hora</label>
                  <input type="time" class="form-control js-copy-source" value="{{ $producto->hora ? substr($producto->hora, 0, 5) : '' }}" readonly>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Sección</label>
                  <input type="text" class="form-control js-copy-source" value="{{ $producto->seccion }}" readonly>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Prioridad</label>
                  <input type="text" class="form-control js-copy-source" value="{{ $producto->prioridad }}" readonly>
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold">Láminas</div>
              </div>

              <div id="laminasWrap" class="mb-3">
                @forelse($laminasRows as $index => $lamina)
                  <div class="lamina-box mb-3" data-lamina="{{ $index }}">
                    <div class="fw-bold mb-2">{{ $index === 0 ? 'Portada' : 'Lámina '.($index + 1) }}</div>

                    <div class="mb-2">
                      <label class="form-label">Título</label>
                      <div class="copy-group">
                        <input type="text" class="form-control js-copy-source" value="{{ $lamina['titulo'] ?? '' }}" readonly>
                        <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                          <i class="bi bi-clipboard"></i>
                        </button>
                      </div>
                    </div>

                    <div class="mb-2">
                      <label class="form-label">Descripción</label>
                      <div class="copy-group">
                        <textarea class="form-control js-copy-source" rows="3" readonly>{{ $lamina['descripcion'] ?? '' }}</textarea>
                        <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                          <i class="bi bi-clipboard"></i>
                        </button>
                      </div>
                    </div>

                    @if(!empty($lamina['archivos']))
                      <div class="mb-2">
                        <label class="form-label">Archivos adjuntos</label>
                        <div class="d-grid gap-2">
                          @foreach($lamina['archivos'] as $archivo)
                            @php
                              $archivoPath = $archivo['archivo_path'] ?? null;
                              $archivoUrl = $archivoPath
                                ? (preg_match('/^https?:\/\//i', $archivoPath) ? $archivoPath : asset('storage/'.ltrim($archivoPath, '/')))
                                : null;
                            @endphp

                            @if($archivoUrl)
                              <div class="archivo-link-box">
                                <a href="{{ $archivoUrl }}" target="_blank" class="form-control rounded-0 text-decoration-none d-flex align-items-center">
                                  {{ $archivo['archivo_original'] ?? 'Ver archivo' }}
                                </a>
                                <button type="button"
                                        class="btn btn-copy js-copy-text"
                                        data-copy-text="{{ $archivoUrl }}"
                                        title="Copiar enlace">
                                  <i class="bi bi-clipboard"></i>
                                </button>
                              </div>
                            @endif
                          @endforeach
                        </div>
                      </div>
                    @endif

                    @if(!empty($lamina['url_externa']))
                      <div class="mb-2">
                        <label class="form-label">URL externa</label>
                        <div class="archivo-link-box">
                          <a href="{{ $lamina['url_externa'] }}" target="_blank" class="form-control rounded-0 text-decoration-none d-flex align-items-center">
                            {{ $lamina['url_externa'] }}
                          </a>
                          <button type="button"
                                  class="btn btn-copy js-copy-text"
                                  data-copy-text="{{ $lamina['url_externa'] }}"
                                  title="Copiar URL">
                            <i class="bi bi-clipboard"></i>
                          </button>
                        </div>
                      </div>
                    @endif

                    <div class="row g-2">
                      <div class="col-md-3">
                        <label class="form-label">Sin foto</label>
                        <input type="text" class="form-control js-copy-source" value="{{ !empty($lamina['sin_foto']) ? 'Sí' : 'No' }}" readonly>
                      </div>

                      <div class="col-md-9">
                        <label class="form-label">Motivo</label>
                        <input type="text" class="form-control js-copy-source" value="{{ $lamina['motivo'] ?? '' }}" readonly>
                      </div>
                    </div>
                  </div>
                @empty
                  <div class="text-muted">No hay láminas.</div>
                @endforelse
              </div>

              <div class="mb-3">
                <label class="form-label">Copy / caption sugerido</label>
                <div class="copy-group">
                  <textarea class="form-control js-copy-source" rows="4" id="copyInput" readonly>{{ $producto->copy }}</textarea>
                  <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                  </button>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Hashtags</label>
                <div class="copy-group">
                  <input type="text" class="form-control js-copy-source" value="{{ $producto->hashtags }}" readonly>
                  <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                  </button>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Créditos</label>
                <div class="copy-group">
                  <input type="text" class="form-control js-copy-source" value="{{ $producto->creditos }}" readonly>
                  <button type="button" class="btn btn-copy js-copy-btn" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                  </button>
                </div>
              </div>
            </div>

            <hr>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label d-block">Programado en Metricool</label>
                <div class="form-check mt-2">
                  <input class="form-check-input"
                         type="checkbox"
                         name="programado_metricool"
                         id="chkMetricool"
                         value="1"
                         @disabled($metricoolBloqueado)
                         {{ old('programado_metricool', $producto->programado_metricool) ? 'checked' : '' }}>
                  <label class="form-check-label" for="chkMetricool">Sí</label>
                </div>
                @if($metricoolBloqueado)
                  <div class="text-muted small mt-2">Ya quedó programado y no se puede desmarcar.</div>
                @endif
              </div>
            </div>

            @if($metricoolBloqueado)
              <input type="hidden" name="programado_metricool" value="1">
            @endif

            <input type="hidden" name="accion" id="accionHidden" value="">
            <input type="hidden" name="canva_url" id="canvaUrlHidden" value="{{ old('canva_url', $producto->canva_url) }}">

            <div class="editor-actions">
              <a href="{{ route('disenador.productos.index') }}" class="btn btn-outline-secondary rounded-0">
                Cancelar
              </a>

              <button type="button" class="btn btn-action-save rounded-0" id="btnGuardar">
                Guardar
              </button>

              <button type="button" class="btn btn-action-send rounded-0" id="btnFinalizar">
                Guardar y finalizar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <aside class="editor-side">
      <div class="card mb-3">
        <div class="card-header">Producto</div>
        <div class="card-body">
          <div class="product-meta">
            <div class="product-meta-item">
              <span class="product-meta-label">Estado</span>
              <strong>{{ $producto->estado }}</strong>
            </div>
            <div class="product-meta-item">
              <span class="product-meta-label">Responsable</span>
              <strong>{{ $producto->user?->name ?? '-' }}</strong>
            </div>
            <div class="product-meta-item">
              <span class="product-meta-label">Diseñador</span>
              <strong>{{ $producto->disenador?->name ?? '-' }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Vista previa</div>
        <div class="card-body">
          <div class="muted-box" style="min-height:170px;">
            <div class="preview-media-grid">
              @foreach($laminasRows as $lamina)
                @foreach(($lamina['archivos'] ?? []) as $archivo)
                  @php
                    $archivoPath = $archivo['archivo_path'] ?? null;
                    $archivoUrl = $archivoPath
                      ? (preg_match('/^https?:\/\//i', $archivoPath) ? $archivoPath : asset('storage/'.ltrim($archivoPath, '/')))
                      : null;
                    $isImage = $archivoPath && preg_match('/\.(jpg|jpeg|png|gif|webp|avif|svg)$/i', $archivoPath);
                  @endphp

                  @if($archivoUrl)
                    <a class="preview-media-item text-decoration-none text-reset" href="{{ $archivoUrl }}" target="_blank" rel="noopener">
                      @if($isImage)
                        <img class="preview-media-thumb" src="{{ $archivoUrl }}" alt="{{ $archivo['archivo_original'] ?? 'Archivo' }}">
                      @else
                        <div class="preview-media-file">Archivo</div>
                      @endif
                      <div class="preview-media-name">{{ $archivo['archivo_original'] ?? 'Archivo' }}</div>
                    </a>
                  @endif
                @endforeach
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <i class="bi bi-clock-history me-1"></i> Movimientos
        </div>
        <div class="card-body" style="max-height:220px; overflow:auto;">
          @include('periodista.productos.partials.movimientos', ['movimientos' => $movimientos])
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="bi bi-chat-dots me-1"></i> Mensajes
        </div>
        <div class="card-body" style="max-height:240px; overflow:auto;">
          @include('periodista.productos.partials.mensajes', ['mensajes' => $mensajes])
        </div>

        <div class="card-footer bg-white border-top">
          <form method="POST" action="{{ route('disenador.productos.mensajes.store', $producto) }}">
            @csrf
            <div class="input-group">
              <input type="text" name="mensaje" class="form-control rounded-0" placeholder="Escribe un mensaje..." required>
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

<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-0">
      <div class="modal-header">
        <h5 class="modal-title">Guardar y finalizar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">URL de Canva</label>
        <input type="url"
               class="form-control rounded-0"
               id="canvaUrlInput"
               placeholder="https://www.canva.com/design/..."
               value="{{ old('canva_url', $producto->canva_url) }}"
               @readonly($canvaBloqueado)>
        <div class="invalid-feedback">La URL de Canva es obligatoria.</div>
        @if($canvaBloqueado)
          <div class="text-muted small mt-2">La URL de Canva ya fue registrada y no se puede cambiar.</div>
        @endif
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-action-send rounded-0" id="btnConfirmarFinalizar">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<div class="copy-toast" id="copyToast">Copiado</div>
@include('periodista.productos.partials.autosave', [
  'autosaveEnabled' => true,
  'autosaveUrl' => route('disenador.productos.autosave', $producto),
  'autosaveFields' => ['programado_metricool'],
])
@endsection

@push('scripts')
<script>
(() => {
  const formMain = document.getElementById('formMain');
  const accionHidden = document.getElementById('accionHidden');
  const canvaUrlHidden = document.getElementById('canvaUrlHidden');
  const btnGuardar = document.getElementById('btnGuardar');
  const btnFinalizar = document.getElementById('btnFinalizar');
  const btnConfirmarFinalizar = document.getElementById('btnConfirmarFinalizar');
  const canvaUrlInput = document.getElementById('canvaUrlInput');
  const modalFinalizarEl = document.getElementById('modalFinalizar');
  const copyToast = document.getElementById('copyToast');

  function showCopyToast(text) {
    if (!copyToast) return;
    copyToast.textContent = text || 'Copiado';
    copyToast.classList.add('show');
    clearTimeout(window.__copyToastTimer);
    window.__copyToastTimer = setTimeout(() => copyToast.classList.remove('show'), 1200);
  }

  async function copyPlainText(text) {
    const plain = String(text || '');

    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(plain);
      return;
    }

    const temp = document.createElement('textarea');
    temp.value = plain;
    temp.setAttribute('readonly', '');
    temp.style.position = 'fixed';
    temp.style.opacity = '0';
    document.body.appendChild(temp);
    temp.focus();
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
  }

  function setCopiedState(btn) {
    const icon = btn.querySelector('i');
    btn.classList.add('copied');
    if (icon) icon.className = 'bi bi-check2';
    setTimeout(() => {
      btn.classList.remove('copied');
      if (icon) icon.className = 'bi bi-clipboard';
    }, 1200);
  }

  document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-copy-btn, .js-copy-text');
    if (!button) return;

    event.preventDefault();

    const group = button.closest('.copy-group');
    const field = group ? group.querySelector('.js-copy-source, input, textarea') : null;
    const text = button.dataset.copyText ?? field?.value ?? field?.textContent ?? '';

    try {
      await copyPlainText(text);
      setCopiedState(button);
      showCopyToast('Copiado al portapapeles');
    } catch (error) {
      showCopyToast('No se pudo copiar');
    }
  });

  btnGuardar?.addEventListener('click', () => {
    accionHidden.value = 'guardar';
    formMain.submit();
  });

  btnFinalizar?.addEventListener('click', () => {
    if (window.bootstrap && modalFinalizarEl) {
      bootstrap.Modal.getOrCreateInstance(modalFinalizarEl).show();
    }
  });

  btnConfirmarFinalizar?.addEventListener('click', () => {
    const value = (canvaUrlInput?.value || '').trim();
    if (!value) {
      canvaUrlInput?.classList.add('is-invalid');
      return;
    }

    if (!value.startsWith('https://')) {
      canvaUrlInput?.classList.add('is-invalid');
      return;
    }

    canvaUrlInput?.classList.remove('is-invalid');
    canvaUrlHidden.value = value;
    accionHidden.value = 'finalizar';
    formMain.submit();
  });

  canvaUrlInput?.addEventListener('input', () => canvaUrlInput.classList.remove('is-invalid'));
})();
</script>
@endpush
