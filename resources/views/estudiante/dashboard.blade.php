@extends('layouts.dashboard')

@section('title', 'Mi Panel - Vive Activo')

@push('styles')
<style>
  .st-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
  @media(max-width:900px){ .st-kpis{grid-template-columns:repeat(2,1fr);} }
  .st-kpi { padding:20px 22px; display:flex; align-items:center; gap:14px; }
  .st-kpi-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
  .st-kpi-num { font-size:28px; font-weight:800; line-height:1; }
  .st-kpi-lbl { font-size:12px; color:var(--d-muted); margin-top:4px; }

  .st-tone-success { color: var(--d-success); }
  .st-tone-warning { color: var(--d-warning); }
  .st-tone-danger { color: var(--d-danger); }
  .st-tone-info { color: var(--d-info); }
  .st-tone-brand { color: var(--d-brand); }
  .st-tone-muted { color: var(--d-muted); }

  .st-kpi-icon.st-tone-success { background: color-mix(in srgb, var(--d-success) 16%, transparent); }
  .st-kpi-icon.st-tone-warning { background: color-mix(in srgb, var(--d-warning) 16%, transparent); }
  .st-kpi-icon.st-tone-danger { background: color-mix(in srgb, var(--d-danger) 14%, transparent); }
  .st-kpi-icon.st-tone-info { background: color-mix(in srgb, var(--d-info) 14%, transparent); }
  .st-kpi-icon.st-tone-brand { background: color-mix(in srgb, var(--d-brand) 14%, transparent); }

  body.sidebar-dark .st-kpi-icon.st-tone-success,
  body.sidebar-dark .st-kpi-icon.st-tone-warning,
  body.sidebar-dark .st-kpi-icon.st-tone-danger,
  body.sidebar-dark .st-kpi-icon.st-tone-info,
  body.sidebar-dark .st-kpi-icon.st-tone-brand {
    background: rgba(255,255,255,.06);
  }
  .st-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:20px; }
  @media(max-width:900px){ .st-grid{grid-template-columns:1fr;} }
  .st-next-card { border-radius:16px; padding:20px 22px; color:#fff; display:flex; flex-direction:column; gap:10px; }
  .st-next-card.blue { background: linear-gradient(135deg, var(--d-info), var(--d-brand)); }
  .cumplimiento-bar { height:8px; border-radius:8px; background:rgba(255,255,255,.25); overflow:hidden; }
  .cumplimiento-fill { height:100%; border-radius:8px; background:#fff; transition:width .4s; }
  .st-list-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--d-border); }
  .st-status-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
  .dot-done{background:var(--d-success);} .dot-no_show{background:var(--d-danger);} .dot-pending{background:var(--d-warning);} .dot-rescheduled{background:var(--d-info);} .dot-cancelled{background:var(--d-muted);}

  body.sidebar-dark .st-list-row { border-bottom-color:#24324b; }
</style>
@endpush

@section('content')
<div class="d-topbar">
  <div>
    <h1 class="d-page-title">Bienvenido, {{ $student->user?->name ?? 'Alumno' }} 👋</h1>
    <p style="color:var(--d-muted);font-size:14px;margin:2px 0 0;">
      Panel de desempeño · {{ now()->isoFormat('MMMM YYYY') }}
    </p>
  </div>
  <a href="{{ route('estudiante.sesiones') }}" class="d-btn d-btn-primary">
    <i data-lucide="calendar-check" style="width:16px;"></i> Ver sesiones pendientes
  </a>
</div>

{{-- KPIs --}}
<div class="st-kpis">
  <div class="d-card st-kpi">
    <div class="st-kpi-icon st-tone-success">✅</div>
    <div>
      <div class="st-kpi-num st-tone-success">{{ $sesionesEsteMes }}</div>
      <div class="st-kpi-lbl">Sesiones este mes</div>
    </div>
  </div>
  <div class="d-card st-kpi">
    <div class="st-kpi-icon st-tone-info">🏃</div>
    <div>
      <div class="st-kpi-num st-tone-info">{{ $sesionesTotales }}</div>
      <div class="st-kpi-lbl">Sesiones totales</div>
    </div>
  </div>
  <div class="d-card st-kpi">
    <div class="st-kpi-icon st-tone-warning">👥</div>
    <div>
      <div class="st-kpi-num st-tone-warning">{{ $pacientesActivos }}</div>
      <div class="st-kpi-lbl">Pacientes asignados</div>
    </div>
  </div>
  <div class="d-card st-kpi">
    <div class="st-kpi-icon st-tone-danger">❌</div>
    <div>
      <div class="st-kpi-num st-tone-danger">{{ $noShowEsteMes }}</div>
      <div class="st-kpi-lbl">No shows este mes</div>
    </div>
  </div>
</div>

<div class="st-grid">
  {{-- Próximas sesiones + cumplimiento --}}
  <div>
    <div class="d-card mb-4">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="margin:0;font-size:15px;font-weight:700;">📅 Próximas sesiones</h3>
        <a href="{{ route('estudiante.sesiones') }}" style="font-size:13px;color:var(--d-brand);">Ver todas</a>
      </div>
      @forelse($proximasSesiones as $s)
        <div class="st-list-row">
          <div class="d-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0;">
            {{ mb_substr($s->patient?->user?->name ?? 'P', 0, 1) }}
          </div>
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;">{{ $s->patient?->user?->name ?? '—' }}</div>
            <div style="font-size:12px;color:var(--d-muted);">{{ $s->scheduled_at?->isoFormat('ddd D MMM · H:mm') }}</div>
          </div>
          <a href="{{ route('estudiante.sesiones') }}" class="d-btn d-btn-outline" style="font-size:12px;padding:4px 10px;">
            Registrar
          </a>
        </div>
      @empty
        <p style="color:var(--d-muted);text-align:center;padding:20px 0;margin:0;">Sin sesiones pendientes próximas. 🎉</p>
      @endforelse
    </div>

    {{-- Historial reciente --}}
    <div class="d-card">
      <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;">📋 Historial reciente</h3>
      @forelse($historialSesiones as $s)
        @php
          $dotMap = ['done'=>'dot-done','no_show'=>'dot-no_show','rescheduled'=>'dot-rescheduled','cancelled'=>'dot-cancelled'];
        @endphp
        <div class="st-list-row">
          <div class="st-status-dot {{ $dotMap[$s->status] ?? 'dot-cancelled' }}"></div>
          <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;">{{ $s->patient?->user?->name ?? '—' }}</div>
            <div style="font-size:12px;color:var(--d-muted);">{{ $s->scheduled_at?->format('d/m/Y H:i') }}</div>
          </div>
          <span style="font-size:11px;color:var(--d-muted);">{{ $s->statusLabel() }}</span>
        </div>
      @empty
        <p style="color:var(--d-muted);text-align:center;padding:20px 0;margin:0;">Sin historial aún.</p>
      @endforelse
    </div>
  </div>

  {{-- Columna derecha: desempeño --}}
  <div>
    <div class="st-next-card blue mb-4">
      <div style="font-size:13px;opacity:.85;">📊 Cumplimiento del mes</div>
      <div style="font-size:36px;font-weight:800;">{{ $cumplimiento }}%</div>
      <div class="cumplimiento-bar">
        <div class="cumplimiento-fill" style="width:{{ $cumplimiento }}%;"></div>
      </div>
      <div style="font-size:12px;opacity:.75;">
        {{ $sesionesEsteMes }} realizadas · {{ $noShowEsteMes }} no shows
      </div>
    </div>

    <div class="d-card">
      <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;">🏥 Mis pacientes</h3>
      @if($pacientesActivos === 0)
        <p style="color:var(--d-muted);text-align:center;padding:20px 0;margin:0;">Sin pacientes asignados aún.</p>
      @else
        <p style="font-size:13px;color:var(--d-muted);margin:0 0 12px;">
          Tienes <strong style="color:var(--d-text);">{{ $pacientesActivos }}</strong> paciente{{ $pacientesActivos !== 1 ? 's' : ''}} asignado{{ $pacientesActivos !== 1 ? 's' : ''}}.
        </p>
        <a href="{{ route('estudiante.pacientes') }}" class="d-btn d-btn-primary" style="width:100%;text-align:center;justify-content:center;">
          <i data-lucide="users"></i> Ver mis pacientes
        </a>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@endpush