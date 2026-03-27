@php
  $laminaId = $lamina['id'] ?? null;
  $titulo = $lamina['titulo'] ?? '';
  $descripcion = $lamina['descripcion'] ?? '';
  $urlExterna = $lamina['url_externa'] ?? '';
  $sinFoto = (bool) ($lamina['sin_foto'] ?? false);
  $motivo = $lamina['motivo'] ?? '';
  $archivos = $lamina['archivos'] ?? [];
  $maxArchivos = 1;
@endphp

<div class="lamina-box mb-3" data-lamina="{{ $index }}">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="fw-bold jsLaminaHeading">
      {{ $index === 0 ? 'Portada' : 'Lámina '.($index + 1) }}
    </div>

    @if($index > 0)
      <button type="button" class="btn btn-outline-danger btn-sm rounded-0 jsRemoveLamina">
        <i class="bi bi-trash"></i>
      </button>
    @endif
  </div>

  @if($laminaId)
    <input type="hidden" name="laminas[{{ $index }}][id]" value="{{ $laminaId }}">
  @endif

  <div class="mb-2">
    <input type="text"
           class="form-control rounded-0"
           name="laminas[{{ $index }}][titulo]"
           placeholder="Título de la lámina {{ $index + 1 }}"
           value="{{ old("laminas.$index.titulo", $titulo) }}"
           required>
    <div class="invalid-feedback">
      El título de la lámina es obligatorio.
    </div>
  </div>

  <div class="mb-2">
    <textarea class="form-control rounded-0 jsDesc"
              rows="2"
              name="laminas[{{ $index }}][descripcion]"
              placeholder="Descripción breve"
              maxlength="150"
              required>{{ old("laminas.$index.descripcion", $descripcion) }}</textarea>

    <div class="text-end count-small">
      <span class="jsCount">{{ strlen(old("laminas.$index.descripcion", $descripcion)) }}</span> / 150
    </div>
  </div>

  <div class="mb-2">
    <label class="form-label mb-1">
      Adjuntar archivo{{ $maxArchivos > 1 ? 's' : '' }} (max 16 MB)
    </label>
    <input type="file"
           class="form-control rounded-0 jsArchivo"
           name="laminas[{{ $index }}][archivos][]"
           @if($maxArchivos > 1) multiple @endif>
    <div class="count-small mt-1">
      {{ $index === 0 ? 'Portada: admite 1 archivo.' : 'Esta lámina admite 1 archivo.' }}
      Word / PDF / Imagen
    </div>

    @if($archivos !== [])
      <div class="mt-2" style="font-size:.85rem;">
        <strong>Archivo{{ count($archivos) > 1 ? 's' : '' }} actual{{ count($archivos) > 1 ? 'es' : '' }}:</strong>
        <div class="mt-2 d-grid gap-2">
          @foreach($archivos as $archivo)
            @php
              $archivoId = $archivo['id'] ?? null;
              $archivoPath = $archivo['archivo_path'] ?? null;
              $archivoUrl = null;
              if ($archivoPath) {
                  $archivoUrl = preg_match('/^https?:\/\//i', $archivoPath)
                      ? $archivoPath
                      : asset('storage/'.ltrim($archivoPath, '/'));
              }
            @endphp

            <div class="border rounded-0 p-2 bg-white">
              @if($archivoUrl)
                <div class="jsExistingArchivo"
                     data-archivo-id="{{ $archivoId }}"
                     data-file-url="{{ $archivoUrl }}"
                     data-file-name="{{ $archivo['archivo_original'] ?? 'Ver archivo' }}">
                  <a href="{{ $archivoUrl }}" target="_blank" rel="noopener">
                    {{ $archivo['archivo_original'] ?? 'Ver archivo' }}
                  </a>
                </div>
              @endif

              @if($archivoId)
                <div class="mt-2 d-grid gap-2">
                  <div>
                    <label class="form-label mb-1">Reemplazar este archivo</label>
                    <input type="file"
                           class="form-control rounded-0 jsReplaceArchivo"
                           name="laminas[{{ $index }}][replace_archivos][{{ $archivoId }}]">
                  </div>

                  <div class="form-check">
                    <input class="form-check-input jsDeleteArchivo"
                           type="checkbox"
                           value="{{ $archivoId }}"
                           id="deleteArchivo{{ $index }}_{{ $archivoId }}"
                           name="laminas[{{ $index }}][delete_archivos][]">
                    <label class="form-check-label" for="deleteArchivo{{ $index }}_{{ $archivoId }}">
                      Eliminar este archivo
                    </label>
                  </div>
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <div class="mb-2">
    <label class="form-label mb-1">URL externa</label>
    <input type="text"
           class="form-control rounded-0 jsUrl"
           name="laminas[{{ $index }}][url_externa]"
           placeholder="https://..."
           value="{{ old("laminas.$index.url_externa", $urlExterna) }}">

    @if($urlExterna)
      <div class="mt-2" style="font-size:.85rem;">
        <strong>URL actual:</strong>
        <a href="{{ $urlExterna }}" target="_blank" rel="noopener">Ver</a>
      </div>
    @endif
  </div>

  <div class="form-check mb-2">
    <input class="form-check-input jsSinFoto"
           type="checkbox"
           value="1"
           id="sinFoto{{ $index }}"
           name="laminas[{{ $index }}][sin_foto]"
           {{ old("laminas.$index.sin_foto", $sinFoto ? 1 : 0) ? 'checked' : '' }}>
    <label class="form-check-label" for="sinFoto{{ $index }}">
      Sin foto
    </label>
  </div>

  <div class="mb-0">
    <input type="text"
           class="form-control rounded-0 jsMotivo"
           name="laminas[{{ $index }}][motivo]"
           placeholder="Motivo"
           value="{{ old("laminas.$index.motivo", $motivo) }}"
           {{ old("laminas.$index.sin_foto", $sinFoto ? 1 : 0) ? '' : 'disabled' }}>
  </div>
</div>
