@extends('layouts.dashboard')

@section('title', 'Detalle de Alumno - Supervisor')

@section('content')
  <div class="d-topbar" style="margin-bottom:16px;">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
        <a href="{{ route('supervisor.alumnos') }}" style="color:var(--d-brand);">Alumnos</a> › {{ $student->user?->name }}
      </div>
      <h1 class="d-page-title">🎓 {{ $student->user?->name }}</h1>
      <div style="font-size:13px;color:var(--d-muted);margin-top:4px;">{{ $student->user?->email }}</div>
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <span class="d-badge d-badge-blue">⭐ Promedio: {{ $avgRating > 0 ? number_format($avgRating, 2) : '—' }}/5</span>
      <span class="d-badge" style="background:var(--d-bg);color:var(--d-text);border:1px solid var(--d-border);">
        Reviews: {{ $reviewsCount }}
      </span>
      <span class="d-badge {{ $student->is_active ? 'd-badge-green' : 'd-badge-red' }}">
        {{ $student->is_active ? 'Activo' : 'Inactivo' }}
      </span>
    </div>
  </div>

  <div class="d-card" style="padding:0;overflow:hidden;">
    <table class="d-table" style="min-width:920px;">
      <thead>
        <tr>
          <th>Sesión</th>
          <th>Paciente</th>
          <th style="text-align:center;">Rating</th>
          <th>Comentario</th>
          <th style="text-align:center;">Fecha review</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reviews as $r)
          @php
            $session = $r->session;
            $patient = $session?->patient;
            $patientName = $patient?->user?->name ?? '—';
            $patientEmail = $patient?->user?->email ?? '';
          @endphp
          <tr>
            <td>
              <div style="font-weight:700;color:var(--d-text);">
                {{ $session?->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
              </div>
              <div style="font-size:12px;color:var(--d-muted);">
                Estado: {{ $session?->statusLabel() ?? ($session?->status ?? '—') }}
              </div>
            </td>
            <td>
              <div style="font-weight:700;color:var(--d-text);">{{ $patientName }}</div>
              @if($patientEmail)
                <div style="font-size:12px;color:var(--d-muted);">{{ $patientEmail }}</div>
              @endif
            </td>
            <td style="text-align:center;">
              <span class="d-badge d-badge-blue">{{ $r->rating }}/5</span>
            </td>
            <td style="max-width:420px;">
              <div style="color:var(--d-text);opacity:.9;">
                {{ $r->comment ? \Illuminate\Support\Str::limit($r->comment, 160) : '—' }}
              </div>
            </td>
            <td style="text-align:center;color:var(--d-muted);font-size:13px;">
              {{ $r->created_at?->format('d/m/Y') ?? '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align:center;opacity:.7;">Aún no hay calificaciones registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($reviews->hasPages())
    <div style="margin-top:16px;">{{ $reviews->links() }}</div>
  @endif
@endsection
