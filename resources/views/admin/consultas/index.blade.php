@extends('layouts.dashboard')

@section('title', 'Consultas - Vive Activo')
@section('page_title', 'Consultas')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
  <style>
    .mode-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
    .mode-presencial { background:rgba(16,185,129,.15); color:#059669; }
    .mode-zoom       { background:rgba(59,130,246,.15);  color:#2563eb; }
    .mode-meet       { background:rgba(234,179,8,.15);   color:#d97706; }
    .status-pending_confirmation { background:rgba(245,158,11,.15); color:#b45309; }
    .status-confirmed            { background:rgba(16,185,129,.15); color:#059669; }
    .status-completed            { background:rgba(100,116,139,.15); color:#475569; }
    .status-cancelled            { background:rgba(239,68,68,.15);  color:#dc2626; }
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
      <form method="GET" action="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.index' : 'supervisor.consultas.index') }}">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end;">

          <div>
            <label class="d-label" for="q">Buscar paciente</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input id="q" name="q" type="search" class="d-input" placeholder="Nombre o email" value="{{ request('q') }}">
            </div>
          </div>

          <div>
            <label class="d-label" for="modo">Modo</label>
            <select id="modo" name="modo" class="d-select">
              <option value="">Todos</option>
              <option value="presencial" @selected(request('modo')==='presencial')>Presencial</option>
              <option value="zoom"       @selected(request('modo')==='zoom')>Zoom</option>
              <option value="meet"       @selected(request('modo')==='meet')>Google Meet</option>
            </select>
          </div>

          <div>
            <label class="d-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="d-select">
              <option value="">Todos</option>
              <option value="pending_confirmation" @selected(request('estado')==='pending_confirmation')>Pendiente</option>
              <option value="confirmed"            @selected(request('estado')==='confirmed')>Confirmada</option>
              <option value="completed"            @selected(request('estado')==='completed')>Completada</option>
              <option value="cancelled"            @selected(request('estado')==='cancelled')>Cancelada</option>
            </select>
          </div>

          <div>
            <label class="d-label" for="fecha">Fecha</label>
            <input id="fecha" name="fecha" type="date" class="d-input" value="{{ request('fecha') }}">
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.index' : 'supervisor.consultas.index') }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </div>
      </form>
    </section>

    {{-- Tabla --}}
    <section class="d-card">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:18px;">Listado de Consultas</h3>
        <a href="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.create' : 'supervisor.consultas.create') }}" class="d-btn d-btn-primary">
          <i data-lucide="plus"></i> Nueva Consulta
        </a>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:900px;">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Alumno</th>
              <th style="text-align:center;">Modo</th>
              <th>Fecha programada</th>
              <th style="text-align:center;">Estado</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($consultations as $c)
              @php
                $prefix = request()->routeIs('admin.*') ? 'admin' : 'supervisor';
                $initials = collect(explode(' ', trim($c->patient?->user?->name ?? 'P')))->filter()->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode('');
              @endphp
              <tr>
                {{-- Paciente --}}
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ $initials ?: 'P' }}</div>
                    <div>
                      <div class="user-name">{{ $c->patient?->user?->name ?? '—' }}</div>
                      <div class="user-email">{{ $c->scheduled_at?->format('d/m/Y') ?? 'Sin fecha' }}</div>
                    </div>
                  </div>
                </td>

                {{-- Alumno --}}
                <td>{{ $c->student?->user?->name ?? '—' }}</td>

                {{-- Modo --}}
                <td style="text-align:center;">
                  @php $modeIcons = ['presencial'=>'🏥','zoom'=>'🎥','meet'=>'📹']; @endphp
                  <span class="mode-badge mode-{{ $c->mode }}">
                    {{ $modeIcons[$c->mode] ?? '' }} {{ $c->modeLabel() }}
                  </span>
                </td>

                {{-- Fecha --}}
                <td>{{ $c->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</td>

                {{-- Estado con form inline --}}
                <td style="text-align:center;">
                  <form action="{{ route("{$prefix}.consultas.status", $c) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                      class="d-select status-badge status-{{ $c->status }}"
                      style="padding:3px 8px;font-size:12px;font-weight:600;border-radius:20px;border:none;cursor:pointer;">
                      @foreach(\App\Models\Consultation::STATUSES as $val => $label)
                        <option value="{{ $val }}" @selected($c->status === $val)>{{ $label }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>

                {{-- Acciones --}}
                <td>
                  <div class="row-actions" style="justify-content:center;">
                    <a href="{{ route("{$prefix}.consultas.edit", $c) }}" class="d-btn d-btn-outline action-btn" title="Editar">
                      <i data-lucide="pencil"></i>
                    </a>
                    @if($c->status === 'pending_confirmation')
                      <form action="{{ route("{$prefix}.consultas.destroy", $c) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar esta consulta?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="d-btn d-btn-danger action-btn" title="Eliminar">
                          <i data-lucide="trash-2"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="padding:32px;text-align:center;color:var(--d-muted);">
                  <i data-lucide="calendar-x" style="width:32px;height:32px;display:block;margin:0 auto 8px;"></i>
                  No hay consultas registradas aún.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">{{ $consultations->appends(request()->query())->links() }}</div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
