@extends('layouts.dashboard')

@section('title', 'Crear Usuario - Vive Activo')
@section('page_title', 'Crear Usuario')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
  <div class="d-content">
    <div class="d-container">

      <section class="d-card mb-4">
        <div class="panel-head">
          <div>
            <h3 class="form-title">Crear usuario</h3>
            <p style="margin:0; opacity:.8;">Agregar un nuevo acceso al sistema.</p>
          </div>
          <a href="{{ route('admin.users.index') }}" class="d-btn d-btn-outline">Volver</a>
        </div>

        <form class="user-form" action="{{ route('admin.users.store') }}" method="POST">
          @csrf
          @include('admin.users._form', [
            'user' => null,
            'mode' => 'create',
            'submitText' => 'Guardar usuario',
            'submitClass' => 'd-btn d-btn-primary full-btn',
          ])
        </form>
      </section>

    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush