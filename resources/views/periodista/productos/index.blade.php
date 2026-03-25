@extends('layouts.app')

@section('title','Productos')

@push('styles')
<style>
  .action-btn-edit {
    background: #dbeafe;
    border-color: #bfdbfe;
    color: #1d4ed8;
  }

  .action-btn-edit:hover {
    background: #bfdbfe;
    border-color: #93c5fd;
    color: #1e40af;
  }

  .action-btn-view {
    background: #dcfce7;
    border-color: #bbf7d0;
    color: #15803d;
  }

  .action-btn-view:hover {
    background: #bbf7d0;
    border-color: #86efac;
    color: #166534;
  }

  .filters-row .form-label {
    font-size: .82rem;
  }

  .filters-row .form-control,
  .filters-row .form-select,
  .filters-row .input-group-text,
  .filters-row .btn {
    min-height: 40px;
    font-size: .88rem;
  }
</style>
@endpush

@section('content')
@php
  $searchColClass = 'col-12 col-xl-3 col-lg-4';
  $sectionColClass = 'col-12 col-xl-2 col-lg-2';
  $designerColClass = 'col-12 col-xl-2 col-lg-2';
  $stateColClass = 'col-12 col-xl-1 col-lg-2';
  $dateColClass = 'col-12 col-xl-1 col-lg-2';
  $submitColClass = 'col-12 col-xl-1 col-lg-12 d-grid';
