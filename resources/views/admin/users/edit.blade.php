@extends('layouts.dashboard')

@section('title', 'Editar Usuario - Vive Activo')
@section('page_title', 'Editar Usuario')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
  <div class="d-content">
    <div class="d-container">

      <section class="d-card mb-4">
        <div class="panel-head">
          <div>
            <h3 class="form-title">Editar usuario</h3>
            <p style="margin:0; opacity:.8;">Actualiza datos y permisos.</p>
          </div>
          <a href="{{ route('admin.users.index') }}" class="d-btn d-btn-outline">Volver</a>
        </div>

        <form class="user-form" action="{{ route('admin.users.update', $user) }}" method="POST">
          @csrf
          @method('PUT')

          @include('admin.users._form', [
            'user' => $user,
            'mode' => 'edit',
            'submitText' => 'Actualizar usuario',
            'submitClass' => 'd-btn d-btn-outline full-btn',
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