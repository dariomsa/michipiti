@extends('layouts.app')

@section('title','Pauta')

@push('styles')
<style>
  :root{
    --ec-primary:#e91e63;
    --ec-border:#e5e7eb;
    --ec-green:#009245;
    --bg:#f5f6f8;
    --card:#ffffff;
    --muted:#6b7280;
  }

  body{ background:var(--bg); }
  .page-title{ font-size:1.6rem; font-weight:800; letter-spacing:-0.2px; margin-bottom:0.75rem; }
  .content-card{
    background:var(--card);
    border:1px solid var(--ec-border);
    border-radius:16px;
    box-shadow:0 10px 24px rgba(0,0,0,.05);
  }
  .nav-tabs .nav-link{ font-weight:800; }
  .nav-tabs .nav-link.active{ color:var(--ec-primary); }
  .tab-pane{ padding:1rem; }
  .cronograma-controls{
    display:flex; flex-wrap:wrap; align-items:center; gap:1rem; margin-bottom:1rem;
  }
  .cronograma-controls .label-inline{ font-size:.9rem; font-weight:700; color:#111; }
  .cronograma-header-row{
    display:grid; grid-template-columns:90px 1.6fr 1fr 1fr 1fr;
    font-weight:800; font-size:0.85rem; padding:.6rem 1rem;
    border-bottom:1px solid var(--ec-border); background:#f9fafb;
    border-top-left-radius:12px; border-top-right-radius:12px;
  }
  .cronograma-item{
    display:grid; grid-template-columns:90px 1.6fr 1fr 1fr 1fr;
    padding:.85rem 1rem; border-bottom:1px solid #f3f4f6;
    align-items:center; font-size:.9rem; cursor:pointer;
  }
  .cronograma-item:nth-child(odd){ background:#fcfcfd; }
  .cronograma-item:hover{ background:#f3f4f6; }
  .hora-link{ color:#0d6efd; text-decoration:none; font-weight:800; }
  .hora-link:hover{ text-decoration:underline; }
  .estado-dot{ width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:6px; }
  .estado-aprobado{ background:#39b54a; }
  .estado-revision{ background:#ffbf00; }
  .estado-pendiente{ background:#6c757d; }
  .toolbar{
    display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;
    margin-bottom:1rem;
  }
  .toolbar .left, .toolbar .right{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
  .date-chip{
    padding:6px 12px; border:1px solid var(--ec-border); background:#fff; border-radius:12px;
    font-weight:800; font-size:14px; display:inline-flex; align-items:center; gap:8px;
  }
  .calendar{ overflow:auto; max-height: calc(100vh - 330px); border-radius:16px; }
  .calendar-table{ min-width:760px; border-collapse:collapse; }
  .calendar-table th, .calendar-table td{
    border:1px solid var(--ec-border); vertical-align:middle; height:46px; font-size:13px; white-space:nowrap;
  }
  .calendar-table thead th{
    background:#f9fafb; font-weight:900; position:sticky; top:0; z-index:2; text-align:center;
  }
  .hour{
    background:#f3f4f6; font-weight:900; width:92px; position:sticky; left:0; z-index:1;
    text-align:center; font-variant-numeric:tabular-nums;
  }
  .calendar-table thead .hour{ z-index:3; }
  .slot{ background:#fff; cursor:pointer; transition:background-color 120ms ease, box-shadow 120ms ease; padding:0; }
  .slot:hover{ box-shadow: inset 0 0 0 2px rgba(0,0,0,.06); }
  .slot.busy{ background:#d1d5db; }
  .slot .cell-wrap{ padding:7px 10px; line-height:1.2; }
  .slot .title{ font-weight:900; font-size:12px; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:560px; }
  .slot .meta{ font-size:11px; opacity:.95; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
  .copy-box{
    border:1px solid var(--ec-border);
    background:#f9fafb;
    padding:.75rem;
    border-radius:10px;
    min-height:44px;
    white-space:pre-line;
  }
  .btn-copy-all{
    border-radius:10px;
    font-size:.85rem;
    font-weight:800;
    transition:all .18s ease;
  }
  .btn-copy-all.is-success{
    background:#198754 !important;
    border-color:#198754 !important;
    color:#fff !important;
  }
  .mini-hours-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(84px, 1fr));
    gap:8px;
  }
  .mini-hour-slot{
    border:1px solid var(--ec-border);
    background:#fff;
    border-radius:10px;
    min-height:42px;
    font-size:.82rem;
    font-weight:800;
    color:#111827;
    transition:all .15s ease;
  }
  .mini-hour-slot:hover{
    border-color:#93c5fd;
    background:#eff6ff;
  }
  .mini-hour-slot.is-selected{
    border-color:#2563eb;
    background:#dbeafe;
    color:#1d4ed8;
    box-shadow:inset 0 0 0 1px #2563eb;
  }
  @media (max-width: 768px){
    .cronograma-header-row, .cronograma-item{ grid-template-columns:80px 1.6fr 1fr 1fr 1fr; font-size:.82rem; }
    .calendar-table{ min-width:640px; }
  }
</style>
@endpush

@section('content')
<section class="main-content flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0" id="mainTitle">Planificador</h1>
    <div class="text-muted" style="font-size:.9rem;">
      <i class="bi bi-calendar-week"></i> _
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-0">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
  @endif

  <div class="content-card">
    <div class="p-3 pb-0">
      <ul class="nav nav-tabs" id="vistaTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="agenda-tab" data-bs-toggle="tab" data-bs-target="#agendaPane" type="button" role="tab" aria-controls="agendaPane" aria-selected="true">
            <i class="bi bi-card-list me-1"></i> Vista agenda
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pauta-tab" data-bs-toggle="tab" data-bs-target="#pautaPane" type="button" role="tab" aria-controls="pautaPane" aria-selected="false">
            <i class="bi bi-calendar-week me-1"></i> Vista pauta
          </button>
        </li>
      </ul>
    </div>

    <div class="tab-content" id="vistaTabsContent">
      <div class="tab-pane fade show active" id="agendaPane" role="tabpanel" aria-labelledby="agenda-tab" tabindex="0">
        <div class="toolbar">
          <div class="left">
            <div class="input-group" style="max-width: 380px;">
              <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
              <input type="date" id="agendaFecha" class="form-control" />
            </div>

            <button class="btn btn-outline-secondary btn-sm" id="btnAgendaToday" type="button">
              <i class="bi bi-dot me-1"></i>Hoy
            </button>

            <div class="btn-group" role="group" aria-label="Navegación día">
              <button class="btn btn-outline-secondary btn-sm" id="btnAgendaPrev" type="button" aria-label="Anterior">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm" id="btnAgendaNext" type="button" aria-label="Siguiente">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>

            <button class="btn btn-outline-secondary btn-sm" id="btnOrdenarAgenda" type="button">
              <i class="bi bi-arrow-down-up me-1"></i>Ordenar por hora
            </button>
          </div>

          <div class="right">
            <span class="date-chip" id="agendaChip"><i class="bi bi-calendar3"></i> —</span>

            <div class="d-flex align-items-center gap-2">
              <span class="label-inline">Bloques:</span>
              <select id="pageSizeSelect" class="form-select form-select-sm w-auto">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
              </select>
            </div>
          </div>
        </div>

        <div class="cronograma-header-row">
          <div>Hora</div>
          <div>Contenido</div>
          <div>Autor</div>
          <div>Diseñador</div>
          <div>Estado</div>
        </div>

        <div id="cronogramaList"></div>

        <nav class="mt-3" aria-label="Paginación">
          <ul class="pagination pagination-sm justify-content-center mb-0" id="pagination"></ul>
        </nav>
      </div>

      <div class="tab-pane fade" id="pautaPane" role="tabpanel" aria-labelledby="pauta-tab" tabindex="0">
        <div class="toolbar">
          <div class="left">
            <div class="input-group" style="max-width: 380px;">
              <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
              <input type="date" id="pautaFecha" class="form-control" />
            </div>

            <button class="btn btn-outline-secondary btn-sm" id="btnToday" type="button">
              <i class="bi bi-dot me-1"></i>Hoy
            </button>

            <div class="btn-group" role="group" aria-label="Navegación día">
              <button class="btn btn-outline-secondary btn-sm" id="btnPrev" type="button" aria-label="Anterior">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm" id="btnNext" type="button" aria-label="Siguiente">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>

          <div class="right">
            <span class="date-chip" id="dateChip"><i class="bi bi-calendar3"></i> —</span>
          </div>
        </div>

        <div class="calendar">
          <table class="calendar-table w-100">
            <thead>
              <tr>
                <th class="hour">Hora</th>
                <th id="dayHeader">Día</th>
              </tr>
            </thead>
            <tbody id="dayBody"></tbody>
          </table>
        </div>

        <div class="mt-2 text-muted" style="font-size: 13px;">
          Tip: haz clic en un bloque gris para abrir el panel y editar la programación.
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="cronogramaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:0;">
      <div style="background-color: var(--ec-green); color:#fff; padding:0.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
        <span style="font-weight:700;">Panel de cronograma</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" style="padding:1.25rem 1.5rem;">
        <div class="row">
          <div class="col-md-6 mb-3 mb-md-0">
            <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
              <h6 class="fw-bold mb-0">Carrusel final</h6>
              <button type="button" class="btn btn-outline-secondary btn-sm btn-copy-all" id="btnCopiarTodo">
                <i class="bi bi-clipboard me-1"></i>
                <span id="btnCopiarTodoText">Copiar texto</span>
              </button>
            </div>

            <div class="mb-2">
              <div class="fw-bold small mb-1">Título:</div>
              <div id="modalTitulo"></div>
            </div>

            <div class="mb-2">
              <div class="fw-bold small mb-1">Enlace Canva:</div>
              <a href="#" target="_blank" id="modalEnlaceCanva" rel="noopener">Abrir diseño</a>
            </div>

            <div class="mb-3">
              <div class="fw-bold small mb-1">Copy:</div>
              <div id="modalCopy" class="copy-box"></div>
            </div>

            <div class="mb-3">
              <div class="fw-bold small mb-1">Hashtag:</div>
              <div id="modalHashtags" class="copy-box"></div>
            </div>

            <div class="mb-3">
              <div class="fw-bold small mb-1">Créditos:</div>
              <div id="modalCreditos" class="copy-box"></div>
            </div>

            <div class="mb-2">
              <div class="fw-bold small mb-1">Día publicación:</div>
              <div id="modalDiaPublicacion"></div>
            </div>
          </div>

          <div class="col-md-6">
            <h6 class="fw-bold mb-3">Programación</h6>

            <div class="mb-3">
              <label class="form-label form-label-sm">Fecha de publicación</label>
              <input type="date" class="form-control form-control-sm" id="modalFechaPub" />
            </div>

            <div class="mb-3">
              <label class="form-label form-label-sm">Hora de publicación</label>
              <input type="hidden" id="modalHoraPub" />
              <div class="mini-hours-grid" id="miniHoursGrid"></div>
              <div class="text-muted mt-1" style="font-size:12px;">Haz clic en un horario disponible para seleccionarlo.</div>
            </div>

            <input type="hidden" id="modalCarruselId" value="">

            <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
              <button class="btn btn-success flex-grow-1" type="button" id="btnProgramar">
                Guardar programación
              </button>
              <button class="btn btn-outline-secondary flex-grow-1" type="button" data-bs-dismiss="modal">Cancelar</button>
            </div>

            <div class="mt-2 text-muted" style="font-size:12px;">
              Guarda la fecha/hora usando <code>/pauta/{id}/programar</code>.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const URL_ITEMS = @json(route('pauta.items'));
  const URL_PROGRAMAR_TPL = @json(url('/pauta/__ID__/programar'));
  const CSRF = @json(csrf_token());
  const STORAGE_KEY = 'pauta_fecha_actual';
  const STORAGE_TTL = 3 * 60 * 1000;
  let scheduleItems = [];
  const scheduleItemsCache = {};
  let currentDateISO = todayLocalISO();
  let pageSize = 20;
  let currentPage = 1;
  const START_MINUTES = 6 * 60;
  const END_MINUTES   = 24 * 60;
  const STEP_MINUTES  = 15;
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
  const mainTitle = document.getElementById('mainTitle');
  const agendaFecha = document.getElementById('agendaFecha');
  const agendaChip = document.getElementById('agendaChip');
  const cronogramaList = document.getElementById('cronogramaList');
  const paginationEl = document.getElementById('pagination');
  const pageSizeSelect = document.getElementById('pageSizeSelect');
  const btnOrdenarAgenda = document.getElementById('btnOrdenarAgenda');
  const btnAgendaPrev = document.getElementById('btnAgendaPrev');
  const btnAgendaNext = document.getElementById('btnAgendaNext');
  const btnAgendaToday = document.getElementById('btnAgendaToday');
  const pautaFecha = document.getElementById('pautaFecha');
  const dateChip = document.getElementById('dateChip');
  const dayHeader = document.getElementById('dayHeader');
  const dayBody = document.getElementById('dayBody');
  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const btnToday = document.getElementById('btnToday');
  const modalEl = document.getElementById('cronogramaModal');
  const modalTitulo = document.getElementById('modalTitulo');
  const modalEnlaceCanva = document.getElementById('modalEnlaceCanva');
  const modalCopy = document.getElementById('modalCopy');
  const modalHashtags = document.getElementById('modalHashtags');
  const modalCreditos = document.getElementById('modalCreditos');
  const modalDiaPublicacion = document.getElementById('modalDiaPublicacion');
  const modalFechaPub = document.getElementById('modalFechaPub');
  const modalHoraPub = document.getElementById('modalHoraPub');
  const miniHoursGrid = document.getElementById('miniHoursGrid');
  const modalCarruselId = document.getElementById('modalCarruselId');
  const btnProgramar = document.getElementById('btnProgramar');
  const btnCopiarTodo = document.getElementById('btnCopiarTodo');
  const modalBootstrap =
    (window.bootstrap && window.bootstrap.Modal && modalEl)
      ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
      : null;

  function saveFechaEnSesion(fecha) {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ fecha, expira: Date.now() + STORAGE_TTL }));
    } catch (e) {}
  }

  function getFechaDeSesion() {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (!data?.fecha || !data?.expira || Date.now() > data.expira) {
        sessionStorage.removeItem(STORAGE_KEY);
        return null;
      }
      return data.fecha;
    } catch (e) {
      try { sessionStorage.removeItem(STORAGE_KEY); } catch (_) {}
      return null;
    }
  }

  function pad2(n) { return String(n).padStart(2, '0'); }

  function formatDateES(iso) {
    const d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('es-ES', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }

  function minutesToHHMM(total) {
    const h = Math.floor(total / 60);
    const m = total % 60;
    return pad2(h) + ':' + pad2(m);
  }

  function addDays(iso, delta) {
    const d = new Date(iso + 'T00:00:00');
    d.setDate(d.getDate() + delta);
    return d.toISOString().slice(0, 10);
  }

  function escapeHTML(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function estadoDotClass(estado) {
    const e = String(estado || '').toUpperCase();
    if (e === 'APROBADO') return 'estado-dot estado-aprobado';
    if (e === 'EN_REVISION' || e === 'REVISION') return 'estado-dot estado-revision';
    return 'estado-dot estado-pendiente';
  }

  function normalizeToStep(hhmm) {
    const m = /^(\d{2}):(\d{2})$/.exec(hhmm || '');
    if (!m) return hhmm;
    const total = parseInt(m[1], 10) * 60 + parseInt(m[2], 10);
    return minutesToHHMM(Math.max(0, Math.min((24 * 60) - 1, Math.round(total / STEP_MINUTES) * STEP_MINUTES)));
  }

  function calendarDayIndexFromDate(dateStr) {
    if (!dateStr) return null;
    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    const day = date.getDay();
    return day === 0 ? 6 : day - 1;
  }

  function getAllowedHoursForDate(dateStr) {
    const idx = calendarDayIndexFromDate(dateStr);
    return idx === null ? [] : (scheduleByDay[idx] || []);
  }

  function isPastDateHour(dateStr, hour) {
    if (!dateStr || !hour) return false;
    return new Date(`${dateStr}T${hour}:00`).getTime() < Date.now();
  }

  function getItemsForDate(iso) {
    return scheduleItems.filter(function (it) { return it.fechaISO === iso; });
  }

  function buildIndexByDateAndTime(items) {
    const idx = {};
    items.forEach(function (it) { idx[it.fechaISO + '__' + it.hora] = it; });
    return idx;
  }

  function copyPlainText(text) {
    const plain = String(text || '');
    const temp = document.createElement('textarea');
    temp.value = plain;
    temp.setAttribute('readonly', '');
    temp.style.position = 'fixed';
    temp.style.opacity = '0';
    temp.style.left = '-9999px';
    document.body.appendChild(temp);
    temp.focus();
    temp.select();
    temp.setSelectionRange(0, temp.value.length);
    const ok = document.execCommand('copy');
    document.body.removeChild(temp);
    if (!ok) {
      throw new Error('copy_failed');
    }
  }

  async function copiarContenidoCompleto() {
    if (!btnCopiarTodo) return;
    const copy = (modalCopy.textContent || '').trim() === '—' ? '' : (modalCopy.textContent || '').trim();
    const hashtags = (modalHashtags.textContent || '').trim() === '—' ? '' : (modalHashtags.textContent || '').trim();
    const creditos = (modalCreditos.textContent || '').trim() === '—' ? '' : (modalCreditos.textContent || '').trim();

    const partes = [];
    if (copy) partes.push(copy);
    if (hashtags) partes.push(hashtags);
    if (creditos) partes.push(creditos);

    const textoFinal = partes.join('\n\n');

    if (!textoFinal) {
      animateCopyButton(false);
      return;
    }

    try {
      copyPlainText(textoFinal);
      animateCopyButton(true);
    } catch (e) {
      console.error(e);
      animateCopyButton(false);
    }
  }

  function animateCopyButton(ok) {
    if (!btnCopiarTodo) return;
    if (ok) {
      btnCopiarTodo.classList.remove('btn-outline-secondary', 'btn-outline-danger');
      btnCopiarTodo.classList.add('is-success');
      btnCopiarTodo.innerHTML = '<i class="bi bi-check2 me-1"></i><span>Copiado</span>';
    } else {
      btnCopiarTodo.classList.remove('btn-outline-secondary', 'is-success');
      btnCopiarTodo.classList.add('btn-outline-danger');
      btnCopiarTodo.innerHTML = '<i class="bi bi-x-circle me-1"></i><span>No se pudo copiar</span>';
    }
    setTimeout(function () {
      btnCopiarTodo.classList.remove('is-success', 'btn-outline-danger');
      btnCopiarTodo.classList.add('btn-outline-secondary');
      btnCopiarTodo.innerHTML = '<i class="bi bi-clipboard me-1"></i><span>Copiar texto</span>';
    }, 1800);
  }

  async function loadItemsByDate(fechaISO) {
    try {
      const url = new URL(URL_ITEMS, window.location.origin);
      if (fechaISO) url.searchParams.set('fecha', fechaISO);
      const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      const raw = Array.isArray(data) ? data : (Array.isArray(data.items) ? data.items : []);
      scheduleItems = raw.map(function (r) {
        return {
          id: r.id,
          fechaISO: (r.fechaISO || r.fecha || '').slice(0, 10) || fechaISO || '',
          hora: normalizeToStep((r.hora || '').slice(0, 5)),
          contenido: r.contenido || r.titulo || '',
          autor: r.autor || (r.user && r.user.name ? r.user.name : '') || '',
          disenador: r.disenador || '',
          estado: r.estado || '',
          copy: r.copy || '',
          hashtags: r.hashtags || '',
          creditos: r.creditos || '',
          canvaUrl: r.canvaUrl || r.canva_url || ''
        };
      });
      scheduleItemsCache[fechaISO] = scheduleItems;
    } catch (e) {
      console.error('loadItemsByDate error:', e);
      scheduleItems = [];
      scheduleItemsCache[fechaISO] = [];
    }
  }

  async function getItemsForDateRemote(iso) {
    if (scheduleItemsCache[iso]) {
      return scheduleItemsCache[iso];
    }

    try {
      const url = new URL(URL_ITEMS, window.location.origin);
      url.searchParams.set('fecha', iso);
      const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      const raw = Array.isArray(data) ? data : (Array.isArray(data.items) ? data.items : []);
      const items = raw.map(function (r) {
        return {
          id: r.id,
          fechaISO: (r.fechaISO || r.fecha || '').slice(0, 10) || iso,
          hora: normalizeToStep((r.hora || '').slice(0, 5)),
          contenido: r.contenido || r.titulo || '',
          autor: r.autor || (r.user && r.user.name ? r.user.name : '') || '',
          disenador: r.disenador || '',
          estado: r.estado || '',
          copy: r.copy || '',
          hashtags: r.hashtags || '',
          creditos: r.creditos || '',
          canvaUrl: r.canvaUrl || r.canva_url || ''
        };
      });
      scheduleItemsCache[iso] = items;
      return items;
    } catch (e) {
      console.error('getItemsForDateRemote error:', e);
      return [];
    }
  }

  async function renderAvailableHours(dateISO, currentItem) {
    const items = await getItemsForDateRemote(dateISO);
    const occupied = new Set(
      items
        .filter(item => String(item.id) !== String(currentItem?.id || ''))
        .map(item => item.hora)
        .filter(Boolean)
    );

    const allowedHours = getAllowedHoursForDate(dateISO);
    const availableHours = allowedHours
      .filter(hour => !isPastDateHour(dateISO, hour))
      .filter(hour => !occupied.has(hour));

    const preferredHour = currentItem?.hora && availableHours.includes(currentItem.hora)
      ? currentItem.hora
      : (availableHours[0] || '');

    modalHoraPub.value = preferredHour;
    miniHoursGrid.innerHTML = '';

    if (!allowedHours.length) {
      miniHoursGrid.innerHTML = '<div class="text-muted small">Sin horarios configurados para este día.</div>';
      btnProgramar.disabled = true;
      return;
    }

    allowedHours.forEach(function (hour) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'mini-hour-slot';
      button.textContent = hour;

      if (hour === preferredHour) {
        button.classList.add('is-selected');
      }

      button.addEventListener('click', function () {
        modalHoraPub.value = hour;
        miniHoursGrid.querySelectorAll('.mini-hour-slot.is-selected').forEach(function (node) {
          node.classList.remove('is-selected');
        });
        button.classList.add('is-selected');
        btnProgramar.disabled = false;
      });

      miniHoursGrid.appendChild(button);
    });

    btnProgramar.disabled = !preferredHour;
  }

  function renderAgenda() {
    const items = getItemsForDate(currentDateISO);
    const totalPages = Math.max(1, Math.ceil(items.length / pageSize));
    if (currentPage > totalPages) currentPage = totalPages;
    const pageItems = items.slice((currentPage - 1) * pageSize, currentPage * pageSize);
    cronogramaList.innerHTML = '';

    pageItems.forEach(function (item) {
      const row = document.createElement('div');
      row.className = 'cronograma-item';
      row.dataset.id = item.id;
      row.innerHTML = `
        <div><a class="hora-link">${escapeHTML(item.hora)}</a></div>
        <div>${escapeHTML(item.contenido)}</div>
        <div>${escapeHTML(item.autor || '—')}</div>
        <div>${escapeHTML(item.disenador || '—')}</div>
        <div><span class="${estadoDotClass(item.estado)}"></span><span style="font-weight:800;">${escapeHTML(item.estado || '—')}</span></div>
      `;
      row.addEventListener('click', function () { openModal(item); });
      cronogramaList.appendChild(row);
    });

    renderAgendaPagination(totalPages);
  }

  function renderAgendaPagination(totalPages) {
    paginationEl.innerHTML = '';
    const prevLi = document.createElement('li');
    prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
    prevLi.innerHTML = '<span class="page-link">&lt;</span>';
    if (currentPage > 1) prevLi.addEventListener('click', function () { currentPage--; renderAgenda(); });
    paginationEl.appendChild(prevLi);

    for (let p = 1; p <= totalPages; p++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (p === currentPage ? ' active' : '');
      li.innerHTML = '<span class="page-link">' + p + '</span>';
      if (p !== currentPage) li.addEventListener('click', function () { currentPage = p; renderAgenda(); });
      paginationEl.appendChild(li);
    }

    const nextLi = document.createElement('li');
    nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
    nextLi.innerHTML = '<span class="page-link">&gt;</span>';
    if (currentPage < totalPages) nextLi.addEventListener('click', function () { currentPage++; renderAgenda(); });
    paginationEl.appendChild(nextLi);
  }

  function renderPauta() {
    const texto = formatDateES(currentDateISO);
    dateChip.innerHTML = '<i class="bi bi-calendar3"></i> ' + texto;
    dayHeader.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
    const index = buildIndexByDateAndTime(getItemsForDate(currentDateISO));
    dayBody.innerHTML = '';

    for (let mins = START_MINUTES; mins <= END_MINUTES; mins += STEP_MINUTES) {
      const hhmm = minutesToHHMM(mins);
      const tr = document.createElement('tr');
      const hourTd = document.createElement('td');
      hourTd.className = 'hour';
      hourTd.textContent = hhmm;
      tr.appendChild(hourTd);

      const td = document.createElement('td');
      td.className = 'slot';
      const item = index[currentDateISO + '__' + hhmm];
      if (item) td.classList.add('busy');
      td.innerHTML = item ? `
        <div class="cell-wrap">
          <div class="title">${escapeHTML(item.contenido)}</div>
          <div class="meta">
            <span class="${estadoDotClass(item.estado)}"></span>
            <span>${escapeHTML(item.estado || '—')}</span>
            <span>•</span>
            <span>${escapeHTML(item.autor || '—')}</span>
            <span>•</span>
            <span>${escapeHTML(item.disenador || '—')}</span>
          </div>
        </div>
      ` : `<div class="cell-wrap text-muted" style="font-size:12px;">(Libre)</div>`;
      td.addEventListener('click', function () { openModalForSlot(currentDateISO, hhmm, item || null); });
      tr.appendChild(td);
      dayBody.appendChild(tr);
    }
  }

  function setDiaPublicacionFromFecha(fechaISO) {
    const d = new Date((fechaISO || currentDateISO) + 'T00:00:00');
    return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
  }

  async function openModal(item) {
    modalCarruselId.value = item.id || '';
    modalTitulo.textContent = item.contenido || '—';
    if (item.canvaUrl && item.canvaUrl.trim() !== '') {
      modalEnlaceCanva.href = item.canvaUrl;
      modalEnlaceCanva.textContent = 'Abrir diseño';
      modalEnlaceCanva.style.pointerEvents = 'auto';
      modalEnlaceCanva.style.opacity = '1';
    } else {
      modalEnlaceCanva.href = 'javascript:void(0)';
      modalEnlaceCanva.textContent = 'Sin enlace de Canva';
      modalEnlaceCanva.style.pointerEvents = 'none';
      modalEnlaceCanva.style.opacity = '.6';
    }
    modalCopy.textContent = item.copy || '—';
    modalHashtags.textContent = item.hashtags || '—';
    modalCreditos.textContent = item.creditos || '—';
    modalFechaPub.value = item.fechaISO || currentDateISO;
    modalDiaPublicacion.textContent = setDiaPublicacionFromFecha(modalFechaPub.value);
    await renderAvailableHours(modalFechaPub.value, item);
    if (modalBootstrap) modalBootstrap.show();
  }

  async function openModalForSlot(fechaISO, hora, item) {
    if (item) return openModal(item);
    modalCarruselId.value = '';
    modalTitulo.textContent = '(Libre)';
    modalEnlaceCanva.href = 'javascript:void(0)';
    modalEnlaceCanva.textContent = 'Sin enlace de Canva';
    modalEnlaceCanva.style.pointerEvents = 'none';
    modalEnlaceCanva.style.opacity = '.6';
    modalCopy.textContent = '';
    modalHashtags.textContent = '';
    modalCreditos.textContent = '';
    modalFechaPub.value = fechaISO;
    modalDiaPublicacion.textContent = setDiaPublicacionFromFecha(fechaISO);
    await renderAvailableHours(fechaISO, null);
    if (modalBootstrap) modalBootstrap.show();
  }

  async function programar() {
    const id = (modalCarruselId.value || '').trim();
    if (!id) return alert('Este bloque está libre. Selecciona un carrusel programado para editarlo.');
    const fecha = (modalFechaPub.value || '').trim();
    let hora = (modalHoraPub.value || '').trim();
    if (!fecha) return alert('Selecciona fecha.');
    if (!hora) return alert('Selecciona hora.');

    try {
      const res = await fetch(URL_PROGRAMAR_TPL.replace('__ID__', encodeURIComponent(id)), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ fecha, hora })
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      await setCurrentDate(fecha);
      if (modalBootstrap) modalBootstrap.hide();
    } catch (e) {
      console.error(e);
      alert('No se pudo guardar la programación.');
    }
  }

  async function setCurrentDate(iso, opts) {
    opts = opts || {};
    currentDateISO = iso || currentDateISO;
    saveFechaEnSesion(currentDateISO);
    if (agendaFecha) agendaFecha.value = currentDateISO;
    if (pautaFecha) pautaFecha.value = currentDateISO;
    const texto = formatDateES(currentDateISO);
    if (mainTitle) mainTitle.textContent = 'Pauta — ' + texto;
    if (agendaChip) agendaChip.innerHTML = '<i class="bi bi-calendar3"></i> ' + texto;
    if (dateChip) dateChip.innerHTML = '<i class="bi bi-calendar3"></i> ' + texto;
    if (!opts.skipLoad) await loadItemsByDate(currentDateISO);
    currentPage = 1;
    renderAgenda();
    renderPauta();
  }

  agendaFecha?.addEventListener('change', async function () { await setCurrentDate(agendaFecha.value); });
  btnAgendaPrev?.addEventListener('click', async function () { await setCurrentDate(addDays(currentDateISO, -1)); });
  btnAgendaNext?.addEventListener('click', async function () { await setCurrentDate(addDays(currentDateISO, 1)); });
  btnAgendaToday?.addEventListener('click', async function () { await setCurrentDate(todayLocalISO()); });
  pageSizeSelect?.addEventListener('change', function () { pageSize = parseInt(pageSizeSelect.value, 10); currentPage = 1; renderAgenda(); });
  btnOrdenarAgenda?.addEventListener('click', function () {
    scheduleItems.sort(function (a, b) { return (a.hora || '').localeCompare(b.hora || ''); });
    renderAgenda();
    renderPauta();
  });
  pautaFecha?.addEventListener('change', async function () { await setCurrentDate(pautaFecha.value); });
  modalFechaPub?.addEventListener('change', async function () {
    modalDiaPublicacion.textContent = setDiaPublicacionFromFecha(modalFechaPub.value);
    const currentId = modalCarruselId.value || '';
    const currentItem = currentId
      ? (scheduleItems.find(item => String(item.id) === String(currentId)) || { id: currentId, hora: null })
      : null;
    await renderAvailableHours(modalFechaPub.value, currentItem);
  });
  btnPrev?.addEventListener('click', async function () { await setCurrentDate(addDays(currentDateISO, -1)); });
  btnNext?.addEventListener('click', async function () { await setCurrentDate(addDays(currentDateISO, 1)); });
  btnToday?.addEventListener('click', async function () { await setCurrentDate(todayLocalISO()); });
  btnProgramar?.addEventListener('click', programar);
  btnCopiarTodo?.addEventListener('click', copiarContenidoCompleto);

  (async function init() {
    const qs = new URLSearchParams(window.location.search);
    currentDateISO = qs.get('fecha') || getFechaDeSesion() || todayLocalISO();
    await setCurrentDate(currentDateISO);
  })();
})();

function todayLocalISO() {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}
</script>
@endpush
