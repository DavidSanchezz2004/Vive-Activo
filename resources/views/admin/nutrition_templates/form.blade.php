@extends('layouts.dashboard')

@section('content')
@php
    $isEdit = (bool) ($template->id ?? false);
    $action = $isEdit ? route('admin.nutrition_templates.update', $template) : route('admin.nutrition_templates.store');
@endphp

<div class="d-card" style="margin-bottom: 16px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
        <div>
            <h2 style="margin:0;">{{ $isEdit ? 'Editar plantilla' : 'Nueva plantilla' }}</h2>
            <p style="margin:6px 0 0;color:var(--d-muted);">Define macros y alimentos por tiempo de comida.</p>
        </div>
        <a class="d-btn" href="{{ route('admin.nutrition_templates.index') }}">Volver</a>
    </div>
</div>

@if ($errors->any())
    <div class="d-card" style="margin-bottom: 12px; border-left: 4px solid var(--d-danger);">
        <div style="font-weight:600;margin-bottom:6px;">Revisa estos campos:</div>
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="d-card" style="margin-bottom: 16px;">
        <div class="d-grid" style="grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px;">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Nombre *</label>
                <input class="d-input" type="text" name="name" value="{{ old('name', $template->name) }}" required maxlength="120" style="width:100%;" />
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Activa</label>
                <label style="display:flex;align-items:center;gap:8px;color:var(--d-muted);">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} />
                    Disponible para cargar en planes
                </label>
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Fase</label>
                <input class="d-input" type="text" name="phase" value="{{ old('phase', $template->phase) }}" maxlength="120" style="width:100%;" />
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Objetivo</label>
                <input class="d-input" type="text" name="goal" value="{{ old('goal', $template->goal) }}" maxlength="200" style="width:100%;" />
            </div>
        </div>

        <div class="d-grid" style="grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; margin-top: 12px;">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Kcal objetivo</label>
                <input class="d-input" type="number" name="kcal_target" value="{{ old('kcal_target', $template->kcal_target) }}" min="500" max="10000" style="width:100%;" />
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Proteína (g)</label>
                <input class="d-input" type="number" step="0.1" name="protein_g" value="{{ old('protein_g', $template->protein_g) }}" min="0" style="width:100%;" />
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Carbs (g)</label>
                <input class="d-input" type="number" step="0.1" name="carbs_g" value="{{ old('carbs_g', $template->carbs_g) }}" min="0" style="width:100%;" />
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Grasa (g)</label>
                <input class="d-input" type="number" step="0.1" name="fat_g" value="{{ old('fat_g', $template->fat_g) }}" min="0" style="width:100%;" />
            </div>
        </div>

        <div style="margin-top: 12px;">
            <label style="display:block;font-weight:600;margin-bottom:6px;">Notas</label>
            <textarea class="d-input" name="notes" rows="3" maxlength="2000" style="width:100%;">{{ old('notes', $template->notes) }}</textarea>
        </div>
    </div>

    <div class="d-card">
        <h3 style="margin:0 0 10px;">Alimentos por tiempo de comida</h3>
        <p style="margin:0 0 12px;color:var(--d-muted);">Agrega alimentos y, si deseas, macros por item.</p>

        <div id="meals" style="display:grid;grid-template-columns: 1fr; gap: 12px;"></div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top: 16px;">
            <button class="d-btn" type="submit">{{ $isEdit ? 'Guardar cambios' : 'Crear plantilla' }}</button>
        </div>
    </div>
</form>

