@extends('layouts.dashboard')

@section('title', 'Mis Pacientes - Vive Activo')

@push('styles')
<style>
  .pac-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
  .pac-card { padding:18px 20px; display:flex; flex-direction:column; gap:12px; transition: transform .15s ease, box-shadow .15s ease; }
  .pac-card:hover { transform: translateY(-1px); box-shadow: var(--d-shadow); }
  .pac-head { display:flex; align-items:center; gap:12px; }
  .pac-meta { display:flex; gap:8px; flex-wrap:wrap; }
  .pac-tag { font-size:11px; padding:2px 8px; border-radius:20px; background:var(--d-bg); border:1px solid var(--d-border); color:var(--d-muted); }

  body.sidebar-dark .pac-tag { background:#0f172a; border-color:#24324b; color:#94a3b8; }
  body.sidebar-dark .pac-tag.is-active,
  body.sidebar-dark .pac-tag.is-inactive { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.10); }

  .pac-avatar { width:44px; height:44px; font-size:16px; flex-shrink:0; }
  .pac-avatar.tone-info { background: color-mix(in srgb, var(--d-info) 14%, transparent); color: var(--d-info); }
  .pac-avatar.tone-brand { background: color-mix(in srgb, var(--d-brand) 14%, transparent); color: var(--d-brand); }

  body.sidebar-dark .pac-avatar.tone-info,
  body.sidebar-dark .pac-avatar.tone-brand {
    background: rgba(255,255,255,.06);
  }

  .pac-tag.is-active { background: color-mix(in srgb, var(--d-success) 14%, transparent); color: var(--d-success); border-color: color-mix(in srgb, var(--d-success) 35%, transparent); }
  .pac-tag.is-inactive { background: color-mix(in srgb, var(--d-danger) 12%, transparent); color: var(--d-danger); border-color: color-mix(in srgb, var(--d-danger) 35%, transparent); }

  .pac-next { background: color-mix(in srgb, var(--d-info) 10%, transparent); border-radius:10px; padding:9px 12px; font-size:13px; }
  body.sidebar-dark .pac-next { background: rgba(255,255,255,.06); }
</style>
@endpush

@section('content')
<div class="d-topbar">
  <div>
    <h1 class="d-page-title">Mis Pacientes</h1>
    <p style="color:var(--d-muted);font-size:14px;margin:2px 0 0;">Solo pacientes con asignación activa</p>
  </div>
  <span class="d-card" style="padding:8px 16px;border-radius:10px;font-size:14px;font-weight:600;">
    {{ $pacientes->total() }} pacientes
  </span>
</div>

{{-- Buscador --}}
<form method="GET" class="d-card mb-4" style="padding:14px 16px;display:flex;gap:10px;align-items:center;">
  <i data-lucide="search" style="width:16px;color:var(--d-muted);flex-shrink:0;"></i>
  <input type="text" name="q" value="{{ request('q') }}" class="d-input" placeholder="Buscar por nombre…" style="flex:1;border:none;background:none;padding:0;">
  <button type="submit" class="d-btn d-btn-primary" style="font-size:13px;">Buscar</button>
  @if(request('q'))
    <a href="{{ route('estudiante.pacientes') }}" class="d-btn d-btn-outline" style="font-size:13px;">Limpiar</a>
  @endif
</form>

@if($pacientes->isEmpty())
  <div class="d-card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:12px;">🏥</div>
    <h3 style="margin:0 0 8px;font-size:16px;">Sin pacientes asignados</h3>
    <p style="color:var(--d-muted);font-size:14px;margin:0;">Cuando el administrador te asigne pacientes, aparecerán aquí.</p>
  </div>
@else
  <div class="pac-grid">
    @foreach($pacientes as $pac)
      @php
        $nextSession = $pac->patientSessions->first();
        $initials = collect(explode(' ', $pac->user?->name ?? 'P'))->map(fn($w)=>mb_substr($w,0,1))->take(2)->join('');
        $sexTone = $pac->sex === 'M' ? 'tone-info' : 'tone-brand';
      @endphp
      <div class="d-card pac-card">
        <div class="pac-head">
          <div class="d-avatar pac-avatar {{ $sexTone }}">{{ $initials }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $pac->user?->name ?? '—' }}
            </div>
            <div style="font-size:12px;color:var(--d-muted);">
              @if($pac->user?->profile?->birthdate)
                {{ \Carbon\Carbon::parse($pac->user->profile->birthdate)->age }} años ·
              @endif
              {{ $pac->user?->profile?->district ?? 'Distrito N/D' }}
            </div>
          </div>
        </div>

        <div class="pac-meta">
          @if($pac->condition)
            <span class="pac-tag">{{ $pac->condition }}</span>
          @endif
          @if($pac->is_active)
            <span class="pac-tag is-active">Activo</span>
          @else
            <span class="pac-tag is-inactive">Inactivo</span>
          @endif
        </div>

        @if($nextSession)
          <div class="pac-next">
            <div style="color:var(--d-muted);font-size:12px;margin-bottom:2px;">Próxima sesión:</div>
            <div style="font-weight:600;">{{ $nextSession->scheduled_at?->isoFormat('ddd D MMM · H:mm') }}</div>
          </div>
        @endif

        <div style="display:flex;gap:8px;margin-top:4px;">
          <a href="{{ route('estudiante.sesiones') }}" class="d-btn d-btn-primary" style="flex:1;justify-content:center;font-size:13px;">
            <i data-lucide="calendar-check" style="width:14px;"></i> Sesiones
          </a>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Paginación --}}
  @if($pacientes->hasPages())
    <div style="margin-top:20px;">{{ $pacientes->withQueryString()->links() }}</div>
  @endif
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@endpush
