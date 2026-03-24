@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-title">Dashboard</div>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card card-form shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase small text-secondary mb-2">Perfil</p>
                    <h2 class="h4 mb-4">Hola, {{ auth()->user()->name }}</h2>

                    <div class="mb-3">
                        <div class="text-secondary small">Email</div>
                        <div class="fw-semibold">{{ auth()->user()->email }}</div>
                    </div>

                    <div>
                        <div class="text-secondary small mb-2">Roles asignados</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach (auth()->user()->getRoleNames() as $role)
                                <span class="badge text-bg-dark px-3 py-2">
                                    {{ str($role)->replace('_', ' ')->title() }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card card-form shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase small text-secondary mb-2">Base visual</p>
                    <h2 class="h4 mb-3">Layout principal listo para extender</h2>
                    <p class="text-secondary mb-4">
                        La estructura ya usa topbar, sidebar, fondo base y una version movil funcional.
                        En el siguiente paso armamos los menus por rol con mejor criterio visual y jerarquia.
                    </p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="border p-3 h-100 bg-light">
                                <div class="fw-semibold mb-2">Responsive</div>
                                <div class="text-secondary small">Sidebar colapsable en movil y contenido a ancho completo.</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border p-3 h-100 bg-light">
                                <div class="fw-semibold mb-2">Visual base</div>
                                <div class="text-secondary small">Se mantiene el fondo degradado y el aire visual de la propuesta anterior.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
