@extends('layouts.dashboard')

@section('title', 'Editar plan - Vive Activo')
@section('page_title', 'Editar plan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container" style="max-width:900px;">

    @if($errors->any())
      <div class="mb-4" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;font-size:14px;font-weight:500;">
        Revisa los campos del formulario.
      </div>
    @endif

    <div class="d-card">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:18px;">Editar plan</h3>
        <a href="{{ route('admin.planes.index') }}" class="d-btn d-btn-outline">Volver</a>
      </div>

      <form method="POST" action="{{ route('admin.planes.update', $plan) }}">
        @csrf @method('PUT')
        @include('admin.planes._form', ['plan' => $plan])

        <div style="display:flex;justify-content:flex-end;margin-top:16px;gap:10px;">
          <button type="submit" class="d-btn d-btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
