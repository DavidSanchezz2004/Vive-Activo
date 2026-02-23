@extends('layouts.dashboard')
@section('title', 'Sesiones - Vive Activo')
@section('page_title', 'Sesiones Nutricionales')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
  <style>
    .s-pending     { background:rgba(245,158,11,.15); color:#b45309; }
    .s-done        { background:rgba(16,185,129,.15);  color:#059669; }
    .s-no_show     { background:rgba(239,68,68,.15);   color:#dc2626; }
    .s-rescheduled { background:rgba(59,130,246,.15);  color:#2563eb; }
    .s-cancelled   { background:rgba(100,116,139,.15); color:#475569; }
  </style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    @if(session('ok'))
      <div class="mb-4" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;font-size:14px;font-weight:500;">
        {{ session('ok') }}
      </div>
    @endif

    {{-- Filtros --}}
    <section class="d-card mb-4">
      @php $prefix = request()->routeIs('admin.*') ? 'admin' : 'supervisor'; @endphp
      <form method="GET" action="{{ route("{$prefix}.sesiones.index") }}">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end;">

          <div>
            <label class="d-label" for="q">Paciente</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input id="q" name="q" type="search" class="d-input" placeholder="Nombre o email" value="{{ request('q') }}">
            </div>
          </div>

          <div>
            <label class="d-label" for="alumno">Alumno</label>
            <input id="alumno" name="alumno" type="text" class="d-input" placeholder="Nombre del alumno" value="{{ request('alumno') }}">
          </div>

          <div>
            <label class="d-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="d-select">
              <option value="">Todos</option>
              @foreach(\App\Models\PatientSession::STATUSES as $val => $lbl)
                <option value="{{ $val }}" @selected(request('estado') === $val)>{{ $lbl }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="d-label" for="fecha">Fecha</label>
            <input id="fecha" name="fecha" type="date" class="d-input" value="{{ request('fecha') }}">
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route("{$prefix}.sesiones.index") }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </div>
      </form>
    </section>

    {{-- Tabla --}}
    <section class="d-card">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:18px;">Listado de Sesiones</h3>
        <a href="{{ route("{$prefix}.sesiones.create") }}" class="d-btn d-btn-primary">
          <i data-lucide="plus"></i> Nueva Sesión
        </a>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:900px;">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Alumno</th>
              <th>Fecha</th>
              <th style="text-align:center;">Estado</th>
              <th style="text-align:center;">Descuenta</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sessions as $s)
              @php
                $initials = collect(explode(' ', trim($s->patient?->user?->name ?? 'P')))
                  ->filter()->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode('');
              @endphp
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ $initials ?: 'P' }}</div>
                    <div>
                      <div class="user-name">{{ $s->patient?->user?->name ?? '—' }}</div>
                      <div class="user-email">{{ $s->patient?->user?->email ?? '—' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $s->student?->user?->name ?? '—' }}</td>
                <td>{{ $s->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</td>

                {{-- Estado inline --}}
                <td style="text-align:center;">
                  <form action="{{ route("{$prefix}.sesiones.status", $s) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                      class="d-select s-{{ $s->status }}"
                      style="padding:3px 8px;font-size:12px;font-weight:600;border-radius:20px;border:none;cursor:pointer;">
                      @foreach(\App\Models\PatientSession::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" @selected($s->status === $val)>{{ $lbl }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>

                <td style="text-align:center;">
                  @if($s->deducts)
                    <span class="d-badge d-badge-red" style="font-size:12px;">Sí</span>
                  @else
                    <span class="d-badge" style="font-size:12px;background:var(--d-border);color:var(--d-muted);">No</span>
                  @endif
                </td>

                <td>
                  <div class="row-actions" style="justify-content:center;">
                    <a href="{{ route("{$prefix}.sesiones.edit", $s) }}" class="d-btn d-btn-outline action-btn" title="Editar">
                      <i data-lucide="pencil"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="padding:32px;text-align:center;color:var(--d-muted);">
                  <i data-lucide="calendar-x" style="width:32px;height:32px;display:block;margin:0 auto 8px;"></i>
                  No hay sesiones registradas aún.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $sessions->appends(request()->query())->links() }}</div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
