@extends('layouts.dashboard')

@section('title', 'Pacientes - Supervisor')
@section('page_title', 'Pacientes')

@section('content')
  <div class="d-topbar" style="margin-bottom:16px;">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">Supervisor</div>
      <h1 class="d-page-title">👥 Pacientes</h1>
    </div>

    <form method="GET" action="{{ route('supervisor.pacientes') }}" style="display:flex;gap:10px;align-items:center;">
      <input name="q" value="{{ $q }}" class="d-input" placeholder="Buscar por nombre o correo" style="width:280px;max-width:60vw;">
      <button class="d-btn d-btn-primary" type="submit">Buscar</button>
      <a class="d-btn d-btn-outline" href="{{ route('supervisor.pacientes') }}">Limpiar</a>
    </form>
  </div>

  @if (session('success'))
    <div class="d-card" style="margin-bottom:14px;">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="d-card" style="margin-bottom:14px;border-color:#fecaca;">
      {{ session('error') }}
    </div>
  @endif

  <div class="d-card" style="padding:0;overflow:hidden;">
    <table class="d-table">
      <thead>
        <tr>
          <th>Paciente</th>
          <th>Alumno asignado</th>
          <th style="text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($patients as $p)
          <tr>
            <td>
              <div style="font-weight:700;color:var(--d-text);">{{ $p->user?->name ?? '—' }}</div>
              <div style="font-size:12px;color:var(--d-muted);">{{ $p->user?->email ?? '' }}</div>
            </td>
            <td>
              <div style="color:var(--d-text);">
                {{ $p->activeAssignment?->student?->user?->name ?? '—' }}
              </div>
            </td>
            <td style="text-align:right;">
              <a class="d-btn d-btn-outline" style="padding:7px 12px;font-size:13px;" href="{{ route('supervisor.pacientes.show', $p) }}">
                Ver
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" style="text-align:center;opacity:.7;">No hay pacientes para mostrar.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($patients->hasPages())
    <div style="margin-top:16px;">{{ $patients->links() }}</div>
  @endif
@endsection
