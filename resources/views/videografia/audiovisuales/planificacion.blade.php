@extends('layouts.app')

@section('title', 'Planificación Audiovisual')

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Planificación Audiovisual</h1>
      <div class="text-muted small">Base inicial conectada a la nueva tabla `audiovisuales`.</div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card card-form h-100">
        <div class="card-body">
          <div class="text-secondary small text-uppercase mb-2">Total</div>
          <div class="display-6 fw-semibold">{{ $totalAudiovisuales }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card card-form h-100">
        <div class="card-body">
          <div class="text-secondary small text-uppercase mb-2">Pendientes</div>
          <div class="display-6 fw-semibold">{{ $totalPendientes }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card card-form h-100">
        <div class="card-body">
          <div class="text-secondary small text-uppercase mb-2">Finalizados</div>
          <div class="display-6 fw-semibold">{{ $totalFinalizados }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-8">
      <div class="card card-form h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Próximos audiovisuales</h2>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Responsable</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                @forelse($proximosAudiovisuales as $audiovisual)
                  <tr>
                    <td>{{ $audiovisual->titulo }}</td>
                    <td>{{ $audiovisual->user?->name ?: '-' }}</td>
                    <td>
                      {{ optional($audiovisual->fecha)?->format('Y-m-d') ?: '-' }}
                      @if($audiovisual->hora)
                        <span class="text-muted">· {{ substr((string) $audiovisual->hora, 0, 5) }}</span>
                      @endif
                    </td>
                    <td>{{ $audiovisual->estado }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Todavía no hay audiovisuales planificados.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card card-form h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Equipo videografía</h2>
          <div class="d-flex flex-column gap-2">
            @forelse($videografos as $videografo)
              <div class="border p-3 bg-light">{{ $videografo->name }}</div>
            @empty
              <div class="text-muted">No hay usuarios con rol `videografia` todavía.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
