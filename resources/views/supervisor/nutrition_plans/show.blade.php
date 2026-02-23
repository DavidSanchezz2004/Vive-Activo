@extends('layouts.dashboard')

@section('title', 'Plan Nutricional - Supervisor')

@section('content')
  <div class="d-topbar" style="margin-bottom:16px;">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
        <a href="{{ route('supervisor.pacientes.show', $patient) }}" style="color:var(--d-brand);">{{ $patient->user?->name }}</a>
        › Plan nutricional
      </div>
      <h1 class="d-page-title">🥗 {{ $plan->phase ?? 'Plan Nutricional' }}</h1>
      <div style="margin-top:6px;color:var(--d-muted);font-size:13px;">
        Vigencia: {{ $plan->valid_from?->format('d/m/Y') }}
        @if($plan->valid_until) – {{ $plan->valid_until->format('d/m/Y') }} @endif
        @if($plan->is_active)
          · <span class="d-badge d-badge-green">Activo</span>
        @else
          · <span class="d-badge" style="background:var(--d-bg);color:var(--d-muted);border:1px solid var(--d-border);">Archivado</span>
        @endif
      </div>
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      @if($plan->is_active)
        <form action="{{ route('supervisor.pacientes.nutrition_plans.deactivate', [$patient, $plan]) }}" method="POST">
          @csrf
          @method('PATCH')
          <button type="submit" class="d-btn d-btn-outline">Archivar</button>
        </form>
      @endif

      <form action="{{ route('supervisor.pacientes.nutrition_plans.destroy', [$patient, $plan]) }}" method="POST" onsubmit="return confirm('¿Eliminar este plan?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="d-btn d-btn-danger">Eliminar</button>
      </form>

      <a href="{{ route('supervisor.pacientes.nutrition_plans.create', $patient) }}" class="d-btn d-btn-primary">Nuevo plan</a>
    </div>
  </div>

  @if (session('success'))
    <div class="d-card" style="margin-bottom:14px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="d-grid" style="grid-template-columns:1fr;">
    <div class="d-card">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        @if($plan->goal)
          <div class="d-badge d-badge-blue">🎯 {{ $plan->goal }}</div>
        @endif
        @if($plan->kcal_target)
          <div class="d-badge" style="background:var(--d-bg);color:var(--d-text);border:1px solid var(--d-border);">{{ number_format($plan->kcal_target) }} kcal/día</div>
        @endif
        @if($plan->protein_g)
          <div class="d-badge" style="background:var(--d-bg);color:var(--d-text);border:1px solid var(--d-border);">P {{ $plan->protein_g }}g</div>
        @endif
        @if($plan->carbs_g)
          <div class="d-badge" style="background:var(--d-bg);color:var(--d-text);border:1px solid var(--d-border);">C {{ $plan->carbs_g }}g</div>
        @endif
        @if($plan->fat_g)
          <div class="d-badge" style="background:var(--d-bg);color:var(--d-text);border:1px solid var(--d-border);">G {{ $plan->fat_g }}g</div>
        @endif
      </div>

      @if($plan->pdf_path)
        <div style="margin-top:12px;color:var(--d-muted);font-size:13px;">
          PDF adjunto: <span style="color:var(--d-text);">{{ basename($plan->pdf_path) }}</span>
          <span style="opacity:.8;">(almacenado en servidor)</span>
        </div>
      @endif

      @if($plan->items->isEmpty())
        <div style="margin-top:16px;opacity:.7;">Sin ítems registrados.</div>
      @else
        <div style="margin-top:16px;display:grid;gap:12px;">
          @php $grouped = $plan->items->groupBy('meal_time'); @endphp
          @foreach(\App\Models\NutritionPlan::mealTimes() as $key => $label)
            @php $items = $grouped->get($key, collect()); @endphp
            @if($items->isEmpty())
              @continue
            @endif
            <div style="border:1px solid var(--d-border);border-radius:14px;overflow:hidden;">
              <div style="background:var(--d-bg);padding:10px 14px;font-weight:800;color:var(--d-text);">{{ $label }}</div>
              <div style="padding:12px 14px;display:grid;gap:10px;">
                @foreach($items as $it)
                  <div style="border:1px solid var(--d-border);border-radius:12px;padding:10px 12px;background:var(--d-surface);">
                    <div style="font-weight:700;color:var(--d-text);">{{ $it->food_name }}</div>
                    <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                      @if($it->quantity) {{ $it->quantity }} @endif
                      @if($it->kcal) · {{ $it->kcal }} kcal @endif
                      @if($it->protein_g) · P {{ $it->protein_g }}g @endif
                      @if($it->carbs_g) · C {{ $it->carbs_g }}g @endif
                      @if($it->fat_g) · G {{ $it->fat_g }}g @endif
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

      @if($plan->notes)
        <div style="margin-top:16px;">
          <div style="font-weight:800;color:var(--d-text);margin-bottom:6px;">Notas</div>
          <div style="white-space:pre-wrap;color:var(--d-text);opacity:.85;">{{ $plan->notes }}</div>
        </div>
      @endif
    </div>
  </div>
@endsection
