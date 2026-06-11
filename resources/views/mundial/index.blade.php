@extends('layouts.app')

@section('title', 'Listado Mundial')

@push('styles')
<style>
  .mundial-page{
    --ink:#0e1726;
    --blue:#0b3d6b;
    --blue2:#16578f;
    --paper:#f4f1ea;
    --card:#ffffff;
    --line:#e3ddd0;
    --muted:#6b6256;
    --com:#b9551f;
    --gold:#caa24a;
    --green:#2f7d56;
    color:var(--ink);
    background:
      radial-gradient(circle at 12% 6%, rgba(11,61,107,.05), transparent 40%),
      radial-gradient(circle at 88% 0%, rgba(185,85,31,.06), transparent 42%),
      var(--paper);
    border-radius:12px;
    margin:-6px;
    min-height:calc(100vh - 120px);
    padding:26px 22px 46px;
  }

  .mundial-inner{max-width:1180px;margin:0 auto;}
  .mundial-top{border-bottom:3px solid var(--blue);padding-bottom:14px;margin-bottom:18px;}
  .mundial-brand{color:var(--blue);font-size:13px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;}
  .mundial-title{font-family:Georgia, 'Times New Roman', serif;font-size:clamp(30px,4.5vw,46px);font-weight:700;line-height:1;margin:6px 0 4px;}
  .mundial-title em{color:var(--com);font-style:italic;font-weight:700;}
  .mundial-sub{color:var(--muted);font-size:14px;line-height:1.25;max-width:760px;}

  .mundial-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:20px 0 16px;}
  .mundial-stat{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:12px 14px;}
  .mundial-stat b{display:block;font-family:Georgia, 'Times New Roman', serif;font-size:26px;line-height:1;color:var(--blue);}
  .mundial-stat.com b{color:var(--com);}
  .mundial-stat.radio b{color:var(--green);}
  .mundial-stat span{color:var(--muted);display:block;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;}

  .mundial-search{background:#fff;border:1px solid var(--line);border-radius:10px;height:34px;padding:6px 12px;width:100%;font-size:14px;}
  .filter-row{align-items:center;display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;}
  .filter-label{color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;width:82px;}
  .filter-chips{display:flex;gap:6px;flex:1;flex-wrap:wrap;min-width:0;}
  .chip{background:#fff;border:1px solid var(--line);border-radius:999px;color:#111827;font-size:11px;font-weight:600;padding:5px 10px;text-decoration:none;white-space:nowrap;}
  .chip:hover{border-color:var(--blue2);color:var(--blue);}
  .chip.on{background:var(--blue);border-color:var(--blue);color:#fff;}

  .date-group{margin-top:28px;}
  .date-head{align-items:center;border-bottom:1px solid var(--line);display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;padding-bottom:6px;}
  .date-head-title{align-items:baseline;display:flex;gap:12px;min-width:0;}
  .date-head .name{font-family:Georgia, 'Times New Roman', serif;font-size:22px;font-weight:700;}
  .date-head .count{color:var(--muted);font-size:12.5px;}
  .date-nav{align-items:center;display:flex;gap:6px;flex-shrink:0;}
  .date-nav .btn-date{align-items:center;background:#fff;border:1px solid var(--line);border-radius:999px;color:var(--blue);display:inline-flex;font-size:12px;font-weight:800;height:30px;justify-content:center;min-width:30px;padding:0 10px;text-decoration:none;}
  .date-nav .btn-date:hover{border-color:var(--blue2);color:var(--blue2);}
  .date-nav input{background:#fff;border:1px solid var(--line);border-radius:999px;color:var(--ink);font-size:12px;font-weight:700;height:30px;padding:0 8px;width:128px;}
  .mrow{display:grid;grid-template-columns:78px 1fr;gap:14px;margin-bottom:11px;}
  .mtime{border-right:2px solid var(--line);padding-right:12px;padding-top:13px;text-align:right;}
  .mtime b{display:block;font-family:Georgia, 'Times New Roman', serif;font-size:19px;line-height:1;}
  .mtime span{color:var(--muted);display:block;font-size:10.5px;margin-top:2px;word-break:break-word;}
  .metricool-check{align-items:center;background:transparent;border:0;color:var(--muted);display:inline-flex;font-size:10px;font-weight:800;gap:5px;letter-spacing:.03em;padding:0;text-transform:uppercase;white-space:nowrap;}
  .metricool-meta{flex-basis:100%;margin-top:0;}
  .metricool-check.is-on{color:#166534;}
  .metricool-check input{appearance:none;-webkit-appearance:none;background:#fff;border:1px solid #9ca3af;border-radius:3px;cursor:pointer;height:13px;margin:0;position:relative;width:13px;}
  .metricool-check input:checked{background:#16a34a;border-color:#16a34a;}
  .metricool-check input:checked::after{border:solid #fff;border-width:0 2px 2px 0;content:"";height:7px;left:4px;position:absolute;top:1px;transform:rotate(45deg);width:4px;}
  .metricool-check input:disabled{cursor:not-allowed;}
  .mcard{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;padding:13px 15px;position:relative;}
  .mcard.is-clickable{cursor:pointer;transition:transform .15s, box-shadow .15s;}
  .mcard.is-clickable:hover{box-shadow:0 9px 22px -16px rgba(14,23,38,.45);transform:translateY(-1px);}
  .mcard:before{background:var(--blue);bottom:0;content:"";left:0;position:absolute;top:0;width:4px;}
  .mcard.com:before{background:var(--com);}
  .mcard.radio:before{background:var(--green);}
  .mcard-head{align-items:flex-start;display:flex;gap:10px;justify-content:space-between;padding-left:6px;}
  .mcard-title{font-family:Georgia, 'Times New Roman', serif;font-size:18px;font-weight:700;line-height:1.14;margin:0 0 8px;}
  .badge-stack{align-items:flex-end;display:flex;flex-direction:column;gap:5px;flex-shrink:0;}
  .type-badge{background:var(--blue);border-radius:6px;color:#fff;font-size:10px;font-weight:800;letter-spacing:.05em;padding:3px 7px;text-transform:uppercase;white-space:nowrap;}
  .type-badge.com{background:var(--com);}
  .type-badge.radio{background:var(--green);}
  .stage-badge{border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.04em;padding:3px 8px;text-transform:uppercase;white-space:nowrap;}
  .stage-borrador{background:#fee2e2;color:#991b1b;}
  .stage-en-proceso,
  .stage-por-cerrar{background:#fef3c7;color:#92400e;}
  .stage-terminado{background:#dcfce7;color:#166534;}
  .stage-por-entregar{background:#fff1f2;color:#be123c;}
  .visible-badge{background:#e0f2fe;border-radius:999px;color:#075985;font-size:10px;font-weight:800;letter-spacing:.04em;padding:3px 8px;text-transform:uppercase;white-space:nowrap;}
  .tags{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:9px;padding-left:6px;}
  .tag{background:#eef1f5;border-radius:6px;color:var(--blue);font-size:10.5px;font-weight:800;letter-spacing:.03em;padding:3px 7px;text-transform:uppercase;}
  .tag.format{background:#f3eee6;color:#7a6a4d;}
  .meta{display:flex;flex-wrap:wrap;gap:14px;font-size:12.5px;padding-left:6px;}
  .meta b{color:var(--muted);font-weight:800;}
  .meta .sponsor-text{color:#b9812a;font-weight:800;}
  .empty{background:#fff;border:1px solid var(--line);border-radius:12px;color:var(--muted);font-style:italic;margin-top:18px;padding:40px;text-align:center;}
  #mundialEditModal .modal-dialog{max-width:720px;width:calc(100% - 1rem);}
  #mundialEditModal .modal-content,
  #mundialEditModal form{max-width:100%;min-width:0;overflow-x:hidden;}
  #mundialEditModal .modal-header,
  #mundialEditModal .modal-footer{padding:.55rem .75rem;}
  #mundialEditModal .modal-body{overflow-x:hidden;padding:.65rem .75rem;}
  #mundialEditModal .row.g-3{--bs-gutter-x:.55rem;--bs-gutter-y:.45rem;}
  #mundialEditModal .row > [class*="col-"]{min-width:0;}
  #mundialEditModal .form-label{font-size:.78rem;font-weight:600;margin-bottom:.25rem;}
  #mundialEditModal .form-control,
  #mundialEditModal .form-select{border-radius:.35rem;font-size:.84rem;height:31px;min-height:31px;padding:.22rem .5rem;}
  #mundialEditModal textarea.form-control{height:auto;line-height:1.2;min-height:56px;padding-bottom:.35rem;padding-top:.35rem;}
  #mundialEditModal .btn{font-size:.82rem;padding:.32rem .62rem;}
  .platform-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(34px,1fr));gap:.14rem;min-width:0;}
  .platform-check{position:relative;}
  .platform-check input{opacity:0;position:absolute;pointer-events:none;}
  .platform-card{align-items:center;background:#fff;border:1px solid #cbd5e1;border-radius:.55rem;cursor:pointer;display:flex;font-size:.7rem;font-weight:800;justify-content:center;min-height:30px;padding:.1rem .2rem;text-align:center;}
  .platform-card img{display:block;filter:drop-shadow(0 1px 1px rgba(15,23,42,.18));height:18px;object-fit:contain;width:18px;}
  .platform-fallback{color:#111827;font-size:.72rem;font-weight:800;line-height:1;}
  .platform-check input:checked + .platform-card{background:#dbeafe;border-color:#0d6efd;box-shadow:inset 0 0 0 1px #0d6efd;color:#0b3d6b;}
  .platform-grid.is-invalid{background:#fff5f5;border:1px solid #dc3545;border-radius:.55rem;padding:2px;}

  @media (max-width: 767.98px){
    .mundial-page{margin:0;padding:18px 12px 34px;}
    .mundial-stats{grid-template-columns:repeat(2,minmax(0,1fr));}
    .filter-label{width:100%;}
    .date-head{align-items:flex-start;flex-direction:column;}
    .mrow{grid-template-columns:62px 1fr;gap:10px;}
  }

  @media (max-width: 420px){
    .date-group{
      margin-left:-4px;
      margin-right:-4px;
      overflow-x:auto;
      overscroll-behavior-x:contain;
      padding:0 4px 6px;
      -webkit-overflow-scrolling:touch;
    }
    .date-head,
    .mrow{
      min-width:360px;
    }
    .mcard{
      min-width:0;
    }
    .mcard-title{
      overflow-wrap:anywhere;
    }
  }
</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Str;
  $chipUrl = function (array $override = []) use ($filters) {
      return route('mundial.index', array_filter(array_merge($filters, $override), fn ($value) => $value !== '' && $value !== 0 && $value !== null));
  };
  $fmtDate = fn ($date) => $date ? ucfirst($date->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) : 'Sin fecha';
  $fmtTime = fn ($time) => $time ? \Carbon\Carbon::parse($time)->format('H\hi') : '--';
  $puedeEditarMundial = $puedeEditarMundial ?? true;
  $canEditDateTime = $puedeEditarMundial && (auth()->user()?->hasRole('director') ?? false);
  $plataformaIconMap = [
      'web' => 'web',
      'facebook' => 'facebook',
      'instagram' => 'instagram',
      'podcast' => 'podcast',
      'radio' => 'radio',
      'shorts' => 'shorts',
      'tiktok' => 'tiktok',
      'youtube' => 'youtube',
      'whatsapp' => 'whatsapp',
  ];
  $editItems = $productos->getCollection()
      ->mapWithKeys(function ($producto) {
          return [
              $producto->id => [
                  'id' => $producto->id,
                  'tipo_producto_id' => $producto->tipo_producto_id,
                  'fecha' => optional($producto->fecha)->format('Y-m-d'),
                  'hora' => $producto->hora ? \Carbon\Carbon::parse($producto->hora)->format('H:i') : null,
                  'mundial_prioridad_id' => $producto->mundial_prioridad_id,
                  'mundial_plataformas_ids' => collect($producto->mundial_plataformas_ids ?? ($producto->mundial_plataforma_id ? [$producto->mundial_plataforma_id] : []))->map(fn ($id) => (int) $id)->values()->all(),
                  'mundial_equipo_id' => $producto->mundial_equipo_id,
                  'mundial_tipo_id' => $producto->mundial_tipo_id,
                  'titulo' => $producto->titulo,
                  'descripcion' => $producto->copy,
                  'auspicio' => $producto->creditos,
                  'estado' => $producto->estado,
                  'asignado_a' => $producto->user_id,
                  'responsable2_id' => $producto->responsable2_id,
                  'edicion_id' => $producto->manager_id,
                  'etapa' => $producto->referencia ?: 'Borrador',
                  'visible' => (bool) $producto->visible,
                  'es_michipiti' => (bool) $producto->productoConvertido?->estado,
                  'programado_metricool' => (bool) ($producto->productoConvertido?->programado_metricool ?? $producto->programado_metricool),
              ],
          ];
      })
      ->all();
@endphp

<div class="mundial-page">
  <div class="mundial-inner">
    <header class="mundial-top">
      <div class="mundial-brand">EL COMERCIO · OPERATIVO MUNDIAL</div>
      <h1 class="mundial-title">Calendario <em>Mundial 2026</em> · torneo completo</h1>
      <div class="mundial-sub">Vista de productos planificados para el Especial Mundial: prioridad, plataforma, equipo responsable y tipo de contenido.</div>
    </header>

    <div class="mundial-stats">
      <div class="mundial-stat"><b>{{ $stats['total'] }}</b><span>Productos</span></div>
      <div class="mundial-stat"><b>{{ $stats['editorial'] }}</b><span>Editorial</span></div>
      <div class="mundial-stat com"><b>{{ $stats['comercial'] }}</b><span>Comercial</span></div>
      <div class="mundial-stat radio"><b>{{ $stats['radio'] }}</b><span>Radio</span></div>
    </div>

    <form method="GET" action="{{ route('mundial.index') }}">
      <input class="mundial-search" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Buscar producto, equipo, responsable...">
    </form>

    <div class="filter-row">
      <div class="filter-label">Prioridad</div>
      <div class="filter-chips">
        <a class="chip {{ $filters['prioridad'] === 0 ? 'on' : '' }}" href="{{ $chipUrl(['prioridad' => 0]) }}">Todas</a>
        @foreach($prioridades as $prioridad)
          <a class="chip {{ $filters['prioridad'] === $prioridad->id ? 'on' : '' }}" href="{{ $chipUrl(['prioridad' => $prioridad->id]) }}">{{ $prioridad->nombre }}</a>
        @endforeach
      </div>
    </div>

    <div class="filter-row">
      <div class="filter-label">Plataforma</div>
      <div class="filter-chips">
        <a class="chip {{ $filters['plataforma'] === 0 ? 'on' : '' }}" href="{{ $chipUrl(['plataforma' => 0]) }}">Todas</a>
        @foreach($plataformas as $plataforma)
          <a class="chip {{ $filters['plataforma'] === $plataforma->id ? 'on' : '' }}" href="{{ $chipUrl(['plataforma' => $plataforma->id]) }}">{{ $plataforma->nombre }}</a>
        @endforeach
      </div>
    </div>

    <div class="filter-row">
      <div class="filter-label">Equipo</div>
      <div class="filter-chips">
        <a class="chip {{ $filters['equipo'] === 0 ? 'on' : '' }}" href="{{ $chipUrl(['equipo' => 0]) }}">Todos</a>
        @foreach($equipos as $equipo)
          <a class="chip {{ $filters['equipo'] === $equipo->id ? 'on' : '' }}" href="{{ $chipUrl(['equipo' => $equipo->id]) }}">{{ $equipo->nombre }}</a>
        @endforeach
      </div>
    </div>

    <div class="filter-row">
      <div class="filter-label">Tipo</div>
      <div class="filter-chips">
        <a class="chip {{ $filters['tipo'] === 0 ? 'on' : '' }}" href="{{ $chipUrl(['tipo' => 0]) }}">Todos</a>
        @foreach($tipos as $tipo)
          <a class="chip {{ $filters['tipo'] === $tipo->id ? 'on' : '' }}" href="{{ $chipUrl(['tipo' => $tipo->id]) }}">{{ $tipo->nombre }}</a>
        @endforeach
      </div>
    </div>

    @forelse($productos->getCollection()->groupBy(fn ($producto) => optional($producto->fecha)->format('Y-m-d') ?: 'sin-fecha') as $fechaKey => $items)
      @php
        $first = $items->first();
        $fechaGrupo = $first->fecha;
        $fechaIso = optional($fechaGrupo)->format('Y-m-d');
        $prevDate = $fechaGrupo ? $fechaGrupo->copy()->subDay()->format('Y-m-d') : null;
        $nextDate = $fechaGrupo ? $fechaGrupo->copy()->addDay()->format('Y-m-d') : null;
      @endphp
      <section class="date-group">
        <div class="date-head">
          <div class="date-head-title">
            <span class="name">{{ $fmtDate($first->fecha) }}</span>
            <span class="count">{{ $items->count() }} productos</span>
          </div>
          @if($fechaIso)
            <form class="date-nav" method="GET" action="{{ route('mundial.index') }}">
              @foreach($filters as $filterKey => $filterValue)
                @if($filterKey !== 'fecha' && $filterValue !== '' && $filterValue !== 0 && $filterValue !== null)
                  <input type="hidden" name="{{ $filterKey }}" value="{{ $filterValue }}">
                @endif
              @endforeach
              <a class="btn-date" href="{{ $chipUrl(['fecha' => $prevDate]) }}" aria-label="Día anterior">
                <i class="bi bi-chevron-left"></i>
              </a>
              <input type="date" name="fecha" value="{{ $fechaIso }}" onchange="this.form.submit()" aria-label="Seleccionar fecha">
              <a class="btn-date" href="{{ $chipUrl(['fecha' => $nextDate]) }}" aria-label="Día siguiente">
                <i class="bi bi-chevron-right"></i>
              </a>
              @if($filters['fecha'] !== '')
                <a class="btn-date" href="{{ $chipUrl(['fecha' => '']) }}">Todos</a>
              @endif
            </form>
          @endif
        </div>

        @foreach($items as $producto)
          @php
            $tipoNombre = $producto->mundialTipo?->nombre ?? 'Editorial';
            $tipoClass = strcasecmp($tipoNombre, 'Comercial') === 0 ? 'com' : (strcasecmp($tipoNombre, 'Radio') === 0 ? 'radio' : '');
            $plataformaIds = collect($producto->mundial_plataformas_ids ?? ($producto->mundial_plataforma_id ? [$producto->mundial_plataforma_id] : []))->map(fn ($id) => (int) $id);
            $plataformaNombres = $plataformaIds->map(fn ($id) => $plataformasById->get($id)?->nombre)->filter()->values();
            $etapa = $producto->referencia ?: 'Borrador';
            $estadoMichipiti = $producto->productoConvertido?->estado;
            $esMichipiti = (bool) $estadoMichipiti;
            $metricoolProgramado = (bool) ($producto->productoConvertido?->programado_metricool ?? $producto->programado_metricool);
            $puedeMarcarMetricool = $puedeEditarMundial && ! $esMichipiti && ! $metricoolProgramado;
            $badgeEstado = $producto->visible ? $etapa : ($esMichipiti ? $estadoMichipiti : $etapa);
            $etapaClass = match ($etapa) {
                'Terminado' => 'stage-terminado',
                'En proceso' => 'stage-en-proceso',
                'Por cerrar' => 'stage-por-cerrar',
                'Por entregar' => 'stage-por-entregar',
                default => 'stage-borrador',
            };
            $estadoClass = match ($badgeEstado) {
                'APROBADO', 'FINALIZADO', 'PROGRAMADO' => 'stage-terminado',
                'PENDIENTE' => 'stage-por-cerrar',
                default => $producto->visible ? $etapaClass : 'stage-en-proceso',
            };
          @endphp

          <div class="mrow">
            <div class="mtime">
              <b>{{ $fmtTime($producto->hora) }}</b>
              <span>{{ $plataformaNombres->first() ?? 'Mundial' }}</span>
            </div>

            <article
              class="mcard {{ $tipoClass }} {{ $puedeEditarMundial && ! $esMichipiti ? 'is-clickable' : 'is-locked' }}"
              @if($puedeEditarMundial && ! $esMichipiti) data-edit-product="{{ $producto->id }}" @endif
            >
              <div class="mcard-head">
                <p class="mcard-title">{{ $producto->titulo }}</p>
                <div class="badge-stack">
                  <span class="type-badge {{ $tipoClass }}">{{ $tipoNombre }}</span>
                  <span class="stage-badge {{ $estadoClass }}">{{ $badgeEstado }}</span>
                  @if($esMichipiti)
                    <span class="visible-badge">Michipiti</span>
                  @endif
                </div>
              </div>

              <div class="tags">
                @if($producto->mundialPrioridad)
                  <span class="tag format">{{ $producto->mundialPrioridad->nombre }}</span>
                @endif
                @foreach($plataformaNombres as $plataformaNombre)
                  <span class="tag">{{ $plataformaNombre }}</span>
                @endforeach
              </div>

              <div class="meta">
                <span><b>Equipo:</b> {{ $producto->mundialEquipo?->nombre ?? $producto->seccion ?? 'Sin equipo' }}</span>
                <span><b>Líder:</b> {{ $producto->user?->name ?? 'Sin líder' }}</span>
                @if($producto->responsable2)
                  <span><b>Responsable:</b> {{ $producto->responsable2->name }}</span>
                @endif
                @if($producto->manager)
                  <span><b>Edición:</b> {{ $producto->manager->name }}</span>
                @endif
                @if($producto->copy)
                  <span><b>Nota:</b> {{ str($producto->copy)->limit(120) }}</span>
                @endif
                @if(strcasecmp($tipoNombre, 'Comercial') === 0 && $producto->creditos)
                  <span><b>Auspicio:</b> <span class="sponsor-text">{{ $producto->creditos }}</span></span>
                @endif
                <label class="metricool-check metricool-meta {{ $metricoolProgramado ? 'is-on' : '' }}" title="{{ $metricoolProgramado ? 'Ya fue marcado en Metricool' : ($esMichipiti ? 'Estado Metricool del producto Michipiti' : 'Marcar como programado en Metricool') }}">
                  <input
                    type="checkbox"
                    class="metricool-toggle"
                    data-metricool-id="{{ $producto->id }}"
                    @checked($metricoolProgramado)
                    @disabled(! $puedeMarcarMetricool)
                  >
                  <span>{{ $esMichipiti ? 'Metricool Michipiti' : 'Metricool' }}</span>
                </label>
              </div>
            </article>
          </div>
        @endforeach
      </section>
    @empty
      <div class="empty">No hay productos mundiales con estos filtros.</div>
    @endforelse

    <div class="mt-4">
      {{ $productos->links() }}
    </div>
  </div>
</div>

<div class="modal fade" id="mundialEditModal" tabindex="-1" aria-labelledby="mundialEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mundialEditModalLabel">Editar producto mundial</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="mundialEditForm" class="needs-validation" novalidate>
          <input type="hidden" id="editId">
          <input type="hidden" id="editTipoProductoId" value="{{ $defaultTipoProductoId }}">
          <input type="hidden" id="editEstado" value="BORRADOR">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Fecha</label>
              <input class="form-control" type="date" id="editFecha" required @disabled(! $canEditDateTime)>
            </div>
            <div class="col-md-6">
              <label class="form-label">Hora</label>
              <input class="form-control" type="time" id="editHora" required @disabled(! $canEditDateTime)>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prioridad</label>
              <select class="form-select" id="editPrioridad" required>
                <option value="">-- Seleccione prioridad --</option>
                @foreach($prioridades as $prioridad)
                  <option value="{{ $prioridad->id }}">{{ $prioridad->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Plataforma</label>
              <div class="platform-grid" id="editPlataformasGrid">
                @foreach($plataformas as $plataforma)
                  @php
                    $plataformaSlug = Str::slug($plataforma->nombre);
                    $plataformaIcon = $plataformaIconMap[$plataformaSlug] ?? $plataformaSlug;
                    $plataformaIconPath = public_path('images/redes-sociales/'.$plataformaIcon.'.svg');
                  @endphp
                  <label class="platform-check">
                    <input type="checkbox" value="{{ $plataforma->id }}">
                    <span class="platform-card" title="{{ $plataforma->nombre }}">
                      @if(file_exists($plataformaIconPath))
                        <img
                          src="{{ asset('images/redes-sociales/'.$plataformaIcon.'.svg') }}"
                          alt="{{ $plataforma->nombre }}"
                          loading="lazy"
                        >
                      @else
                        <span class="platform-fallback">{{ strtoupper(substr($plataforma->nombre, 0, 2)) }}</span>
                      @endif
                    </span>
                  </label>
                @endforeach
              </div>
              <div class="invalid-feedback d-block d-none" id="editPlataformasError">Selecciona al menos una plataforma.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Equipo</label>
              <select class="form-select" id="editEquipo" required>
                <option value="">-- Seleccione equipo --</option>
                @foreach($equipos as $equipo)
                  <option value="{{ $equipo->id }}">{{ $equipo->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo</label>
              <select class="form-select" id="editMundialTipo" required>
                <option value="">-- Seleccione tipo --</option>
                @foreach($tipos as $tipo)
                  <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 d-none" id="editAuspicioWrap">
              <label class="form-label">Auspicio</label>
              <input class="form-control" type="text" id="editAuspicio" maxlength="600" placeholder="Marca o auspiciante">
            </div>
            <div class="col-12">
              <label class="form-label">Título</label>
              <input class="form-control" type="text" id="editTitulo" required>
            </div>
            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="editDescripcion" rows="4"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Líder</label>
              <select class="form-select" id="editLider">
                <option value="">-- Seleccione líder --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Responsable</label>
              <select class="form-select" id="editResponsable" required>
                <option value="">-- Seleccione responsable --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Edición</label>
              <select class="form-select" id="editEdicion">
                <option value="">-- Opcional --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Etapa</label>
              <select class="form-select" id="editEtapa">
                <option value="Borrador">Borrador</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
                <option value="Por cerrar">Por cerrar</option>
                <option value="Por entregar">Por entregar</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        @if($puedeEditarMundial)
          <button type="button" class="btn btn-dark" id="saveMundialEditBtn">Guardar</button>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const mundialEditItems = @json($editItems);
  const canEditDateTime = @json($canEditDateTime);
  const puedeEditarMundial = @json($puedeEditarMundial);
  const mundialEditModalEl = document.getElementById('mundialEditModal');
  const mundialEditModal = new bootstrap.Modal(mundialEditModalEl);
  const mundialEditForm = document.getElementById('mundialEditForm');
  const saveMundialEditBtn = document.getElementById('saveMundialEditBtn');
  const editPlataformasGrid = document.getElementById('editPlataformasGrid');
  const editPlataformasError = document.getElementById('editPlataformasError');
  const editMundialTipo = document.getElementById('editMundialTipo');
  const editAuspicioWrap = document.getElementById('editAuspicioWrap');
  const editAuspicio = document.getElementById('editAuspicio');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let editUsersLoaded = false;

  async function fetchJSON(url, options = {}){
    const res = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        ...(options.headers || {})
      },
      ...options
    });

    if(!res.ok){
      const data = await res.json().catch(() => ({}));
      throw new Error(data.message || 'No se pudo guardar.');
    }

    return res.json();
  }

  function fillSelect(select, users, placeholder){
    const current = select.value;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    users.forEach(user => {
      const option = document.createElement('option');
      option.value = user.id;
      option.textContent = user.name;
      select.appendChild(option);
    });
    select.value = current;
  }

  async function ensureEditUsersLoaded(){
    if(editUsersLoaded) return;

    const [periodistas, videografos] = await Promise.all([
      fetchJSON('/mundial/planificador/periodistas'),
      fetchJSON('/mundial/planificador/videografos'),
    ]);

    fillSelect(document.getElementById('editLider'), periodistas, '-- Seleccione líder --');
    fillSelect(document.getElementById('editResponsable'), periodistas, '-- Seleccione responsable --');
    fillSelect(document.getElementById('editEdicion'), videografos, '-- Opcional --');
    editUsersLoaded = true;
  }

  function setEditPlataformas(values = []){
    const selected = new Set((values || []).map(value => Number(value)));
    editPlataformasGrid.querySelectorAll('input[type="checkbox"]').forEach(input => {
      input.checked = selected.has(Number(input.value));
    });
  }

  function getEditPlataformas(){
    return [...editPlataformasGrid.querySelectorAll('input[type="checkbox"]:checked')].map(input => Number(input.value));
  }

  function syncEditPlataformasValidation(showError = false){
    const valid = getEditPlataformas().length > 0;
    editPlataformasGrid.classList.toggle('is-invalid', showError && !valid);
    editPlataformasError.classList.toggle('d-none', !(showError && !valid));
    return valid;
  }

  function selectedTipoName(select){
    return select.options[select.selectedIndex]?.textContent?.trim() || '';
  }

  function isComercialTipo(select){
    return selectedTipoName(select).toLowerCase() === 'comercial';
  }

  function syncEditAuspicioVisibility(){
    const show = isComercialTipo(editMundialTipo);
    editAuspicioWrap.classList.toggle('d-none', !show);
    if(!show){
      editAuspicio.value = '';
    }
  }

  function syncEditEtapaVisibility(){
    const etapaOption = [...document.getElementById('editEtapa').options].find(option => option.value === 'Por entregar');
    const comercial = isComercialTipo(editMundialTipo);

    if(etapaOption){
      etapaOption.hidden = !comercial;
      etapaOption.disabled = !comercial;
    }

    if(!comercial && document.getElementById('editEtapa').value === 'Por entregar'){
      document.getElementById('editEtapa').value = 'Borrador';
    }
  }

  async function openEditModal(productId){
    if(!puedeEditarMundial) return;

    const item = mundialEditItems[String(productId)] || mundialEditItems[productId];
    if(!item) return;
    if(item.es_michipiti) return;

    await ensureEditUsersLoaded();

    document.getElementById('editId').value = item.id || '';
    document.getElementById('editTipoProductoId').value = item.tipo_producto_id || document.getElementById('editTipoProductoId').value || '';
    document.getElementById('editEstado').value = item.estado || 'BORRADOR';
    document.getElementById('editFecha').value = item.fecha || '';
    document.getElementById('editHora').value = item.hora || '';
    document.getElementById('editFecha').disabled = !canEditDateTime;
    document.getElementById('editHora').disabled = !canEditDateTime;
    document.getElementById('editPrioridad').value = item.mundial_prioridad_id || '';
    setEditPlataformas(item.mundial_plataformas_ids || []);
    document.getElementById('editEquipo').value = item.mundial_equipo_id || '';
    editMundialTipo.value = item.mundial_tipo_id || '';
    document.getElementById('editTitulo').value = item.titulo || '';
    document.getElementById('editDescripcion').value = item.descripcion || '';
    editAuspicio.value = item.auspicio || '';
    syncEditAuspicioVisibility();
    syncEditEtapaVisibility();
    document.getElementById('editLider').value = item.asignado_a || '';
    document.getElementById('editResponsable').value = item.responsable2_id || '';
    document.getElementById('editEdicion').value = item.edicion_id || '';
    document.getElementById('editEtapa').value = item.etapa || 'Borrador';
    mundialEditForm.classList.remove('was-validated');
    syncEditPlataformasValidation(false);
    mundialEditModal.show();
  }

  document.querySelectorAll('[data-edit-product]').forEach(card => {
    card.addEventListener('click', () => openEditModal(card.dataset.editProduct));
  });

  document.querySelectorAll('[data-metricool-id]').forEach(input => {
    input.addEventListener('click', event => {
      event.stopPropagation();
    });

    input.addEventListener('change', async () => {
      if(!input.checked){
        input.checked = true;
        return;
      }

      input.disabled = true;

      try{
        const res = await fetchJSON(`/mundial/listado/${input.dataset.metricoolId}/metricool`, {
          method: 'POST',
          body: JSON.stringify({})
        });

        if(!res.ok){
          throw new Error(res.message || 'No se pudo marcar Metricool.');
        }

        input.closest('.metricool-check')?.classList.add('is-on');
      }catch(error){
        input.checked = false;
        input.disabled = false;
        input.closest('.metricool-check')?.classList.remove('is-on');
        Swal.fire({
          icon: 'error',
          text: error.message || 'No se pudo marcar Metricool.',
          confirmButtonText: 'Aceptar',
        });
      }
    });
  });

  editPlataformasGrid?.addEventListener('change', () => {
    if(editPlataformasGrid.classList.contains('is-invalid')){
      syncEditPlataformasValidation(true);
    }
  });
  editMundialTipo?.addEventListener('change', syncEditAuspicioVisibility);
  editMundialTipo?.addEventListener('change', syncEditEtapaVisibility);

  saveMundialEditBtn?.addEventListener('click', async () => {
    if(!puedeEditarMundial) return;

    const hasPlataformas = syncEditPlataformasValidation(true);
    if(!hasPlataformas || !mundialEditForm.checkValidity()){
      mundialEditForm.classList.add('was-validated');
      return;
    }

    saveMundialEditBtn.disabled = true;
    saveMundialEditBtn.textContent = 'Guardando...';

    try{
      await fetchJSON('/mundial/planificador/store', {
        method: 'POST',
        body: JSON.stringify({
          id: Number(document.getElementById('editId').value),
          fecha: document.getElementById('editFecha').value,
          hora: document.getElementById('editHora').value,
          mundial_prioridad_id: Number(document.getElementById('editPrioridad').value),
          mundial_plataformas_ids: getEditPlataformas(),
          mundial_equipo_id: Number(document.getElementById('editEquipo').value),
          mundial_tipo_id: Number(editMundialTipo.value),
          titulo: document.getElementById('editTitulo').value.trim(),
          descripcion: document.getElementById('editDescripcion').value.trim(),
          auspicio: isComercialTipo(editMundialTipo) ? editAuspicio.value.trim() : null,
          estado: document.getElementById('editEstado').value || 'BORRADOR',
          tipo_producto_id: Number(document.getElementById('editTipoProductoId').value),
          redes_sociales_ids: [],
          asignado_a: document.getElementById('editLider').value ? Number(document.getElementById('editLider').value) : null,
          responsable2_id: Number(document.getElementById('editResponsable').value),
          edicion_id: document.getElementById('editEdicion').value ? Number(document.getElementById('editEdicion').value) : null,
          etapa: document.getElementById('editEtapa').value || 'Borrador',
        })
      });

      window.location.reload();
    }catch(error){
      Swal.fire({
        icon: 'error',
        text: error.message || 'No se pudo guardar.',
        confirmButtonText: 'Aceptar',
      });
    }finally{
      saveMundialEditBtn.disabled = false;
      saveMundialEditBtn.textContent = 'Guardar';
    }
  });
</script>
@endpush
