@extends('layouts.dashboard')

@section('title', 'Supervisor - Inicio')
@section('page_title', 'Inicio')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- KPIs --}}
    <section class="d-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); margin-bottom:24px;">
      <article class="d-card kpi-card-admin kpi-users">
        <span class="d-kpi-label">Pacientes</span>
        <strong class="d-kpi-value">{{ $kpis['patients_total'] }}</strong>
        <span class="kpi-foot">Total registrados</span>
      </article>

      <article class="d-card kpi-card-admin kpi-admins">
        <span class="d-kpi-label">Sin asignación</span>
        <strong class="d-kpi-value">{{ $kpis['patients_unassigned'] }}</strong>
        <span class="kpi-foot">Sin alumno activo</span>
      </article>

      <article class="d-card kpi-card-admin kpi-clients">
        <span class="d-kpi-label">Alumnos activos</span>
        <strong class="d-kpi-value">{{ $kpis['students_active'] }}</strong>
        <span class="kpi-foot">Cuenta habilitada</span>
      </article>

      <article class="d-card kpi-card-admin kpi-supervisors">
        <span class="d-kpi-label">Pendientes (7d)</span>
        <strong class="d-kpi-value">{{ $kpis['sessions_pending_7d'] }}</strong>
        <span class="kpi-foot">Sesiones próximas</span>
      </article>

      <article class="d-card kpi-card-admin kpi-users">
        <span class="d-kpi-label">Completadas ({{ $windowDays }}d)</span>
        <strong class="d-kpi-value">{{ $kpis['sessions_done_30d'] }}</strong>
        <span class="kpi-foot">Sesiones realizadas</span>
      </article>

      <article class="d-card kpi-card-admin kpi-clients">
        <span class="d-kpi-label">Rating promedio</span>
        <strong class="d-kpi-value">{{ number_format($kpis['avg_rating'], 2) }}</strong>
        <span class="kpi-foot">Sobre 5</span>
      </article>
    </section>

    {{-- Accesos rápidos --}}
    <section class="d-card" style="margin-bottom:24px;">
      <div class="flex-between" style="gap:12px; flex-wrap:wrap;">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Accesos rápidos</h3>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <a href="{{ route('supervisor.pacientes') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="users"></i> Pacientes
          </a>
          <a href="{{ route('supervisor.alumnos') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="graduation-cap"></i> Alumnos
          </a>
          <a href="{{ route('supervisor.sesiones.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="clock"></i> Sesiones
          </a>
          <a href="{{ route('supervisor.consultas.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="calendar"></i> Consultas
          </a>
          <a href="{{ route('supervisor.reportes') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="file-text"></i> Reportes
          </a>
        </div>
      </div>
    </section>

    <section class="d-grid" style="grid-template-columns:1fr 1fr; gap:24px;">
      {{-- Próximas sesiones pendientes --}}
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Sesiones pendientes (próx. 7 días)</h3>
          <a href="{{ route('supervisor.sesiones.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver todo</a>
        </div>

        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:760px;">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Alumno</th>
              </tr>
            </thead>
            <tbody>
              @forelse($upcomingSessions as $s)
                <tr>
                  <td style="font-weight:600;">{{ $s->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</td>
                  <td>{{ $s->patient?->user?->name ?? '—' }}</td>
                  <td>{{ $s->student?->user?->name ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="padding:16px;opacity:.8;">Sin sesiones pendientes próximas.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Ranking alumnos --}}
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Alumnos (rating)</h3>
          <a href="{{ route('supervisor.alumnos') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver alumnos</a>
        </div>

        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:640px;">
            <thead>
              <tr>
                <th>Top</th>
                <th style="text-align:center;">Rating</th>
                <th style="text-align:center;">Reviews</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topStudents as $st)
                <tr>
                  <td style="font-weight:600;">
                    <a href="{{ route('supervisor.alumnos.show', $st) }}" style="color:var(--d-text);text-decoration:none;">
                      {{ $st->user?->name ?? '—' }}
                    </a>
                  </td>
                  <td style="text-align:center;">
                    <span class="d-badge d-badge-green">{{ number_format((float)($st->avg_rating ?? 0), 2) }}</span>
                  </td>
                  <td style="text-align:center;">
                    <span class="d-badge d-badge-blue">{{ (int)($st->reviews_count ?? 0) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="padding:16px;opacity:.8;">Sin datos de calificación.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div style="margin-top:16px;">
          <h4 style="margin:0 0 10px;font-size:13px;color:var(--d-muted);">Más bajos</h4>
          <div class="d-table-wrapper">
            <table class="d-table" style="min-width:640px;">
              <thead>
                <tr>
                  <th>Alumno</th>
                  <th style="text-align:center;">Rating</th>
                  <th style="text-align:center;">Reviews</th>
                </tr>
              </thead>
              <tbody>
                @forelse($lowStudents as $st)
                  <tr>
                    <td style="font-weight:600;">
                      <a href="{{ route('supervisor.alumnos.show', $st) }}" style="color:var(--d-text);text-decoration:none;">
                        {{ $st->user?->name ?? '—' }}
                      </a>
                    </td>
                    <td style="text-align:center;">
                      <span class="d-badge d-badge-yellow">{{ number_format((float)($st->avg_rating ?? 0), 2) }}</span>
                    </td>
                    <td style="text-align:center;">
                      <span class="d-badge d-badge-blue">{{ (int)($st->reviews_count ?? 0) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" style="padding:16px;opacity:.8;">Sin datos.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush