@extends('layouts.dashboard')

@section('content')
@php
    $isEdit = (bool) ($template->id ?? false);
    $action = $isEdit ? route('admin.routine_templates.update', $template) : route('admin.routine_templates.store');
@endphp

<div class="d-card" style="margin-bottom: 16px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
        <div>
            <h2 style="margin:0;">{{ $isEdit ? 'Editar plantilla' : 'Nueva plantilla' }}</h2>
            <p style="margin:6px 0 0;color:var(--d-muted);">Define ejercicios por día para reutilizarlos en rutinas.</p>
        </div>
        <a class="d-btn" href="{{ route('admin.routine_templates.index') }}">Volver</a>
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
                    Disponible para cargar en rutinas
                </label>
            </div>
            <div style="grid-column:1 / -1;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">Objetivo</label>
                <input class="d-input" type="text" name="goal" value="{{ old('goal', $template->goal) }}" maxlength="200" style="width:100%;" />
            </div>
            <div style="grid-column:1 / -1;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">Notas</label>
                <textarea class="d-input" name="notes" rows="3" maxlength="2000" style="width:100%;">{{ old('notes', $template->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-card">
        <h3 style="margin:0 0 10px;">Ejercicios</h3>
        <p style="margin:0 0 12px;color:var(--d-muted);">Agrega ejercicios y el día correspondiente.</p>

        <div id="items"></div>

        <div style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-top: 12px;">
            <button type="button" class="d-btn d-btn-outline" onclick="addItem()">Agregar ejercicio</button>
            <button class="d-btn" type="submit">{{ $isEdit ? 'Guardar cambios' : 'Crear plantilla' }}</button>
        </div>
    </div>
</form>

<script>
    const days = @json($days);

    @php
        $existingItems = collect(old('items', []));

        if ($existingItems->isEmpty() && ($template->id ?? false)) {
            $existingItems = $template->items
                ->sortBy('order')
                ->values()
                ->map(function ($it) {
                    return [
                        'day' => $it->day,
                        'exercise_name' => $it->exercise_name,
                        'sets' => $it->sets,
                        'reps' => $it->reps,
                        'rest_seconds' => $it->rest_seconds,
                        'notes' => $it->notes,
                    ];
                })
                ->values();
        }

        $existingItems = $existingItems->values();
    @endphp

    const existingItems = @json($existingItems);

    const itemsRoot = document.getElementById('items');
    let itemIndex = 0;

    function dayOptions(selected) {
        return Object.entries(days).map(([k, v]) => {
            const sel = (selected && String(selected) === String(k)) ? 'selected' : '';
            return `<option value="${k}" ${sel}>${v}</option>`;
        }).join('');
    }

    function addItem(prefill = null) {
        const idx = itemIndex++;

        const row = document.createElement('div');
        row.className = 'd-card';
        row.style.marginBottom = '10px';

        row.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                <div style="font-weight:600;">Ejercicio</div>
                <button type="button" class="d-btn d-btn-danger" data-remove>Quitar</button>
            </div>

            <div style="display:grid;grid-template-columns:160px 1fr;gap:10px;margin-top:10px;">
                <div>
                    <label class="d-label" style="font-size:11px;">Día *</label>
                    <select name="items[${idx}][day]" class="d-select" required>
                        ${dayOptions(prefill?.day)}
                    </select>
                </div>
                <div>
                    <label class="d-label" style="font-size:11px;">Ejercicio *</label>
                    <input type="text" name="items[${idx}][exercise_name]" class="d-input" required maxlength="255" placeholder="Ej: Sentadilla" />
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:10px;">
                <div>
                    <label class="d-label" style="font-size:11px;">Series</label>
                    <input type="number" name="items[${idx}][sets]" class="d-input" min="1" max="50" placeholder="3" />
                </div>
                <div>
                    <label class="d-label" style="font-size:11px;">Reps</label>
                    <input type="text" name="items[${idx}][reps]" class="d-input" maxlength="50" placeholder="8-12" />
                </div>
                <div>
                    <label class="d-label" style="font-size:11px;">Descanso (seg)</label>
                    <input type="number" name="items[${idx}][rest_seconds]" class="d-input" min="0" max="3600" placeholder="90" />
                </div>
                <div>
                    <label class="d-label" style="font-size:11px;">Nota</label>
                    <input type="text" name="items[${idx}][notes]" class="d-input" maxlength="500" placeholder="Opcional" />
                </div>
            </div>
        `;

        row.querySelector('[data-remove]').addEventListener('click', () => row.remove());

        if (prefill) {
            if (prefill.exercise_name != null) row.querySelector(`[name="items[${idx}][exercise_name]"]`).value = prefill.exercise_name;
            if (prefill.sets != null) row.querySelector(`[name="items[${idx}][sets]"]`).value = prefill.sets;
            if (prefill.reps != null) row.querySelector(`[name="items[${idx}][reps]"]`).value = prefill.reps;
            if (prefill.rest_seconds != null) row.querySelector(`[name="items[${idx}][rest_seconds]"]`).value = prefill.rest_seconds;
            if (prefill.notes != null) row.querySelector(`[name="items[${idx}][notes]"]`).value = prefill.notes;
        }

        itemsRoot.appendChild(row);
    }

    if (Array.isArray(existingItems) && existingItems.length) {
        existingItems.forEach((it) => addItem(it));
    }
</script>
@endsection
