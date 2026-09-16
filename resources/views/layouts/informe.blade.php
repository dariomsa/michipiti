<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'Informe')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/favicon/ico-michipiti.png" sizes="32x32" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

    <style>
        :root {
            --ec-primary: #e91e63;
            --ec-primary-dark: #c2185b;
            --ec-border: #e7e5e4;
            --ec-text: #1c1917;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Roboto", sans-serif;
            color: var(--ec-text);
            background: #fff;
            min-height: 100vh;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1040;
            background: linear-gradient(90deg, var(--ec-primary-dark), var(--ec-primary));
            color: #fff;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .topbar-inner {
            width: 100%;
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

        .brand-version {
            font-size: 0.72rem;
            font-weight: 600;
            color: #fbbf24;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            white-space: nowrap;
        }

        .topbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .topbar-link,
        .logout-btn {
            border-radius: 0;
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #fff;
            background: transparent;
            font-size: 0.85rem;
            padding: 0.35rem 0.8rem;
            text-decoration: none;
        }

        .topbar-link:hover,
        .logout-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
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

        .informe-shell {
            min-height: calc(100vh - 69px);
            background: #fff;
        }

        @media (max-width: 768px) {
            .topbar-inner,
            .topbar-actions {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .topbar-actions {
                width: 100%;
                margin-left: 0;
            }

            .user-chip {
                flex: 1 1 220px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
@endphp

<header class="topbar">
    <div class="topbar-inner">
        <a href="{{ route('informes.index') }}" class="brand">
            <img src="https://michipiti.elcomercio.com/images/michitipiti-blanco.png" alt="Chat Michipiti" />
            <span class="brand-version">3.0</span>
        </a>

        <div class="topbar-actions">
            <a href="{{ route('informes.index') }}" class="topbar-link">
                <i class="bi bi-card-list"></i> Informes
            </a>

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
                <button type="submit" class="logout-btn">Salir</button>
            </form>
        </div>
    </div>
</header>

<main class="informe-shell">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
