@extends('layouts.app')

@section('title','Dashboard')

@push('styles')
<style>
  :root{
    --primary:#e91e63;
    --sidebar:#1c1c1c;
  }
  body{ background:#f4f6f8; }
  .card{ border-radius:0; }
  .stat-value{ font-size:1.5rem; font-weight:800; line-height:1; }
  .stat-sub{ font-size:.78rem; color:#6c757d; margin-top:.25rem; }
  .kpi-card .card-body{ padding:1rem 1.1rem; }
  .kpi-title{ font-weight:700; font-size:.9rem; }
  .kpi-icon{ font-size:1.15rem; opacity:.9; }
  .block-title{ font-weight:800; font-size:1rem; }
  .table thead th{ font-weight:800; font-size:.85rem; }
  .dashboard-filter-label{
    font-size:.82rem;
  }
  .dashboard-filter-select{
    font-size:.85rem;
    min-height:36px;
    padding-top:.3rem;
    padding-bottom:.3rem;
  }
  .dashboard-filter-btn{
    font-size:.82rem;
    min-height:36px;
    padding:.35rem .7rem;
  }
  .section-filters{
    display:flex;
    gap:14px;
    flex-wrap:wrap;
    align-items:center;
  }
  .section-filters .form-check{
    margin-bottom:0;
    font-size:.82rem;
    white-space:nowrap;
  }
  .section-filters .form-check-label{
    cursor:pointer;
  }
  .section-filters .form-check-input{
    border-color:#6b7280;
  }
  .section-filters .form-check-input:checked{
    background-color:#111827;
    border-color:#111827;
  }
  .listing-action-btn{
    min-width:34px;
    min-height:34px;
    border-radius:0;
  }
  .dashboard-listing-filters .form-label{
    font-size:.82rem;
  }
  .dashboard-listing-filters .form-control,
  .dashboard-listing-filters .form-select,
  .dashboard-listing-filters .input-group-text,
  .dashboard-listing-filters .btn{
    min-height:40px;
    font-size:.88rem;
  }
  .dashboard-listing-table thead th{
    padding:14px 16px;
    background:#f7f7f7;
    font-weight:800;
    font-size:.85rem;
  }
  .dashboard-listing-table tbody td{
    padding:16px;
    vertical-align:middle;
  }
  .estado-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    display:inline-block;
    margin-right:6px;
  }
  .estado-aprobado{ background:#2e7d32; }
  .estado-revision{ background:#f9a825; }
  .estado-diseno{ background:#2563eb; }
  .estado-borrador{ background:#9e9e9e; }
  .estado-devuelto{ background:#ef6c00; }
  .estado-rechazado{ background:#c62828; }
  .estado-publicado{ background:#1565c0; }
  .estado-pendiente{ background:#6c757d; }
  .dashboard-listing-pagination .page-link{
    border-radius:0;
    color:#495057;
    min-width:36px;
    text-align:center;
  }
  .dashboard-listing-pagination .page-item.active .page-link{
    background:#111827;
    border-color:#111827;
    color:#fff;
  }
  .dashboard-listing-pagination{
    flex-wrap:wrap;
    justify-content:flex-end;
    row-gap:.35rem;
  }
</style>
@endpush

@section('content')
<section class="main-content flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0">Dashboard de estadisticas</h1>
    <div class="text-muted" style="font-size:.9rem;">
      <i class="bi bi-person-circle"></i> Director
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end" id="dashboardFiltersForm">
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label mb-1 dashboard-filter-label">Tipo de producto</label>
          <select name="tipo_producto_id" class="form-select dashboard-filter-select" id="tipoProductoFilter">
            <option value="">Carrusel</option>
            @foreach($tiposProducto as $tipo)
              <option value="{{ $tipo->id }}" @selected((string)($tipoProductoId ?? '') === (string)$tipo->id)>
                {{ $tipo->nombre }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12">
          <label class="form-label mb-2">Secciones</label>
          <div class="section-filters" id="sectionFilters">
            @foreach($secciones as $seccion)
              <div class="form-check">
                <input class="form-check-input section-filter" type="checkbox" value="{{ $seccion }}" id="sec{{ md5($seccion) }}"
                  @checked(in_array($seccion, $seccionesSeleccionadas ?? [], true))>
                <label class="form-check-label" for="sec{{ md5($seccion) }}">{{ $seccion }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-12 col-md-auto d-flex gap-2">
          <button type="submit" class="btn btn-outline-secondary dashboard-filter-btn">Filtrar</button>
          <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary dashboard-filter-btn">Limpiar</a>
        </div>
      </form>
    </div>
  </div>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
      <div class="card kpi-card text-center">
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
            <span class="kpi-icon">⏱</span>
            <div class="kpi-title">Tiempo total promedio</div>
          </div>
          <div class="stat-value" id="kpiTotalPromedioDynamic">{{ $kpi_total_promedio ?? '—' }}</div>
          <div class="stat-sub">Creado → Ultima actualizacion</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card kpi-card text-center">
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
            <span class="kpi-icon">🎨</span>
            <div class="kpi-title">Diseño</div>
          </div>
          <div class="stat-value" id="kpiDisenoPromedioDynamic">{{ $kpi_diseno_promedio ?? '—' }}</div>
          <div class="stat-sub">Asignado → Ultima actualizacion</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card kpi-card text-center">
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
            <span class="kpi-icon">✍️</span>
            <div class="kpi-title">Edicion/Revisión</div>
          </div>
          <div class="stat-value" id="kpiRevisionPromedioDynamic">{{ $kpi_revision_promedio ?? '—' }}</div>
          <div class="stat-sub">Revision → Asignado a diseño</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card kpi-card text-center">
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
            <span class="kpi-icon">📦</span>
            <div class="kpi-title">Total carruseles</div>
          </div>
          <div class="stat-value" id="kpiTotalCarruselesDynamic">{{ (int)($kpi_total_carruseles ?? 0) }}</div>
          <div class="stat-sub">Todos los estados</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="block-title mb-2">Carruseles por día (ultimos 7 dias)</div>
          <canvas id="chartDia" height="140"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="block-title mb-2">Carruseles por semana (ultimas 4)</div>
          <canvas id="chartSemana" height="140"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-body">
          <div class="block-title mb-2">Por seccion (categoria)</div>
          <canvas id="chartCategorias" height="170"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card">
        <div class="card-body">
          <div class="block-title mb-2">Detalle por seccion</div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th>Seccion</th>
                  <th style="width:120px;">Total</th>
                </tr>
              </thead>
              <tbody id="categoriasTableBody">
                @forelse(($categoriasTabla ?? []) as $row)
                  <tr>
                    <td>{{ $row['seccion'] }}</td>
                    <td class="fw-bold">{{ (int)$row['total'] }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="2" class="text-muted text-center py-3">Sin datos.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="block-title mb-3">Listado de productos</div>
      <div class="row g-2 align-items-end dashboard-listing-filters mb-3">
        <div class="col-12 col-xl-3 col-lg-4">
          <label class="form-label mb-1">Buscar</label>
          <div class="input-group">
            <span class="input-group-text" style="border-radius:0;">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control" id="dashboardListQ" placeholder="Título, copy o hashtags" style="border-radius:0;">
          </div>
        </div>

        <div class="col-12 col-xl-2 col-lg-2">
          <label class="form-label mb-1">Sección</label>
          <select class="form-select" id="dashboardListSeccion" style="border-radius:0;">
            <option value="">Todas</option>
            @foreach($secciones as $seccion)
              <option value="{{ $seccion }}">{{ $seccion }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-xl-2 col-lg-2">
          <label class="form-label mb-1">Periodista</label>
          <select class="form-select" id="dashboardListPeriodista" style="border-radius:0;">
            <option value="">Todos</option>
            @foreach($periodistas as $periodista)
              <option value="{{ $periodista->name }}">{{ $periodista->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-xl-2 col-lg-2">
          <label class="form-label mb-1">Diseñador</label>
          <select class="form-select" id="dashboardListDisenador" style="border-radius:0;">
            <option value="">Todos</option>
            @foreach($disenadores as $disenador)
              <option value="{{ $disenador->name }}">{{ $disenador->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-xl-1 col-lg-2">
          <label class="form-label mb-1">Estado</label>
          <select class="form-select" id="dashboardListEstado" style="border-radius:0;">
            <option value="">Todos</option>
            @foreach($estados as $estado)
              <option value="{{ $estado }}">{{ $estado }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-xl-1 col-lg-2">
          <label class="form-label mb-1">Fecha</label>
          <input type="date" class="form-control" id="dashboardListFecha" style="border-radius:0;">
        </div>

        <div class="col-6 col-xl-1 col-lg-12 d-grid">
          <label class="form-label mb-1 d-none d-md-block">&nbsp;</label>
          <button type="button" class="btn btn-outline-secondary" id="dashboardListApply" style="border-radius:0;">
            Buscar
          </button>
        </div>

        <div class="col-6 d-flex gap-2 mt-2">
          <button type="button" class="btn btn-outline-secondary" id="dashboardListClear" style="border-radius:0;">
            Limpiar filtros
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table mb-0 align-middle dashboard-listing-table">
          <thead>
            <tr>
              <th>Contenido</th>
              <th style="width:140px;">Sección</th>
              <th style="width:190px;">Periodista</th>
              <th style="width:190px;">Diseñador</th>
              <th style="width:170px;">Fecha</th>
              <th style="width:150px;">Estado</th>
              <th style="width:170px;">Tiempo total</th>
            </tr>
          </thead>
          <tbody id="ultimosCarruselesBody">
            @forelse(($ultimosCarruseles ?? []) as $c)
              <tr>
                <td class="fw-semibold">{{ \Illuminate\Support\Str::limit($c->titulo, 55) }}</td>
                <td>{{ $c->seccion ?? '-' }}</td>
                <td>{{ $c->user?->name ?? '-' }}</td>
                <td>{{ $c->disenador?->name ?? '-' }}</td>
                <td>
                  <div style="line-height:1.15;">
                    <div>{{ $c->fecha ? $c->fecha->format('d/m/Y') : '-' }}</div>
                    <div class="text-muted" style="font-size:.85rem;">
                      {{ $c->hora ? \Illuminate\Support\Str::substr((string) $c->hora, 0, 5) : '-' }}
                    </div>
                  </div>
                </td>
                <td>
                  <span class="d-inline-flex align-items-center gap-2">
                    <span class="{{ match($c->estado) {
                      'APROBADO' => 'estado-dot estado-aprobado',
                      'EN_REVISION' => 'estado-dot estado-revision',
                      'EN_DISENO' => 'estado-dot estado-diseno',
                      'BORRADOR' => 'estado-dot estado-borrador',
                      'DEVUELTO' => 'estado-dot estado-devuelto',
                      'RECHAZADO' => 'estado-dot estado-rechazado',
                      'PUBLICADO' => 'estado-dot estado-publicado',
                      default => 'estado-dot estado-pendiente',
                    } }}"></span>
                    <span>
                      @if($c->estado === 'EN_REVISION')
                        Revisión
                      @elseif($c->estado === 'EN_DISENO')
                        En diseño
                      @elseif($c->estado === 'DEVUELTO')
                        Devuelto
                      @else
                        {{ ucfirst(strtolower($c->estado ?? '-')) }}
                      @endif
                    </span>
                  </span>
                </td>
                <td class="fw-bold">{{ $c->tiempo_total ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-muted text-center py-3">Sin datos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="text-muted small" id="dashboardListSummary">Mostrando 0 resultados</div>
        <nav aria-label="Paginación listado dashboard">
          <ul class="pagination pagination-sm mb-0 dashboard-listing-pagination" id="dashboardListPagination"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@php
  $dashboardListRowsInitial = ($ultimosCarruseles ?? collect())->map(function ($producto) {
      return [
          'id' => $producto->id,
          'titulo' => $producto->titulo,
          'estado' => $producto->estado,
          'seccion' => $producto->seccion,
          'periodista' => $producto->user?->name,
          'disenador' => $producto->disenador?->name,
          'fecha' => $producto->fecha?->format('d/m/Y'),
          'hora' => $producto->hora ? \Illuminate\Support\Str::substr((string) $producto->hora, 0, 5) : '—',
          'tiempo_total' => $producto->tiempo_total ?? '—',
      ];
  })->values()->all();
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const diaLabels = @json($chartDiaLabels ?? []);
  const diaData = @json($chartDiaData ?? []);
  const semanaLabels = @json($chartSemanaLabels ?? []);
  const semanaData = @json($chartSemanaData ?? []);
  const catLabels = @json($chartCatLabels ?? []);
  const catData = @json($chartCatData ?? []);
  let dashboardListRows = @json($dashboardListRowsInitial);
  let dashboardListFilteredRows = [...dashboardListRows];
  let dashboardListPage = 1;
  const dashboardListPageSize = 10;
  const dashboardUrl = @json(route('dashboard'));

  let chartDiaInstance = null;
  let chartSemanaInstance = null;
  let chartCatInstance = null;

  const ctxDia = document.getElementById('chartDia');
  if (ctxDia) {
    chartDiaInstance = new Chart(ctxDia, {
      type: 'line',
      data: {
        labels: diaLabels,
        datasets: [{ data: diaData, tension: 0.3 }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  const ctxSemana = document.getElementById('chartSemana');
  if (ctxSemana) {
    chartSemanaInstance = new Chart(ctxSemana, {
      type: 'bar',
      data: {
        labels: semanaLabels,
        datasets: [{ data: semanaData }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  const ctxCat = document.getElementById('chartCategorias');
  if (ctxCat) {
    chartCatInstance = new Chart(ctxCat, {
      type: 'doughnut',
      data: {
        labels: catLabels,
        datasets: [{ data: catData }]
      },
      options: {
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  function updateKpi(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '—';
  }

  function renderCategoriasTable(rows) {
    const body = document.getElementById('categoriasTableBody');
    if (!body) return;

    if (!rows || !rows.length) {
      body.innerHTML = '<tr><td colspan="2" class="text-muted text-center py-3">Sin datos.</td></tr>';
      return;
    }

    body.innerHTML = rows.map(row => `
      <tr>
        <td>${row.seccion ?? '-'}</td>
        <td class="fw-bold">${parseInt(row.total ?? 0, 10)}</td>
      </tr>
    `).join('');
  }

  function renderUltimosCarruseles(rows) {
    const body = document.getElementById('ultimosCarruselesBody');
    if (!body) return;

    if (!rows || !rows.length) {
      body.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-3">Sin datos.</td></tr>';
      return;
    }

    body.innerHTML = rows.map(row => `
      <tr>
        <td class="fw-semibold">${row.titulo ?? '-'}</td>
        <td>${row.seccion ?? '-'}</td>
        <td>${row.periodista ?? '-'}</td>
        <td>${row.disenador ?? '-'}</td>
        <td>
          <div style="line-height:1.15;">
            <div>${row.fecha ?? '—'}</div>
            <div class="text-muted" style="font-size:.85rem;">${row.hora ?? '—'}</div>
          </div>
        </td>
        <td>
          <span class="d-inline-flex align-items-center gap-2">
            <span class="${estadoDotClass(row.estado)}"></span>
            <span>${estadoLabel(row.estado)}</span>
          </span>
        </td>
        <td class="fw-bold">${row.tiempo_total ?? '—'}</td>
      </tr>
    `).join('');
  }

  function renderDashboardListPagination(totalRows) {
    const pagination = document.getElementById('dashboardListPagination');
    const summary = document.getElementById('dashboardListSummary');

    if (summary) {
      if (!totalRows) {
        summary.textContent = 'Mostrando 0 resultados';
      } else {
        const start = ((dashboardListPage - 1) * dashboardListPageSize) + 1;
        const end = Math.min(start + dashboardListPageSize - 1, totalRows);
        summary.textContent = `Mostrando ${start}-${end} de ${totalRows} resultados`;
      }
    }

    if (!pagination) return;

    const totalPages = Math.max(1, Math.ceil(totalRows / dashboardListPageSize));

    if (totalRows <= dashboardListPageSize) {
      pagination.innerHTML = '';
      return;
    }

    const items = [];
    const prevDisabled = dashboardListPage <= 1 ? ' disabled' : '';
    const nextDisabled = dashboardListPage >= totalPages ? ' disabled' : '';
    const pages = [];
    const addPage = (page) => {
      if (page >= 1 && page <= totalPages && !pages.includes(page)) {
        pages.push(page);
      }
    };

    items.push(`
      <li class="page-item${prevDisabled}">
        <button type="button" class="page-link" data-page="${dashboardListPage - 1}" aria-label="Anterior">&lsaquo;</button>
      </li>
    `);

    addPage(1);
    addPage(dashboardListPage - 1);
    addPage(dashboardListPage);
    addPage(dashboardListPage + 1);
    addPage(totalPages);

    pages.sort((a, b) => a - b);

    let previousPage = null;
    for (const page of pages) {
      if (previousPage !== null && page - previousPage > 1) {
        items.push(`
          <li class="page-item disabled">
            <span class="page-link">…</span>
          </li>
        `);
      }

      items.push(`
        <li class="page-item${page === dashboardListPage ? ' active' : ''}">
          <button type="button" class="page-link" data-page="${page}">${page}</button>
        </li>
      `);

      previousPage = page;
    }

    items.push(`
      <li class="page-item${nextDisabled}">
        <button type="button" class="page-link" data-page="${dashboardListPage + 1}" aria-label="Siguiente">&rsaquo;</button>
      </li>
    `);

    pagination.innerHTML = items.join('');
  }

  function renderDashboardListPage(rows) {
    dashboardListFilteredRows = rows;

    const totalPages = Math.max(1, Math.ceil(rows.length / dashboardListPageSize));
    if (dashboardListPage > totalPages) {
      dashboardListPage = totalPages;
    }

    const start = (dashboardListPage - 1) * dashboardListPageSize;
    const pageRows = rows.slice(start, start + dashboardListPageSize);

    renderUltimosCarruseles(pageRows);
    renderDashboardListPagination(rows.length);
  }

  function estadoDotClass(estado) {
    const e = String(estado || '').toUpperCase();
    if (e === 'APROBADO') return 'estado-dot estado-aprobado';
    if (e === 'EN_REVISION' || e === 'REVISION') return 'estado-dot estado-revision';
    if (e === 'EN_DISENO') return 'estado-dot estado-diseno';
    if (e === 'BORRADOR') return 'estado-dot estado-borrador';
    if (e === 'DEVUELTO') return 'estado-dot estado-devuelto';
    if (e === 'RECHAZADO') return 'estado-dot estado-rechazado';
    if (e === 'PUBLICADO') return 'estado-dot estado-publicado';
    return 'estado-dot estado-pendiente';
  }

  function estadoLabel(estado) {
    const e = String(estado || '').toUpperCase();
    if (e === 'EN_REVISION') return 'Revisión';
    if (e === 'EN_DISENO') return 'En diseño';
    if (e === 'DEVUELTO') return 'Devuelto';
    return e ? (e.charAt(0) + e.slice(1).toLowerCase()) : '-';
  }

  function normalizeDashboardDate(value) {
    if (!value) return '';

    if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
      const [day, month, year] = value.split('/');
      return `${year}-${month}-${day}`;
    }

    return value;
  }

  function applyDashboardListFilters(resetPage = true) {
    const q = (document.getElementById('dashboardListQ')?.value || '').trim().toLowerCase();
    const seccion = document.getElementById('dashboardListSeccion')?.value || '';
    const periodista = document.getElementById('dashboardListPeriodista')?.value || '';
    const disenador = document.getElementById('dashboardListDisenador')?.value || '';
    const estado = document.getElementById('dashboardListEstado')?.value || '';
    const fecha = document.getElementById('dashboardListFecha')?.value || '';

    const filtered = (dashboardListRows || []).filter((row) => {
      const haystack = [
        row.titulo,
        row.seccion,
        row.periodista,
        row.disenador,
        row.estado,
      ].filter(Boolean).join(' ').toLowerCase();

      if (q && !haystack.includes(q)) return false;
      if (seccion && row.seccion !== seccion) return false;
      if (periodista && row.periodista !== periodista) return false;
      if (disenador && row.disenador !== disenador) return false;
      if (estado && row.estado !== estado) return false;
      if (fecha && normalizeDashboardDate(row.fecha) !== fecha) return false;

      return true;
    });

    if (resetPage) {
      dashboardListPage = 1;
    }

    renderDashboardListPage(filtered);
  }

  function selectedSections() {
    return Array.from(document.querySelectorAll('.section-filter:checked')).map(node => node.value);
  }

  async function refreshDashboard() {
    const params = new URLSearchParams();
    const tipo = document.getElementById('tipoProductoFilter')?.value || '';

    if (tipo) {
      params.set('tipo_producto_id', tipo);
    }

    selectedSections().forEach(value => params.append('secciones[]', value));

    const url = `${dashboardUrl}?${params.toString()}`;
    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }
    });

    if (!res.ok) {
      throw new Error('No se pudo refrescar el dashboard.');
    }

    const data = await res.json();

    updateKpi('kpiTotalPromedioDynamic', data.kpi_total_promedio);
    updateKpi('kpiDisenoPromedioDynamic', data.kpi_diseno_promedio);
    updateKpi('kpiRevisionPromedioDynamic', data.kpi_revision_promedio);
    updateKpi('kpiTotalCarruselesDynamic', data.kpi_total_carruseles);

    if (chartDiaInstance) {
      chartDiaInstance.data.labels = data.chartDiaLabels || [];
      chartDiaInstance.data.datasets[0].data = data.chartDiaData || [];
      chartDiaInstance.update();
    }

    if (chartSemanaInstance) {
      chartSemanaInstance.data.labels = data.chartSemanaLabels || [];
      chartSemanaInstance.data.datasets[0].data = data.chartSemanaData || [];
      chartSemanaInstance.update();
    }

    if (chartCatInstance) {
      chartCatInstance.data.labels = data.chartCatLabels || [];
      chartCatInstance.data.datasets[0].data = data.chartCatData || [];
      chartCatInstance.update();
    }

    renderCategoriasTable(data.categoriasTabla || []);
    dashboardListRows = data.ultimosCarruseles || [];
    applyDashboardListFilters();
    window.history.replaceState({}, '', url);
  }

  document.getElementById('tipoProductoFilter')?.addEventListener('change', function () {
    refreshDashboard().catch(console.error);
  });

  document.querySelectorAll('.section-filter').forEach(node => {
    node.addEventListener('change', function () {
      refreshDashboard().catch(console.error);
    });
  });

  document.getElementById('dashboardListApply')?.addEventListener('click', function () {
    applyDashboardListFilters(true);
  });
  document.getElementById('dashboardListClear')?.addEventListener('click', function () {
    document.getElementById('dashboardListQ').value = '';
    document.getElementById('dashboardListSeccion').value = '';
    document.getElementById('dashboardListPeriodista').value = '';
    document.getElementById('dashboardListDisenador').value = '';
    document.getElementById('dashboardListEstado').value = '';
    document.getElementById('dashboardListFecha').value = '';
    applyDashboardListFilters(true);
  });

  document.getElementById('dashboardListPagination')?.addEventListener('click', function (event) {
    const button = event.target.closest('[data-page]');
    if (!button) return;

    const page = Number(button.dataset.page || 1);
    const totalPages = Math.max(1, Math.ceil(dashboardListFilteredRows.length / dashboardListPageSize));

    if (page < 1 || page > totalPages || page === dashboardListPage) {
      return;
    }

    dashboardListPage = page;
    renderDashboardListPage(dashboardListFilteredRows);
  });

  applyDashboardListFilters(true);
</script>
@endpush
