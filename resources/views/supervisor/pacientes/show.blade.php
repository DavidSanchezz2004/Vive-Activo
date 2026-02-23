@extends('layouts.dashboard')

@section('title', 'Paciente - Supervisor')

@section('content')
  <div class="d-topbar" style="margin-bottom:16px;">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
        <a href="{{ route('supervisor.pacientes') }}" style="color:var(--d-brand);">Pacientes</a> › {{ $patient->user?->name }}
      </div>
      <h1 class="d-page-title">🧍 {{ $patient->user?->name }}</h1>
      <div style="font-size:13px;color:var(--d-muted);margin-top:4px;">{{ $patient->user?->email }}</div>
      <div style="font-size:13px;color:var(--d-muted);margin-top:4px;">
        Alumno asignado: <strong style="color:var(--d-text);">{{ $patient->activeAssignment?->student?->user?->name ?? '—' }}</strong>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="d-card" style="margin-bottom:14px;">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="d-card" style="margin-bottom:14px;border-color:#fecaca;">
      {{ session('error') }}
    </div>
  @endif

  <div class="d-grid" style="grid-template-columns:1fr;">
    <div class="d-card">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
          <div style="font-weight:800;font-size:16px;color:var(--d-text);">🥗 Plan Nutricional</div>
          <div style="font-size:13px;color:var(--d-muted);">Crear, ver y archivar planes nutricionales.</div>
        </div>
        <a href="{{ route('supervisor.pacientes.nutrition_plans.create', $patient) }}" class="d-btn d-btn-primary" style="font-size:13px;">
          Nuevo plan
        </a>
      </div>

      <div style="margin-top:14px;">
        @if($nutritionPlans->isEmpty())
          <div style="opacity:.7;color:var(--d-text);">Sin planes nutricionales aún.</div>
        @else
          <div style="display:grid;gap:10px;">
            @foreach($nutritionPlans as $np)
              <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid var(--d-border);border-radius:12px;padding:12px 14px;">
                <div>
                  <div style="font-weight:800;color:var(--d-text);">
                    {{ $np->phase ?? 'Plan Nutricional' }}
                    @if($np->is_active)
                      <span class="d-badge d-badge-green" style="margin-left:8px;">Activo</span>
                    @else
                      <span class="d-badge" style="margin-left:8px;background:var(--d-bg);color:var(--d-muted);border:1px solid var(--d-border);">Archivado</span>
                    @endif
                  </div>
                  <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                    {{ $np->valid_from?->format('d/m/Y') }}
                    @if($np->valid_until) – {{ $np->valid_until->format('d/m/Y') }} @endif
                    · {{ $np->items_count }} ítems
                  </div>
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                  <a href="{{ route('supervisor.pacientes.nutrition_plans.show', [$patient, $np]) }}" class="d-btn d-btn-outline" style="font-size:13px;padding:6px 12px;">Ver</a>

                  @if($np->is_active)
                    <form method="POST" action="{{ route('supervisor.pacientes.nutrition_plans.deactivate', [$patient, $np]) }}">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="d-btn d-btn-outline" style="font-size:13px;padding:6px 12px;">Archivar</button>
                    </form>
                  @endif

                  <form method="POST" action="{{ route('supervisor.pacientes.nutrition_plans.destroy', [$patient, $np]) }}" onsubmit="return confirm('¿Eliminar este plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="d-btn d-btn-danger" style="font-size:13px;padding:6px 12px;">Eliminar</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div class="d-card">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
          <div style="font-weight:800;font-size:16px;color:var(--d-text);">🏋️ Rutinas</div>
          <div style="font-size:13px;color:var(--d-muted);">Asignar y administrar rutinas de entrenamiento.</div>
        </div>
        <a href="{{ route('supervisor.pacientes.routines.create', $patient) }}" class="d-btn d-btn-primary" style="font-size:13px;">
          Nueva rutina
        </a>
      </div>

      <div style="margin-top:14px;">
        @if($routines->isEmpty())
          <div style="opacity:.7;color:var(--d-text);">Sin rutinas aún.</div>
        @else
          <div style="display:grid;gap:10px;">
            @foreach($routines as $rt)
              <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid var(--d-border);border-radius:12px;padding:12px 14px;">
                <div>
                  <div style="font-weight:800;color:var(--d-text);">
                    {{ $rt->title ?? 'Rutina' }}
                    @if($rt->is_active)
                      <span class="d-badge d-badge-green" style="margin-left:8px;">Activa</span>
                    @else
                      <span class="d-badge" style="margin-left:8px;background:var(--d-bg);color:var(--d-muted);border:1px solid var(--d-border);">Archivada</span>
                    @endif
                  </div>
                  <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                    {{ $rt->valid_from?->format('d/m/Y') }}
                    @if($rt->valid_until) – {{ $rt->valid_until->format('d/m/Y') }} @endif
                    · {{ $rt->items_count }} ejercicios
                  </div>
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                  <a href="{{ route('supervisor.pacientes.routines.show', [$patient, $rt]) }}" class="d-btn d-btn-outline" style="font-size:13px;padding:6px 12px;">Ver</a>

                  @if($rt->is_active)
                    <form method="POST" action="{{ route('supervisor.pacientes.routines.deactivate', [$patient, $rt]) }}">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="d-btn d-btn-outline" style="font-size:13px;padding:6px 12px;">Archivar</button>
                    </form>
                  @endif

                  <form method="POST" action="{{ route('supervisor.pacientes.routines.destroy', [$patient, $rt]) }}" onsubmit="return confirm('¿Eliminar esta rutina?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="d-btn d-btn-danger" style="font-size:13px;padding:6px 12px;">Eliminar</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
