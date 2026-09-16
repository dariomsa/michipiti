<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>{{ $informe->nombre }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="icon" href="/favicon/ico-michipiti.png" sizes="32x32" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

<style>
  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    background: #fff;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
  }

  .informe-toolbar {
    min-height: 46px;
    display: flex;
    align-items: center;
    gap: .8rem;
    justify-content: space-between;
    padding: .55rem .9rem;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
  }

  .informe-title {
    min-width: 0;
  }

  .informe-title strong,
  .informe-title span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .informe-title span {
    font-size: .8rem;
    color: #6b7280;
  }

  .informe-html {
    background: #fff;
    min-height: calc(100vh - 115px);
    overflow: auto;
    -webkit-user-select: none;
    user-select: none;
  }

  .informe-html * {
    -webkit-user-select: none !important;
    user-select: none !important;
  }

  .security-overlay {
    align-items: center;
    background: rgba(17, 24, 39, .92);
    color: #fff;
    display: none;
    inset: 0;
    justify-content: center;
    padding: 24px;
    position: fixed;
    text-align: center;
    z-index: 99999;
  }

  .security-overlay.is-visible {
    display: flex;
  }

  .security-overlay-card {
    border: 1px solid rgba(255, 255, 255, .18);
    max-width: 420px;
    padding: 28px;
  }

  .security-overlay-card i {
    color: #fbbf24;
    font-size: 2rem;
  }

  @media print {
    body * {
      visibility: hidden !important;
    }

    body::before {
      color: #111827;
      content: "La impresion de este informe esta deshabilitada.";
      display: block;
      font: 16px sans-serif;
      padding: 24px;
      visibility: visible !important;
    }
  }
</style>
</head>
<body>

  <div class="informe-toolbar">
    <div class="informe-title">
      <strong>{{ $informe->nombre }}</strong>
      <span>Subido el {{ $informe->created_at?->format('d/m/Y H:i') }}</span>
    </div>

    <a href="{{ route('informes.index') }}" class="btn btn-sm btn-outline-secondary rounded-0">
      <i class="bi bi-arrow-left"></i> Listado
    </a>
  </div>

  <div class="informe-html">
    {!! $html !!}
  </div>

  <div class="security-overlay" id="securityOverlay" aria-hidden="true">
    <div class="security-overlay-card">
      <i class="bi bi-shield-lock"></i>
      <div class="fw-bold mt-3">Accion deshabilitada</div>
      <div class="mt-2">Este informe no permite imprimir, copiar, seleccionar texto ni usar click derecho.</div>
    </div>
  </div>

<script>
(() => {
  const overlay = document.getElementById('securityOverlay');
  const trackUrl = @json(route('informes.track', $informe));
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  let pendingSeconds = 0;
  let lastTick = Date.now();
  let isVisible = !document.hidden;
  let overlayTimer = null;

  function showSecurityNotice() {
    if (!overlay) {
      return;
    }

    overlay.classList.add('is-visible');
    overlay.setAttribute('aria-hidden', 'false');
    clearTimeout(overlayTimer);

    overlayTimer = setTimeout(() => {
      overlay.classList.remove('is-visible');
      overlay.setAttribute('aria-hidden', 'true');
    }, 1500);
  }

  function blockEvent(event, silent = false) {
    event.preventDefault();
    event.stopPropagation();

    if (!silent) {
      showSecurityNotice();
    }

    return false;
  }

  document.addEventListener('contextmenu', blockEvent);
  document.addEventListener('copy', blockEvent);
  document.addEventListener('cut', blockEvent);
  document.addEventListener('dragstart', blockEvent);
  document.addEventListener('selectstart', blockEvent);

  document.addEventListener('keydown', (event) => {
    const key = String(event.key || '').toLowerCase();
    const blockedCombo = (event.ctrlKey || event.metaKey) && ['a', 'c', 'i', 'p', 's', 'u', 'x'].includes(key);
    const blockedShiftCombo = event.ctrlKey && event.shiftKey && ['i', 'j', 'c'].includes(key);

    if (key === 'printscreen') {
      if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText('').catch(() => {});
      }

      blockEvent(event, true);
      return;
    }

    if (blockedCombo || blockedShiftCombo || event.key === 'F12') {
      blockEvent(event);
    }
  });

  document.addEventListener('keyup', (event) => {
    if (String(event.key || '').toLowerCase() === 'printscreen') {
      if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText('').catch(() => {});
      }

      blockEvent(event, true);
    }
  });

  function collect() {
    const now = Date.now();

    if (isVisible) {
      pendingSeconds += Math.floor((now - lastTick) / 1000);
    }

    lastTick = now;
  }

  function flush(useBeacon = false) {
    collect();

    if (pendingSeconds < 1) {
      return;
    }

    const seconds = Math.min(pendingSeconds, 300);
    pendingSeconds -= seconds;

    if (useBeacon && navigator.sendBeacon) {
      const data = new FormData();
      data.append('_token', token);
      data.append('seconds', String(seconds));
      navigator.sendBeacon(trackUrl, data);
      return;
    }

    fetch(trackUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ seconds }),
      keepalive: true,
    }).catch(() => {
      pendingSeconds += seconds;
    });
  }

  document.addEventListener('visibilitychange', () => {
    collect();
    isVisible = !document.hidden;

    if (!isVisible) {
      flush(true);
    }
  });

  window.addEventListener('beforeunload', () => flush(true));
  window.addEventListener('pagehide', () => flush(true));
  window.setInterval(() => flush(false), 15000);
})();
</script>
</body>
</html>
