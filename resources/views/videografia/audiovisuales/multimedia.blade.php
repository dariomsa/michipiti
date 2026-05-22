@extends('layouts.app')

@section('title','Multimedia Audiovisual')

@push('styles')
<style>
  .media-link-btn {
    border-radius: 0;
  }
</style>
@endpush

@section('content')
<section class="flex-grow-1 compact-listing">
  @if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
  @endif

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0">Multimedia</h1>
  </div>

  <div class="card card-form mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('videografia.audiovisuales.multimedia') }}">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-lg-5">
            <label class="form-label mb-1">Buscar</label>
            <div class="input-group">
              <span class="input-group-text" style="border-radius:0;">
                <i class="bi bi-search"></i>
              </span>
              <input
                type="text"
                class="form-control"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Título, sección o file id"
                style="border-radius:0;"
              >
            </div>
          </div>

          <div class="col-12 col-lg-2 d-grid">
            <label class="form-label mb-1 d-none d-md-block">&nbsp;</label>
            <button class="btn btn-outline-secondary" type="submit" style="border-radius:0;">
              Buscar
            </button>
          </div>

          <div class="col-12 d-flex gap-2 mt-2">
            <a class="btn btn-outline-secondary" href="{{ route('videografia.audiovisuales.multimedia') }}" style="border-radius:0;">
              Limpiar filtros
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card card-form">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead style="background:#f7f7f7;">
            <tr>
              <th style="padding:14px 16px;">Contenido</th>
              <th style="padding:14px 16px; width:140px;">Sección</th>
              <th style="padding:14px 16px; width:190px;">Responsable</th>
              <th style="padding:14px 16px; width:190px;">Videógrafo</th>
              <th style="padding:14px 16px; width:170px;">Fecha</th>
              <th style="padding:14px 16px; width:190px;">Archivo</th>
              <th style="padding:14px 16px; width:140px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($audiovisuales as $audiovisual)
              @php
                $mediaUrl = $audiovisual->slack_permalink ?: $audiovisual->slack_private_url;
              @endphp
              <tr>
                <td style="padding:16px;">
                  <a
                    href="{{ route('videografia.audiovisuales.edit', $audiovisual) }}"
                    class="text-decoration-none"
                    style="color:#1a73e8; font-weight:500;"
                  >
                    {{ \Illuminate\Support\Str::limit($audiovisual->titulo, 55) }}
                  </a>
                </td>

                <td style="padding:16px;">{{ $audiovisual->seccion ?: '-' }}</td>
                <td style="padding:16px;">{{ optional($audiovisual->user)->name ?: '-' }}</td>
                <td style="padding:16px;">{{ optional($audiovisual->disenador)->name ?: '-' }}</td>

                <td style="padding:16px;">
                  <div style="line-height:1.15;">
                    <div>{{ $audiovisual->fecha ? $audiovisual->fecha->format('d/m/Y') : '-' }}</div>
                    <div class="text-muted" style="font-size:.85rem;">
                      {{ $audiovisual->hora ? \Illuminate\Support\Str::substr((string) $audiovisual->hora, 0, 5) : '-' }}
                    </div>
                  </div>
                </td>

                <td style="padding:16px;">
                  <div class="small fw-semibold">{{ $audiovisual->archivo_final_original_name ?: 'Archivo en Slack' }}</div>
                  <div class="text-muted small">{{ $audiovisual->slack_file_id }}</div>
                </td>

                <td style="padding:16px;">
                  @if($mediaUrl)
                    <a href="{{ $mediaUrl }}"
                       class="btn btn-sm btn-outline-success media-link-btn"
                       target="_blank"
                       rel="noopener">
                      Abrir
                    </a>
                    <form method="POST"
                          action="{{ route('videografia.audiovisuales.slack-media.destroy', $audiovisual) }}"
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar este archivo de Slack? Esta acción no se puede deshacer.');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger media-link-btn">
                        Eliminar
                      </button>
                    </form>
                  @else
                    <span class="text-muted small">Sin enlace</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center p-4 text-muted">
                  No hay archivos multimedia para mostrar.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    {{ $audiovisuales->links() }}
  </div>
</section>
@endsection
