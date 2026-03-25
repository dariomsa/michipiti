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
      <div class="block-title mb-2">Rendimiento por carrusel</div>
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Carrusel</th>
              <th style="width:90px;">Estado</th>
              <th style="width:120px;">Seccion</th>
              <th style="width:170px;">Creado</th>
              <th style="width:170px;">Actualizado</th>
              <th style="width:130px;">Tiempo diseño</th>
            </tr>
          </thead>
          <tbody id="ultimosCarruselesBody">
            @forelse(($ultimosCarruseles ?? []) as $c)
              <tr>
                <td class="fw-semibold">{{ \Illuminate\Support\Str::limit($c->titulo, 50) }}</td>
                <td><span class="badge bg-secondary">{{ $c->estado }}</span></td>
                <td>{{ $c->seccion ?? '-' }}</td>
                <td>{{ $c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('d M Y H:i') : '—' }}</td>
                <td>{{ $c->updated_at ? \Carbon\Carbon::parse($c->updated_at)->format('d M Y H:i') : '—' }}</td>
                <td class="fw-bold">{{ $c->tiempo_diseno ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-muted text-center py-3">Sin datos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const diaLabels = @json($chartDiaLabels ?? []);
  const diaData = @json($chartDiaData ?? []);
  const semanaLabels = @json($chartSemanaLabels ?? []);
  const semanaData = @json($chartSemanaData ?? []);
  const catLabels = @json($chartCatLabels ?? []);
  const catData = @json($chartCatData ?? []);
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
      body.innerHTML = '<tr><td colspan="6" class="text-muted text-center py-3">Sin datos.</td></tr>';
      return;
    }

    body.innerHTML = rows.map(row => `
      <tr>
        <td class="fw-semibold">${row.titulo ?? '-'}</td>
        <td><span class="badge bg-secondary">${row.estado ?? '-'}</span></td>
        <td>${row.seccion ?? '-'}</td>
        <td>${row.created_at ?? '—'}</td>
        <td>${row.updated_at ?? '—'}</td>
        <td class="fw-bold">${row.tiempo_diseno ?? '—'}</td>
      </tr>
    `).join('');
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
    renderUltimosCarruseles(data.ultimosCarruseles || []);
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
</script>
@endpush
