@extends('layouts.dashboard')

@section('title', 'Mi Perfil - Vive Activo')
@section('page_title', 'Mi Perfil')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

@endpush

@section('content')
@php
  $user = auth()->user();
  $p = $user->profile; // en controller: firstOrCreate()
  $avatar = $p?->avatar_path ? asset('storage/'.$p->avatar_path) : asset('images/default-avatar.png');

  $roleLabel = match($user->role) {
    'admin' => 'Administrador',
    'supervisor' => 'Supervisor',
    'estudiante' => 'Alumno',
    default => 'Miembro',
  };
@endphp

  {{-- (opcional) alertas --}}
  @include('profile.partials._alerts')

  <main class="profile-shell">

    @include('profile.partials._banner')

    @include('profile.partials._summary', compact('user','p','avatar'))

    <section class="profile-grid">
      @include('profile.partials._card-personal', compact('user','p','roleLabel'))
      @include('profile.partials._card-address', compact('p'))
    </section>

    {{-- Modales (separados) --}}
    @include('profile.partials._modal-personal', compact('p'))
    @include('profile.partials._modal-address', compact('p'))

  </main>
@endsection

@push('scripts')
  @include('profile.partials._scripts')
@endpush