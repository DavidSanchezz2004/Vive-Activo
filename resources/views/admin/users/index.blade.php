@extends('layouts.dashboard')

@section('title', 'Usuarios - Vive Activo')
@section('page_title', 'Gestión de Usuarios')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
  <div class="d-content">
    <div class="d-container">

      {{-- KPIs --}}
      <section class="d-grid users-kpis">
        <article class="d-card kpi-card-admin kpi-users">
          <span class="d-kpi-label">Total de usuarios</span>
          <strong class="d-kpi-value">{{ $kpis['total'] ?? 0 }}</strong>
          <span class="kpi-foot">Activos en plataforma</span>
        </article>

        <article class="d-card kpi-card-admin kpi-admins">
          <span class="d-kpi-label">Administradores</span>
          <strong class="d-kpi-value">{{ $kpis['admins'] ?? 0 }}</strong>
          <span class="kpi-foot">Mínimo requerido: 1</span>
        </article>

        <article class="d-card kpi-card-admin kpi-supervisors">
          <span class="d-kpi-label">Supervisores</span>
          <strong class="d-kpi-value">{{ $kpis['supervisors'] ?? 0 }}</strong>
          <span class="kpi-foot">Control operativo</span>
        </article>

        <article class="d-card kpi-card-admin kpi-clients">
          <span class="d-kpi-label">Alumnos + Pacientes</span>
          <strong class="d-kpi-value">{{ $kpis['others'] ?? 0 }}</strong>
          <span class="kpi-foot">Usuarios de atención</span>
        </article>
      </section>

      {{-- Alertas --}}
      @if (session('success'))
        <div class="d-alert d-alert-success mb-4">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="d-alert d-alert-danger mb-4">{{ session('error') }}</div>
      @endif

      {{-- Filtros --}}
      <section class="d-card mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-grid">
          <div>
            <label class="d-label" for="q">Buscar usuario</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input
                id="q"
                name="q"
                type="search"
                class="d-input"
                placeholder="Nombre o correo"
                value="{{ request('q') }}"
              >
            </div>
          </div>

          <div>
            <label class="d-label" for="role">Rol</label>
            <select id="role" name="role" class="d-select">
              <option value="">Todos</option>
              @php $role = request('role'); @endphp
              <option value="admin"      @selected($role==='admin')>admin</option>
              <option value="supervisor" @selected($role==='supervisor')>supervisor</option>
              <option value="student"    @selected($role==='student')>estudiante</option>
              <option value="patient"    @selected($role==='patient')>paciente</option>
            </select>
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route('admin.users.index') }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </form>
      </section>

      {{-- Tabla --}}
      <section class="d-card mb-4">
        <div class="flex-between mb-4 users-table-header">
          <h3>Listado de Usuarios</h3>
          <div class="table-header-actions">
            <a href="{{ route('admin.users.create') }}" class="d-btn d-btn-primary">
              <i data-lucide="plus"></i>
              Nuevo Usuario
            </a>
          </div>
        </div>

        <div class="d-table-wrapper">
          <table class="d-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              @forelse($users as $u)
                @php
                  $initials = collect(explode(' ', trim($u->name ?? 'U')))
                    ->filter()
                    ->take(2)
                    ->map(fn($w) => mb_substr($w,0,1))
                    ->implode('');
                  $role = $u->role ?? 'miembro';
                  $isActive = (bool)($u->is_active ?? true);

                  $roleBadge = match($role) {
                    'admin'      => 'd-badge-green',
                    'supervisor' => 'd-badge-yellow',
                    'student' => 'd-badge-blue',
                    'patient'   => 'd-badge-blue',
                    default      => 'd-badge-blue',
                  };

                  $statusBadge = $isActive ? 'd-badge-blue' : 'd-badge-danger';
                  $statusText  = $isActive ? 'Activo' : 'Inactivo';
                @endphp

                <tr>
                  <td>#{{ $u->id }}</td>
                  <td>
                    <div class="user-cell">
                      <div class="d-avatar avatar-mini">{{ $initials ?: 'U' }}</div>
                      <div>
                        <div class="user-name">{{ $u->name }}</div>
                        <div class="user-email">{{ $u->email }}</div>
                      </div>
                    </div>
                  </td>
                  <td><span class="d-badge {{ $roleBadge }}">{{ $role }}</span></td>
                  <td><span class="d-badge {{ $statusBadge }}">{{ $statusText }}</span></td>
                  <td>
                    <div class="row-actions">
                      <a href="{{ route('admin.users.edit', $u) }}" class="d-btn d-btn-outline action-btn" title="Editar">
                        <i data-lucide="pencil"></i>
                      </a>

                      <form action="{{ route('admin.users.destroy', $u) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar usuario {{ $u->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="d-btn d-btn-danger action-btn" title="Eliminar">
                          <i data-lucide="trash-2"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" style="padding:16px; opacity:.8;">No hay usuarios para mostrar.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-3">
          {{ $users->appends(request()->query())->links() }}
        </div>
      </section>

    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush