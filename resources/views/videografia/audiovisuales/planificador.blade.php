@extends('layouts.app')

@section('title','Planificacion Audiovisual')

@push('styles')
<style>
  :root {
    --border: #e5e7eb;
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
    box-shadow: 1px 0 0 0 var(--border);
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

  .drag-hint{
    font-size: 12px;
    color: #6b7280;
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

  #slotModal .modal-dialog{
    max-width: 760px;
  }

  #slotModal .modal-header,
  #slotModal .modal-footer{
    padding: .7rem .9rem;
  }

  #slotModal .modal-body{
    padding: .85rem .9rem;
  }

  #slotModal .modal-title{
    font-size: 1rem;
  }

  #slotModal .badge{
    font-size: .72rem;
    padding: .38rem .5rem;
  }

  #slotModal .row.g-3{
    --bs-gutter-x: .75rem;
    --bs-gutter-y: .6rem;
  }

  #slotModal .form-label{
    margin-bottom: .25rem;
    font-size: .83rem;
    font-weight: 600;
  }

  #slotModal .form-control,
  #slotModal .form-select{
    min-height: 34px;
    height: 34px;
    padding: .28rem .65rem;
    font-size: .9rem;
    border-radius: .35rem;
  }

  #slotModal textarea.form-control{
    min-height: 78px;
    height: auto;
    padding-top: .45rem;
    padding-bottom: .45rem;
    line-height: 1.25;
  }

  #slotModal .invalid-feedback{
    font-size: .74rem;
  }

  #slotModal .btn{
    padding: .38rem .75rem;
    font-size: .88rem;
  }

  .social-grid{
    display:grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap:.12rem;
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
    min-height:32px;
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
    width:20px;
    height:20px;
    object-fit:contain;
    display:block;
    filter: drop-shadow(0 1px 1px rgba(15,23,42,.18));
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
</style>
@endpush

@section('content')

@php
  $puedeAprobar = auth()->user() && auth()->user()->hasAnyRole(['editor', 'director']);
  $puedeDragDrop = auth()->user() && auth()->user()->hasAnyRole(['editor', 'director']);
@endphp

<div class="propuestas-wrap">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="page-title m-0">Planificación Audiovisual</h3>
  </div>

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
    <span><span class="legend-dot" style="background:#dcfce7;border:1px solid #15803d;"></span>Aprobado / Finalizado</span>
    <span><span class="legend-dot" style="background:#ffedd5;border:1px solid #fb923c;"></span>Pendiente</span>
    <span><span class="legend-dot" style="background:#fef3c7;border:1px solid #f59e0b;"></span>En Proceso</span>
    <span><span class="legend-dot" style="background:#dbeafe;border:1px solid #93c5fd;"></span>Horario fuera de pauta editable</span>
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

  <div class="mt-3 text-muted" style="font-size: 13px;">
    Tip: haz clic en una celda para agregar o editar. Los audiovisuales nuevos se guardan por defecto en estado <strong>BORRADOR</strong>.
  </div>
</div>

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
        </div>

        <form id="slotForm" class="needs-validation" novalidate autocomplete="off">
          <input type="hidden" id="formId" name="id" />
          <input type="hidden" id="formIsAllowed" value="1" />
          <input type="hidden" id="formDate" name="fecha" />
          <input type="hidden" id="formTime" name="hora" />
          <input type="hidden" id="formStatus" name="estado" value="BORRADOR" />

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Sección</label>
              <select class="form-select" id="formSeccion" name="seccion" required>
                <option value="">-- Seleccione sección --</option>
                @foreach($secciones as $seccion)
                  <option value="{{ $seccion }}">{{ $seccion }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Selecciona una sección.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Origen</label>
              <select class="form-select" id="formOrigen" name="origen">
                <option value="pauta">Pauta</option>
                <option value="comercial">Comercial</option>
                <option value="pendiente">Pendiente</option>
                <option value="propuesta">Propuesta</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tipo de audiovisual</label>
              <select class="form-select" id="formTipoAudiovisual" name="tipo_audiovisual_id" required>
                <option value="">-- Seleccione tipo --</option>
                @foreach($tiposAudiovisuales as $tipoAudiovisual)
                  <option value="{{ $tipoAudiovisual->id }}">{{ $tipoAudiovisual->nombre }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Selecciona un tipo de audiovisual.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Redes sociales</label>
              <div class="social-grid" id="redesSocialesGrid">
                @foreach($redesSociales as $redSocial)
                  <label class="social-check">
                    <input
                      type="checkbox"
                      class="form-check-input d-none"
                      name="redes_sociales_ids[]"
                      value="{{ $redSocial->id }}"
                    >
                    <span class="social-card">
                      <img
                        src="{{ asset('images/redes-sociales/'.$redSocial->slug.'.svg') }}"
                        alt="{{ $redSocial->nombre }}"
                        loading="lazy"
                      >
                    </span>
                  </label>
                @endforeach
              </div>
              <div class="invalid-feedback d-block d-none" id="redesSocialesError">Selecciona al menos una red social.</div>
            </div>

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
              <label class="form-label">Responsable</label>
              <select class="form-select" id="formResponsable" name="asignado_a">
                <option value="">-- Seleccione periodista --</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Responsable 2</label>
              <select class="form-select" id="formResponsable2" name="responsable2_id">
                <option value="">-- Opcional --</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Publicar también en</label>
              <select class="form-select" id="formPublicarTambienEn" name="publicar_tambien_en">
                <option value="">-- Opcional --</option>
                @foreach($empresasPublicacion as $empresaPublicacion)
                  <option value="{{ $empresaPublicacion->id }}">{{ $empresaPublicacion->nombre }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Link/Referencia</label>
              <input type="url" class="form-control" id="formLink" name="link" placeholder="https://..." />
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

        <button type="button" class="btn btn-primary d-none" id="toCarruselBtn">
          <i class="bi bi-arrow-repeat me-1"></i> Cambiar a pauta
        </button>

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
  const plannerBaseUrl = '/videografia/planificacion';
  const puedeAprobar = @json($puedeAprobar);
  const puedeDragDrop = @json($puedeDragDrop);

  const dayNames = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

  const weekdaysHours = [
    '06:00','07:00','08:15','09:30','10:45','11:30','12:15','12:30','12:45','13:00','13:15','13:30',
    '13:45','14:00','14:15','14:30','14:45','15:30','16:00','17:15','18:30','19:45','20:15','21:00',
    '22:15','22:45'
  ];

  const saturdayHours = [
    '09:00','10:30','11:30','12:00','13:30','15:00',
    '15:30','16:30','18:00','19:30','20:30','22:00'
  ];

  const sundayHours = [
    '09:30','10:45','12:00','13:30','15:00',
    '16:30','18:00','19:30','21:00','22:00'
  ];

  const scheduleByDay = {
    0: weekdaysHours,
    1: weekdaysHours,
    2: weekdaysHours,
    3: weekdaysHours,
    4: weekdaysHours,
    5: saturdayHours,
    6: sundayHours
  };

  const allHours = [...new Set(Object.values(scheduleByDay).flat())].sort((a, b) => {
    const [ah, am] = a.split(':').map(Number);
    const [bh, bm] = b.split(':').map(Number);
    return (ah * 60 + am) - (bh * 60 + bm);
  });

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
  const formSeccion = document.getElementById('formSeccion');
  const formTitle = document.getElementById('formTitle');
  const formDesc = document.getElementById('formDesc');
  const formStatus = document.getElementById('formStatus');
  const formOrigen = document.getElementById('formOrigen');
  const formTipoAudiovisual = document.getElementById('formTipoAudiovisual');
  const formResponsable = document.getElementById('formResponsable');
  const formResponsable2 = document.getElementById('formResponsable2');
  const formPublicarTambienEn = document.getElementById('formPublicarTambienEn');
  const formLink = document.getElementById('formLink');
  const formIsAllowed = document.getElementById('formIsAllowed');
  const redesSocialesGrid = document.getElementById('redesSocialesGrid');
  const redesSocialesError = document.getElementById('redesSocialesError');

  const saveBtn = document.getElementById('saveSlotBtn');
  const approveBtn = document.getElementById('approveSlotBtn');
  const toCarruselBtn = document.getElementById('toCarruselBtn');
  const deleteSlotBtn = document.getElementById('deleteSlotBtn');
  const canvaLinkBtn = document.getElementById('canvaLinkBtn');

  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const btnToday = document.getElementById('btnToday');
  const rangeLabel = document.getElementById('rangeLabel');
  const searchInput = document.getElementById('searchInput');

  let weekStart = startOfWeekMonday(new Date());
  const slotData = {};
  let dragSource = null;
  let isDragging = false;

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

  function slotKey(fecha, hora){
    return `${fecha}|${hora}`;
  }

  function isAllowedHour(dayIndex, hour){
    return (scheduleByDay[dayIndex] || []).includes(hour);
  }

  function isPastSlot(dayIndex, hour){
    const fecha = fmtISODate(addDays(weekStart, dayIndex));
    const slotDate = new Date(`${fecha}T${hour}:00`);

    return slotDate.getTime() < Date.now();
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
    const idx = calendarDayIndexFromDate(dateStr);
    return idx === null ? [] : (scheduleByDay[idx] || []);
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

  function syncRedesSocialesValidation(showError = false){
    const hasSelection = getSelectedRedesSociales().length > 0;
    const shouldShowError = showError && !hasSelection;

    redesSocialesGrid.classList.toggle('is-invalid', shouldShowError);
    redesSocialesError.classList.toggle('d-none', !shouldShowError);

    return hasSelection;
  }

  function getSelectedEmpresasPublicacion(){
    const empresaId = Number(formPublicarTambienEn.value);
    return Number.isInteger(empresaId) && empresaId > 0 ? [empresaId] : [];
  }

  function resetEmpresasPublicacion(){
    formPublicarTambienEn.value = '';
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
        th.innerHTML = `
          <div class="day-head">
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
      const fecha = fmtISODate(addDays(weekStart, dayIndex));
      const allowedHours = scheduleByDay[dayIndex] || [];

      let occupied = 0;
      let available = 0;

      allowedHours.forEach(hour => {
        const key = slotKey(fecha, hour);
        const item = slotData[key];
        if(item && (item.titulo || item.copy || item.seccion)){
          occupied++;
        } else {
          available++;
        }
      });

      const th = document.getElementById(`h${dayIndex}`);
      if(th){
        th.innerHTML = `
          <div class="day-head">
            <div class="day-head-title">${fmtHeader(addDays(weekStart, dayIndex), dayNames[dayIndex])}</div>
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

  function getSlotFromCell(td){
    if(!td) return null;

    return {
      key: td.dataset.key || '',
      dayIndex: Number(td.dataset.day),
      hour: td.dataset.hour,
      allowed: td.dataset.allowed === '1',
      fecha: td.dataset.key ? td.dataset.key.split('|')[0] : null,
      item: slotData[td.dataset.key || ''] || null,
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

  function canDropOnTarget(source, target){
    if(!source || !source.item || !source.item.id) return false;
    if(!target || !target.key) return false;
    if(source.key === target.key) return false;
    if(target.allowed !== true) return false;
    if(target.td?.dataset.past === '1') return false;
    return true;
  }

  function renderCellContent(td, data){
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

    if(!data || !(data.titulo || data.copy || data.seccion)) return;

    const wrap = document.createElement('div');
    wrap.style.padding = '6px 8px';
    wrap.style.textAlign = 'left';
    wrap.style.lineHeight = '1.15';

    const title = document.createElement('div');
    title.style.fontWeight = '700';
    title.style.fontSize = '12px';
    title.textContent = data.titulo || data.seccion || 'Contenido';

    const meta = document.createElement('div');
    meta.style.fontSize = '11px';
    meta.style.opacity = '0.90';

    meta.textContent = [
      data.estado || 'BORRADOR',
      data.origen || '',
      data.responsable_nombre || 'Sin responsable'
    ].filter(Boolean).join(' • ');

    wrap.appendChild(title);
    wrap.appendChild(meta);
    td.appendChild(wrap);
    td.classList.add('busy', getStatusClass(data));

    if(isDraggableItem(data)){
      td.draggable = true;
      td.classList.add('slot-draggable');

      td.ondragstart = (e) => {
        dragSource = getSlotFromCell(td);
        isDragging = true;
        td.classList.add('slot-dragging');
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

  function buildGrid(){
    tbody.innerHTML = '';

    allHours.forEach(hour => {
      const tr = document.createElement('tr');

      const hourTd = document.createElement('td');
      hourTd.className = 'hour';
      hourTd.textContent = hour;
      tr.appendChild(hourTd);

      for(let dayIndex = 0; dayIndex < 7; dayIndex++){
        const td = document.createElement('td');
        const allowed = isAllowedHour(dayIndex, hour);
        const isPast = isPastSlot(dayIndex, hour);

        td.dataset.hour = hour;
        td.dataset.day = String(dayIndex);
        td.dataset.allowed = allowed ? '1' : '0';
        td.dataset.past = isPast ? '1' : '0';
        td.className = 'slot';
        td.title = `${hour} | ${dayNames[dayIndex]}`;

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
            return;
          }

          if(!canDropOnTarget(dragSource, target)){
            dragSource = null;
            isDragging = false;
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
      const item = slotData[td.dataset.key || ''];

      if(!q){
        td.style.opacity = '';
        return;
      }

      const haystack = [
        item?.titulo,
        item?.descripcion,
        item?.responsable_nombre,
        item?.responsable2_nombre,
        item?.tipo_audiovisual_nombre,
        item?.seccion,
        item?.copy,
        item?.hashtags,
        item?.creditos,
        item?.estado,
        item?.prioridad,
        item?.dificultad
      ].filter(Boolean).join(' ').toLowerCase();

      td.style.opacity = haystack.includes(q) ? '' : '0.25';
    });
  }

  async function loadPeriodistas(){
    const users = await fetchJSON(`${plannerBaseUrl}/responsables`);

    const buildOptions = (select) => {
      const placeholder = select === formResponsable2
        ? '-- Opcional --'
        : '-- Seleccione periodista --';

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

  async function loadWeek(){
    Object.keys(slotData).forEach(key => delete slotData[key]);

    const start = fmtISODate(weekStart);
    const items = await fetchJSON(`${plannerBaseUrl}/week?week_start=${encodeURIComponent(start)}`);

    items.forEach(item => {
      const hora = (item.hora || '').slice(0, 5);
      slotData[slotKey(item.fecha, hora)] = { ...item, hora };
    });

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

  function setFormEditable(mode = 'full'){
    const fullEditable = mode === 'full';
    const pautaEditable = mode === 'pauta';

    formSeccion.disabled = !fullEditable;
    formTitle.readOnly = !fullEditable;
    formDesc.readOnly = !fullEditable;
    formStatus.disabled = !fullEditable;
    formOrigen.disabled = !fullEditable;
    formTipoAudiovisual.disabled = !(fullEditable || pautaEditable);
    formResponsable.disabled = !(fullEditable || pautaEditable);
    formResponsable2.disabled = !(fullEditable || pautaEditable);
    formPublicarTambienEn.disabled = !fullEditable || !!formId.value;
    formLink.readOnly = !(fullEditable || pautaEditable);
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

  function setOriginOptions(options, selectedValue, disabled = false){
    formOrigen.innerHTML = '';

    options.forEach(optionValue => {
      const option = document.createElement('option');
      option.value = optionValue;
      option.textContent = optionValue.charAt(0).toUpperCase() + optionValue.slice(1);
      formOrigen.appendChild(option);
    });

    formOrigen.value = selectedValue;
    formOrigen.disabled = disabled;
  }

  function syncStatusBySchedule(){
    const allowed = getAllowedHoursForDate(formDate.value).includes(formTime.value);
    formIsAllowed.value = allowed ? '1' : '0';
    modalOutOfScheduleLabel.classList.toggle('d-none', allowed);
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

    toCarruselBtn.dataset.propuestaId = existing.id || '';
    toCarruselBtn.dataset.key = existing.__key || '';
    toCarruselBtn.classList.toggle('d-none', !existing.id || existing.origen === 'pauta' || existing.origen === 'pendiente' || currentStatus === 'PENDIENTE');

    if(approveBtn){
      const showApprove = puedeAprobar && !!existing.id && (backendStatus === 'PENDIENTE' || existing.origen === 'pendiente');
      approveBtn.dataset.propuestaId = existing.id || '';
      approveBtn.dataset.key = existing.__key || '';
      approveBtn.classList.toggle('d-none', !showApprove);
    }
  }

  function openModal(dayIndex, hourValue, allowed = true){
    if(isPastSlot(dayIndex, hourValue)){
      return;
    }

    const fecha = fmtISODate(addDays(weekStart, dayIndex));
    const key = slotKey(fecha, hourValue);
    const existing = slotData[key] || {};

    formId.value = existing.id || '';
    formDate.value = existing.fecha || fecha;
    formTime.value = (existing.hora || hourValue).slice(0, 5);
    formSeccion.value = existing.seccion || '';
    formTitle.value = existing.titulo || '';
    formOrigen.value = existing.origen || 'propuesta';
    formTipoAudiovisual.value = existing.tipo_audiovisual_id || '';

    formDesc.value = existing.descripcion || '';

    if(existing.estado){
      formStatus.value = existing.estado;
    } else {
      formStatus.value = 'BORRADOR';
    }

    formResponsable.value = existing.asignado_a || '';
    formResponsable2.value = existing.responsable2_id || '';
    resetEmpresasPublicacion();
    setSelectedRedesSociales(existing.redes_sociales_ids || []);
    formLink.value = existing.link || '';
    formIsAllowed.value = allowed ? '1' : '0';

    form.classList.remove('was-validated');
    refreshModalLabels();
    syncStatusBySchedule();

    if(existing.id){
      if(existing.origen === 'pauta'){
        setOriginOptions(['pauta'], 'pauta', true);
      } else if(existing.origen === 'propuesta'){
        setOriginOptions(['propuesta', 'comercial', 'pendiente'], 'propuesta', false);
      } else if(allowed){
        setOriginOptions(['propuesta', 'comercial'], existing.origen === 'pendiente' ? 'propuesta' : (existing.origen || 'propuesta'), false);
      } else {
        setOriginOptions(['pendiente'], 'pendiente', true);
      }
    } else if(allowed) {
      setOriginOptions(['propuesta', 'comercial'], 'propuesta', false);
    } else {
      setOriginOptions(['pendiente'], 'pendiente', true);
    }

    modalTitle.textContent = existing.id ? 'Editar audiovisual' : 'Crear audiovisual';
    setFormEditable(existing.origen === 'pauta' ? 'pauta' : 'full');

    toggleActionButtons({ ...existing, __key: key });
    slotModal.show();
  }

  async function moveSlot(source, target){
    if(!source?.item || !source.item.id) return;
    if(source.key === target.key) return;

    if(target.allowed !== true){
      return;
    }

    const res = await fetchJSON(`${plannerBaseUrl}/move`, {
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
    const hasRedesSociales = syncRedesSocialesValidation(true);

    if(!hasRedesSociales){
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
      seccion: formSeccion.value,
      titulo: formTitle.value.trim(),
      descripcion: formDesc.value.trim(),
      estado: formStatus.value,
      origen: formIsAllowed.value === '1' ? (formOrigen.value || 'propuesta') : 'pendiente',
      tipo_audiovisual_id: formTipoAudiovisual.value ? Number(formTipoAudiovisual.value) : null,
      asignado_a: formResponsable.value ? Number(formResponsable.value) : null,
      responsable2_id: formResponsable2.value ? Number(formResponsable2.value) : null,
      redes_sociales_ids: getSelectedRedesSociales(),
      publicar_tambien_en: formId.value ? [] : getSelectedEmpresasPublicacion(),
      link: formLink.value.trim() || null,
    };

    const oldKey = Object.keys(slotData).find(key => Number(slotData[key]?.id) === Number(payload.id));

    const res = await fetchJSON(`${plannerBaseUrl}/store`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });

    if(!res.ok){
      return res;
    }

    const item = { ...res.item, hora: (res.item.hora || '').slice(0, 5) };

    if(oldKey && oldKey !== slotKey(item.fecha, item.hora)){
      delete slotData[oldKey];
    }

    const selectedDate = new Date(item.fecha + 'T00:00:00');
    const newWeekStart = startOfWeekMonday(selectedDate);

    if(fmtISODate(newWeekStart) !== fmtISODate(weekStart)){
      weekStart = newWeekStart;
      renderHeaders();
    }

    await loadWeek();

    const newKey = slotKey(item.fecha, item.hora);
    formId.value = item.id || '';
    formStatus.value = item.estado || payload.estado;
    formOrigen.value = item.origen || payload.origen;

    deleteSlotBtn.dataset.propuestaId = item.id || '';
    deleteSlotBtn.dataset.key = newKey;

    toCarruselBtn.dataset.propuestaId = item.id || '';
    toCarruselBtn.dataset.key = newKey;

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
      }
    }catch(error){
      console.error(error);
      await showError(getErrorMessage(error, 'No se pudo guardar el audiovisual.'));
    }
  });

  if(approveBtn){
    approveBtn.addEventListener('click', async () => {
      const propuestaId = Number(approveBtn.dataset.propuestaId || 0);

      if(!propuestaId){
        return;
      }

      const confirmation = await confirmAction('¿Aprobar este audiovisual?', 'Aprobar');
      if(!confirmation.isConfirmed){
        return;
      }

      try{
        const res = await fetchJSON(`${plannerBaseUrl}/aprobar`, {
          method: 'POST',
          body: JSON.stringify({ id: propuestaId })
        });

        if(res.ok){
          await loadWeek();
          slotModal.hide();
          await showSuccess('Audiovisual aprobado correctamente.');
        }else{
          await showError(res.message || 'No se pudo aprobar.');
        }
      }catch(error){
        console.error(error);
        await showError(getErrorMessage(error, 'No se pudo aprobar el audiovisual.'));
      }
    });
  }

  toCarruselBtn?.addEventListener('click', async () => {
    const propuestaId = Number(toCarruselBtn.dataset.propuestaId || 0);
    const key = toCarruselBtn.dataset.key || '';
    const responsableId = formResponsable.value ? Number(formResponsable.value) : null;

    if(!propuestaId){
      await showError('Primero guarda la propuesta.');
      return;
    }

    if(!responsableId){
      await showError('Selecciona un responsable antes de enviar a pauta.');
      return;
    }

    const confirmation = await confirmAction('Enviar este audiovisual a pauta y asignar un responsable?', 'Aceptar');
    if(!confirmation.isConfirmed){
      return;
    }

    try{
      const res = await fetchJSON(`${plannerBaseUrl}/to-pauta`, {
        method: 'POST',
        body: JSON.stringify({
          propuesta_id: propuestaId,
          asignado_a: responsableId
        })
      });

      if(!res.ok){
        await showError(res.message || 'No se pudo convertir.');
        return;
      }

      if(slotData[key]){
        slotData[key].origen = 'pauta';
        slotData[key].asignado_a = responsableId;
        slotData[key].responsable_nombre = formResponsable.options[formResponsable.selectedIndex]?.text || '';
        slotData[key].can_delete = false;
      }

      const td = document.querySelector(`.slot[data-key="${key}"]`);
      if(td && slotData[key]){
        renderCellContent(td, slotData[key]);
      }

      updateHeaderCounters();
      toCarruselBtn.classList.add('d-none');
      applySearchFilter();
      slotModal.hide();

      await showSuccess('Audiovisual enviado a pauta.');
    }catch(error){
      console.error(error);
      await showError(getErrorMessage(error, 'No se pudo cambiar el audiovisual a pauta.'));
    }
  });

  deleteSlotBtn?.addEventListener('click', async () => {
    const propuestaId = Number(deleteSlotBtn.dataset.propuestaId || 0);
    const key = deleteSlotBtn.dataset.key || '';

    if(!propuestaId){
      return;
    }

    const confirmation = await confirmAction('¿Seguro que deseas eliminar este audiovisual?', 'Eliminar');
    if(!confirmation.isConfirmed){
      return;
    }

    try{
      const res = await fetchJSON(`${plannerBaseUrl}/${propuestaId}`, {
        method: 'DELETE'
      });

      if(!res.ok){
        await showError(res.message || 'No se pudo eliminar.');
        return;
      }

      delete slotData[key];

      const td = document.querySelector(`.slot[data-key="${key}"]`);
      if(td){
        renderCellContent(td, null);
      }

      updateHeaderCounters();

      deleteSlotBtn.classList.add('d-none');
      toCarruselBtn.classList.add('d-none');
      if(approveBtn){
        approveBtn.classList.add('d-none');
      }

      applySearchFilter();
      slotModal.hide();
      await showSuccess('Audiovisual eliminado correctamente.');
    }catch(error){
      console.error(error);
      await showError(getErrorMessage(error, 'No se pudo eliminar el audiovisual.'));
    }
  });

  btnPrev.addEventListener('click', async () => {
    weekStart = addDays(weekStart, -7);
    renderHeaders();
    await loadWeek();
  });

  btnNext.addEventListener('click', async () => {
    weekStart = addDays(weekStart, 7);
    renderHeaders();
    await loadWeek();
  });

  btnToday.addEventListener('click', async () => {
    weekStart = startOfWeekMonday(new Date());
    renderHeaders();
    await loadWeek();
  });

  searchInput.addEventListener('input', applySearchFilter);
  redesSocialesGrid?.addEventListener('change', () => {
    if(redesSocialesGrid.classList.contains('is-invalid')){
      syncRedesSocialesValidation(true);
    }
  });

  (async function init(){
    renderHeaders();
    buildGrid();
    await loadPeriodistas();
    await loadWeek();
  })();
</script>
@endpush
