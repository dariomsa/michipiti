@extends('layouts.app')

@section('title', 'Subir informe')

@section('content')
<section class="flex-grow-1">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h1 class="page-title mb-0">Subir informe</h1>
      <div class="text-muted mt-1" style="font-size:.9rem;">
        Carga un archivo HTML.
      </div>
    </div>

    <a href="{{ route('informes.index') }}" class="btn btn-outline-secondary rounded-0">
      Volver
    </a>
  </div>

  <div class="card card-form">
    <div class="card-body">
      @if ($errors->any())
        <div class="alert alert-danger rounded-0">
          <div class="fw-bold mb-1">No se pudo subir el informe.</div>
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('informes.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
          <label class="form-label">Nombre del informe</label>
          <input type="text" name="nombre" class="form-control rounded-0" value="{{ old('nombre') }}" maxlength="150" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Archivo HTML</label>
          <input type="file" name="archivo" class="form-control rounded-0" accept=".html,.htm,text/html" required>
          <div class="form-text">Solo archivos .html o .htm. Máximo 10 MB.</div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('informes.index') }}" class="btn btn-outline-secondary rounded-0">Cancelar</a>
          <button type="submit" class="btn btn-dark rounded-0">
            <i class="bi bi-cloud-arrow-up"></i> Subir
          </button>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
