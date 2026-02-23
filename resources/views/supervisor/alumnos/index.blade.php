@extends('layouts.dashboard')

@section('title', 'Alumnos - Supervisor')
@section('page_title', 'Alumnos')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- KPIs --}}
    <section class="d-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); margin-bottom:24px;">
      <article class="d-card kpi-card-admin kpi-users">
        <span class="d-kpi-label">Total Alumnos</span>
        <strong class="d-kpi-value">{{ $kpis['total'] }}</strong>
        <span class="kpi-foot">Registrados en el sistema</span>
      </article>
      <article class="d-card kpi-card-admin kpi-clients">
        <span class="d-kpi-label">Activos</span>
        <strong class="d-kpi-value">{{ $kpis['activos'] }}</strong>
        <span class="kpi-foot">Con cuenta habilitada</span>
      </article>
    </section>

    {{-- FILTROS --}}
    <section class="d-card mb-4">
      <form method="GET" action="{{ route('supervisor.alumnos') }}">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr auto; gap:12px; align-items:end;">

          <div>
            <label class="d-label" for="q">Buscar</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input id="q" name="q" type="search" class="d-input" placeholder="Nombre o email" value="{{ request('q') }}">
            </div>
          </div>

          <div>
            <label class="d-label" for="distrito">Distrito</label>
            <select id="distrito" name="distrito" class="d-select">
              <option value="">Todos</option>
              @foreach($distritos as $d)
                <option value="{{ $d->id }}" @selected(request('distrito') == $d->id)>{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="d-label" for="universidad">Universidad</label>
            <select id="universidad" name="universidad" class="d-select">
              <option value="">Todas</option>
              @foreach($universidades as $u)
                <option value="{{ $u->id }}" @selected(request('universidad') == $u->id)>{{ $u->short_name ?? $u->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="d-label" for="ciclo">Ciclo</label>
            <select id="ciclo" name="ciclo" class="d-select">
              <option value="">Todos</option>
              @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" @selected(request('ciclo') == $i)>{{ $i }}</option>
              @endfor
            </select>
          </div>

          <div>
            <label class="d-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="d-select">
              <option value="">Todos</option>
              <option value="activo" @selected(request('estado') === 'activo')>Activo</option>
              <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option>
            </select>
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route('supervisor.alumnos') }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </div>
      </form>
    </section>

    {{-- TABLA --}}
    <section class="d-card mb-4">
      <div class="flex-between mb-4">
        <h3 style="margin:0; font-size:18px;">Listado de Alumnos</h3>
        <span style="font-size:12px;color:var(--d-muted);">Solo lectura</span>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:900px;">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Distrito</th>
              <th>Universidad / Carrera</th>
              <th style="text-align:center;">Ciclo</th>
              <th style="text-align:center;">Activos ({{ $activeWindowDays }}d)</th>
              <th style="text-align:center;">Atendidos</th>
              <th style="text-align:center;">Calificación</th>
              <th style="text-align:center;">Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($students as $student)
              @php
                $user      = $student->user;
                $initials  = collect(explode(' ', trim($user->name ?? 'A')))
                               ->filter()->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('');
                $stars     = round($student->avg_rating ?? 0, 1);
                $starsInt  = (int) round($stars);
              @endphp
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ $initials ?: 'A' }}</div>
                    <div>
                      <div class="user-name">
                        <a href="{{ route('supervisor.alumnos.show', $student) }}" style="color:var(--d-text);text-decoration:none;">
                          {{ $user->name }}
                        </a>
                      </div>
                      <div class="user-email">{{ $user->email }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $student->district?->name ?? '—' }}</td>
                <td>
                  <div class="user-name" style="font-size:13px;">{{ $student->university?->short_name ?? $student->university?->name ?? '—' }}</div>
                  <div class="user-email">{{ $student->career?->name ?? '—' }}</div>
                </td>
                <td style="text-align:center;">
                  @if($student->cycle)
                    <span class="d-badge d-badge-blue">Ciclo {{ $student->cycle }}</span>
                  @else
                    <span style="color:var(--d-muted); font-size:13px;">—</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  @if(($student->active_patients ?? 0) > 0)
                    <span class="d-badge d-badge-green">{{ $student->active_patients }}</span>
                  @else
                    <span class="d-badge d-badge-yellow">0</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  @if(($student->attended_patients ?? 0) > 0)
                    <span class="d-badge d-badge-blue">{{ $student->attended_patients }}</span>
                  @else
                    <span style="color:var(--d-muted); font-size:13px;">—</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  @if($stars > 0)
                    <span title="{{ $stars }}/5" style="font-size:13px; font-weight:600; color:var(--d-accent);">
                      @for($i = 1; $i <= 5; $i++)
                        {{ $i <= $starsInt ? '★' : '☆' }}
                      @endfor
                      <small style="color:var(--d-muted);">({{ $stars }})</small>
                    </span>
                  @else
                    <span style="color:var(--d-muted); font-size:12px;">Sin datos</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  <span class="d-badge {{ $student->is_active ? 'd-badge-green' : 'd-badge-red' }}">
                    {{ $student->is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="padding:32px; text-align:center; color:var(--d-muted);">
                  No hay alumnos registrados aún.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $students->appends(request()->query())->links() }}
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
