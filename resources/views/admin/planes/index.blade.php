@extends('layouts.dashboard')

@section('title', 'Planes - Vive Activo')
@section('page_title', 'Planes')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    @if(session('ok'))
      <div class="mb-4" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;font-size:14px;font-weight:500;">
        {{ session('ok') }}
      </div>
    @endif

    <section class="d-card mb-4">
      <form method="GET" action="{{ route('admin.planes.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:12px;align-items:end;">
        <div>
          <label class="d-label" for="q">Buscar</label>
          <div class="input-icon-wrap">
            <i data-lucide="search" class="input-icon"></i>
            <input id="q" name="q" type="search" class="d-input" placeholder="Nombre o slug" value="{{ request('q') }}">
          </div>
        </div>
        <div class="filter-actions">
          <button type="submit" class="d-btn d-btn-primary">Buscar</button>
          <a href="{{ route('admin.planes.index') }}" class="d-btn d-btn-outline">Limpiar</a>
        </div>
      </form>
    </section>

    <section class="d-card">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:18px;">Catálogo de planes</h3>
        <a href="{{ route('admin.planes.create') }}" class="d-btn d-btn-primary">
          <i data-lucide="plus"></i> Nuevo plan
        </a>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:900px;">
          <thead>
            <tr>
              <th>Plan</th>
              <th>Slug</th>
              <th style="text-align:center;">Sesiones</th>
              <th style="text-align:center;">Duración</th>
              <th style="text-align:center;">Precio</th>
              <th style="text-align:center;">Estado</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($plans as $plan)
              <tr>
                <td>
                  <div style="font-weight:700;">{{ $plan->name }}</div>
                  @if($plan->description)
                    <div style="font-size:12px;color:var(--d-muted);">{{ Str::limit($plan->description, 80) }}</div>
                  @endif
                </td>
                <td><span style="font-size:12px;color:var(--d-muted);">{{ $plan->slug }}</span></td>
                <td style="text-align:center;">{{ $plan->sessions_total === 0 ? '∞' : $plan->sessions_total }}</td>
                <td style="text-align:center;">{{ $plan->duration_months }} мес</td>
                <td style="text-align:center;">{{ $plan->currency }} {{ number_format((float)$plan->price, 2) }}</td>
                <td style="text-align:center;">
                  <span class="d-badge {{ $plan->is_active ? 'd-badge-green' : 'd-badge-red' }}">
                    {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td style="text-align:center;">
                  <div class="row-actions" style="justify-content:center;">
                    <a href="{{ route('admin.planes.edit', $plan) }}" class="d-btn d-btn-outline action-btn" title="Editar">
                      <i data-lucide="pencil"></i>
                    </a>
                    <form action="{{ route('admin.planes.destroy', $plan) }}" method="POST" onsubmit="return confirm('¿Eliminar el plan {{ $plan->name }}?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="d-btn d-btn-danger action-btn" title="Eliminar">
                        <i data-lucide="trash-2"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" style="padding:16px;opacity:.8;">Sin planes registrados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $plans->appends(request()->query())->links() }}
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush
