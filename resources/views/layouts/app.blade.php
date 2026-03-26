<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'Michipiti')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

    <style>
        :root {
            --ec-primary: #e91e63;
            --ec-primary-dark: #c2185b;
            --ec-sidebar-bg: #171717;
            --ec-sidebar-border: rgba(255, 255, 255, 0.08);
            --ec-surface: #ffffff;
            --ec-surface-strong: #ffffff;
            --ec-border: #e7e5e4;
            --ec-text: #1c1917;
            --ec-muted: #57534e;
            --ec-shadow: 0 30px 80px rgba(28, 25, 23, 0.1);
            --ec-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Roboto", sans-serif;
            color: var(--ec-text);
            background: var(--ec-bg);
            min-height: 100vh;
        }

        .page-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1040;
            background: linear-gradient(90deg, var(--ec-primary-dark), var(--ec-primary));
            color: #fff;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
        }

        .topbar-inner {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            min-width: 0;
        }

        .brand img {
            max-width: 165px;
            width: 100%;
            height: auto;
        }

        .brand-text {
            display: inline-flex;
            flex-direction: column;
            line-height: 1.05;
            min-width: 0;
        }

        .brand-version {
            font-size: 0.72rem;
            font-weight: 600;
            color: #fbbf24;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            white-space: nowrap;
        }

        .brand-mark {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .topbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .mobile-toggle {
            border-radius: 0;
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            width: 2.75rem;
            height: 2.75rem;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.45rem 0.6rem 0.45rem 0.8rem;
            border-radius: 0;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.16);
            min-width: 0;
        }

        .user-chip-text {
            line-height: 1.1;
            min-width: 0;
        }

        .user-chip-name {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .user-chip-role {
            font-size: 0.78rem;
            opacity: 0.86;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-btn {
            border-radius: 0;
            border-color: rgba(255, 255, 255, 0.28);
            color: #fff;
            background: transparent;
            font-size: 0.85rem;
            padding-inline: 0.9rem;
        }

        .layout-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: 100vh;
            align-items: start;
            flex: 1;
            background: linear-gradient(to right, #171717 0, #171717 210px, #ffffff 210px, #ffffff 100%);
        }

        .sidebar {
            width: 100%;
            background: #171717;
            color: #fff;
            border: 1px solid var(--ec-sidebar-border);
            border-radius: 0;
            padding: 1rem 0.75rem;
            box-shadow: var(--ec-shadow);
            align-self: start;
            position: sticky;
            top: 0;
            backdrop-filter: blur(12px);
            box-sizing: border-box;
        }

        .sidebar-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(255, 255, 255, 0.72);
            margin-bottom: 0.75rem;
            padding: 0.2rem 0.45rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .sidebar-nav a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 1.8rem;
            font-size: 0.88rem;
            border-radius: 0;
            transition: 0.18s ease-in-out;
            border: 1px solid transparent;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateX(2px);
        }

        .sidebar-nav i {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .content-panel {
            width: 100%;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .content-panel > .main-content {
            width: 100%;
            max-width: 100%;
            padding: 2rem 2.5rem;
            background: transparent;
            overflow: visible;
            box-sizing: border-box;
        }

        .main-content {
            min-width: 0;
            background: var(--ec-surface);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 0;
            padding: 0;
            box-shadow: var(--ec-shadow);
            overflow: visible;
        }

        .page-title {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .card-form {
            border-radius: 0;
            border-color: var(--ec-border);
            overflow: hidden;
        }

        .card-form .card-body {
            padding: 1.5rem 1.75rem;
        }

        .compact-listing .page-title {
            font-size: 1.3rem;
            margin-bottom: 0.7rem;
        }

        .compact-listing .card-form .card-body {
            padding: 0.95rem 1.05rem;
        }

        .compact-listing .filters-row .form-label {
            font-size: 0.72rem;
        }

        .compact-listing .filters-row .form-control,
        .compact-listing .filters-row .form-select,
        .compact-listing .filters-row .input-group-text,
        .compact-listing .filters-row .btn {
            min-height: 34px;
            font-size: 0.83rem;
        }

        .compact-listing .table {
            font-size: 0.87rem;
            table-layout: auto;
        }

        .compact-listing .table thead th {
            font-size: 0.78rem;
            letter-spacing: 0.01em;
        }

        .compact-listing .table td,
        .compact-listing .table th {
            padding-top: 9px !important;
            padding-bottom: 9px !important;
        }

        .compact-listing .table th:first-child,
        .compact-listing .table td:first-child {
            width: 42%;
            min-width: 320px;
        }

        .compact-listing .table th:nth-child(2),
        .compact-listing .table td:nth-child(2) {
            width: 11%;
        }

        .compact-listing .table th:nth-child(3),
        .compact-listing .table td:nth-child(3),
        .compact-listing .table th:nth-child(4),
        .compact-listing .table td:nth-child(4) {
            width: 14%;
        }

        .compact-listing .table th:nth-child(5),
        .compact-listing .table td:nth-child(5) {
            width: 10%;
        }

        .compact-listing .table th:nth-child(6),
        .compact-listing .table td:nth-child(6) {
            width: 8%;
        }

        .compact-listing .table th:nth-child(7),
        .compact-listing .table td:nth-child(7) {
            width: 11%;
            min-width: 112px;
            white-space: nowrap;
        }

        .compact-listing .table .btn-sm {
            --bs-btn-padding-y: 0.16rem;
            --bs-btn-padding-x: 0.34rem;
            --bs-btn-font-size: 0.78rem;
        }

        .compact-listing .text-muted {
            font-size: 0.8rem;
        }

        label.form-label {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            font-size: 0.95rem;
            border-radius: 0;
            border-color: #d6d3d1;
            min-height: 46px;
        }

        textarea.form-control {
            min-height: calc(1.5em + 4.75rem + calc(var(--bs-border-width) * 2));
        }

        .footer {
            max-width: 1440px;
            width: 100%;
            margin: 0 auto;
            padding: 0 1rem 1.5rem;
        }

        .footer-inner {
            border-top: 1px solid rgba(214, 211, 209, 0.9);
            padding: 1.25rem 0.25rem 0;
            font-size: 0.78rem;
            color: var(--ec-muted);
        }

        .footer .contacts {
            margin-top: 0.75rem;
            font-weight: 500;
        }

        img,
        svg {
            vertical-align: middle;
        }

        #topLoadingBar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: #1ee94a;
            z-index: 99999;
            transition: width 0.25s ease, opacity 0.3s ease;
            opacity: 0;
            box-shadow: 0 0 8px rgba(233, 30, 99, 0.45);
        }

        #topLoadingBar.active {
            opacity: 1;
            width: 35%;
        }

        #topLoadingBar.mid {
            width: 70%;
        }

        #topLoadingBar.end {
            width: 100%;
        }

        .ec-pagination-nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .ec-pagination-shell {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 1rem;
            border: 1px solid var(--ec-border);
            background: linear-gradient(180deg, #ffffff 0%, #fafaf9 100%);
            box-shadow: 0 10px 24px rgba(28, 25, 23, 0.05);
        }

        .ec-pagination-summary {
            color: var(--ec-muted);
            font-size: 0.86rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .ec-pagination-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .ec-pagination-item {
            display: flex;
        }

        .ec-pagination-link {
            min-width: 2.6rem;
            height: 2.6rem;
            padding: 0 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d6d3d1;
            background: #ffffff;
            color: var(--ec-text);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .ec-pagination-link:hover {
            background: #fdf2f8;
            border-color: rgba(233, 30, 99, 0.28);
            color: var(--ec-primary-dark);
            transform: translateY(-1px);
        }

        .ec-pagination-link.is-arrow {
            min-width: 2.9rem;
            padding: 0;
            font-size: 1rem;
        }

        .ec-pagination-link.is-gap {
            background: transparent;
            border-color: transparent;
            color: #78716c;
            min-width: auto;
            padding: 0 0.35rem;
        }

        .ec-pagination-item.is-active .ec-pagination-link {
            background: linear-gradient(90deg, #1d4ed8, #2563eb);
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.22);
        }

        .ec-pagination-item.is-disabled .ec-pagination-link {
            background: #f5f5f4;
            border-color: #e7e5e4;
            color: #a8a29e;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 1199.98px) {
            .layout-wrapper {
                grid-template-columns: 190px minmax(0, 1fr);
                background: linear-gradient(to right, #171717 0, #171717 190px, #ffffff 190px, #ffffff 100%);
            }

            .content-panel > .main-content {
                padding: 1.5rem 1.75rem;
            }
        }

        @media (max-width: 991.98px) {
            .mobile-toggle {
                display: inline-flex;
            }

            .layout-wrapper {
                grid-template-columns: 1fr;
                padding-top: 0;
                gap: 0;
                background: #ffffff;
            }

            .sidebar {
                display: none;
                position: static;
                top: auto;
                padding: 0.85rem 0.65rem;
            }

            .sidebar.show-mobile {
                display: block;
            }

            .main-content {
                padding: 0;
                border-radius: 0;
            }

            .content-panel {
                width: 100%;
            }

            .content-panel > .main-content {
                padding: 1rem;
            }
        }

        @media (max-width: 767.98px) {
            .topbar {
                padding: 0.75rem;
            }

            .topbar-inner {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .brand {
                flex: 1 1 auto;
                min-width: 0;
            }

            .brand img {
                max-width: 150px;
            }

            .topbar-actions {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            .user-chip {
                flex: 1 1 auto;
                min-width: 0;
            }

            .user-chip-name,
            .user-chip-role {
                max-width: 100%;
            }

            .layout-wrapper,
            .footer {
                padding-inline: 0;
            }

            .main-content {
                padding: 0;
                border-radius: 0;
            }

            .page-title {
                font-size: 1.35rem;
                margin-bottom: 1rem;
            }

            .compact-listing .page-title {
                font-size: 1.08rem;
            }

            .ec-pagination-shell {
                flex-direction: column;
                align-items: stretch;
                padding: 0.85rem;
            }

            .ec-pagination-summary {
                text-align: center;
            }

            .ec-pagination-list {
                justify-content: center;
            }

            .ec-pagination-link {
                min-width: 2.35rem;
                height: 2.35rem;
                font-size: 0.88rem;
            }

            .ec-pagination-link.is-arrow {
                min-width: 2.55rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
    $listadoUrl = route('dashboard');
    $showDashboard = $user?->hasRole('director') ?? false;

    if ($user?->hasRole('editor')) {
        $listadoUrl = route('editor.productos.index');
    } elseif ($user?->hasRole('director')) {
        $listadoUrl = route('director.productos.index');
    } elseif ($user?->hasRole('disenador')) {
        $listadoUrl = route('disenador.productos.index');
    } elseif ($user?->hasRole('disenador_manager')) {
        $listadoUrl = route('manager.productos.index');
    } elseif ($user?->hasRole('periodista')) {
        $listadoUrl = route('periodista.productos.index');
    } elseif ($user?->hasRole('videografia')) {
        $listadoUrl = route('videografia.audiovisuales.index');
    }

    $audiovisualesMenu = $audiovisualesMenu ?? [
        ['label' => 'Listado', 'icon' => 'bi-card-list', 'url' => route('videografia.audiovisuales.index')],
        ['label' => 'Planificación', 'icon' => 'bi-calendar3', 'url' => route('videografia.audiovisuales.planificacion')],
    ];

    $layoutMenu = $layoutMenu ?? array_values(array_filter([
        $showDashboard ? ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => route('dashboard')] : null,
        ['label' => 'Listado', 'icon' => 'bi-card-list', 'url' => $listadoUrl],
        ['label' => 'Pauta', 'icon' => 'bi-calendar-week', 'url' => route('pauta.index')],
        ['label' => 'Planificador', 'icon' => 'bi-calendar3', 'url' => route('planificador')],
        ['label' => 'Horarios', 'icon' => 'bi-clock-history', 'url' => route('planificador.horarios')],
    ]));
@endphp

<div class="page-shell">
    <header class="topbar">
        <div class="topbar-inner">
            <a href="{{ $listadoUrl }}" class="brand">
                <img src="https://michipiti.elcomercio.com/images/chatmichipiti-logo.svg" alt="Chat Michipiti" />
                <span class="brand-text">
                    <span class="brand-version">2.0</span>
                </span>
            </a>

            <button class="mobile-toggle" type="button" data-sidebar-toggle aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-actions">
                <div class="user-chip">
                    <i class="bi bi-person-circle fs-5"></i>
                    <div class="user-chip-text">
                        <div class="user-chip-name">{{ $user?->name ?? 'Usuario' }}</div>
                        <div class="user-chip-role">
                            {{ $user ? $user->getRoleNames()->map(fn ($role) => str($role)->replace('_', ' ')->title())->join(' · ') : 'Sin rol asignado' }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm logout-btn">Salir</button>
                </form>
            </div>
        </div>
    </header>

    <div class="layout-wrapper">
        <aside class="sidebar" data-sidebar>
            <div class="sidebar-title">
                <i class="bi bi-layout-text-sidebar-reverse"></i>
                <span>Menú</span>
            </div>

            <ul class="sidebar-nav">
                @foreach($layoutMenu as $item)
                    @php
                        $href = $item['url'] ?? '#';
                        $active = $href !== '#' && url()->current() === $href;
                    @endphp

                    <li>
                        <a href="{{ $href }}" class="{{ $active ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- @if($audiovisualesMenu !== [])
                <div class="sidebar-title pt-3">
                    <i class="bi bi-camera-reels"></i>
                    <span>Audiovisuales</span>
                </div>

                <ul class="sidebar-nav">
                    @foreach($audiovisualesMenu as $item)
                        @php
                            $href = $item['url'] ?? '#';
                            $active = $href !== '#' && url()->current() === $href;
                        @endphp

                        <li>
                            <a href="{{ $href }}" class="{{ $active ? 'active' : '' }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif --}}
        </aside>

        <div class="content-panel">
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <small>
                © Derechos reservados 2025 Grupo EL COMERCIO. Queda prohibida la reproduccion total o parcial,
                por cualquier medio, de todos los contenidos sin autorizacion expresa de Grupo EL COMERCIO.
            </small>
            <div class="contacts">
                CONTACTOS: <a href="mailto:soporte@elcomercio.com">soporte@elcomercio.com</a>
            </div>
        </div>
    </footer>
</div>

<div id="topLoadingBar"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bar = document.getElementById('topLoadingBar');
    const sidebar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('show-mobile');
        });
    }

    if (!bar) return;

    let timer1 = null;
    let timer2 = null;

    function startBar() {
        if (bar.classList.contains('active')) return;

        bar.classList.remove('mid', 'end');
        bar.classList.add('active');

        clearTimeout(timer1);
        clearTimeout(timer2);

        timer1 = setTimeout(() => bar.classList.add('mid'), 180);
        timer2 = setTimeout(() => bar.classList.add('end'), 900);
    }

    function finishBar() {
        clearTimeout(timer1);
        clearTimeout(timer2);

        bar.classList.add('end');

        setTimeout(() => {
            bar.classList.remove('active', 'mid', 'end');
            bar.style.width = '0';
            bar.style.opacity = '0';

            setTimeout(() => {
                bar.style.width = '';
                bar.style.opacity = '';
            }, 50);
        }, 250);
    }

    document.querySelectorAll('a[href]').forEach((a) => {
        a.addEventListener('click', function (e) {
            const href = a.getAttribute('href') || '';

            if (
                a.classList.contains('no-loading') ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                a.target === '_blank' ||
                e.ctrlKey || e.metaKey || e.shiftKey || e.altKey
            ) {
                return;
            }

            startBar();
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function () {
            if (form.classList.contains('no-loading')) return;
            startBar();
        });
    });

    document.querySelectorAll('button[type="button"]').forEach((btn) => {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('no-loading')) return;
            if (btn.getAttribute('data-bs-dismiss')) return;
            startBar();
        });
    });

    window.addEventListener('load', finishBar);
    window.addEventListener('pageshow', finishBar);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
</body>
</html>
