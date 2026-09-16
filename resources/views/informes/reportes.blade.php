@extends('layouts.app')

@section('title', 'Reportes')

@push('styles')
<style>
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

  .role-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
  }

  .role-tag {
    background: #eef1f5;
    border-radius: 999px;
    color: #0b3d6b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .03em;
    padding: 3px 7px;
    text-transform: uppercase;
  }

  .metric {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    white-space: nowrap;
  }

  .report-dashboard {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .report-card {
    background:
      radial-gradient(circle at 18% 12%, rgba(255, 255, 255, .52), transparent 26%),
      linear-gradient(135deg, var(--dash-from), var(--dash-to));
    border: 0;
    border-radius: 0;
    box-shadow: 0 12px 28px rgba(16, 24, 40, .14);
    color: #fff;
    min-height: 350px;
    overflow: hidden;
    position: relative;
  }

  .report-card::after {
    background: rgba(255, 255, 255, .16);
    content: "";
    height: 150px;
    position: absolute;
    right: -52px;
    top: -44px;
    transform: rotate(18deg);
    width: 150px;
  }

  .report-card-body {
    display: grid;
    gap: 12px;
    grid-template-rows: auto 190px auto;
    padding: 18px;
    position: relative;
    z-index: 1;
  }

  .report-kicker {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    opacity: .82;
    text-transform: uppercase;
  }

  .report-name {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 2px 0 10px;
    min-height: 38px;
  }

  .report-total {
    align-items: center;
    background: rgba(255, 255, 255, .18);
    border: 1px solid rgba(255, 255, 255, .22);
    display: flex;
    justify-content: space-between;
    padding: 9px 11px;
  }

  .report-total span {
    font-size: 11px;
    font-weight: 800;
    opacity: .8;
    text-transform: uppercase;
  }

  .report-total strong {
    font-variant-numeric: tabular-nums;
  }

  .chart-wrap {
    align-items: center;
    background: rgba(255, 255, 255, .92);
    display: flex;
    justify-content: center;
    min-height: 190px;
    padding: 8px;
  }

  .chart-empty {
    color: #53616f;
    font-size: .9rem;
    font-weight: 700;
    text-align: center;
  }

  .top-viewers {
    display: grid;
    gap: 7px;
    margin-top: 2px;
  }

  .top-viewer {
    align-items: center;
    background: rgba(0, 0, 0, .16);
    display: grid;
    gap: 8px;
    grid-template-columns: 24px minmax(0, 1fr) auto;
    padding: 7px 8px;
  }

  .top-viewer-rank {
    align-items: center;
    background: rgba(255, 255, 255, .24);
    display: inline-flex;
    font-size: 11px;
    font-weight: 900;
    height: 24px;
    justify-content: center;
    width: 24px;
  }

  .top-viewer-name {
    font-size: .82rem;
    font-weight: 800;
    line-height: 1.15;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .top-viewer-meta {
    font-size: 10px;
    opacity: .76;
  }

  .top-viewer-time {
    font-size: .78rem;
    font-variant-numeric: tabular-nums;
    font-weight: 900;
    white-space: nowrap;
  }

  @media (max-width: 1199.98px) {
    .report-dashboard {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
@php
  $formatDuration = function (int $seconds): string {
      $hours = intdiv($seconds, 3600);
      $minutes = intdiv($seconds % 3600, 60);
      $remainingSeconds = $seconds % 60;

      if ($hours > 0) {
          return sprintf('%dh %02dm %02ds', $hours, $minutes, $remainingSeconds);
      }

      if ($minutes > 0) {
          return sprintf('%dm %02ds', $minutes, $remainingSeconds);
      }

      return sprintf('%ds', $remainingSeconds);
  };
@endphp

<section class="flex-grow-1 compact-listing">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0">Reportes</h1>
  </div>

  @php
    $dashboardGradients = [
        ['#ff4d6d', '#6c2bd9'],
        ['#00b4d8', '#007f5f'],
        ['#ffb703', '#fb5607'],
    ];
  @endphp

  <div class="report-dashboard mb-3">
    @forelse($dashboardInformes as $dashboardIndex => $dashboardInforme)
      @php
        $gradient = $dashboardGradients[$dashboardIndex % count($dashboardGradients)];
      @endphp
      <article
        class="report-card"
        style="--dash-from: {{ $gradient[0] }}; --dash-to: {{ $gradient[1] }};"
      >
        <div class="report-card-body">
          <div>
            <div class="report-kicker">Informe {{ $dashboardInforme['created_at'] ?? '-' }}</div>
            <div class="report-name">
              {{ \Illuminate\Support\Str::limit($dashboardInforme['nombre'], 62) }}
            </div>
            <div class="report-total">
              <span>Tiempo total</span>
              <strong>{{ $formatDuration((int) $dashboardInforme['total_seconds']) }}</strong>
            </div>
          </div>

          <div class="chart-wrap">
            @if(count($dashboardInforme['chart_segments']) > 0)
              <canvas id="informeChart{{ $dashboardInforme['id'] }}" height="180"></canvas>
            @else
              <div class="chart-empty">Todavía no hay permanencia registrada.</div>
            @endif
          </div>

          <div class="top-viewers">
            @forelse($dashboardInforme['top_viewers'] as $viewerIndex => $viewer)
              <div class="top-viewer">
                <span class="top-viewer-rank">{{ $viewerIndex + 1 }}</span>
                <div style="min-width:0;">
                  <div class="top-viewer-name">{{ $viewer['name'] }}</div>
                  <div class="top-viewer-meta">{{ $viewer['aperturas'] }} aperturas</div>
                </div>
                <div class="top-viewer-time">{{ $formatDuration((int) $viewer['seconds']) }}</div>
              </div>
            @empty
              <div class="top-viewer">
                <span class="top-viewer-rank">-</span>
                <div class="top-viewer-name">Sin lecturas</div>
                <div class="top-viewer-time">0s</div>
              </div>
            @endforelse
          </div>
        </div>
      </article>
    @empty
      <div class="card card-form">
        <div class="card-body text-muted">
          No hay informes subidos para armar el dashboard.
        </div>
      </div>
    @endforelse
  </div>

  <div class="card card-form mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('informes.reportes') }}">
        <div class="row g-2 align-items-end filters-row">
          <div class="col-12 col-xl-3 col-lg-4">
            <label class="form-label mb-1">Buscar</label>
            <div class="input-group">
              <span class="input-group-text" style="border-radius:0;">
                <i class="bi bi-search"></i>
              </span>
              <input
                type="text"
                class="form-control"
                name="q"
                value="{{ $filters['q'] }}"
                placeholder="Informe o usuario"
                style="border-radius:0;"
              >
            </div>
          </div>

          <div class="col-12 col-xl-3 col-lg-4">
            <label class="form-label mb-1">Título</label>
            <input
              type="text"
              class="form-control"
              name="titulo"
              value="{{ $filters['titulo'] }}"
              placeholder="Nombre del informe"
              style="border-radius:0;"
            >
          </div>

          <div class="col-12 col-xl-2 col-lg-2">
            <label class="form-label mb-1">Periodista</label>
            <select class="form-select" name="periodista_id" style="border-radius:0;">
              <option value="0">Todos</option>
              @foreach($periodistas as $periodista)
                <option value="{{ $periodista->id }}" @selected($filters['periodista_id'] === $periodista->id)>
                  {{ $periodista->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-xl-2 col-lg-2">
            <label class="form-label mb-1">Diseñador</label>
            <select class="form-select" name="disenador_id" style="border-radius:0;">
              <option value="0">Todos</option>
              @foreach($disenadores as $disenador)
                <option value="{{ $disenador->id }}" @selected($filters['disenador_id'] === $disenador->id)>
                  {{ $disenador->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-xl-1 col-lg-2">
            <label class="form-label mb-1">Fecha</label>
            <input
              type="date"
              class="form-control"
              name="fecha"
              value="{{ $filters['fecha'] }}"
              style="border-radius:0;"
            >
          </div>

          <div class="col-6 col-xl-1 col-lg-12 d-grid">
            <label class="form-label mb-1 d-none d-md-block">&nbsp;</label>
            <button class="btn btn-primary" type="submit" style="border-radius:0;">
              Buscar
            </button>
          </div>

          <div class="col-6 d-flex gap-2 mt-2">
            <a class="btn btn-outline-secondary" href="{{ route('informes.reportes') }}" style="border-radius:0;">
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
              <th style="padding:14px 16px;">Informe</th>
              <th style="padding:14px 16px; width:220px;">Quién lo vio</th>
              <th style="padding:14px 16px; width:210px;">Rol</th>
              <th style="padding:14px 16px; width:150px;">IP</th>
              <th style="padding:14px 16px; width:130px;">Aperturas</th>
              <th style="padding:14px 16px; width:210px;">Tiempo de permanencia</th>
              <th style="padding:14px 16px; width:180px;">Última vista</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lecturas as $lectura)
              <tr>
                <td style="padding:16px;">
                  <div style="color:#1a73e8; font-weight:500;">
                    {{ \Illuminate\Support\Str::limit($lectura->informe?->nombre ?? 'Informe eliminado', 70) }}
                  </div>
                </td>

                <td style="padding:16px;">
                  {{ $lectura->user?->name ?? 'Usuario eliminado' }}
                </td>

                <td style="padding:16px;">
                  <div class="role-tags">
                    @forelse(($lectura->user?->roles ?? collect()) as $role)
                      <span class="role-tag">{{ str($role->name)->replace('_', ' ')->title() }}</span>
                    @empty
                      <span class="text-muted">-</span>
                    @endforelse
                  </div>
                </td>

                <td style="padding:16px;" class="metric">
                  {{ $lectura->last_ip ?? '-' }}
                </td>

                <td style="padding:16px;" class="metric">
                  {{ $lectura->aperturas }}
                </td>

                <td style="padding:16px;" class="metric">
                  {{ $formatDuration((int) $lectura->total_seconds) }}
                </td>

                <td style="padding:16px;">
                  <div style="line-height:1.15;">
                    <div>{{ $lectura->last_seen_at?->format('d/m/Y') ?? '-' }}</div>
                    <div class="text-muted" style="font-size:.85rem;">
                      {{ $lectura->last_seen_at?->format('H:i') ?? '-' }}
                    </div>
                  </div>
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
        {{ $lecturas->links() }}
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const informes = @json($dashboardInformes);
    const palette = [
      '#ff4d6d',
      '#7c3aed',
      '#06b6d4',
      '#22c55e',
      '#f97316',
      '#facc15',
      '#ec4899',
      '#14b8a6',
      '#3b82f6',
      '#a855f7',
      '#64748b'
    ];

    informes.forEach((informe) => {
      const canvas = document.getElementById(`informeChart${informe.id}`);

      if (!canvas || !Array.isArray(informe.chart_segments) || informe.chart_segments.length === 0) {
        return;
      }

      new Chart(canvas, {
        type: 'pie',
        data: {
          labels: informe.chart_segments.map((segment) => segment.name),
          datasets: [{
            data: informe.chart_segments.map((segment) => segment.seconds),
            backgroundColor: informe.chart_segments.map((segment, index) => palette[index % palette.length]),
            borderColor: '#ffffff',
            borderWidth: 2,
            hoverOffset: 8
          }]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                boxWidth: 11,
                color: '#172033',
                font: {
                  size: 10,
                  weight: '700'
                }
              }
            },
            tooltip: {
              callbacks: {
                label(context) {
                  const seconds = Number(context.raw || 0);
                  const hours = Math.floor(seconds / 3600);
                  const minutes = Math.floor((seconds % 3600) / 60);
                  const remainingSeconds = seconds % 60;
                  let formatted = `${remainingSeconds}s`;

                  if (hours > 0) {
                    formatted = `${hours}h ${String(minutes).padStart(2, '0')}m ${String(remainingSeconds).padStart(2, '0')}s`;
                  } else if (minutes > 0) {
                    formatted = `${minutes}m ${String(remainingSeconds).padStart(2, '0')}s`;
                  }

                  return `${context.label}: ${formatted}`;
                }
              }
            }
          }
        }
      });
    });
  });
</script>
@endpush