@endphp
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0">Listado de Productos</h1>
  </div>

  <div class="card card-form mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route($routeBase.'.index') }}">
        <div class="row g-2 align-items-end filters-row">

          <div class="{{ $searchColClass }}">
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
                placeholder="Título, copy o hashtags"
                style="border-radius:0;"
              >
            </div>
          </div>

          <div class="{{ $sectionColClass }}">
            <label class="form-label mb-1">Sección</label>
            <select class="form-select" name="seccion" style="border-radius:0;">
              <option value="">Todas</option>
              @foreach($secciones as $s)
                <option value="{{ $s->nombre }}" {{ ($seccionFiltro ?? '') == $s->nombre ? 'selected' : '' }}>
                  {{ $s->nombre }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-xl-2 col-lg-2">
            <label class="form-label mb-1">Periodista</label>
            <select class="form-select" name="periodista" style="border-radius:0;">
              <option value="">Todos</option>
              @foreach($periodistas as $p)
                <option value="{{ $p->id }}" {{ (string)($periodistaFiltro ?? '') == (string)$p->id ? 'selected' : '' }}>
                  {{ $p->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="{{ $designerColClass }}">
            <label class="form-label mb-1">Diseñador</label>
            <select class="form-select" name="disenador" style="border-radius:0;">
              <option value="">Todos</option>
              @foreach($disenadores as $d)
                <option value="{{ $d->id }}" {{ (string)($disenadorFiltro ?? '') == (string)$d->id ? 'selected' : '' }}>
                  {{ $d->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="{{ $stateColClass }}">
            <label class="form-label mb-1">Estado</label>
            <select class="form-select" name="estado" style="border-radius:0;">
              <option value="">Todos</option>
              @foreach($estados as $e)
                <option value="{{ $e }}" @selected(($estado ?? '') === $e)>
                  {{ $e }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="{{ $dateColClass }}">
            <label class="form-label mb-1">Fecha</label>
            <input
              type="date"
              class="form-control"
              name="fecha"
              value="{{ $fecha ?? '' }}"
              style="border-radius:0;"
            >
          </div>

          <div class="{{ $submitColClass }}">
            <label class="form-label mb-1 d-none d-md-block">&nbsp;</label>
            <button class="btn btn-outline-secondary" type="submit" style="border-radius:0;">
              Buscar
            </button>
          </div>

          <div class="col-12 d-flex gap-2 mt-2">
            <a class="btn btn-outline-secondary" href="{{ route($routeBase.'.index') }}" style="border-radius:0;">
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
              <th style="padding:14px 16px; width:190px;">Periodista</th>
              <th style="padding:14px 16px; width:190px;">Diseñador</th>
              <th style="padding:14px 16px; width:170px;">Fecha</th>
              <th style="padding:14px 16px; width:150px;">Estado</th>
              <th style="padding:14px 16px; width:160px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($productos as $producto)
              @php
                $dotColor = '#9e9e9e';
                $label = $producto->estado;
                $esEditor = auth()->user()?->hasRole('editor');
                $esDisenador = auth()->user()?->hasAnyRole(['disenador', 'disenador_manager']);

                if ($producto->estado === 'APROBADO') $dotColor = '#2e7d32';
                if ($producto->estado === 'EN_REVISION') $dotColor = '#f9a825';
                if ($producto->estado === 'EN_DISENO') $dotColor = '#2563eb';
                if ($producto->estado === 'BORRADOR') $dotColor = '#9e9e9e';
                if ($producto->estado === 'DEVUELTO') $dotColor = '#ef6c00';
                if ($producto->estado === 'RECHAZADO') $dotColor = '#c62828';
                if ($producto->estado === 'PUBLICADO') $dotColor = '#1565c0';

                $puedeEditar = $esEditor
                  ? in_array($producto->estado, ['BORRADOR', 'EN_REVISION', 'DEVUELTO'], true)
                  : ($esDisenador ? $producto->estado === 'EN_DISENO' : in_array($producto->estado, ['BORRADOR', 'DEVUELTO'], true));
                $puedeVer = ! $puedeEditar;
              @endphp

              <tr>
                <td style="padding:16px;">
                  <a
                    href="{{ route($routeBase.'.edit', $producto->id) }}"
                    class="text-decoration-none"
                    style="color:#1a73e8; font-weight:500;"
                  >
                    {{ \Illuminate\Support\Str::limit($producto->titulo, 55) }}
                  </a>

                  @if($producto->hashtags)
                    <div class="text-muted mt-1" style="font-size:.8rem;">
                      {{ \Illuminate\Support\Str::limit($producto->hashtags, 80) }}
                    </div>
                  @endif
                </td>

                <td style="padding:16px;">
                  {{ $producto->seccion ?: '-' }}
                </td>

                <td style="padding:16px;">
                  {{ optional($producto->user)->name ?: '-' }}
                </td>

                <td style="padding:16px;">
                  {{ optional($producto->disenador)->name ?: '-' }}
                </td>

                <td style="padding:16px;">
                  <div style="line-height:1.15;">
                    <div>{{ optional($producto->created_at)->format('d/m/Y') }}</div>
                    <div class="text-muted" style="font-size:.85rem;">
                      {{ optional($producto->created_at)->format('H:i') }}
                    </div>
                  </div>
                </td>

                <td style="padding:16px;">
                  <span class="d-inline-flex align-items-center gap-2">
                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $dotColor }};"></span>
                    <span>
                      @if($label === 'EN_REVISION')
                        Revisión
                      @elseif($label === 'EN_DISENO')
                        En diseño
                      @elseif($label === 'DEVUELTO')
                        Devuelto
                      @else
                        {{ ucfirst(strtolower($label)) }}
                      @endif
                    </span>
                  </span>
                </td>

                <td style="padding:16px;">
                  @if($puedeEditar)
                    <a href="{{ route($routeBase.'.edit', $producto->id) }}"
                       class="btn btn-sm action-btn-edit"
                       style="border-radius:0;"
                       title="Editar"
                       aria-label="Editar">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                  @elseif($puedeVer)
                    <a href="{{ route($routeBase.'.edit', $producto->id) }}"
                       class="btn btn-sm action-btn-view"
                       style="border-radius:0;"
                       title="Ver"
                       aria-label="Ver">
                      <i class="bi bi-eye"></i>
                    </a>
                  @else
                    <button class="btn btn-sm btn-outline-secondary" style="border-radius:0;" disabled>
                      <i class="bi bi-slash-circle"></i>
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center p-4 text-muted">
                  No hay registros para mostrar.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $productos->links() }}
      </div>
    </div>
  </div>
</section>
@endsection
