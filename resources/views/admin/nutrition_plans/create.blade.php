@extends('layouts.dashboard')

@section('title', 'Nuevo Plan Nutricional - Vive Activo')

@push('styles')
<style>
  .np-section { background:var(--d-card); border:1px solid var(--d-border); border-radius:16px; padding:22px 24px; margin-bottom:20px; }
  .np-section-title { font-size:14px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
  .grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
  @media(max-width:768px){ .grid-2,.grid-3,.grid-4{grid-template-columns:1fr;} }
  .meal-section { border:1px solid var(--d-border); border-radius:12px; margin-bottom:12px; overflow:hidden; }
  .meal-header { background:var(--d-bg); padding:10px 14px; font-weight:700; font-size:13px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; user-select:none; }
  .meal-body { padding:12px; display:none; }
  .meal-body.open { display:block; }
  .item-row { background:var(--d-bg); border-radius:10px; padding:10px 12px; margin-bottom:8px; position:relative; }
  .btn-remove-item { position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--d-danger); font-size:18px; line-height:1; }
</style>
@endpush

@section('content')
<div class="d-topbar">
  <div>
    <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
      <a href="{{ route('admin.pacientes.show', $patient) }}" style="color:var(--d-brand);">{{ $patient->user?->name }}</a>
      › Nuevo plan nutricional
    </div>
    <h1 class="d-page-title">🥗 Nuevo Plan Nutricional</h1>
  </div>
</div>

<form action="{{ route('admin.pacientes.nutrition_plans.store', $patient) }}" method="POST" enctype="multipart/form-data">
  @csrf

  {{-- Información general --}}
  <div class="np-section">
    <div class="np-section-title">📋 Información general</div>

    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:14px;">
      <div style="min-width:260px;flex:1;">
        <label class="d-label">Plantilla (opcional)</label>
        <select id="np-template" class="d-input">
          <option value="">— Seleccionar —</option>
          @foreach(($templates ?? []) as $tpl)
            <option value="{{ $tpl['id'] }}">{{ $tpl['name'] }}</option>
          @endforeach
        </select>
      </div>
      <button type="button" id="np-apply-template" class="d-btn d-btn-outline" style="font-size:13px;">
        <i data-lucide="wand-2" style="width:14px;"></i> Cargar plantilla
      </button>
    </div>

    <div class="grid-2 mb-3">
      <div>
        <label class="d-label">Fase / Nombre del plan</label>
        <input type="text" name="phase" class="d-input" value="{{ old('phase') }}" placeholder="Ej: Fase 1 – Déficit calórico">
      </div>
      <div>
        <label class="d-label">Objetivo</label>
        <input type="text" name="goal" class="d-input" value="{{ old('goal') }}" placeholder="Ej: Reducción de grasa, Mantenimiento…">
      </div>
    </div>
    <div class="grid-2 mb-3">
      <div>
        <label class="d-label">Válido desde <span style="color:var(--d-danger);">*</span></label>
        <input type="date" name="valid_from" class="d-input" value="{{ old('valid_from', now()->format('Y-m-d')) }}" required>
      </div>
      <div>
        <label class="d-label">Válido hasta</label>
        <input type="date" name="valid_until" class="d-input" value="{{ old('valid_until') }}">
      </div>
    </div>
    <div>
      <label class="d-label">Adjuntar PDF del plan <span style="font-size:12px;color:var(--d-muted);">(máx. 5 MB)</span></label>
      <input type="file" name="pdf" accept=".pdf" class="d-input">
    </div>
  </div>

  {{-- Metas calóricas y macros --}}
  <div class="np-section">
    <div class="np-section-title">🔢 Metas diarias (opcionales)</div>
    <div class="grid-4">
      <div>
        <label class="d-label">Calorías (kcal/día)</label>
        <input type="number" name="kcal_target" class="d-input" value="{{ old('kcal_target') }}" min="500" max="10000" placeholder="2000">
      </div>
      <div>
        <label class="d-label">Proteínas (g/día)</label>
        <input type="number" name="protein_g" class="d-input" step="0.1" value="{{ old('protein_g') }}" placeholder="150">
      </div>
      <div>
        <label class="d-label">Carbohidratos (g/día)</label>
        <input type="number" name="carbs_g" class="d-input" step="0.1" value="{{ old('carbs_g') }}" placeholder="200">
      </div>
      <div>
        <label class="d-label">Grasas (g/día)</label>
        <input type="number" name="fat_g" class="d-input" step="0.1" value="{{ old('fat_g') }}" placeholder="60">
      </div>
    </div>
  </div>

  {{-- Constructor de alimentos por tiempo de comida --}}
  <div class="np-section">
    <div class="np-section-title">🍽️ Alimentos por tiempo de comida</div>
    <div id="meals-container">
      @foreach($mealTimes as $key => $label)
        <div class="meal-section" id="meal-{{ $key }}">
          <div class="meal-header" onclick="toggleMeal('{{ $key }}')">
            <span>{{ $label }}</span>
            <div style="display:flex;gap:8px;align-items:center;">
              <span class="item-count" data-meal="{{ $key }}" style="font-size:12px;color:var(--d-muted);">0 alimentos</span>
              <i data-lucide="chevron-down" style="width:14px;transition:transform .2s;" id="chevron-{{ $key }}"></i>
            </div>
          </div>
          <div class="meal-body" id="body-{{ $key }}">
            <div class="items-list" id="items-{{ $key }}"></div>
            <button type="button" onclick="addItem('{{ $key }}', '{{ addslashes($label) }}')"
              class="d-btn d-btn-outline" style="font-size:13px;margin-top:4px;">
              <i data-lucide="plus" style="width:14px;"></i> Agregar alimento
            </button>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Notas --}}
  <div class="np-section">
    <label class="d-label">Notas generales del plan</label>
    <textarea name="notes" class="d-input" rows="3" placeholder="Indicaciones adicionales, restricciones, suplementos…" style="resize:vertical;">{{ old('notes') }}</textarea>
  </div>

  <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px;">
    <a href="{{ route('admin.pacientes.show', $patient) }}" class="d-btn d-btn-outline">Cancelar</a>
    <button type="submit" class="d-btn d-btn-primary">
      <i data-lucide="save"></i> Guardar plan nutricional
    </button>
  </div>
