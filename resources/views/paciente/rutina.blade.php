@extends('layouts.dashboard')

@section('title', 'Mi Rutina - Vive Activo')
@section('page_title', 'Mi Rutina')

@section('content')
  <div class="d-content">
    <div class="d-container">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--d-text);">🏋️ Mi Rutina</h3>

      @if(!$routine)
        <div class="d-card" style="text-align:center;padding:32px;">
          <p style="margin:0;color:var(--d-muted);font-size:14px;">Aún no tienes una rutina activa asignada.</p>
        </div>
      @else
        <div class="d-card" style="margin-bottom:14px;">
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
            <div>
              <div style="font-size:18px;font-weight:800;color:var(--d-text);">{{ $routine->title ?? 'Rutina' }}</div>
              <div style="font-size:13px;color:var(--d-muted);margin-top:4px;">
                Vigencia: {{ $routine->valid_from?->format('d/m/Y') }}
                @if($routine->valid_until) – {{ $routine->valid_until->format('d/m/Y') }} @endif
              </div>
              @if($routine->goal)
                <div style="font-size:13px;color:var(--d-text);opacity:.85;margin-top:6px;">🎯 {{ $routine->goal }}</div>
              @endif
            </div>
            <span class="d-badge d-badge-green">Activa</span>
          </div>
        </div>

        <div class="d-card">
          @if($routine->items->isEmpty())
            <p style="margin:0;color:var(--d-muted);text-align:center;font-size:14px;">Sin ejercicios registrados.</p>
          @else
            @php $days = \App\Models\Routine::days(); $grouped = $routine->items->groupBy('day'); @endphp
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
      @endif
    </div>
  </div>
@endsection
