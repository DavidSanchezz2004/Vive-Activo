@extends('layouts.dashboard')

@section('title', 'Mi Plan - Vive Activo')
@section('page_title', 'Mi plan')

@push('styles')
<style>
  .plan-comercial { background:linear-gradient(135deg,#2563eb,#7c3aed); border-radius:16px; padding:24px; color:#fff; margin-bottom:24px; }
  .plan-comercial .pc-title { font-size:20px; font-weight:800; margin-bottom:4px; }
  .plan-comercial .pc-meta  { font-size:13px; opacity:.8; }
  .pc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:16px; }
  .pc-stat  { background:rgba(255,255,255,.15); border-radius:12px; padding:12px; text-align:center; }
  .pc-stat-num { font-size:22px; font-weight:800; }
  .pc-stat-lbl { font-size:11px; opacity:.8; margin-top:2px; }

  .np-header { background:linear-gradient(135deg,#059669,#10b981); border-radius:16px; padding:20px 24px; color:#fff; margin-bottom:20px; }
  .macro-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-top:14px; }
  .macro-item { background:rgba(255,255,255,.18); border-radius:10px; padding:10px; text-align:center; }
  .macro-num  { font-size:18px; font-weight:800; }
  .macro-lbl  { font-size:10px; opacity:.85; }

  .meal-group { margin-bottom:20px; }
  .meal-title { font-size:13px; font-weight:700; padding:8px 14px; background:var(--d-bg); border-radius:10px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; color: var(--d-text); }
  .meal-title span { color: var(--d-text); }
  .food-item  { display:flex; align-items:center; gap:12px; padding:10px 14px; background:var(--d-card); border:1px solid var(--d-border); border-radius:10px; margin-bottom:6px; color: var(--d-text); }
  .food-item div { color: var(--d-text); }
  .food-macros { display:flex; gap:6px; flex-wrap:wrap; }
  .food-macro { font-size:11px; padding:2px 7px; border-radius:8px; background:var(--d-bg); border:1px solid var(--d-border); color: var(--d-text); }
  @media(max-width:600px){ .macro-grid{grid-template-columns:repeat(2,1fr);} .pc-stats{grid-template-columns:1fr 1fr;} }
</style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- ────── PLAN COMERCIAL ────── --}}
    @if($activePlan)
      @php $plan = $activePlan->plan; $rem = $activePlan->sessionsRemaining(); @endphp
      <div class="plan-comercial">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
          <div>
            <div class="pc-title">{{ $plan->name }}</div>
            <div class="pc-meta">{{ $plan->description }}</div>
          </div>
          <span style="background:rgba(255,255,255,.2);padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">
            {{ $activePlan->statusLabel() }}
          </span>
        </div>
        <div class="pc-stats">
          <div class="pc-stat">
            <div class="pc-stat-num">{{ $activePlan->daysLeft() }}</div>
            <div class="pc-stat-lbl">Días restantes</div>
          </div>
          <div class="pc-stat">
            <div class="pc-stat-num">{{ $activePlan->sessions_used }}</div>
            <div class="pc-stat-lbl">Sesiones usadas</div>
          </div>
          <div class="pc-stat">
            <div class="pc-stat-num">{{ $rem === null ? '∞' : $rem }}</div>
            <div class="pc-stat-lbl">Sesiones restantes</div>
          </div>
        </div>
        <div style="font-size:12px;opacity:.7;margin-top:10px;">
          Vigente {{ $activePlan->starts_at->format('d/m/Y') }} – {{ $activePlan->ends_at->format('d/m/Y') }}
        </div>
      </div>
    @else
      <div class="d-card" style="text-align:center;padding:28px;margin-bottom:20px;">
        <div style="font-size:40px;margin-bottom:10px;">📋</div>
        <p style="margin:0;color:var(--d-muted);font-size:14px;">Aún no tienes un plan comercial activo.<br>Consulta a tu asesor para más información.</p>
      </div>
    @endif

    {{-- ────── PLAN NUTRICIONAL ────── --}}
    @if($nutritionPlan)
      <div class="np-header">
        <div style="font-size:17px;font-weight:800;">🥗 {{ $nutritionPlan->phase ?? 'Plan Nutricional' }}</div>
        @if($nutritionPlan->goal)
          <div style="font-size:13px;opacity:.85;margin-top:4px;">🎯 {{ $nutritionPlan->goal }}</div>
        @endif
        <div style="font-size:12px;opacity:.7;margin-top:6px;">
          📅 {{ $nutritionPlan->valid_from?->format('d/m/Y') }}
          @if($nutritionPlan->valid_until) – {{ $nutritionPlan->valid_until->format('d/m/Y') }} @endif
        </div>

        @if($nutritionPlan->kcal_target || $nutritionPlan->protein_g)
          <div class="macro-grid">
            @if($nutritionPlan->kcal_target)
              <div class="macro-item"><div class="macro-num">{{ number_format($nutritionPlan->kcal_target) }}</div><div class="macro-lbl">kcal/día</div></div>
            @endif
            @if($nutritionPlan->protein_g)
              <div class="macro-item"><div class="macro-num">{{ $nutritionPlan->protein_g }}g</div><div class="macro-lbl">Proteínas</div></div>
            @endif
            @if($nutritionPlan->carbs_g)
              <div class="macro-item"><div class="macro-num">{{ $nutritionPlan->carbs_g }}g</div><div class="macro-lbl">Carbohidratos</div></div>
            @endif
            @if($nutritionPlan->fat_g)
              <div class="macro-item"><div class="macro-num">{{ $nutritionPlan->fat_g }}g</div><div class="macro-lbl">Grasas</div></div>
            @endif
          </div>
        @endif
      </div>

      {{-- Alimentos por tiempo de comida --}}
      <div class="d-card">
        <h3 style="margin:0 0 18px;font-size:15px;font-weight:700;color:var(--d-text);">🍽️ Plan de alimentación</h3>

        @if($nutritionPlan->items->isEmpty())
          <p style="text-align:center;padding:24px;margin:0;color:var(--d-muted);font-size:14px;">El plan aún no tiene alimentos detallados.</p>
        @else
          @php $grouped = $nutritionPlan->items->groupBy('meal_time'); @endphp
          @foreach(\App\Models\NutritionPlan::mealTimes() as $key => $label)
            @if(isset($grouped[$key]))
              <div class="meal-group">
                <div class="meal-title">
                  <span>🍴 {{ $label }}</span>
                  <span style="font-size:11px;opacity:.6;color:var(--d-text);">{{ $grouped[$key]->sum('kcal') }} kcal</span>
                </div>
                @foreach($grouped[$key] as $item)
                  <div class="food-item">
                    <div style="flex:1;">
                      <div style="font-size:14px;font-weight:600;color:var(--d-text);">{{ $item->food_name }}</div>
                      @if($item->quantity)
                        <div style="font-size:12px;opacity:.7;color:var(--d-text);">{{ $item->quantity }}</div>
                      @endif
                      @if($item->notes)
                        <div style="font-size:12px;opacity:.6;font-style:italic;color:var(--d-text);">{{ $item->notes }}</div>
                      @endif
                    </div>
                    <div class="food-macros">
                      @if($item->kcal) <span class="food-macro">🔥 {{ $item->kcal }} kcal</span> @endif
                      @if($item->protein_g) <span class="food-macro">💪 {{ $item->protein_g }}g P</span> @endif
                      @if($item->carbs_g) <span class="food-macro">🌾 {{ $item->carbs_g }}g C</span> @endif
                      @if($item->fat_g) <span class="food-macro">🧈 {{ $item->fat_g }}g G</span> @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          @endforeach

          @if($nutritionPlan->totalKcal() > 0)
            <div style="background:var(--d-bg);border-radius:10px;padding:12px 16px;text-align:right;font-size:13px;color:var(--d-text);">
              <strong style="color:var(--d-text);">Total diario:</strong> <span style="color:var(--d-text);">{{ number_format($nutritionPlan->totalKcal()) }} kcal</span>
            </div>
          @endif
        @endif
      </div>

      @if($nutritionPlan->notes)
        <div class="d-card" style="margin-top:16px;">
          <div style="font-size:13px;font-weight:700;margin-bottom:8px;color:var(--d-text);">📝 Indicaciones</div>
          <p style="opacity:.8;font-size:14px;margin:0;white-space:pre-wrap;color:var(--d-text);">{{ $nutritionPlan->notes }}</p>
        </div>
      @endif
    @else
      <div class="d-card" style="text-align:center;padding:40px;">
        <div style="font-size:48px;margin-bottom:12px;">🥗</div>
        <p style="font-size:14px;margin:0;color:var(--d-muted);">Aún no tienes un plan nutricional asignado.<br>Tu alumno lo configurará próximamente.</p>
      </div>
    @endif

  </div>
</div>
@endsection