</form>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();
  // Contadores de ítems por tiempo
  const counters = {};

  function toggleMeal(key) {
    const body = document.getElementById('body-' + key);
    const chevron = document.getElementById('chevron-' + key);
    body.classList.toggle('open');
    chevron.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0)';
  }

  function updateCount(mealKey) {
    const count = document.querySelectorAll(`[data-meal-row="${mealKey}"]`).length;
    const el = document.querySelector(`.item-count[data-meal="${mealKey}"]`);
    if (el) el.textContent = count === 0 ? '0 alimentos' : `${count} alimento${count > 1 ? 's' : ''}`;
  }

  // Global item index across all meals
  let itemIndex = 0;

  function addItem(mealKey, mealLabel, prefill = null) {
    const list = document.getElementById('items-' + mealKey);
    const idx = itemIndex++;
    const body = document.getElementById('body-' + mealKey);
    if (!body.classList.contains('open')) toggleMeal(mealKey);

    const row = document.createElement('div');
    row.className = 'item-row';
    row.setAttribute('data-meal-row', mealKey);
    row.innerHTML = `
      <button type="button" class="btn-remove-item" onclick="removeItem(this, '${mealKey}')">×</button>
      <input type="hidden" name="items[${idx}][meal_time]" value="${mealKey}">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;margin-bottom:8px;">
        <div>
          <label class="d-label" style="font-size:11px;">Alimento *</label>
          <input type="text" name="items[${idx}][food_name]" class="d-input" required placeholder="Ej: Avena con plátano">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Cantidad</label>
          <input type="text" name="items[${idx}][quantity]" class="d-input" placeholder="Ej: 200g, 1 taza">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:8px;">
        <div>
          <label class="d-label" style="font-size:11px;">kcal</label>
          <input type="number" name="items[${idx}][kcal]" class="d-input" min="0" placeholder="250">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Proteína g</label>
          <input type="number" name="items[${idx}][protein_g]" class="d-input" step="0.1" min="0" placeholder="20">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Carbos g</label>
          <input type="number" name="items[${idx}][carbs_g]" class="d-input" step="0.1" min="0" placeholder="30">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Grasa g</label>
          <input type="number" name="items[${idx}][fat_g]" class="d-input" step="0.1" min="0" placeholder="8">
        </div>
      </div>
      <div>
        <label class="d-label" style="font-size:11px;">Nota</label>
        <input type="text" name="items[${idx}][notes]" class="d-input" placeholder="Ej: sin azúcar, integral…">
      </div>
    `;
    list.appendChild(row);

    if (prefill) {
      const set = (field, value) => {
        const el = row.querySelector(`[name="items[${idx}][${field}]"]`);
        if (el) el.value = value ?? '';
      };

      set('food_name', prefill.food_name);
      set('quantity', prefill.quantity);
      set('kcal', prefill.kcal);
      set('protein_g', prefill.protein_g);
      set('carbs_g', prefill.carbs_g);
      set('fat_g', prefill.fat_g);
      set('notes', prefill.notes);
    }

    updateCount(mealKey);
    lucide.createIcons();
  }

  function removeItem(btn, mealKey) {
    btn.closest('.item-row').remove();
    updateCount(mealKey);
  }

  const nutritionPlanTemplates = @json($templatesJson ?? []);

  function setFieldValue(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value ?? '';
  }

  function hasAnyItems() {
    return document.querySelectorAll('.item-row').length > 0;
  }

  function clearAllItems() {
    document.querySelectorAll('.items-list').forEach((list) => {
      list.innerHTML = '';
    });
    document.querySelectorAll('.item-count').forEach((el) => {
      const meal = el.getAttribute('data-meal');
      if (meal) updateCount(meal);
    });
    itemIndex = 0;
  }

  function applyTemplate(templateKey) {
    const tpl = nutritionPlanTemplates[templateKey];
    if (!tpl) return;

    if (hasAnyItems()) {
      const ok = confirm('Esto reemplazará los alimentos ya agregados. ¿Continuar?');
      if (!ok) return;
    }

    clearAllItems();

    setFieldValue('phase', tpl.phase);
    setFieldValue('goal', tpl.goal);
    setFieldValue('kcal_target', tpl.kcal_target);
    setFieldValue('protein_g', tpl.protein_g);
    setFieldValue('carbs_g', tpl.carbs_g);
    setFieldValue('fat_g', tpl.fat_g);
    setFieldValue('notes', tpl.notes);

    Object.entries(tpl.items || {}).forEach(([mealKey, items]) => {
      (items || []).forEach((item) => addItem(mealKey, mealKey, item));
    });
  }

  document.getElementById('np-apply-template')?.addEventListener('click', function () {
    const key = document.getElementById('np-template')?.value;
    if (!key) return;
    applyTemplate(key);
  });
</script>
@endpush
