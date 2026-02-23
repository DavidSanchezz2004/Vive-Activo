@extends('layouts.dashboard')

@section('title', 'Editar Alumno - Vive Activo')
@section('page_title', 'Perfil del Alumno')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container" style="max-width:760px;">

    {{-- Breadcrumb --}}
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:var(--d-muted);">
      <a href="{{ route('admin.alumnos.index') }}" style="color:var(--d-muted); text-decoration:none;">Alumnos</a>
      <i data-lucide="chevron-right" style="width:14px;"></i>
      <span style="color:var(--d-text); font-weight:500;">{{ $student->user->name }}</span>
    </div>

    {{-- Errores --}}
    @if($errors->any())
      <div class="mb-4" style="
        background:#fef2f2; border:1px solid #fecaca; color:#b91c1c;
        padding:12px 16px; border-radius:10px; font-size:14px;
      ">
        <ul style="margin:0; padding-left:16px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Card info del usuario (solo lectura) --}}
    <div class="d-card mb-4" style="display:flex; align-items:center; gap:16px;">
      @php
        $user     = $student->user;
        $initials = collect(explode(' ', trim($user->name ?? 'A')))
                      ->filter()->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('');
      @endphp
      <div class="d-avatar" style="width:52px;height:52px;font-size:18px;">{{ $initials }}</div>
      <div style="flex:1;">
        <div style="font-size:16px; font-weight:600;">{{ $user->name }}</div>
        <div style="font-size:13px; color:var(--d-muted);">{{ $user->email }}</div>
      </div>
      <a href="{{ route('admin.users.edit', $user) }}" class="d-btn d-btn-outline" style="font-size:13px;">
        <i data-lucide="settings"></i>
        Editar cuenta
      </a>
    </div>

    {{-- Formulario del perfil del alumno --}}
    <div class="d-card">
      <div class="panel-head mb-4">
        <h2 class="form-title">Perfil académico</h2>
        <span style="font-size:13px; color:var(--d-muted);">Solo se editan los datos académicos aquí.</span>
      </div>

      <form action="{{ route('admin.alumnos.update', $student) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

          {{-- Distrito --}}
          <div class="d-form-group">
            <label class="d-label" for="district_id">Distrito</label>
            <select id="district_id" name="district_id" class="d-select">
              <option value="">— Sin distrito —</option>
              @foreach($distritos as $d)
                <option value="{{ $d->id }}" @selected(old('district_id', $student->district_id) == $d->id)>
                  {{ $d->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Universidad --}}
          <div class="d-form-group">
            <label class="d-label" for="university_id">Universidad</label>
            <select id="university_id" name="university_id" class="d-select">
              <option value="">— Sin universidad —</option>
              @foreach($universidades as $u)
                <option value="{{ $u->id }}" @selected(old('university_id', $student->university_id) == $u->id)>
                  {{ $u->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Carrera --}}
          <div class="d-form-group">
            <label class="d-label" for="career_id">Carrera</label>
            <select id="career_id" name="career_id" class="d-select">
              <option value="">— Sin carrera —</option>
              @foreach($carreras as $c)
                <option value="{{ $c->id }}" @selected(old('career_id', $student->career_id) == $c->id)>
                  {{ $c->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Ciclo --}}
          <div class="d-form-group">
            <label class="d-label" for="cycle">Ciclo</label>
            <select id="cycle" name="cycle" class="d-select">
              <option value="">— Sin ciclo —</option>
              @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" @selected(old('cycle', $student->cycle) == $i)>Ciclo {{ $i }}</option>
              @endfor
            </select>
          </div>

          {{-- Sexo --}}
          <div class="d-form-group">
            <label class="d-label" for="sex">Sexo</label>
            <select id="sex" name="sex" class="d-select">
              <option value="">— No especificado —</option>
              <option value="M" @selected(old('sex', $student->sex) === 'M')>Masculino</option>
              <option value="F" @selected(old('sex', $student->sex) === 'F')>Femenino</option>
              <option value="O" @selected(old('sex', $student->sex) === 'O')>Otro</option>
            </select>
          </div>

          {{-- Fecha de nacimiento --}}
          <div class="d-form-group">
            <label class="d-label" for="birthdate">Fecha de nacimiento</label>
            <input
              id="birthdate"
              name="birthdate"
              type="date"
              class="d-input"
              value="{{ old('birthdate', $student->birthdate?->format('Y-m-d')) }}"
              max="{{ now()->subYears(14)->format('Y-m-d') }}"
            >
          </div>

        </div>

        {{-- Estado --}}
        <div class="d-form-group" style="margin-top:8px;">
          <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <input
              type="hidden" name="is_active" value="0">
            <input
              id="is_active"
              type="checkbox"
              name="is_active"
              value="1"
              style="width:18px;height:18px;accent-color:var(--d-brand);"
              @checked(old('is_active', $student->is_active))>
            <span class="d-label" style="margin:0;">Alumno activo</span>
          </label>
        </div>

        {{-- Acciones --}}
        <div style="display:flex; gap:12px; margin-top:24px; justify-content:flex-end;">
          <a href="{{ route('admin.alumnos.index') }}" class="d-btn d-btn-outline">Cancelar</a>
          <button type="submit" class="d-btn d-btn-primary">
            <i data-lucide="save"></i>
            Guardar cambios
          </button>
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
