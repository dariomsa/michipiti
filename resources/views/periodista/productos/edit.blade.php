@extends('layouts.app')

@section('title', ($readOnly ? 'Ver ' : 'Editar ').$producto->titulo)

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

  .laminas-toolbar {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin: 0 -1rem .75rem;
    padding: .65rem 1rem;
    position: sticky;
    top: 4.75rem;
    z-index: 1030;
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

  .btn-send-design {
    background: #fde68a;
    border-color: #fcd34d;
    color: #78350f;
  }

  .btn-send-design:hover {
    background: #fcd34d;
    border-color: #fbbf24;
    color: #78350f;
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

  .btn-action-finalize {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #b91c1c;
  }

  .btn-action-finalize:hover {
    background: #fecaca;
    border-color: #f87171;
    color: #991b1b;
  }

  @media (max-width: 991.98px) {
    .editor-layout {
      grid-template-columns: 1fr;
    }

    .laminas-toolbar {
      top: 4.25rem;
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
  $isEditor = auth()->user()?->hasRole('editor');
  $isRevisionEditableByEditor = $isEditor && in_array($producto->estado, ['BORRADOR', 'EN_REVISION'], true);
@endphp

<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">{{ $readOnly ? 'Detalle del producto' : 'Panel de edición' }}</h1>
      <div class="text-muted small">{{ $producto->tipoProducto?->nombre ?? 'Producto' }}</div>
    </div>

    <a href="{{ route($routeBase.'.index') }}" class="btn btn-outline-secondary rounded-0">
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

  @if($readOnly)
    <div class="alert alert-secondary rounded-0">
      Este producto está en modo visualización. No se permiten cambios ni acciones mientras no esté en un estado editable.
    </div>
  @endif

  <div class="editor-layout">
    <div class="editor-panel">
      <div class="card card-form">
        <div class="card-body">
          <form method="POST"
                id="formMain"
                action="{{ route($routeBase.'.update', $producto) }}"
                enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <fieldset @disabled($readOnly)>

            <div class="mb-3">
              <label class="form-label">Título</label>
              <input required
                     type="text"
                     name="titulo"
                     class="form-control rounded-0"
                     placeholder="Escribir titular"
                     value="{{ old('titulo', $producto->titulo) }}">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input required
                       type="date"
                       name="fecha"
                       class="form-control rounded-0"
                       value="{{ old('fecha', optional($producto->fecha)->format('Y-m-d')) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Hora</label>
                <input required
                       type="time"
                       name="hora"
                       class="form-control rounded-0"
                       value="{{ old('hora', $producto->hora ? substr($producto->hora, 0, 5) : '') }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Sección</label>
                <select required name="seccion" class="form-select rounded-0">
                  <option value="" disabled {{ old('seccion', $producto->seccion) ? '' : 'selected' }}>
                    Seleccione una sección
                  </option>

                  @foreach($secciones as $seccion)
                    <option value="{{ $seccion->nombre }}" @selected(old('seccion', $producto->seccion) === $seccion->nombre)>
                      {{ $seccion->nombre }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Prioridad</label>
                <select name="prioridad" class="form-select rounded-0" required>
                  <option value="" disabled {{ old('prioridad', $producto->prioridad) ? '' : 'selected' }}>
                    Seleccione prioridad
                  </option>
                  @foreach($prioridades as $prioridad)
                    <option value="{{ $prioridad }}" @selected(old('prioridad', $producto->prioridad) === $prioridad)>
                      {{ $prioridad }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            @if($canEditLaminas)
              <div class="laminas-toolbar mb-2">
                <div class="fw-bold">Láminas</div>
                @unless($readOnly)
                  <button type="button" class="btn btn-dark btn-sm rounded-0" id="btnAddLamina">
                    <i class="bi bi-plus-lg"></i> Agregar lámina
                  </button>
                @endunless
              </div>

              <div id="laminasWrap" class="mb-3">
                @foreach($laminasRows as $index => $lamina)
                  @include('periodista.productos.partials.lamina_form', [
                    'index' => $index,
                    'lamina' => $lamina,
                  ])
                @endforeach
              </div>
            @else
              <div class="muted-box mb-3">
                Este producto no usa láminas porque su tipo no es carrusel.
              </div>
            @endif

            <div class="mb-3">
              <label class="form-label">Copy / caption sugerido</label>
              <textarea name="copy"
                        id="copyInput"
                        class="form-control rounded-0"
                        rows="4"
                        placeholder="Escribir el copy">{{ old('copy', $producto->copy) }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Hashtags</label>
              <input type="text"
                     name="hashtags"
                     class="form-control rounded-0"
                     placeholder="#hashtags sugeridos"
                     required
                     value="{{ old('hashtags', $producto->hashtags) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Créditos</label>
              <input type="text"
                     name="creditos"
                     class="form-control rounded-0"
                     placeholder="Créditos"
                     value="{{ old('creditos', $producto->creditos) }}">
            </div>
            </fieldset>

            <input type="hidden" name="accion" id="accionHidden" value="">
            <input type="hidden" name="canva_url" id="canvaUrlHidden" value="{{ old('canva_url', $producto->canva_url) }}">

            <div class="editor-actions">
              <a href="{{ route($routeBase.'.index') }}" class="btn btn-outline-secondary rounded-0">
                Cancelar
              </a>
              @unless($readOnly)
                <button type="button" class="btn btn-action-save rounded-0" id="btnGuardar">
                  Guardar
                </button>

                <button type="button"
                        class="btn btn-action-send rounded-0"
                        id="btnEnviarRevision">
                  {{ $isRevisionEditableByEditor ? 'Enviar a diseño' : 'Enviar a revisión' }}
                </button>

                <button type="button" class="btn btn-action-finalize rounded-0" id="btnFinalizarSinRevision">
                  Finalizar sin revisión
                </button>
              @endunless
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
          <div class="muted-box" id="prevLaminaBox" style="min-height:170px;">
            <div class="preview-media-grid" id="prevMediaGrid"></div>
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

        @unless($readOnly)
          <div class="card-footer bg-white border-top">
            <form method="POST" action="{{ route($routeBase.'.mensajes.store', $producto) }}">
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
        @endunless
      </div>
    </aside>
  </div>
</section>

<div class="modal fade" id="modalCanva" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-0">
      <div class="modal-header">
        <h5 class="modal-title">Finalizar sin revisión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="text-muted mb-2" style="font-size:.9rem;">
          Pega el enlace de Canva del diseño final. Es obligatorio para finalizar.
        </div>

        <label class="form-label">Enlace Canva</label>
        <input type="url"
               class="form-control rounded-0"
               id="canvaUrlInput"
               placeholder="https://www.canva.com/design/..."
               value="{{ old('canva_url', $producto->canva_url) }}">

        <div class="invalid-feedback" id="canvaError">
          El enlace de Canva es obligatorio y debe ser una URL válida.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="button" class="btn btn-dark rounded-0" id="btnConfirmarFinalizar">
          Finalizar
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const formMain = document.getElementById('formMain');
  const laminasWrap = document.getElementById('laminasWrap');
  const btnAddLamina = document.getElementById('btnAddLamina');
  const btnGuardar = document.getElementById('btnGuardar');
  const btnEnviarRevision = document.getElementById('btnEnviarRevision');
  const btnFinalizar = document.getElementById('btnFinalizarSinRevision');
  const accionHidden = document.getElementById('accionHidden');
  const canvaUrlHidden = document.getElementById('canvaUrlHidden');
  const modalCanvaEl = document.getElementById('modalCanva');
  const canvaUrlInput = document.getElementById('canvaUrlInput');
  const canvaError = document.getElementById('canvaError');
  const btnConfirmarFinalizar = document.getElementById('btnConfirmarFinalizar');

  const q = (selector, root = document) => root.querySelector(selector);
  const qa = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function setInvalid(field, message) {
    if (!field) return;
    field.classList.add('is-invalid');

    let feedback = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
    if (!feedback && field.parentElement) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      field.parentElement.appendChild(feedback);
    }

    if (feedback) {
      feedback.textContent = message;
    }
  }

  function clearInvalid(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
  }

  function fileHasValue(input) {
    return !!(input && input.files && input.files.length > 0);
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function fileNameFromUrl(url) {
    const parts = (url || '').split('/');
    return parts[parts.length - 1] || 'Archivo';
  }

  function isImageFile(name = '', type = '') {
    return type.startsWith('image/')
      || /\.(jpg|jpeg|png|gif|webp|avif|svg)$/i.test(name);
  }

  function buildMediaPreviewItem({ name, url, isImage }) {
    const safeName = escapeHtml(name || 'Archivo');
    const safeUrl = escapeHtml(url || '#');

    return `
      <a class="preview-media-item text-decoration-none text-reset" href="${safeUrl}" target="_blank" rel="noopener">
        ${isImage
          ? `<img class="preview-media-thumb" src="${safeUrl}" alt="${safeName}">`
          : `<div class="preview-media-file">Archivo</div>`}
        <div class="preview-media-name" title="${safeName}">${safeName}</div>
      </a>
    `;
  }

  function refreshPreview() {
    const previewMediaGrid = document.getElementById('prevMediaGrid');

    if (previewMediaGrid) {
      const items = [];

      qa('[data-lamina]', formMain || document).forEach((laminaBox) => {
        qa('.jsExistingArchivo', laminaBox).forEach((node) => {
          const archivoId = node.dataset.archivoId;
          const deleteCheckbox = archivoId
            ? q(`input[name^="laminas["][name$="[delete_archivos][]"][value="${archivoId}"]`, laminaBox)
            : null;

          if (deleteCheckbox && deleteCheckbox.checked) {
            return;
          }

          const replaceInput = archivoId
            ? q(`input[name^="laminas["][name$="[replace_archivos][${archivoId}]"]`, laminaBox)
            : null;

          if (replaceInput && replaceInput.files && replaceInput.files[0]) {
            const file = replaceInput.files[0];
            items.push(buildMediaPreviewItem({
              name: file.name,
              url: URL.createObjectURL(file),
              isImage: isImageFile(file.name, file.type),
            }));
            return;
          }

          const href = node.dataset.fileUrl || '';
          items.push(buildMediaPreviewItem({
            name: node.dataset.fileName || fileNameFromUrl(href),
            url: href,
            isImage: isImageFile(node.dataset.fileName || href),
          }));
        });

        qa('input[name^="laminas["][name$="[archivos][]"]', laminaBox).forEach((newFilesInput) => {
          if (!newFilesInput.files) {
            return;
          }

          Array.from(newFilesInput.files).forEach((file) => {
            items.push(buildMediaPreviewItem({
              name: file.name,
              url: URL.createObjectURL(file),
              isImage: isImageFile(file.name, file.type),
            }));
          });
        });
      });

      previewMediaGrid.innerHTML = items.length > 0
        ? items.join('')
        : '<div class="text-muted small">Sin archivos para previsualizar.</div>';
    }
  }

  function applyMediaRules(box) {
    if (!box) return;

    const archivo = q('.jsArchivo', box);
    const url = q('.jsUrl', box);
    const sinFoto = q('.jsSinFoto', box);
    const motivo = q('.jsMotivo', box);

    const hasFile = fileHasValue(archivo);
    const hasUrl = !!(url && url.value.trim());
    const isWithoutImage = !!(sinFoto && sinFoto.checked);

    if (archivo) archivo.disabled = false;
    if (url) url.disabled = false;
    if (sinFoto) sinFoto.disabled = false;
    if (motivo) motivo.disabled = !isWithoutImage;

    if (hasFile) {
      if (url) {
        url.value = '';
        url.disabled = true;
      }
      if (sinFoto) {
        sinFoto.checked = false;
        sinFoto.disabled = true;
      }
      if (motivo) {
        motivo.value = '';
        motivo.disabled = true;
      }
      return;
    }

    if (hasUrl) {
      if (archivo) {
        archivo.value = '';
        archivo.disabled = true;
      }
      if (sinFoto) {
        sinFoto.checked = false;
        sinFoto.disabled = true;
      }
      if (motivo) {
        motivo.value = '';
        motivo.disabled = true;
      }
      return;
    }

    if (isWithoutImage) {
      if (archivo) {
        archivo.value = '';
        archivo.disabled = true;
      }
      if (url) {
        url.value = '';
        url.disabled = true;
      }
    }
  }

  function initLaminaBox(box) {
    if (!box) return;

    const desc = q('.jsDesc', box);
    const count = q('.jsCount', box);
    if (desc && count) {
      count.textContent = String((desc.value || '').length);
    }

    applyMediaRules(box);
  }

  function validateMainForm() {
    const errors = [];
    qa('.is-invalid', formMain || document).forEach(clearInvalid);

    const requiredFields = [
      ['[name="titulo"]', 'Título'],
      ['[name="fecha"]', 'Fecha'],
      ['[name="hora"]', 'Hora'],
      ['[name="seccion"]', 'Sección'],
    ];

    requiredFields.forEach(([selector, label]) => {
      const field = q(selector, formMain);
      if (!field || !field.value.trim()) {
        setInvalid(field, `${label} obligatorio.`);
        errors.push(`${label}: es obligatorio.`);
      }
    });

    qa('[data-lamina]', formMain).forEach((box, index) => {
      const number = index + 1;
      const title = q('[name$="[titulo]"]', box);
      const description = q('[name$="[descripcion]"]', box);
      const sinFoto = q('.jsSinFoto', box);
      const motivo = q('.jsMotivo', box);

      if (title && !title.value.trim()) {
        setInvalid(title, `El título de la lámina ${number} es obligatorio.`);
        errors.push(`Lámina ${number}: título obligatorio.`);
      }

      if (description && !description.value.trim()) {
        setInvalid(description, `La descripción de la lámina ${number} es obligatoria.`);
        errors.push(`Lámina ${number}: descripción obligatoria.`);
      }

      if (sinFoto && sinFoto.checked && motivo && !motivo.value.trim()) {
        setInvalid(motivo, `Indica el motivo de la lámina ${number}.`);
        errors.push(`Lámina ${number}: si marcas "Sin foto", el motivo es obligatorio.`);
      }
    });

    return errors;
  }

  function newLaminaHtml(index) {
    const label = index === 0 ? 'Portada' : `Lámina ${index + 1}`;
    const allowsMultipleFiles = index === 0;

    return `
      <div class="lamina-box mb-3" data-lamina="${index}">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="fw-bold">${label}</div>
          ${index > 0 ? `
            <button type="button" class="btn btn-outline-danger btn-sm rounded-0 jsRemoveLamina">
              <i class="bi bi-trash"></i>
            </button>
          ` : ''}
        </div>

        <div class="mb-2">
          <input type="text"
                 class="form-control rounded-0"
                 name="laminas[${index}][titulo]"
                 placeholder="Título de la lámina ${index + 1}"
                 required>
        </div>

        <div class="mb-2">
          <textarea class="form-control rounded-0 jsDesc"
                    rows="2"
                    name="laminas[${index}][descripcion]"
                    placeholder="Descripción breve"
                    maxlength="600"
                    required></textarea>
          <div class="text-end count-small"><span class="jsCount">0</span> / 600</div>
        </div>

        <div class="mb-2">
          <label class="form-label mb-1">Adjuntar archivo${allowsMultipleFiles ? 's' : ''} (max 16 MB)</label>
          <input type="file"
                 class="form-control rounded-0 jsArchivo"
                 name="laminas[${index}][archivos][]"
                 ${allowsMultipleFiles ? 'multiple' : ''}>
          <div class="count-small mt-1">
            ${allowsMultipleFiles ? 'Portada: hasta 3 archivos.' : 'Esta lámina admite 1 archivo.'}
            Word / PDF / Imagen
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label mb-1">URL externa</label>
          <input type="text"
                 class="form-control rounded-0 jsUrl"
                 name="laminas[${index}][url_externa]"
                 placeholder="https://...">
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input jsSinFoto"
                 type="checkbox"
                 value="1"
                 id="sinFoto${index}"
                 name="laminas[${index}][sin_foto]">
          <label class="form-check-label" for="sinFoto${index}">
            Sin foto
          </label>
        </div>

        <div class="mb-0">
          <input type="text"
                 class="form-control rounded-0 jsMotivo"
                 name="laminas[${index}][motivo]"
                 placeholder="Motivo"
                 disabled>
        </div>
      </div>
    `;
  }

  function isValidUrl(value) {
    try {
      const url = new URL(value.trim());
      return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (error) {
      return false;
    }
  }

  function openCanvaModal() {
    if (window.bootstrap && modalCanvaEl) {
      bootstrap.Modal.getOrCreateInstance(modalCanvaEl).show();
    }
  }

  function submitWithAction(action) {
    if (!formMain) return;

    const errors = validateMainForm();
    if (errors.length > 0) {
      return;
    }

    accionHidden.value = action;
    formMain.submit();
  }

  if (btnAddLamina && laminasWrap) {
    btnAddLamina.addEventListener('click', () => {
      const index = qa('[data-lamina]', laminasWrap).length;
      const wrapper = document.createElement('div');
      wrapper.innerHTML = newLaminaHtml(index);
      const box = wrapper.firstElementChild;
      laminasWrap.appendChild(box);
      initLaminaBox(box);
    });
  }

  document.addEventListener('click', (event) => {
    const removeButton = event.target.closest('.jsRemoveLamina');
    if (!removeButton) return;

    const box = removeButton.closest('[data-lamina]');
    if (box) {
      box.remove();
      refreshPreview();
    }
  });

  document.addEventListener('input', (event) => {
    if (event.target.classList.contains('jsDesc')) {
      const box = event.target.closest('[data-lamina]');
      const counter = q('.jsCount', box);
      if (counter) {
        counter.textContent = String(event.target.value.length);
      }
    }

    if (event.target.classList.contains('jsUrl')) {
      applyMediaRules(event.target.closest('[data-lamina]'));
    }

    if (event.target === canvaUrlInput) {
      canvaUrlInput.classList.remove('is-invalid');
      canvaError.style.display = 'none';
    }
  });

  document.addEventListener('change', (event) => {
    if (
      event.target.classList.contains('jsArchivo')
      || event.target.classList.contains('jsSinFoto')
      || event.target.classList.contains('jsReplaceArchivo')
      || event.target.classList.contains('jsDeleteArchivo')
    ) {
      applyMediaRules(event.target.closest('[data-lamina]'));
      refreshPreview();
    }
  });

  if (btnEnviarRevision) {
    btnEnviarRevision.addEventListener('click', () => submitWithAction('revision'));
  }

  if (btnGuardar) {
    btnGuardar.addEventListener('click', () => submitWithAction('guardar'));
  }

  if (btnFinalizar) {
    btnFinalizar.addEventListener('click', () => {
      const errors = validateMainForm();
      if (errors.length > 0) {
        return;
      }

      openCanvaModal();
    });
  }

  if (btnConfirmarFinalizar) {
    btnConfirmarFinalizar.addEventListener('click', () => {
      const value = canvaUrlInput ? canvaUrlInput.value.trim() : '';
      if (!value || !isValidUrl(value)) {
        if (canvaUrlInput) {
          canvaUrlInput.classList.add('is-invalid');
        }
        if (canvaError) {
          canvaError.style.display = 'block';
        }
        return;
      }

      canvaUrlHidden.value = value;
      accionHidden.value = 'finalizar';
      formMain.submit();
    });
  }

  if (formMain) {
    formMain.addEventListener('submit', (event) => {
      if (!accionHidden.value) {
        event.preventDefault();
      }
    });
  }

  qa('[data-lamina]').forEach(initLaminaBox);
  refreshPreview();
})();
</script>
@endpush