<script>
    const mealTimes = @json($mealTimes);

    @php
        $existingItems = collect(old('items', []));

        if ($existingItems->isEmpty() && ($template->id ?? false)) {
            $existingItems = $template->items
                ->sortBy('order')
                ->values()
                ->map(function ($it) {
                    return [
                        'meal_time' => $it->meal_time,
                        'food_name' => $it->food_name,
                        'quantity' => $it->quantity,
                        'kcal' => $it->kcal,
                        'protein_g' => $it->protein_g,
                        'carbs_g' => $it->carbs_g,
                        'fat_g' => $it->fat_g,
                        'notes' => $it->notes,
                    ];
                })
                ->values();
        }

        $existingItems = $existingItems->values();
    @endphp

    const existingItems = @json($existingItems);

    const mealsRoot = document.getElementById('meals');
    const itemCounters = {};

    function createMealCard(mealKey, mealLabel) {
        const card = document.createElement('div');
        card.className = 'd-card';

        card.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div>
                    <h4 style="margin:0;">${mealLabel}</h4>
                    <p style="margin:6px 0 0;color:var(--d-muted);">Agrega alimentos para este tiempo.</p>
                </div>
                <button type="button" class="d-btn" data-add>Agregar item</button>
            </div>
            <div style="margin-top: 12px; overflow:auto;">
                <table class="d-table" style="min-width: 980px;">
                    <thead>
                        <tr>
                            <th>Alimento *</th>
                            <th>Cantidad</th>
                            <th>Kcal</th>
                            <th>Prot (g)</th>
                            <th>Carbs (g)</th>
                            <th>Grasa (g)</th>
                            <th>Notas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody data-body></tbody>
                </table>
            </div>
        `;

        const btn = card.querySelector('[data-add]');
        btn.addEventListener('click', () => addItemRow(mealKey));

        return card;
    }

    function addItemRow(mealKey, prefill = null) {
        if (!itemCounters[mealKey]) itemCounters[mealKey] = 0;
        const idx = `${mealKey}_${itemCounters[mealKey]++}_${Date.now()}`;

        const mealCard = document.querySelector(`[data-meal="${mealKey}"]`);
        const tbody = mealCard.querySelector('[data-body]');

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input class="d-input" style="width:100%;" name="items[${idx}][food_name]" required maxlength="255" />
                <input type="hidden" name="items[${idx}][meal_time]" value="${mealKey}" />
            </td>
            <td><input class="d-input" style="width:100%;" name="items[${idx}][quantity]" maxlength="80" /></td>
            <td><input class="d-input" style="width:100%;" type="number" min="0" name="items[${idx}][kcal]" /></td>
            <td><input class="d-input" style="width:100%;" type="number" step="0.1" min="0" name="items[${idx}][protein_g]" /></td>
            <td><input class="d-input" style="width:100%;" type="number" step="0.1" min="0" name="items[${idx}][carbs_g]" /></td>
            <td><input class="d-input" style="width:100%;" type="number" step="0.1" min="0" name="items[${idx}][fat_g]" /></td>
            <td><input class="d-input" style="width:100%;" name="items[${idx}][notes]" maxlength="500" /></td>
            <td><button type="button" class="d-btn d-btn-danger" data-remove>Quitar</button></td>
        `;

        tr.querySelector('[data-remove]').addEventListener('click', () => tr.remove());

        if (prefill) {
            if (prefill.food_name != null) tr.querySelector(`[name="items[${idx}][food_name]"]`).value = prefill.food_name;
            if (prefill.quantity != null) tr.querySelector(`[name="items[${idx}][quantity]"]`).value = prefill.quantity;
            if (prefill.kcal != null) tr.querySelector(`[name="items[${idx}][kcal]"]`).value = prefill.kcal;
            if (prefill.protein_g != null) tr.querySelector(`[name="items[${idx}][protein_g]"]`).value = prefill.protein_g;
            if (prefill.carbs_g != null) tr.querySelector(`[name="items[${idx}][carbs_g]"]`).value = prefill.carbs_g;
            if (prefill.fat_g != null) tr.querySelector(`[name="items[${idx}][fat_g]"]`).value = prefill.fat_g;
            if (prefill.notes != null) tr.querySelector(`[name="items[${idx}][notes]"]`).value = prefill.notes;
        }

        tbody.appendChild(tr);
    }

    // Render meal cards
    Object.entries(mealTimes).forEach(([mealKey, mealLabel]) => {
        const wrap = document.createElement('div');
        wrap.dataset.meal = mealKey;
        wrap.appendChild(createMealCard(mealKey, mealLabel));
        mealsRoot.appendChild(wrap);
    });

    // Prefill existing
    if (Array.isArray(existingItems)) {
        existingItems.forEach((it) => {
            if (!it || !it.meal_time) return;
            addItemRow(it.meal_time, it);
        });
    }
</script>
@endsection
