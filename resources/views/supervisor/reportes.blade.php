@extends('layouts.dashboard')

@section('title', 'Reportes - Supervisor')
@section('page_title', 'Reportes')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    <section class="d-card" style="margin-bottom:24px;">
      <div class="flex-between" style="gap:12px;flex-wrap:wrap;">
        <div>
          <h3 style="margin:0;font-size:16px;font-weight:700;">Resumen</h3>
          <div style="font-size:12px;color:var(--d-muted);">Ventana operativa: últimos {{ $windowDays }} días</div>
        </div>
        <a href="{{ route('supervisor.dashboard') }}" class="d-btn d-btn-outline" style="font-size:13px;">
          <i data-lucide="layout-dashboard"></i> Volver al dashboard
        </a>
      </div>
    </section>

    <section class="d-grid" style="grid-template-columns:1fr 1fr; gap:24px;">
      {{-- Pacientes sin asignación activa --}}
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Pacientes sin asignación</h3>
          <a href="{{ route('supervisor.pacientes') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver pacientes</a>
        </div>
        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:760px;">
            <thead>
              <tr>
                <th>Paciente</th>
                <th>Distrito</th>
                <th style="text-align:center;">Acción</th>
              </tr>
            </thead>
            <tbody>
              @forelse($unassignedPatients as $p)
                @php
                  $u = $p->user;
                  $district = $u?->profile?->district;
                @endphp
                <tr>
                  <td style="font-weight:600;">{{ $u?->name ?? '—' }}</td>
                  <td>{{ $district ?: '—' }}</td>
                  <td style="text-align:center;">
                    <a href="{{ route('supervisor.pacientes.show', $p) }}" class="d-btn d-btn-outline action-btn" title="Ver detalle">
                      <i data-lucide="eye"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="padding:16px;opacity:.8;">No hay pacientes pendientes de asignación.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Ranking de alumnos por rating --}}
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Alumnos por calificación</h3>
          <a href="{{ route('supervisor.alumnos') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver alumnos</a>
        </div>
        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:760px;">
            <thead>
              <tr>
                <th>Alumno</th>
                <th style="text-align:center;">Rating</th>
                <th style="text-align:center;">Reviews</th>
              </tr>
            </thead>
            <tbody>
              @forelse($studentsByRating as $st)
                <tr>
                  <td style="font-weight:600;">
                    <a href="{{ route('supervisor.alumnos.show', $st) }}" style="color:var(--d-text);text-decoration:none;">
                      {{ $st->user?->name ?? '—' }}
                    </a>
                  </td>
                  <td style="text-align:center;">
                    @php $rating = (float)($st->avg_rating ?? 0); @endphp
                    <span class="d-badge {{ $rating >= 4 ? 'd-badge-green' : ($rating >= 3 ? 'd-badge-blue' : 'd-badge-yellow') }}">
                      {{ number_format($rating, 2) }}
                    </span>
                  </td>
                  <td style="text-align:center;">
                    <span class="d-badge d-badge-blue">{{ (int)($st->reviews_count ?? 0) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="padding:16px;opacity:.8;">Sin calificaciones registradas.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="d-card" style="margin-top:24px;">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Sesiones completadas por alumno ({{ $windowDays }}d)</h3>
        <a href="{{ route('supervisor.sesiones.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver sesiones</a>
      </div>
      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:860px;">
          <thead>
            <tr>
              <th>Alumno</th>
              <th style="text-align:center;">Completadas</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sessionsDone30d as $row)
              <tr>
                <td style="font-weight:600;">{{ $row->student?->user?->name ?? '—' }}</td>
                <td style="text-align:center;">
                  <span class="d-badge d-badge-green">{{ (int)($row->done_count ?? 0) }}</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="2" style="padding:16px;opacity:.8;">Sin datos en la ventana.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
