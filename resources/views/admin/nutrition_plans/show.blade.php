@extends('layouts.dashboard')

@section('title', 'Plan Nutricional - Vive Activo')

@push('styles')
<style>
  .np-view-header { background:linear-gradient(135deg,#059669,#10b981); border-radius:16px; padding:22px 26px; color:#fff; margin-bottom:24px; }
  .macro-card { background:rgba(255,255,255,.18); border-radius:12px; padding:12px 16px; text-align:center; }
  .macro-num { font-size:22px; font-weight:800; }
  .macro-lbl { font-size:11px; opacity:.85; margin-top:2px; }
  .meal-block { margin-bottom:20px; }
  .meal-block-title { font-size:13px; font-weight:700; padding:8px 14px; background:var(--d-bg); border-radius:10px; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
  .food-row { display:flex; align-items:center; gap:12px; padding:10px 14px; background:var(--d-card); border:1px solid var(--d-border); border-radius:10px; margin-bottom:6px; }
  .food-macros { display:flex; gap:8px; flex-wrap:wrap; }
  .food-macro { font-size:11px; padding:2px 7px; border-radius:8px; background:var(--d-bg); border:1px solid var(--d-border); }
</style>
@endpush

@section('content')
<div class="d-topbar" style="margin-bottom:0;">
  <div style="font-size:13px;color:var(--d-muted);">
    <a href="{{ route('admin.pacientes.show', $patient) }}" style="color:var(--d-brand);">{{ $patient->user?->name }}</a>
    › Plan Nutricional #{{ $plan->id }}
  </div>
  <div style="display:flex;gap:8px;">
    @if($plan->is_active)
      <form action="{{ route('admin.pacientes.nutrition_plans.deactivate', [$patient, $plan]) }}" method="POST">
        @csrf @method('PATCH')
        <button type="submit" class="d-btn d-btn-outline" style="font-size:13px;" onclick="return confirm('¿Archivar este plan?')">
          <i data-lucide="archive" style="width:14px;"></i> Archivar
        </button>
      </form>
    @endif
    <form action="{{ route('admin.pacientes.nutrition_plans.destroy', [$patient, $plan]) }}" method="POST">
      @csrf @method('DELETE')
      <button type="submit" class="d-btn d-btn-outline" style="font-size:13px;color:var(--d-danger);border-color:var(--d-danger);" onclick="return confirm('¿Eliminar este plan definitivamente?')">
        <i data-lucide="trash-2" style="width:14px;"></i> Eliminar
      </button>
    </form>
    <a href="{{ route('admin.pacientes.nutrition_plans.create', $patient) }}" class="d-btn d-btn-primary" style="font-size:13px;">
      <i data-lucide="plus" style="width:14px;"></i> Nuevo plan
    </a>
  </div>
</div>

{{-- Header del plan --}}
<div class="np-view-header" style="margin-top:20px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:18px;font-weight:800;margin-bottom:4px;">
        {{ $plan->phase ?? 'Plan Nutricional' }}
        @if(!$plan->is_active)
          <span style="font-size:12px;background:rgba(0,0,0,.25);padding:2px 8px;border-radius:20px;margin-left:8px;">Archivado</span>
        @endif
      </div>
      @if($plan->goal)
        <div style="opacity:.85;font-size:14px;">🎯 {{ $plan->goal }}</div>
      @endif
      <div style="font-size:12px;opacity:.75;margin-top:6px;">
        📅 {{ $plan->valid_from?->format('d/m/Y') }}
        @if($plan->valid_until) – {{ $plan->valid_until->format('d/m/Y') }} @endif
        · Creado por {{ $plan->createdBy?->name ?? '—' }}
      </div>
    </div>
    @if($plan->pdf_path)
      <a href="#" class="d-btn" style="background:rgba(255,255,255,.2);color:#fff;font-size:13px;">
        <i data-lucide="file-text" style="width:14px;"></i> Descargar PDF
      </a>
    @endif
  </div>

  @if($plan->kcal_target || $plan->protein_g || $plan->carbs_g || $plan->fat_g)
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:16px;">
      @if($plan->kcal_target)
        <div class="macro-card">
          <div class="macro-num">{{ number_format($plan->kcal_target) }}</div>
          <div class="macro-lbl">kcal/día</div>
        </div>
      @endif
      @if($plan->protein_g)
        <div class="macro-card">
          <div class="macro-num">{{ $plan->protein_g }}g</div>
          <div class="macro-lbl">Proteínas</div>
        </div>
      @endif
      @if($plan->carbs_g)
        <div class="macro-card">
          <div class="macro-num">{{ $plan->carbs_g }}g</div>
          <div class="macro-lbl">Carbohidratos</div>
        </div>
      @endif
      @if($plan->fat_g)
        <div class="macro-card">
          <div class="macro-num">{{ $plan->fat_g }}g</div>
          <div class="macro-lbl">Grasas</div>
        </div>
      @endif
    </div>
  @endif
</div>

{{-- Alimentos por tiempo de comida --}}
<div class="d-card">
  <h3 style="margin:0 0 18px;font-size:15px;font-weight:700;">🍽️ Plan de alimentación</h3>

  @if($plan->items->isEmpty())
    <p style="color:var(--d-muted);text-align:center;padding:32px;margin:0;">
      Este plan no tiene alimentos registrados.
    </p>
  @else
    @php $grouped = $plan->itemsByMealTime(); @endphp
    @foreach(\App\Models\NutritionPlan::mealTimes() as $key => $label)
      @if(isset($grouped[$key]))
        <div class="meal-block">
          <div class="meal-block-title">
            🍴 {{ $label }}
            <span style="font-size:11px;color:var(--d-muted);font-weight:400;">
              {{ $grouped[$key]->sum('kcal') }} kcal · {{ $grouped[$key]->count() }} alimento{{ $grouped[$key]->count() > 1 ? 's' : '' }}
            </span>
          </div>
          @foreach($grouped[$key] as $item)
            <div class="food-row">
              <div style="flex:1;">
                <div style="font-size:14px;font-weight:600;">{{ $item->food_name }}</div>
                @if($item->quantity)
                  <div style="font-size:12px;color:var(--d-muted);">{{ $item->quantity }}</div>
                @endif
                @if($item->notes)
                  <div style="font-size:12px;color:var(--d-muted);font-style:italic;">{{ $item->notes }}</div>
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

    {{-- Total real --}}
    @if($plan->totalKcal() > 0)
      <div style="background:var(--d-bg);border-radius:10px;padding:12px 16px;text-align:right;margin-top:8px;font-size:13px;color:var(--d-muted);">
        <strong style="color:var(--d-text);">Total energético:</strong> {{ number_format($plan->totalKcal()) }} kcal
      </div>
    @endif
  @endif
</div>

@if($plan->notes)
  <div class="d-card" style="margin-top:16px;">
    <div style="font-size:13px;font-weight:700;margin-bottom:8px;">📝 Notas del plan</div>
    <p style="color:var(--d-muted);font-size:14px;margin:0;white-space:pre-wrap;">{{ $plan->notes }}</p>
  </div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@endpush
