@extends('layouts.dashboard')

@section('title', 'Pacientes - Vive Activo')
@section('page_title', 'Directorio de Pacientes')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- Alerta --}}
    @if(session('ok'))
      <div class="mb-4" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;font-size:14px;font-weight:500;">
        {{ session('ok') }}
      </div>
    @endif

    {{-- KPIs --}}
    <section class="d-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));margin-bottom:24px;">
      <article class="d-card kpi-card-admin kpi-users">
        <span class="d-kpi-label">Total Pacientes</span>
        <strong class="d-kpi-value">{{ $kpis['total'] }}</strong>
        <span class="kpi-foot">Registrados en el sistema</span>
      </article>
      <article class="d-card kpi-card-admin kpi-clients">
        <span class="d-kpi-label">Activos</span>
        <strong class="d-kpi-value">{{ $kpis['activos'] }}</strong>
        <span class="kpi-foot">Con cuenta habilitada</span>
      </article>
      <article class="d-card kpi-card-admin kpi-supervisors">
        <span class="d-kpi-label">Con alumno asignado</span>
        <strong class="d-kpi-value">{{ $kpis['asignados'] }}</strong>
        <span class="kpi-foot">Asignación activa</span>
      </article>
      <article class="d-card kpi-card-admin kpi-admins">
        <span class="d-kpi-label">Sin asignar</span>
        <strong class="d-kpi-value">{{ $kpis['sin_asignar'] }}</strong>
        <span class="kpi-foot">Pendientes de asignación</span>
      </article>
    </section>

    {{-- Filtros --}}
    <section class="d-card mb-4">
      <form method="GET" action="{{ route('admin.pacientes.index') }}">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end;">

          <div>
            <label class="d-label" for="q">Buscar</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input id="q" name="q" type="search" class="d-input" placeholder="Nombre, email o DNI" value="{{ request('q') }}">
            </div>
          </div>

          <div>
            <label class="d-label" for="distrito">Distrito</label>
            <input id="distrito" name="distrito" type="text" class="d-input" placeholder="Ej: Miraflores" value="{{ request('distrito') }}">
          </div>

          <div>
            <label class="d-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="d-select">
              <option value="">Todos</option>
              <option value="activo"   @selected(request('estado')==='activo')>Activo</option>
              <option value="inactivo" @selected(request('estado')==='inactivo')>Inactivo</option>
            </select>
          </div>

          <div>
            <label class="d-label" for="asignacion">Asignación</label>
            <select id="asignacion" name="asignacion" class="d-select">
              <option value="">Todos</option>
              <option value="con"  @selected(request('asignacion')==='con')>Con alumno</option>
              <option value="sin"  @selected(request('asignacion')==='sin')>Sin alumno</option>
            </select>
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route('admin.pacientes.index') }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </div>
      </form>
    </section>

    {{-- Tabla --}}
    <section class="d-card mb-4">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:18px;">Listado de Pacientes</h3>
        <a href="{{ route('admin.users.create') }}" class="d-btn d-btn-primary">
          <i data-lucide="plus"></i> Nuevo Paciente
        </a>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:900px;">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Documento</th>
              <th>Distrito</th>
              <th>Alumno asignado</th>
              <th style="text-align:center;">Estado</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($patients as $patient)
              @php
                $user      = $patient->user;
                $profile   = $user?->profile;
                $assignment = $patient->activeAssignment;
                $initials  = collect(explode(' ', trim($user->name ?? 'P')))
                               ->filter()->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('');
              @endphp
              <tr>
                {{-- Paciente --}}
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ $initials ?: 'P' }}</div>
                    <div>
                      <div class="user-name">{{ $user->name ?? '—' }}</div>
                      <div class="user-email">{{ $user->email ?? '—' }}</div>
                    </div>
                  </div>
                </td>

                {{-- Documento --}}
                <td>
                  @if($profile?->document_number)
                    <div style="font-size:12px;color:var(--d-muted);font-weight:500;text-transform:uppercase;">
                      {{ $profile->document_type ?? 'DNI' }}
                    </div>
                    <div style="font-weight:600;">{{ $profile->document_number }}</div>
                  @else
                    <span style="color:var(--d-muted);font-size:13px;">—</span>
                  @endif
                </td>

                {{-- Distrito --}}
                <td>{{ $profile?->district ?? '—' }}</td>

                {{-- Alumno asignado --}}
                <td>
                  @if($assignment)
                    <div class="user-cell">
                      <div class="d-avatar avatar-mini" style="background:var(--d-info);font-size:10px;">
                        {{ mb_substr($assignment->student?->user?->name ?? 'A', 0, 1) }}
                      </div>
                      <div>
                        <div style="font-size:13px;font-weight:600;">{{ $assignment->student?->user?->name ?? '—' }}</div>
                        <div class="user-email">Desde {{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}</div>
                      </div>
                    </div>
                  @else
                    <span class="d-badge d-badge-yellow">Sin asignar</span>
                  @endif
                </td>

                {{-- Estado --}}
                <td style="text-align:center;">
                  <span class="d-badge {{ $patient->is_active ? 'd-badge-green' : 'd-badge-red' }}">
                    {{ $patient->is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>

                {{-- Acciones --}}
                <td>
                  <div class="row-actions" style="justify-content:center;">
                    <a href="{{ route('admin.pacientes.show', $patient) }}"
                       class="d-btn d-btn-outline action-btn" title="Ver detalle">
                      <i data-lucide="eye"></i>
                    </a>
                    <form action="{{ route('admin.pacientes.toggle', $patient) }}" method="POST">
                      @csrf @method('PATCH')
                      <button type="submit"
                        class="d-btn action-btn {{ $patient->is_active ? 'd-btn-danger' : 'd-btn-outline' }}"
                        title="{{ $patient->is_active ? 'Desactivar' : 'Activar' }}">
                        <i data-lucide="{{ $patient->is_active ? 'user-x' : 'user-check' }}"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="padding:32px;text-align:center;color:var(--d-muted);">
                  <i data-lucide="users" style="width:32px;height:32px;margin-bottom:8px;display:block;margin-left:auto;margin-right:auto;"></i>
                  No hay pacientes registrados aún.
                  <br>
                  <a href="{{ route('admin.users.create') }}" class="d-btn d-btn-primary" style="margin-top:12px;">
                    Crear primer paciente
                  </a>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $patients->appends(request()->query())->links() }}
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
