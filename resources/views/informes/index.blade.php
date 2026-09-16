@extends('layouts.app')

@section('title', 'Informes')

@push('styles')
<style>
  .informes-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 10px 24px rgba(0,0,0,.05);
    overflow: hidden;
  }

  .informes-table {
    width: 100%;
    border-collapse: collapse;
  }

  .informes-table th,
  .informes-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: .85rem .9rem;
    vertical-align: middle;
    font-size: .92rem;
  }

  .informes-table th {
    background: #f8fafc;
    font-weight: 800;
    white-space: nowrap;
  }

  .informes-url {
    max-width: 520px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .82rem;
  }

  @media (max-width: 900px) {
    .informes-table {
      min-width: 760px;
    }
  }
</style>
@endpush

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Informes</h1>
      <div class="text-muted mt-1" style="font-size:.9rem;">
       
      </div>
    </div>

    @if(auth()->user()?->hasRole('director'))
      <a href="{{ route('informes.create') }}" class="btn btn-dark rounded-0">
        <i class="bi bi-cloud-arrow-up"></i> Subir informe
      </a>
    @endif
  </div>

  @if(session('status'))
    <div class="alert alert-success rounded-0">
      <div class="fw-bold">{{ session('status') }}</div>
      @if(session('uploaded_url'))
        <div class="mt-1">
          <span class="text-muted">URL generada:</span>
          <a href="{{ session('uploaded_url') }}" target="_blank" rel="noopener">{{ session('uploaded_url') }}</a>
        </div>
      @endif
    </div>
  @endif

  <div class="informes-card">
    @if($informes->isEmpty())
      <div class="p-4 text-muted">Todavía no hay informes subidos.</div>
    @else
      <div class="table-responsive">
        <table class="informes-table">
          <thead>
            <tr>
              <th>Informe</th>
              <th>Fecha de subida</th>
              <th>Subido por</th>
              <th>URL</th>
              <th style="width: 170px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($informes as $informe)
              @php($url = route('informes.show', $informe))
              <tr>
                <td>
                  <div class="fw-bold">{{ $informe->nombre }}</div>
                  <div class="text-muted small">{{ $informe->archivo_original }}</div>
                </td>
                <td>{{ $informe->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $informe->user?->name ?? 'Usuario eliminado' }}</td>
                <td>
                  <div class="informes-url">
                    <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a>
                  </div>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-0">
                      Ver
                    </a>

                    @if(auth()->user()?->hasRole('director'))
                      <form
                        method="POST"
                        action="{{ route('informes.destroy', $informe) }}"
                        class="js-delete-informe no-loading"
                        data-informe="{{ $informe->nombre }}"
                      >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0">
                          Eliminar
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $informes->links() }}
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-delete-informe').forEach((form) => {
      form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const informe = form.dataset.informe || 'este informe';

        if (!window.Swal) {
          if (confirm('¿Eliminar este informe? Esta acción no se puede deshacer.')) {
            form.submit();
          }

          return;
        }

        const result = await Swal.fire({
          title: '¿Eliminar informe?',
          text: `Se eliminará "${informe}" y no se podrá recuperar.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          reverseButtons: true,
        });

        if (result.isConfirmed) {
          window.showBlockingLoader?.('Eliminando informe...');
          form.submit();
        }
      });
    });
  });
</script>
@endpush
