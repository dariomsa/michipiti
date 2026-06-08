@extends('layouts.app')

@section('title','Especial Mundial')

@push('styles')
<style>
  :root {
    --border: #9ca3af;
    --slot: #ffffff;

    --slot-disabled-editable: #dbeafe;

    --green-bg: #82fdac;
    --green-border: #15803d;

    --orange-bg: #f59894;
    --orange-border: #fb923c;

    --yellow-bg: #fef3c7;
    --yellow-border: #f59e0b;

    --gray-bg: #f3f4f6;
    --gray-border: #d1d5db;

    --thead-h: 66px;
    --hour-col-w: 82px;
    --day-col-w: 250px;
  }

  .propuestas-wrap{
    width: 100%;
    max-width: 100%;
    padding: 0;
    background: transparent;
    overflow: hidden;
    box-sizing: border-box;
  }

  .page-title {
    font-weight: 800;
    letter-spacing: -0.2px;
  }

  .mundial-top{
    border-bottom:3px solid #0b3d6b;
    margin-bottom:18px;
    padding-bottom:14px;
  }

  .mundial-brand{
    color:#0b3d6b;
    font-size:13px;
    font-weight:800;
    letter-spacing:.22em;
    text-transform:uppercase;
  }

  .mundial-title{
    color:#0e1726;
    font-family:Georgia, 'Times New Roman', serif;
    font-size:clamp(30px,4.5vw,46px);
    font-weight:700;
    line-height:1;
    margin:6px 0 4px;
  }

  .mundial-title em{
    color:#b9551f;
    font-style:italic;
    font-weight:700;
  }

  .btn-pill {
    border-radius: 999px;
  }

  .toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
    justify-content:space-between;
    min-width: 0;
  }

  .toolbar .left,
  .toolbar .right{
    display:flex;
    gap:10px;
    align-items:center;
    flex-wrap:wrap;
    min-width: 0;
  }

  .date-chip{
    padding: 6px 12px;
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 12px;
    font-weight: 600;
    white-space: nowrap;
  }

  .content-card{
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(0,0,0,0.05);
    overflow: hidden;
    width: 100%;
    max-width: 100%;
    min-width: 0;
  }

  .calendar{
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 260px);
    position: relative;
    display: block;
    box-sizing: border-box;
  }

  .calendar-table{
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
    width: max-content;
    min-width: calc(var(--hour-col-w) + (var(--day-col-w) * 7));
    margin: 0;
  }

  .calendar-table th,
  .calendar-table td{
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    text-align: center;
    vertical-align: middle;
    height: 46px;
    font-size: 13px;
    white-space: nowrap;
    box-sizing: border-box;
  }

  .calendar-table tr > *:first-child{
    border-left: 1px solid var(--border);
  }

  .calendar-table thead tr:first-child > *{
    border-top: 1px solid var(--border);
  }

  .calendar-table thead th{
    background: #f9fafb;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 3;
    height: var(--thead-h);
    border-bottom: 2px solid #6b7280;
  }

  .calendar-table thead th:not(.hour),
  .calendar-table tbody td:not(.hour){
    width: var(--day-col-w);
    min-width: var(--day-col-w);
    max-width: var(--day-col-w);
  }

  .hour{
    width: var(--hour-col-w);
    min-width: var(--hour-col-w);
    max-width: var(--hour-col-w);
    background-color: #f3f4f6 !important;
    font-weight: 700;
    position: sticky;
    left: 0;
    z-index: 4;
    box-shadow: 2px 0 0 0 #6b7280;
  }

  .calendar-table thead .hour{
    top: 0;
    left: 0;
    z-index: 6;
  }

  .hour-label{
    display:flex;
    align-items:center;
    justify-content:center;
    height:100%;
  }

  .slot{
    background-color: var(--slot);
    cursor: pointer;
    transition: background-color 120ms ease, box-shadow 120ms ease, opacity 120ms ease, transform 120ms ease;
    overflow: hidden;
    user-select: none;
  }

  .slot:hover{
    box-shadow: inset 0 0 0 2px rgba(0,0,0,0.06);
  }

  .slot-not-allowed{
    background: #e5e7eb;
    border-color: #cbd5e1 !important;
    cursor: not-allowed !important;
  }

  .slot-not-allowed:hover{
    box-shadow: inset 0 0 0 2px #dc2626;
  }

  .slot-out-of-schedule{
    background: var(--slot-disabled-editable);
  }

  .slot-programado{
    background: var(--green-bg) !important;
    border-color: #15803d !important;
    box-shadow: inset 0 0 0 1px #15803d;
  }

  .slot-pendiente{
    background: var(--orange-bg) !important;
    border-color: var(--orange-border) !important;
  }

  .slot-carrusel{
    background: var(--yellow-bg) !important;
    border-color: var(--yellow-border) !important;
  }

  .slot-default-busy{
    background: var(--gray-bg) !important;
    border-color: var(--gray-border) !important;
  }

  .slot-wrap{
    padding: 6px 8px;
    text-align: left;
    line-height: 1.15;
    display:flex;
    flex-direction:column;
    gap:4px;
    min-height:100%;
  }

  .slot-topline{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:6px;
    margin-bottom:2px;
  }

  .slot-title{
    font-weight:700;
    font-size:12px;
    min-width:0;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
  }

  .slot-meta{
    font-size:11px;
    opacity:.9;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .slot-social-footer{
    display:flex;
    justify-content:flex-end;
    gap:6px;
    margin-top:auto;
    padding-top:4px;
    border-top:1px solid rgba(0,0,0,0.05);
  }

  .slot-social-footer img{
    width:13px;
    height:13px;
    object-fit:contain;
    display:block;
  }

  .slot-platform-pill{
    min-width:16px;
    height:14px;
    border-radius:999px;
    background:#fff;
    border:1px solid rgba(15,23,42,.12);
    color:#111827;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:8px;
    font-weight:800;
    line-height:1;
    padding:0 4px;
  }

  .slot-multi{
    display:flex;
    flex-direction:column;
    gap:3px;
    padding:4px;
    height:100%;
  }

  .slot-item{
    background:rgba(255,255,255,.68);
    border:1px solid rgba(15,23,42,.1);
    border-radius:6px;
    min-height:0;
    overflow:hidden;
    padding:3px 5px;
  }

  .slot-item .slot-meta,
  .slot-item .slot-social-footer{
    display:none;
  }

  .slot-item .slot-title{
    font-size:10px;
  }

  .slot-item .slot-origin-badge{
    font-size:8px;
    padding:1px 4px;
  }

  .slot-more{
    align-self:flex-end;
    background:#111827;
    border-radius:999px;
    color:#fff;
    font-size:10px;
    font-weight:800;
    line-height:1;
    padding:3px 7px;
  }

  .slot-origin-badge{
    flex:0 0 auto;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:1px 6px;
    border-radius:999px;
    font-size:9px;
    font-weight:800;
    letter-spacing:.02em;
    text-transform:uppercase;
    border:1px solid transparent;
    line-height:1.2;
  }

  .slot-origin-pauta{
    background:#fff7ed;
    border-color:#fdba74;
    color:#9a3412;
  }

  .slot-origin-comercial{
    background:#eff6ff;
    border-color:#93c5fd;
    color:#1d4ed8;
  }

  .slot-origin-propuesta{
    background:#f3f4f6;
    border-color:#d1d5db;
    color:#374151;
  }

  .slot-origin-pendiente{
    background:#fff7ed;
    border-color:#fed7aa;
    color:#c2410c;
  }

  .slot-type-editorial{
    background:#eff6ff;
    border-color:#93c5fd;
    color:#0b3d6b;
  }

  .slot-type-comercial{
    background:#fff7ed;
    border-color:#fdba74;
    color:#b9551f;
  }

  .slot-type-radio{
    background:#ecfdf5;
    border-color:#86efac;
    color:#2f7d56;
  }

  .slot-border-editorial{
    border-color:#0b3d6b !important;
    box-shadow: inset 0 0 0 1px rgba(11,61,107,.28);
  }

  .slot-border-comercial{
    border-color:#b9551f !important;
    box-shadow: inset 0 0 0 1px rgba(185,85,31,.28);
  }

  .slot-border-radio{
    border-color:#2f7d56 !important;
    box-shadow: inset 0 0 0 1px rgba(47,125,86,.28);
  }

  .slot-item.slot-border-editorial,
  .slot-item.slot-border-comercial,
  .slot-item.slot-border-radio{
    box-shadow:none;
    border-left-width:4px;
  }

  .legend-dot{
    display:inline-block;
    width:12px;
    height:12px;
    border-radius:50%;
    margin-right:6px;
    vertical-align:middle;
  }

  .day-head{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:4px;
    line-height:1.05;
    min-height:54px;
    padding:6px 8px;
  }

  .day-head-title{
    font-weight:800;
    font-size:14px;
    color:#111827;
  }

  .day-head-today{
    background:#fff7ed;
    box-shadow: inset 0 0 0 1px #fdba74;
  }

  .slot-today{
    background-image: linear-gradient(to bottom, rgba(251,146,60,.08), rgba(251,146,60,0));
  }

  .day-head-stats{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
    font-size:11px;
    color:#4b5563;
  }

  .mini-stat{
    display:inline-flex;
    align-items:center;
    gap:5px;
    font-weight:700;
  }

  .mini-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    display:inline-block;
  }

  .mini-dot-green{
    background:#22c55e;
  }

  .mini-dot-orange{
    background:#fb923c;
  }

  .slot-draggable{
    cursor: grab !important;
  }

  .slot-dragging{
    opacity: .45 !important;
    cursor: grabbing !important;
    transform: scale(.985);
  }

  .slot-drop-target{
    box-shadow: inset 0 0 0 2px #0d6efd !important;
  }

  .slot-drop-target-empty{
    box-shadow: inset 0 0 0 2px #22c55e !important;
  }

  .slot-drop-target-busy{
    box-shadow: inset 0 0 0 2px #f59e0b !important;
  }

  .slot-drop-not-allowed{
    box-shadow: inset 0 0 0 2px #dc3545 !important;
    cursor: not-allowed !important;
  }

  .planner-drag-overlay{
    background: rgba(15, 23, 42, 0.34);
    inset: 0;
    opacity: 0;
    pointer-events: none;
    position: fixed;
    transition: opacity 140ms ease;
    z-index: 1025;
  }

  .planner-drag-overlay.is-visible{
    opacity: 1;
  }

  #slotModal .modal-dialog{
    max-width: 720px;
    width: calc(100% - 1rem);
  }

  #slotModal .modal-content,
  #slotModal form{
    max-width: 100%;
    min-width: 0;
    overflow-x: hidden;
  }

  #slotModal .modal-header,
  #slotModal .modal-footer{
    padding: .55rem .75rem;
  }

  #slotModal .modal-body{
    overflow-x: hidden;
    padding: .65rem .75rem;
  }

  #slotModal .modal-title{
    font-size: 1rem;
  }

  #slotModal .badge{
    font-size: .72rem;
    padding: .38rem .5rem;
  }

  #slotModal .row.g-3{
    --bs-gutter-x: .55rem;
    --bs-gutter-y: .45rem;
  }

  #slotModal .row > [class*="col-"]{
    min-width: 0;
  }

  #slotModal .form-label{
    margin-bottom: .25rem;
    font-size: .78rem;
    font-weight: 600;
  }

  #slotModal .form-control,
  #slotModal .form-select{
    min-height: 31px;
    height: 31px;
    padding: .22rem .5rem;
    font-size: .84rem;
    border-radius: .35rem;
  }

  #slotModal textarea.form-control{
    min-height: 56px;
    height: auto;
    padding-top: .35rem;
    padding-bottom: .35rem;
    line-height: 1.2;
  }

  #slotModal .invalid-feedback{
    font-size: .74rem;
  }

  #slotModal .btn{
    padding: .32rem .62rem;
    font-size: .82rem;
  }

  .social-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(34px, 1fr));
    gap:.14rem;
    min-width: 0;
  }

  .social-grid.is-invalid{
    border:1px solid #dc3545;
    border-radius:.55rem;
    background:#fff5f5;
  }

  .social-check{
    position:relative;
  }

  .social-check input{
    position:absolute;
    opacity:0;
    pointer-events:none;
  }

  .social-card{
    border:1px solid #cbd5e1;
    border-radius:.6rem;
    background: linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%);
    min-height:29px;
    padding:.04rem;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    box-shadow:
      0 1px 0 rgba(255,255,255,.95) inset,
      0 2px 4px rgba(15,23,42,.14),
      0 0 0 1px rgba(255,255,255,.4);
    transform: translateY(0);
    transition:border-color 120ms ease, box-shadow 120ms ease, background-color 120ms ease, transform 120ms ease;
  }

  .social-card img{
    width:18px;
    height:18px;
    object-fit:contain;
    display:block;
    filter: drop-shadow(0 1px 1px rgba(15,23,42,.18));
  }

  .platform-card{
    min-height:30px;
  }

  .platform-fallback{
    color:#111827;
    font-size:.72rem;
    font-weight:800;
    line-height:1;
  }

  .social-check:hover .social-card{
    transform: translateY(-1px);
    box-shadow:
      0 1px 0 rgba(255,255,255,.95) inset,
      0 4px 8px rgba(15,23,42,.18),
      0 0 0 1px rgba(255,255,255,.45);
  }

  .social-check input:checked + .social-card{
    border-color:#0d6efd;
    background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow:
      0 1px 0 rgba(255,255,255,.95) inset,
      0 4px 10px rgba(13,110,253,.18),
      inset 0 0 0 1px #0d6efd;
    transform: translateY(-1px);
  }

  @media (max-width: 767.98px){
    .social-grid{
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }

  @media (max-width: 1400px){
    :root{
      --day-col-w: 220px;
    }
  }

  @media (max-width: 1200px){
    :root{
      --day-col-w: 200px;
    }
  }

  @media (max-width: 767.98px){
    .propuestas-wrap{
      padding: 0;
    }

    .mundial-brand{
      font-size:11px;
      letter-spacing:.16em;
    }

    .mundial-title{
      font-size:30px;
    }

    :root{
      --hour-col-w: 76px;
      --day-col-w: 180px;
    }
  }

  html, body {
    overflow-x: hidden;
  }

  main,
  .main-content,
  .content,
  .container,
  .container-fluid,
  .app-content {
    max-width: 100%;
    overflow-x: hidden;
  }
</style>
@endpush

@section('content')

@php
  $puedeAprobar = auth()->user() && auth()->user()->hasAnyRole(['editor', 'director']);
  $puedeDragDrop = auth()->user() && auth()->user()->hasRole('director');
  $plataformaIconMap = [
      'facebook' => 'facebook',
      'instagram' => 'instagram',
      'tiktok' => 'tiktok',
      'youtube' => 'youtube',
      'whatsapp' => 'whatsapp',
	      'podcast' => 'podcast', 
		  'radio' => 'radio', 
		  'shorts' => 'shorts','web' => 'web',
  ];
  $mundialPlataformasMap = $mundialPlataformas
      ->mapWithKeys(function ($plataforma) use ($plataformaIconMap): array {
          $slug = str($plataforma->nombre)
              ->lower()
              ->replace('á', 'a')
              ->replace('é', 'e')
              ->replace('í', 'i')
              ->replace('ó', 'o')
              ->replace('ú', 'u')
              ->replace(' ', '-')
              ->toString();
          $icon = $plataformaIconMap[$slug] ?? null;

          return [
              $plataforma->id => [
                  'nombre' => $plataforma->nombre,
                  'icon' => $icon ? asset('images/redes-sociales/'.$icon.'.svg') : null,
                  'fallback' => strtoupper(substr($plataforma->nombre, 0, 2)),
              ],
          ];
      })
      ->all();
	  
	  
	   
 // echo json_encode($mundialPlataformasMap);
@endphp

<div class="propuestas-wrap">

  <header class="mundial-top">
    <div class="mundial-brand">EL COMERCIO · OPERATIVO MUNDIAL</div>
    <h1 class="mundial-title">Calendario <em>Mundial 2026</em> · torneo completo</h1>
  </header>

  <div class="toolbar mb-3">
    <div class="left flex-grow-1">
      <div class="input-group" style="max-width: 680px;">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar" aria-label="Buscar" id="searchInput" />
      </div>

    </div>

    <div class="right">
      <button class="btn btn-outline-secondary btn-sm btn-pill" aria-label="Anterior" id="btnPrev">
        <i class="bi bi-chevron-left"></i>
      </button>
      <span class="date-chip" id="rangeLabel">
        <i class="bi bi-calendar-event me-2"></i>
        <span>Semana</span>
      </span>
      <button class="btn btn-outline-secondary btn-sm btn-pill" id="btnToday">Hoy</button>
      <button class="btn btn-outline-secondary btn-sm btn-pill" aria-label="Siguiente" id="btnNext">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>

  <div class="mb-3 d-flex flex-wrap gap-3 small text-muted">
    <span class="mini-stat">
      <span class="mini-dot mini-dot-orange"></span>Ocupados
    </span>
    <span class="mini-stat">
      <span class="mini-dot mini-dot-green"></span>Disponibles
    </span>
  </div>

  <div class="content-card p-0">
    <div class="calendar" id="calendarWrap">
      <table class="calendar-table">
        <thead>
          <tr>
            <th class="hour"><div class="hour-label">Hora</div></th>
            <th id="h0"></th>
            <th id="h1"></th>
            <th id="h2"></th>
            <th id="h3"></th>
            <th id="h4"></th>
            <th id="h5"></th>
            <th id="h6"></th>
          </tr>
        </thead>
        <tbody id="calendarBody"></tbody>
      </table>
    </div>
  </div>

  <div class="mt-3 d-flex flex-wrap gap-3 small text-muted">
    <span><span class="legend-dot" style="background:#dcfce7;border:1px solid #15803d;"></span>Aprobado / Finalizado</span>
    <span><span class="legend-dot" style="background:#fb493c;border:1px solid #fb493c;"></span>Pendiente</span>
    <span><span class="legend-dot" style="background:#fef3c7;border:1px solid #f59e0b;"></span>En Proceso</span>
    <span><span class="legend-dot" style="background:#dbeafe;border:1px solid #93c5fd;"></span>Horario fuera de pauta editable</span>
  </div>
</div>

<div class="planner-drag-overlay" id="plannerDragOverlay" aria-hidden="true"></div>

<div class="modal fade" id="slotModal" tabindex="-1" aria-labelledby="slotModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="slotModalLabel">Agregar contenido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge text-bg-secondary" id="modalDayLabel">Día: -</span>
          <span class="badge text-bg-secondary" id="modalHourLabel">Hora: -</span>
          <span class="badge text-bg-warning d-none" id="modalOutOfScheduleLabel">Fuera de pauta</span>
          <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" id="newSameSlotBtn" title="Agregar otro producto en este horario">
            <i class="bi bi-plus-lg"></i>
          </button>
        </div>

        <form id="slotForm" class="needs-validation" novalidate autocomplete="off">
          <input type="hidden" id="formId" name="id" />
          <input type="hidden" id="formIsAllowed" value="1" />
          <input type="hidden" id="formDate" name="fecha" />
          <input type="hidden" id="formTime" name="hora" />
          <input type="hidden" id="formStatus" name="estado" value="BORRADOR" />
          <input type="hidden" id="formTipoProducto" name="tipo_producto_id" value="{{ $tiposProducto->first()?->id }}" />

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Prioridad</label>
              <select class="form-select" id="formPrioridad" name="mundial_prioridad_id" required>
                <option value="">-- Seleccione prioridad --</option>
                @foreach($mundialPrioridades as $prioridad)
                  <option value="{{ $prioridad->id }}">{{ $prioridad->nombre }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Selecciona una prioridad.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Plataforma</label>
              <div class="social-grid" id="plataformasGrid">
                @foreach($mundialPlataformas as $plataforma)
                  @php
                    $plataformaSlug = str($plataforma->nombre)->lower()->replace('á', 'a')->replace('é', 'e')->replace('í', 'i')->replace('ó', 'o')->replace('ú', 'u')->replace(' ', '-')->toString();
                    $plataformaIconMap = [
                        'facebook' => 'facebook',
                        'instagram' => 'instagram',
                        'tiktok' => 'tiktok',
                        'youtube' => 'youtube',
                        'whatsapp' => 'whatsapp',
						'podcast' => 'podcast',
						'radio' => 'radio','shorts' => 'shorts','web' => 'web',
                    ];
                    $plataformaIcon = $plataformaIconMap[$plataformaSlug] ?? null;
                  @endphp
                  <label class="social-check" title="{{ $plataforma->nombre }}">
                    <input
                      type="checkbox"
                      class="form-check-input d-none"
                      name="mundial_plataformas_ids[]"
                      value="{{ $plataforma->id }}"
                    >
                    <span class="social-card platform-card">
                      @if($plataformaIcon)
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
              <div class="invalid-feedback d-block d-none" id="plataformasError">Selecciona al menos una plataforma.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Equipo</label>
              <select class="form-select" id="formEquipo" name="mundial_equipo_id" required>
                <option value="">-- Seleccione equipo --</option>
                @foreach($mundialEquipos as $equipo)
                  <option value="{{ $equipo->id }}">{{ $equipo->nombre }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Selecciona un equipo.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tipo</label>
              <select class="form-select" id="formMundialTipo" name="mundial_tipo_id" required>
                <option value="">-- Seleccione tipo --</option>
                @foreach($mundialTipos as $tipo)
                  <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Selecciona un tipo.</div>
            </div>

            <div class="col-md-6 d-none" id="formAuspicioWrap">
              <label class="form-label">Auspicio</label>
              <input type="text" class="form-control" id="formAuspicio" name="auspicio" maxlength="600" placeholder="Marca o auspiciante">
            </div>

            <div class="d-none" id="redesSocialesGrid"></div>
            <div class="invalid-feedback d-block d-none" id="redesSocialesError"></div>

            <div class="col-12">
              <label class="form-label">Título</label>
              <input type="text" class="form-control" id="formTitle" name="titulo" placeholder="Ej: Titulo" required />
              <div class="invalid-feedback">Agrega un título.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="formDesc" name="descripcion" rows="4" placeholder="Detalle del contenido, enfoque, fuentes, etc."></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Líder</label>
              <select class="form-select" id="formResponsable" name="asignado_a">
                <option value="">-- Seleccione líder --</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Responsable</label>
              <select class="form-select" id="formResponsable2" name="responsable2_id" required>
                <option value="">-- Seleccione responsable --</option>
              </select>
              <div class="invalid-feedback">Selecciona un responsable.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Edición</label>
              <select class="form-select" id="formEdicion" name="edicion_id">
                <option value="">-- Opcional --</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Etapa</label>
              <select class="form-select" id="formEtapa" name="etapa">
                <option value="Borrador">Borrador</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
                <option value="Por cerrar">Por cerrar</option>
              </select>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger d-none me-auto" id="deleteSlotBtn">
          <i class="bi bi-trash me-1"></i> Eliminar
        </button>

        @if($puedeAprobar)
          <button type="button" class="btn btn-outline-success d-none" id="approveSlotBtn">
            <i class="bi bi-check2-circle me-1"></i> Aprobar
          </button>
        @endif

        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

        <a href="#" class="btn btn-success d-none" id="canvaLinkBtn" target="_blank" rel="noopener noreferrer">
          <i class="bi bi-box-arrow-up-right me-1"></i> Ver Canva
        </a>

        <button type="button" class="btn btn-dark" id="saveSlotBtn">Guardar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const puedeAprobar = @json($puedeAprobar);
  const puedeDragDrop = @json($puedeDragDrop);

  const dayNames = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
  const baseAllowedSchedule = @json($baseAllowedSchedule);
  const baseVisibleSchedule = @json($baseVisibleSchedule);
  const specialScheduleByDate = @json($specialScheduleByDate);
  const mundialPlataformasMap = @json($mundialPlataformasMap);

  const tbody = document.getElementById('calendarBody');
  const slotModalEl = document.getElementById('slotModal');
  const slotModal = new bootstrap.Modal(slotModalEl);

  const modalTitle = document.getElementById('slotModalLabel');
  const modalDayLabel = document.getElementById('modalDayLabel');
  const modalHourLabel = document.getElementById('modalHourLabel');
  const modalOutOfScheduleLabel = document.getElementById('modalOutOfScheduleLabel');

  const form = document.getElementById('slotForm');
  const formId = document.getElementById('formId');
  const formDate = document.getElementById('formDate');
  const formTime = document.getElementById('formTime');
  const formPrioridad = document.getElementById('formPrioridad');
  const plataformasGrid = document.getElementById('plataformasGrid');
  const plataformasError = document.getElementById('plataformasError');
  const formEquipo = document.getElementById('formEquipo');
  const formMundialTipo = document.getElementById('formMundialTipo');
  const formTitle = document.getElementById('formTitle');
  const formDesc = document.getElementById('formDesc');
  const formAuspicioWrap = document.getElementById('formAuspicioWrap');
  const formAuspicio = document.getElementById('formAuspicio');
  const formStatus = document.getElementById('formStatus');
  const formTipoProducto = document.getElementById('formTipoProducto');
  const formResponsable = document.getElementById('formResponsable');
  const formResponsable2 = document.getElementById('formResponsable2');
  const formEdicion = document.getElementById('formEdicion');
  const formEtapa = document.getElementById('formEtapa');
  const formIsAllowed = document.getElementById('formIsAllowed');
  const redesSocialesGrid = document.getElementById('redesSocialesGrid');
  const redesSocialesError = document.getElementById('redesSocialesError');

  const saveBtn = document.getElementById('saveSlotBtn');
  const newSameSlotBtn = document.getElementById('newSameSlotBtn');
  const approveBtn = document.getElementById('approveSlotBtn');
  const deleteSlotBtn = document.getElementById('deleteSlotBtn');
  const canvaLinkBtn = document.getElementById('canvaLinkBtn');

  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const btnToday = document.getElementById('btnToday');
  const rangeLabel = document.getElementById('rangeLabel');
  const searchInput = document.getElementById('searchInput');
  const plannerDragOverlay = document.getElementById('plannerDragOverlay');
  const AUTO_REFRESH_MS = 15000;

  let weekStart = startOfWeekMonday(new Date());
  const slotData = {};
  let dragSource = null;
  let isDragging = false;
  let isSavingSlot = false;
  let lastWeekSignature = '';
  let autoRefreshTimer = null;

  function startOfWeekMonday(date){
    const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const day = d.getDay();
    const diff = day === 0 ? 6 : day - 1;
    d.setDate(d.getDate() - diff);
    d.setHours(0,0,0,0);
    return d;
  }

  function fmtISODate(d){
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function addDays(date, days){
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    return d;
  }

  function fmtHeader(d, label){
    return `${label} ${d.getDate()}`;
  }

  function fmtRangeLabel(start){
    const end = addDays(start, 6);
    const months = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    return `${start.getDate()} ${months[start.getMonth()]} ${start.getFullYear()} - ${end.getDate()} ${months[end.getMonth()]} ${end.getFullYear()}`;
  }

  function isTodayDate(date){
    const today = new Date();
    today.setHours(0,0,0,0);

    return date.getFullYear() === today.getFullYear()
      && date.getMonth() === today.getMonth()
      && date.getDate() === today.getDate();
  }

  function slotKey(fecha, hora){
    return `${fecha}|${hora}`;
  }

  function sortHours(hours){
    return [...new Set(hours)].sort((a, b) => {
      const [ah, am] = a.split(':').map(Number);
      const [bh, bm] = b.split(':').map(Number);
      return (ah * 60 + am) - (bh * 60 + bm);
    });
  }

  function buildWeekSignature(items){
    return JSON.stringify(
      (items || []).map(item => [
        Number(item.id || 0),
        item.fecha || '',
        (item.hora || '').slice(0, 5),
        item.estado || '',
        item.origen || '',
        item.updated_at || '',
      ])
    );
  }

  function isModalOpen(){
    return !!slotModalEl?.classList.contains('show');
  }

  function slotItems(value){
    if(Array.isArray(value)){
      return value.filter(Boolean);
    }

    return value ? [value] : [];
  }

  function firstSlotItem(value){
    return slotItems(value)[0] || null;
  }

  function putSlotItem(item){
    const hora = (item.hora || '').slice(0, 5);
    const key = slotKey(item.fecha, hora);

    if(!slotData[key]){
      slotData[key] = [];
    }

    slotData[key].push({ ...item, hora });
  }

  function findSlotKeyByItemId(itemId){
    const id = Number(itemId || 0);
    if(!id) return null;

    return Object.keys(slotData).find(key => slotItems(slotData[key]).some(item => Number(item.id || 0) === id)) || null;
  }

  function findSlotItemById(key, itemId){
    const id = Number(itemId || 0);
    return slotItems(slotData[key]).find(item => Number(item.id || 0) === id) || null;
  }

  function isAllowedHour(dayIndex, hour){
    const date = fmtISODate(addDays(weekStart, dayIndex));
    return getAllowedHoursForDate(date).includes(hour);
  }

  function isPastSlot(dayIndex, hour){
    return false;
  }

  function calendarDayIndexFromDate(dateStr){
    if(!dateStr) return null;

    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    const day = date.getDay();

    if(day === 0) return 6;
    return day - 1;
  }

  function getAllowedHoursForDate(dateStr){
    const special = specialScheduleByDate[dateStr];
    if(special){
      return special.allowed || [];
    }

    const idx = calendarDayIndexFromDate(dateStr);
    return idx === null ? [] : (baseAllowedSchedule[idx] || []);
  }

  function getVisibleHoursForDate(dateStr){
    const special = specialScheduleByDate[dateStr];
    if(special){
      return special.visible || [];
    }

    const idx = calendarDayIndexFromDate(dateStr);
    return idx === null ? [] : (baseVisibleSchedule[idx] || []);
  }

  function getAllHoursForCurrentWeek(){
    const hours = [];

    for(let dayIndex = 0; dayIndex < 7; dayIndex++){
      const date = fmtISODate(addDays(weekStart, dayIndex));
      hours.push(...getVisibleHoursForDate(date));
    }

    return sortHours(hours);
  }

  function formatDayNameFromDate(dateStr){
    if(!dateStr) return '-';

    const [y, m, d] = dateStr.split('-').map(Number);
    const dt = new Date(y, m - 1, d);
    const names = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

    return `${names[dt.getDay()]} ${String(dt.getDate()).padStart(2, '0')}`;
  }

  function refreshModalLabels(){
    modalDayLabel.textContent = `Día: ${formatDayNameFromDate(formDate.value)}`;
    modalHourLabel.textContent = `Hora: ${formTime.value || '-'}`;
  }

  function getSelectedRedesSociales(){
    return [...redesSocialesGrid.querySelectorAll('input[name="redes_sociales_ids[]"]:checked')]
      .map(input => Number(input.value))
      .filter(Number.isInteger);
  }

  function setSelectedRedesSociales(values = []){
    const selected = new Set((values || []).map(value => Number(value)));

    redesSocialesGrid.querySelectorAll('input[name="redes_sociales_ids[]"]').forEach(input => {
      input.checked = selected.has(Number(input.value));
    });
  }

  function getSelectedPlataformas(){
    return [...plataformasGrid.querySelectorAll('input[name="mundial_plataformas_ids[]"]:checked')]
      .map(input => Number(input.value))
      .filter(Number.isInteger);
  }

  function setSelectedPlataformas(values = []){
    const selected = new Set((values || []).map(value => Number(value)));

    plataformasGrid.querySelectorAll('input[name="mundial_plataformas_ids[]"]').forEach(input => {
      input.checked = selected.has(Number(input.value));
    });
  }

  function syncPlataformasValidation(showError = false){
    const hasSelection = getSelectedPlataformas().length > 0;
    const shouldShowError = showError && !hasSelection;

    plataformasGrid.classList.toggle('is-invalid', shouldShowError);
    plataformasError.classList.toggle('d-none', !shouldShowError);

    return hasSelection;
  }

  function syncRedesSocialesValidation(showError = false){
    if(!redesSocialesGrid || redesSocialesGrid.querySelectorAll('input[name="redes_sociales_ids[]"]').length === 0){
      return true;
    }

    const hasSelection = getSelectedRedesSociales().length > 0;
    const shouldShowError = showError && !hasSelection;

    redesSocialesGrid.classList.toggle('is-invalid', shouldShowError);
    redesSocialesError.classList.toggle('d-none', !shouldShowError);

    return hasSelection;
  }

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
      const text = await res.text();
      let message = `Error ${res.status}`;

      try{
        const data = JSON.parse(text);

        if(data.message){
          message = data.message;
        } else if(data.error){
          message = data.error;
        } else if(data.errors && typeof data.errors === 'object'){
          const firstError = Object.values(data.errors).flat()[0];
          if(firstError){
            message = firstError;
          }
        }
      }catch{
        if(text){
          message = text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
        }
      }

      throw new Error(message || `Error ${res.status}`);
    }

    return res.json();
  }

  function getErrorMessage(error, fallback = 'Ocurrió un error inesperado.'){
    if(error?.message){
      return error.message;
    }

    return fallback;
  }

  function escapeHTML(value){
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildHeaderStatHTML(available, occupied){
    return `
      <div class="day-head-stats">
        <span class="mini-stat">
          <span class="mini-dot mini-dot-green"></span>${available}
        </span>
        <span class="mini-stat">
          <span class="mini-dot mini-dot-orange"></span>${occupied}
        </span>
      </div>
    `;
  }

  function renderHeaders(){
    for(let i = 0; i < 7; i++){
      const d = addDays(weekStart, i);
      const th = document.getElementById(`h${i}`);
      if(th){
        const todayClass = isTodayDate(d) ? ' day-head-today' : '';
        th.innerHTML = `
          <div class="day-head${todayClass}">
            <div class="day-head-title">${fmtHeader(d, dayNames[i])}</div>
            ${buildHeaderStatHTML(0, 0)}
          </div>
        `;
      }
    }

    rangeLabel.innerHTML = `<i class="bi bi-calendar-event me-2"></i>${fmtRangeLabel(weekStart)}`;
  }

  function updateHeaderCounters(){
    for(let dayIndex = 0; dayIndex < 7; dayIndex++){
      const currentDate = addDays(weekStart, dayIndex);
      const fecha = fmtISODate(currentDate);
      const allowedHours = getAllowedHoursForDate(fecha);

      let occupied = 0;
      let available = 0;
      let futureSlots = 0;

      allowedHours.forEach(hour => {
        if(isPastSlot(dayIndex, hour)){
          return;
        }

        futureSlots++;
        const key = slotKey(fecha, hour);
        const items = slotItems(slotData[key]);
        if(items.some(item => item && (item.titulo || item.copy || item.seccion))){
          occupied++;
        } else {
          available++;
        }
      });

      const th = document.getElementById(`h${dayIndex}`);
      if(th){
        const todayClass = isTodayDate(currentDate) ? ' day-head-today' : '';
        th.innerHTML = `
          <div class="day-head${todayClass}">
            <div class="day-head-title">${fmtHeader(currentDate, dayNames[dayIndex])}</div>
            ${buildHeaderStatHTML(available, occupied)}
          </div>
        `;
      }
    }
  }

  function getStatusClass(data){
    const status = (data?.estado || '').toUpperCase();
    const origen = (data?.origen || '').toLowerCase();

    if(origen === 'propuesta'){
      return 'slot-default-busy';
    }

    if(status === 'BORRADOR' && (origen === 'pauta' || origen === 'comercial')){
      return 'slot-carrusel';
    }

    if(origen === 'pauta'){
      if(status === 'APROBADO' || status === 'FINALIZADO'){
        return 'slot-programado';
      }

      if(status === 'PENDIENTE'){
        return 'slot-pendiente';
      }

      return 'slot-carrusel';
    }

    if(origen === 'pendiente'){
      return 'slot-pendiente';
    }

    if(origen === 'comercial'){
      return 'slot-programado';
    }

    if(status === 'PENDIENTE'){
      return 'slot-pendiente';
    }

    if(status === 'PROGRAMADO'){
      return 'slot-programado';
    }

    return 'slot-default-busy';
  }

  function clearDropStates(){
    document.querySelectorAll('.slot').forEach(td => {
      td.classList.remove(
        'slot-drop-target',
        'slot-drop-target-empty',
        'slot-drop-target-busy',
        'slot-drop-not-allowed',
        'slot-dragging'
      );
    });
  }

  function setDragOverlayVisible(visible){
    if(!plannerDragOverlay) return;
    plannerDragOverlay.classList.toggle('is-visible', visible);
  }

  function setSaveButtonState(isSaving){
    isSavingSlot = isSaving;
    saveBtn.disabled = isSaving;
    saveBtn.textContent = isSaving ? 'Guardando...' : 'Guardar';
  }

  function getSlotFromCell(td){
    if(!td) return null;

    return {
      key: td.dataset.key || '',
      dayIndex: Number(td.dataset.day),
      hour: td.dataset.hour,
      allowed: td.dataset.allowed === '1',
      fecha: td.dataset.key ? td.dataset.key.split('|')[0] : null,
      item: slotItems(slotData[td.dataset.key || ''])[0] || null,
      items: slotItems(slotData[td.dataset.key || '']),
      td
    };
  }

  function hasRenderableItem(item){
    return !!(item && (item.titulo || item.copy || item.seccion));
  }

  function isDraggableItem(item){
    if(!puedeDragDrop) return false;
    if(!item) return false;
    if(!hasRenderableItem(item)) return false;
    return !!item.id;
  }

  function canMoveItemToSlot(item, slot){
    if(!item || !slot) return false;
    if(slot.td?.dataset.past === '1') return false;

    const origen = String(item.origen || '').toLowerCase();
    return slot.allowed === true || origen === 'pauta';
  }

  function canDropOnTarget(source, target){
    if(!source || !source.item || !source.item.id) return false;
    if(!target || !target.key) return false;
    if(source.key === target.key) return false;

    if(!canMoveItemToSlot(source.item, target)) return false;

    if((target.items || []).length > 0) return false;

    return true;
  }

  function renderCellContent(td, data){
    const items = slotItems(data);
    td.innerHTML = '';
    td.draggable = false;
    td.ondragstart = null;
    td.ondragend = null;

    td.classList.remove(
      'busy',
      'slot-programado',
      'slot-pendiente',
      'slot-carrusel',
      'slot-default-busy',
      'slot-draggable',
      'slot-border-editorial',
      'slot-border-comercial',
      'slot-border-radio',
      'slot-drop-target',
      'slot-drop-target-empty',
      'slot-drop-target-busy',
      'slot-drop-not-allowed',
      'slot-dragging',
      'slot-out-of-schedule'
    );

    if(td.dataset.past === '1'){
      td.classList.add('slot-not-allowed');
      td.classList.remove('slot-out-of-schedule');
    } else if(td.dataset.allowed === '0'){
      td.classList.add('slot-out-of-schedule');
      td.classList.remove('slot-not-allowed');
    } else {
      td.classList.remove('slot-not-allowed');
      td.classList.remove('slot-out-of-schedule');
    }

    if(items.length === 0 || !items.some(item => item.titulo || item.copy || item.seccion)) return;

    td.title = items.map(data => [
      `Titulo: ${data.titulo || data.seccion || 'Contenido'}`,
      `Prioridad: ${data.mundial_prioridad_nombre || data.prioridad || 'Sin prioridad'}`,
      `Plataforma: ${data.mundial_plataforma_nombre || 'Sin plataforma'}`,
      `Equipo: ${data.mundial_equipo_nombre || data.seccion || 'Sin equipo'}`,
      `Tipo: ${data.mundial_tipo_nombre || 'Sin tipo'}`,
      `Líder: ${data.responsable_nombre || 'Sin líder'}`,
      `Responsable: ${data.responsable2_nombre || 'Sin responsable'}`,
      `Edición: ${data.edicion_nombre || 'Sin edición'}`,
      `Etapa: ${data.etapa || 'Borrador'}`,
      `Estado: ${data.estado || 'BORRADOR'}`,
    ].join('\n')).join('\n\n');

    const outer = document.createElement('div');
    outer.className = items.length > 1 ? 'slot-multi' : '';

    items.slice(0, 2).forEach(item => {
      const card = renderSlotItemContent(item, items.length > 1);
      outer.appendChild(card);
    });

    if(items.length > 2){
      const more = document.createElement('div');
      more.className = 'slot-more';
      more.textContent = `+${items.length - 2}`;
      outer.appendChild(more);
    }

    td.appendChild(outer);
    td.classList.add('busy', getStatusClass(items[0]), slotBorderClass(items[0]));

    if(items.length === 1 && isDraggableItem(items[0])){
      td.draggable = true;
      td.classList.add('slot-draggable');

      td.ondragstart = (e) => {
        dragSource = getSlotFromCell(td);
        isDragging = true;
        td.classList.add('slot-dragging');
        setDragOverlayVisible(true);
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragSource.key);
      };

      td.ondragend = () => {
        td.classList.remove('slot-dragging');
        clearDropStates();
        setTimeout(() => {
          isDragging = false;
          dragSource = null;
        }, 0);
      };
    }
  }

  function renderSlotItemContent(data, compact = false){
    const wrap = document.createElement('div');
    wrap.className = 'slot-wrap';
    if(compact){
      wrap.classList.add('slot-item', slotBorderClass(data));
    }

    const topLine = document.createElement('div');
    topLine.className = 'slot-topline';

    const title = document.createElement('div');
    title.className = 'slot-title';
    title.textContent = data.titulo || data.seccion || 'Contenido';

    const tipo = String(data.mundial_tipo_nombre || '').toLowerCase();
    const tipoClass = tipo === 'comercial'
      ? 'slot-type-comercial'
      : (tipo === 'radio' ? 'slot-type-radio' : 'slot-type-editorial');
    const badge = document.createElement('span');
    badge.className = `slot-origin-badge ${tipoClass}`;
    badge.textContent = data.mundial_tipo_nombre || 'Editorial';

    const meta = document.createElement('div');
    meta.className = 'slot-meta';
    meta.textContent = [
      data.estado || 'BORRADOR',
      data.mundial_equipo_nombre || data.seccion || 'Sin equipo',
      data.responsable2_nombre || 'Sin responsable'
    ].filter(Boolean).join(' • ');

    const socialFooter = document.createElement('div');
    socialFooter.className = 'slot-social-footer';

    const plataformaIds = Array.isArray(data.mundial_plataformas_ids)
      ? data.mundial_plataformas_ids
      : (data.mundial_plataforma_id ? [data.mundial_plataforma_id] : []);

    plataformaIds.slice(0, 5).forEach(plataformaId => {
      const plataforma = mundialPlataformasMap[String(plataformaId)] || mundialPlataformasMap[plataformaId];
      if(!plataforma) return;

      if(plataforma.icon){
        const icon = document.createElement('img');
        icon.src = plataforma.icon;
        icon.alt = plataforma.nombre || 'Plataforma';
        icon.title = plataforma.nombre || 'Plataforma';
        socialFooter.appendChild(icon);
        return;
      }

      const fallback = document.createElement('span');
      fallback.className = 'slot-platform-pill';
      fallback.textContent = plataforma.fallback || String(plataforma.nombre || '?').slice(0, 2).toUpperCase();
      fallback.title = plataforma.nombre || 'Plataforma';
      socialFooter.appendChild(fallback);
    });

    topLine.appendChild(title);
    topLine.appendChild(badge);
    wrap.appendChild(topLine);
    wrap.appendChild(meta);
    if(socialFooter.children.length){
      wrap.appendChild(socialFooter);
    }
    return wrap;
  }

  function slotBorderClass(data){
    const tipo = String(data?.mundial_tipo_nombre || '').toLowerCase();

    if(tipo === 'comercial'){
      return 'slot-border-comercial';
    }

    if(tipo === 'radio'){
      return 'slot-border-radio';
    }

    return 'slot-border-editorial';
  }

  function buildGrid(){
    tbody.innerHTML = '';

    getAllHoursForCurrentWeek().forEach(hour => {
      const tr = document.createElement('tr');

      const hourTd = document.createElement('td');
      hourTd.className = 'hour';
      hourTd.textContent = hour;
      tr.appendChild(hourTd);

      for(let dayIndex = 0; dayIndex < 7; dayIndex++){
        const td = document.createElement('td');
        const allowed = isAllowedHour(dayIndex, hour);
        const isPast = isPastSlot(dayIndex, hour);
        const currentDate = addDays(weekStart, dayIndex);

        td.dataset.hour = hour;
        td.dataset.day = String(dayIndex);
        td.dataset.allowed = allowed ? '1' : '0';
        td.dataset.past = isPast ? '1' : '0';
        td.className = 'slot';
        td.title = `${hour} | ${dayNames[dayIndex]}`;

        if(isTodayDate(currentDate)){
          td.classList.add('slot-today');
        }

        if(isPast){
          td.classList.add('slot-not-allowed');
        } else if(!allowed){
          td.classList.add('slot-out-of-schedule');
        }

        td.addEventListener('click', () => {
          if(isDragging) return;
          if(isPast) return;
          openModal(dayIndex, hour, allowed);
        });

        td.addEventListener('dragover', (e) => {
          if(!puedeDragDrop || !dragSource) return;

          const target = getSlotFromCell(td);
          if(!target || !target.key) return;

          e.preventDefault();
          td.classList.remove('slot-drop-target', 'slot-drop-target-empty', 'slot-drop-target-busy', 'slot-drop-not-allowed');

          if(!canDropOnTarget(dragSource, target)){
            td.classList.add('slot-drop-not-allowed');
            return;
          }

          td.classList.add('slot-drop-target');
          td.classList.add(target.item ? 'slot-drop-target-busy' : 'slot-drop-target-empty');
        });

        td.addEventListener('dragleave', () => {
          td.classList.remove('slot-drop-target', 'slot-drop-target-empty', 'slot-drop-target-busy', 'slot-drop-not-allowed');
        });

        td.addEventListener('drop', async (e) => {
          if(!puedeDragDrop) return;

          e.preventDefault();

          const target = getSlotFromCell(td);
          clearDropStates();

          if(!dragSource || !target || !target.key){
            dragSource = null;
            isDragging = false;
            setDragOverlayVisible(false);
            return;
          }

          if(!canDropOnTarget(dragSource, target)){
            dragSource = null;
            isDragging = false;
            setDragOverlayVisible(false);
            return;
          }

          try{
            await moveSlot(dragSource, target);
          }catch(error){
            console.error(error);
            await showError('No se pudo mover el slot.');
          }finally{
            dragSource = null;
            isDragging = false;
            clearDropStates();
            setDragOverlayVisible(false);
          }
        });

        tr.appendChild(td);
      }

      tbody.appendChild(tr);
    });
  }

  function applySearchFilter(){
    const q = (searchInput.value || '').toLowerCase().trim();

    document.querySelectorAll('.slot').forEach(td => {
      const items = slotItems(slotData[td.dataset.key || '']);

      if(!q){
        td.style.opacity = '';
        return;
      }

      const haystack = items.map(item => [
          item?.titulo,
          item?.descripcion,
          item?.responsable_nombre,
          item?.responsable2_nombre,
          item?.edicion_nombre,
          item?.etapa,
          item?.mundial_prioridad_nombre,
          item?.mundial_plataforma_nombre,
          item?.mundial_equipo_nombre,
          item?.mundial_tipo_nombre,
          item?.tipo_producto_nombre,
          item?.seccion,
          item?.copy,
          item?.hashtags,
          item?.creditos,
          item?.estado,
          item?.prioridad,
          item?.dificultad
        ].filter(Boolean).join(' ')
      ).join(' ').toLowerCase();

      td.style.opacity = haystack.includes(q) ? '' : '0.25';
    });
  }

  async function loadPeriodistas(){
    const users = await fetchJSON('/mundial/planificador/periodistas');

    const buildOptions = (select) => {
      const placeholder = select === formResponsable2
        ? '-- Seleccione responsable --'
        : '-- Seleccione líder --';

      select.innerHTML = `<option value="">${placeholder}</option>`;

      users.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = user.name;
        select.appendChild(option);
      });
    };

    buildOptions(formResponsable);
    buildOptions(formResponsable2);
  }

  async function loadVideografos(){
    const users = await fetchJSON('/mundial/planificador/videografos');

    formEdicion.innerHTML = '<option value="">-- Opcional --</option>';

    users.forEach(user => {
      const option = document.createElement('option');
      option.value = user.id;
      option.textContent = user.name;
      formEdicion.appendChild(option);
    });
  }

  async function loadWeek(){
    const start = fmtISODate(weekStart);
    const items = await fetchJSON(`/mundial/planificador/week?week_start=${encodeURIComponent(start)}`);
    lastWeekSignature = buildWeekSignature(items);

    Object.keys(slotData).forEach(key => delete slotData[key]);

    items.forEach(putSlotItem);

    document.querySelectorAll('.slot').forEach(td => {
      const dayIndex = Number(td.dataset.day);
      const hour = td.dataset.hour;
      const fecha = fmtISODate(addDays(weekStart, dayIndex));
      const key = slotKey(fecha, hour);

      td.dataset.key = key;
      renderCellContent(td, slotData[key]);
    });

    updateHeaderCounters();
    applySearchFilter();
  }

  async function syncWeekSilently(){
    if(isDragging || isModalOpen() || document.hidden){
      return;
    }

    const start = fmtISODate(weekStart);
    const items = await fetchJSON(`/mundial/planificador/week?week_start=${encodeURIComponent(start)}`);
    const nextSignature = buildWeekSignature(items);

    if(nextSignature === lastWeekSignature){
      return;
    }

    lastWeekSignature = nextSignature;

    Object.keys(slotData).forEach(key => delete slotData[key]);

    items.forEach(putSlotItem);

    document.querySelectorAll('.slot').forEach(td => {
      const dayIndex = Number(td.dataset.day);
      const hour = td.dataset.hour;
      const fecha = fmtISODate(addDays(weekStart, dayIndex));
      const key = slotKey(fecha, hour);

      td.dataset.key = key;
      renderCellContent(td, slotData[key]);
    });

    updateHeaderCounters();
    applySearchFilter();
  }

  function startAutoRefresh(){
    if(autoRefreshTimer){
      clearInterval(autoRefreshTimer);
    }

    autoRefreshTimer = setInterval(() => {
      syncWeekSilently().catch((error) => {
        console.error('planificador_auto_refresh', error);
      });
    }, AUTO_REFRESH_MS);
  }

  function setFormEditable(mode = 'full'){
    const fullEditable = mode === 'full';
    const pautaEditable = mode === 'pauta';

    formPrioridad.disabled = !fullEditable;
    plataformasGrid.querySelectorAll('input[name="mundial_plataformas_ids[]"]').forEach(input => {
      input.disabled = !fullEditable;
    });
    formEquipo.disabled = !fullEditable;
    formMundialTipo.disabled = !fullEditable;
    formTitle.readOnly = !fullEditable;
    formDesc.readOnly = !fullEditable;
    formAuspicio.readOnly = !fullEditable;
    formStatus.disabled = !fullEditable;
    formResponsable.disabled = !(fullEditable || pautaEditable);
    formResponsable2.disabled = !(fullEditable || pautaEditable);
    formEdicion.disabled = !(fullEditable || pautaEditable);
    formEtapa.disabled = !(fullEditable || pautaEditable);
    redesSocialesGrid.querySelectorAll('input[name="redes_sociales_ids[]"]').forEach(input => {
      input.disabled = !(fullEditable || pautaEditable);
    });
  }

  function showSuccess(message){
    return Swal.fire({
      icon: 'success',
      text: message,
      confirmButtonText: 'Aceptar',
    });
  }

  function showError(message){
    return Swal.fire({
      icon: 'error',
      text: message,
      confirmButtonText: 'Aceptar',
    });
  }

  function confirmAction(message, confirmText = 'Confirmar'){
    return Swal.fire({
      icon: 'question',
      text: message,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
    });
  }

  function syncStatusBySchedule(){
    const allowed = getAllowedHoursForDate(formDate.value).includes(formTime.value);
    formIsAllowed.value = allowed ? '1' : '0';
    modalOutOfScheduleLabel.classList.toggle('d-none', allowed);
  }

  function selectedTipoName(select){
    return select.options[select.selectedIndex]?.textContent?.trim() || '';
  }

  function isComercialTipo(select){
    return selectedTipoName(select).toLowerCase() === 'comercial';
  }

  function syncAuspicioVisibility(){
    const show = isComercialTipo(formMundialTipo);
    formAuspicioWrap.classList.toggle('d-none', !show);
    if(!show){
      formAuspicio.value = '';
    }
  }

  function toggleActionButtons(existing){
    const currentStatus = (formStatus.value || '').toUpperCase();
    const backendStatus = (existing.estado || '').toUpperCase();
    const isLockedStatus = backendStatus === 'APROBADO' || backendStatus === 'FINALIZADO';
    const canvaUrl = (existing.canva_url || '').trim();

    saveBtn.classList.toggle('d-none', isLockedStatus);

    if(canvaLinkBtn){
      canvaLinkBtn.href = canvaUrl || '#';
      canvaLinkBtn.classList.toggle('d-none', !(isLockedStatus && canvaUrl));
    }

    deleteSlotBtn.dataset.propuestaId = existing.id || '';
    deleteSlotBtn.dataset.key = existing.__key || '';
    deleteSlotBtn.classList.toggle('d-none', !existing.id || !existing.can_delete);

    if(approveBtn){
      const showApprove = puedeAprobar && !!existing.id && (backendStatus === 'PENDIENTE' || existing.origen === 'pendiente');
      approveBtn.dataset.propuestaId = existing.id || '';
      approveBtn.dataset.key = existing.__key || '';
      approveBtn.classList.toggle('d-none', !showApprove);
    }
  }

  function prepareNewProductSameSlot(){
    const currentDate = formDate.value;
    const currentTime = formTime.value;
    const allowed = formIsAllowed.value;

    formId.value = '';
    formDate.value = currentDate;
    formTime.value = currentTime;
    formIsAllowed.value = allowed;
    formStatus.value = 'BORRADOR';
    formPrioridad.value = formPrioridad.options[1]?.value || '';
    setSelectedPlataformas([]);
    formEquipo.value = formEquipo.options[1]?.value || '';
    formMundialTipo.value = formMundialTipo.options[1]?.value || '';
    formTitle.value = '';
    formDesc.value = '';
    formAuspicio.value = '';
    formTipoProducto.value = formTipoProducto.value || '';
    formResponsable.value = '';
    formResponsable2.value = '';
    formEdicion.value = '';
    formEtapa.value = 'Borrador';
    setSelectedRedesSociales([]);
    syncPlataformasValidation(false);
    syncRedesSocialesValidation(false);
    syncAuspicioVisibility();
    form.classList.remove('was-validated');

    modalTitle.textContent = 'Crear producto';
    setFormEditable('full');
    deleteSlotBtn.classList.add('d-none');
    if(approveBtn){
      approveBtn.classList.add('d-none');
    }
    if(canvaLinkBtn){
      canvaLinkBtn.classList.add('d-none');
      canvaLinkBtn.href = '#';
    }
    saveBtn.classList.remove('d-none');
    refreshModalLabels();
    syncStatusBySchedule();
  }

  function fillModalForm(existing, fecha, hourValue, allowed, key){
    formId.value = existing.id || '';
    formDate.value = existing.fecha || fecha;
    formTime.value = (existing.hora || hourValue).slice(0, 5);
    formPrioridad.value = existing.mundial_prioridad_id || formPrioridad.options[1]?.value || '';
    setSelectedPlataformas(existing.mundial_plataformas_ids || (existing.mundial_plataforma_id ? [existing.mundial_plataforma_id] : []));
    formEquipo.value = existing.mundial_equipo_id || formEquipo.options[1]?.value || '';
    formMundialTipo.value = existing.mundial_tipo_id || formMundialTipo.options[1]?.value || '';
    formTitle.value = existing.titulo || '';
    formTipoProducto.value = existing.tipo_producto_id || formTipoProducto.value || '';

    formDesc.value = existing.descripcion || '';
    formAuspicio.value = existing.auspicio || existing.creditos || '';
    syncAuspicioVisibility();

    if(existing.estado){
      formStatus.value = existing.estado;
    } else {
      formStatus.value = 'BORRADOR';
    }

    formResponsable.value = existing.asignado_a || '';
    formResponsable2.value = existing.responsable2_id || '';
    formEdicion.value = existing.edicion_id || '';
    setSelectedRedesSociales(existing.redes_sociales_ids || []);
    formEtapa.value = existing.etapa || existing.link || 'Borrador';
    formIsAllowed.value = allowed ? '1' : '0';

    form.classList.remove('was-validated');
    refreshModalLabels();
    syncStatusBySchedule();

    modalTitle.textContent = existing.id ? 'Editar producto' : 'Crear producto';
    setFormEditable(existing.origen === 'pauta' ? 'pauta' : 'full');

    toggleActionButtons({ ...existing, __key: key });
    slotModal.show();
  }

  async function chooseSlotItem(items, fecha, hourValue){
    const options = items.map(item => {
      const tipo = item.mundial_tipo_nombre || 'Tipo';
      const title = item.titulo || item.seccion || `Producto #${item.id}`;
      return `<option value="${Number(item.id)}">${escapeHTML(tipo)} · ${escapeHTML(title)}</option>`;
    }).join('');

    const result = await Swal.fire({
      title: 'Productos en este horario',
      html: `<select class="form-select" id="slotItemPicker">${options}</select>`,
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonText: 'Editar',
      denyButtonText: '+ Nuevo',
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      preConfirm: () => document.getElementById('slotItemPicker')?.value,
    });

    if(result.isDenied){
      return { action: 'new' };
    }

    if(result.isConfirmed && result.value){
      return { action: 'edit', itemId: Number(result.value) };
    }

    return { action: 'cancel' };
  }

  async function openModal(dayIndex, hourValue, allowed = true){
    if(isPastSlot(dayIndex, hourValue)){
      return;
    }

    const fecha = fmtISODate(addDays(weekStart, dayIndex));
    const key = slotKey(fecha, hourValue);
    const items = slotItems(slotData[key]);

    if(items.length > 1){
      const choice = await chooseSlotItem(items, fecha, hourValue);

      if(choice.action === 'cancel'){
        return;
      }

      if(choice.action === 'new'){
        fillModalForm({}, fecha, hourValue, allowed, key);
        return;
      }

      const selected = findSlotItemById(key, choice.itemId) || {};
      fillModalForm(selected, fecha, hourValue, allowed, key);
      return;
    }

    fillModalForm(items[0] || {}, fecha, hourValue, allowed, key);
  }

  async function moveSlot(source, target){
    if(!source?.item || !source.item.id) return;
    if(source.key === target.key) return;

    if(!canDropOnTarget(source, target)){
      return;
    }

    const res = await fetchJSON('/mundial/planificador/move', {
      method: 'POST',
      body: JSON.stringify({
        source_key: source.key,
        target_key: target.key
      })
    });

    if(!res.ok){
      await showError(res.message || 'No se pudo mover.');
      return;
    }

    await loadWeek();
  }

  async function saveCurrentForm(){
    const hasPlataformas = syncPlataformasValidation(true);
    const hasRedesSociales = syncRedesSocialesValidation(true);

    if(!hasPlataformas || !hasRedesSociales){
      return { ok: false, validation: true };
    }

    if(!form.checkValidity()){
      form.classList.add('was-validated');
      return { ok: false, validation: true };
    }

    const payload = {
      id: formId.value ? Number(formId.value) : null,
      fecha: formDate.value,
      hora: formTime.value,
      mundial_prioridad_id: formPrioridad.value ? Number(formPrioridad.value) : null,
      mundial_plataformas_ids: getSelectedPlataformas(),
      mundial_equipo_id: formEquipo.value ? Number(formEquipo.value) : null,
      mundial_tipo_id: formMundialTipo.value ? Number(formMundialTipo.value) : null,
      titulo: formTitle.value.trim(),
      descripcion: formDesc.value.trim(),
      auspicio: isComercialTipo(formMundialTipo) ? formAuspicio.value.trim() : null,
      estado: formStatus.value,
      tipo_producto_id: formTipoProducto.value ? Number(formTipoProducto.value) : null,
      redes_sociales_ids: getSelectedRedesSociales(),
      asignado_a: formResponsable.value ? Number(formResponsable.value) : null,
      responsable2_id: formResponsable2.value ? Number(formResponsable2.value) : null,
      edicion_id: formEdicion.value ? Number(formEdicion.value) : null,
      etapa: formEtapa.value || 'Borrador',
    };

    const oldKey = findSlotKeyByItemId(payload.id);

    const res = await fetchJSON('/mundial/planificador/store', {
      method: 'POST',
      body: JSON.stringify(payload)
    });

    if(!res.ok){
      return res;
    }

    const item = { ...res.item, hora: (res.item.hora || '').slice(0, 5) };

    if(oldKey && oldKey !== slotKey(item.fecha, item.hora)){
      slotData[oldKey] = slotItems(slotData[oldKey]).filter(existingItem => Number(existingItem.id || 0) !== Number(item.id || 0));
      if(slotData[oldKey].length === 0){
        delete slotData[oldKey];
      }
    }

    const selectedDate = new Date(item.fecha + 'T00:00:00');
      const newWeekStart = startOfWeekMonday(selectedDate);

      if(fmtISODate(newWeekStart) !== fmtISODate(weekStart)){
        weekStart = newWeekStart;
        renderHeaders();
        buildGrid();
      }

    await loadWeek();

    const newKey = slotKey(item.fecha, item.hora);
    formId.value = item.id || '';
    formStatus.value = item.estado || payload.estado;

    deleteSlotBtn.dataset.propuestaId = item.id || '';
    deleteSlotBtn.dataset.key = newKey;

    if(approveBtn){
      approveBtn.dataset.propuestaId = item.id || '';
      approveBtn.dataset.key = newKey;
    }

    return {
      ok: true,
      item,
      replicadas: res.replicadas || [],
      replica_conflictos: res.replica_conflictos || [],
    };
  }

  saveBtn.addEventListener('click', async () => {
    if(isSavingSlot){
      return;
    }

    setSaveButtonState(true);

    try{
      const res = await saveCurrentForm();
      if(res.ok){
        if((res.replicadas || []).length || (res.replica_conflictos || []).length){
          const mensajes = [];

          if((res.replicadas || []).length){
            mensajes.push(`Creadas también en: ${res.replicadas.join(', ')}`);
          }

          if((res.replica_conflictos || []).length){
            mensajes.push(`No creadas por horario ocupado: ${res.replica_conflictos.join(', ')}`);
          }

          await Swal.fire({
            icon: (res.replica_conflictos || []).length ? 'warning' : 'success',
            html: mensajes.join('<br>'),
            confirmButtonText: 'Aceptar',
          });
        }

        slotModal.hide();
      } else {
        setSaveButtonState(false);
      }
    }catch(error){
      console.error(error);
      await showError(getErrorMessage(error, 'No se pudo guardar el producto.'));
      setSaveButtonState(false);
    }finally{
      if(!isModalOpen()){
        setSaveButtonState(false);
      }
    }
  });

  slotModalEl?.addEventListener('hidden.bs.modal', () => {
    setSaveButtonState(false);
  });

  newSameSlotBtn?.addEventListener('click', prepareNewProductSameSlot);

  if(approveBtn){
    approveBtn.addEventListener('click', async () => {
      const propuestaId = Number(approveBtn.dataset.propuestaId || 0);

      if(!propuestaId){
        return;
      }

      const confirmation = await confirmAction('¿Aprobar este producto?', 'Aprobar');
      if(!confirmation.isConfirmed){
        return;
      }

      try{
        const res = await fetchJSON('/mundial/planificador/aprobar', {
          method: 'POST',
          body: JSON.stringify({ id: propuestaId })
        });

        if(res.ok){
          await loadWeek();
          slotModal.hide();
          await showSuccess('Producto aprobado correctamente.');
        }else{
          await showError(res.message || 'No se pudo aprobar.');
        }
      }catch(error){
        console.error(error);
        await showError(getErrorMessage(error, 'No se pudo aprobar el producto.'));
      }
    });
  }

  deleteSlotBtn?.addEventListener('click', async () => {
    const propuestaId = Number(deleteSlotBtn.dataset.propuestaId || 0);
    const key = deleteSlotBtn.dataset.key || '';

    if(!propuestaId){
      return;
    }

    const confirmation = await confirmAction('¿Seguro que deseas eliminar este producto?', 'Eliminar');
    if(!confirmation.isConfirmed){
      return;
    }

    try{
      const res = await fetchJSON(`/mundial/planificador/${propuestaId}`, {
        method: 'DELETE'
      });

      if(!res.ok){
        await showError(res.message || 'No se pudo eliminar.');
        return;
      }

      slotData[key] = slotItems(slotData[key]).filter(item => Number(item.id || 0) !== propuestaId);
      if(slotData[key].length === 0){
        delete slotData[key];
      }

      const td = document.querySelector(`.slot[data-key="${key}"]`);
      if(td){
        renderCellContent(td, slotData[key] || null);
      }

      updateHeaderCounters();

      deleteSlotBtn.classList.add('d-none');
      if(approveBtn){
        approveBtn.classList.add('d-none');
      }

      applySearchFilter();
      slotModal.hide();
      await showSuccess('Producto eliminado correctamente.');
    }catch(error){
      console.error(error);
      await showError(getErrorMessage(error, 'No se pudo eliminar el producto.'));
    }
  });

  btnPrev.addEventListener('click', async () => {
    weekStart = addDays(weekStart, -7);
    renderHeaders();
    buildGrid();
    await loadWeek();
  });

  btnNext.addEventListener('click', async () => {
    weekStart = addDays(weekStart, 7);
    renderHeaders();
    buildGrid();
    await loadWeek();
  });

  btnToday.addEventListener('click', async () => {
    weekStart = startOfWeekMonday(new Date());
    renderHeaders();
    buildGrid();
    await loadWeek();
  });

  searchInput.addEventListener('input', applySearchFilter);
  plataformasGrid?.addEventListener('change', () => {
    if(plataformasGrid.classList.contains('is-invalid')){
      syncPlataformasValidation(true);
    }
  });
  redesSocialesGrid?.addEventListener('change', () => {
    if(redesSocialesGrid.classList.contains('is-invalid')){
      syncRedesSocialesValidation(true);
    }
  });
  formMundialTipo?.addEventListener('change', syncAuspicioVisibility);

  (async function init(){
    renderHeaders();
    buildGrid();
    await loadPeriodistas();
    await loadVideografos();
    await loadWeek();
    startAutoRefresh();
  })();
</script>
@endpush
