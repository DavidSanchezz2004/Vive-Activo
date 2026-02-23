@extends('layouts.dashboard')

@section('title', 'Rutina - Supervisor')

@section('content')
  <div class="d-topbar" style="margin-bottom:16px;">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
        <a href="{{ route('supervisor.pacientes.show', $patient) }}" style="color:var(--d-brand);">{{ $patient->user?->name }}</a>
        › Rutina
      </div>
      <h1 class="d-page-title">🏋️ {{ $routine->title ?? 'Rutina' }}</h1>
      <div style="margin-top:6px;color:var(--d-muted);font-size:13px;">
        Vigencia: {{ $routine->valid_from?->format('d/m/Y') }}
        @if($routine->valid_until) – {{ $routine->valid_until->format('d/m/Y') }} @endif
        @if($routine->is_active)
          · <span class="d-badge d-badge-green">Activa</span>
        @else
          · <span class="d-badge" style="background:var(--d-bg);color:var(--d-muted);border:1px solid var(--d-border);">Archivada</span>
        @endif
      </div>
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      @if($routine->is_active)
        <form action="{{ route('supervisor.pacientes.routines.deactivate', [$patient, $routine]) }}" method="POST">
          @csrf
          @method('PATCH')
          <button type="submit" class="d-btn d-btn-outline">Archivar</button>
        </form>
      @endif

      <form action="{{ route('supervisor.pacientes.routines.destroy', [$patient, $routine]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta rutina?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="d-btn d-btn-danger">Eliminar</button>
      </form>

      <a href="{{ route('supervisor.pacientes.routines.create', $patient) }}" class="d-btn d-btn-primary">Nueva rutina</a>
    </div>
  </div>

  @if (session('success'))
    <div class="d-card" style="margin-bottom:14px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="d-card">
    @if($routine->goal)
      <div class="d-badge d-badge-blue" style="margin-bottom:10px;">🎯 {{ $routine->goal }}</div>
    @endif

    @if($routine->items->isEmpty())
      <div style="opacity:.7;">Sin ejercicios registrados.</div>
    @else
      @php $grouped = $routine->items->groupBy('day'); @endphp
      <div style="display:grid;gap:12px;">
        @foreach($days as $key => $label)
          @php $items = $grouped->get($key, collect()); @endphp
          @if($items->isEmpty())
            @continue
          @endif
          <div style="border:1px solid var(--d-border);border-radius:14px;overflow:hidden;">
            <div style="background:var(--d-bg);padding:10px 14px;font-weight:800;color:var(--d-text);">{{ $label }}</div>
            <div style="padding:12px 14px;display:grid;gap:10px;">
              @foreach($items as $it)
                <div style="border:1px solid var(--d-border);border-radius:12px;padding:10px 12px;background:var(--d-surface);">
                  <div style="font-weight:800;color:var(--d-text);">{{ $it->exercise_name }}</div>
                  <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                    @if($it->sets) {{ $it->sets }} series @endif
                    @if($it->reps) · {{ $it->reps }} reps @endif
                    @if($it->rest_seconds !== null) · Descanso {{ $it->rest_seconds }}s @endif
                  </div>
                  @if($it->notes)
                    <div style="font-size:12px;color:var(--d-text);opacity:.8;margin-top:6px;">{{ $it->notes }}</div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @endif

    @if($routine->notes)
      <div style="margin-top:16px;">
        <div style="font-weight:800;color:var(--d-text);margin-bottom:6px;">Notas</div>
        <div style="white-space:pre-wrap;color:var(--d-text);opacity:.85;">{{ $routine->notes }}</div>
      </div>
    @endif
  </div>
@endsection
