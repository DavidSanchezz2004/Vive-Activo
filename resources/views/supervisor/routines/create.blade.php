@extends('layouts.dashboard')

@section('title', 'Nueva Rutina - Supervisor')

@push('styles')
<style>
  .rt-section { background:var(--d-card); border:1px solid var(--d-border); border-radius:16px; padding:22px 24px; margin-bottom:20px; }
  .rt-section-title { font-size:14px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  @media(max-width:768px){ .grid-2{grid-template-columns:1fr;} }
  .item-row { background:var(--d-bg); border:1px solid var(--d-border); border-radius:12px; padding:12px; margin-bottom:10px; position:relative; }
  .btn-remove-item { position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--d-danger); font-size:18px; line-height:1; }
</style>
@endpush

@section('content')
  <div class="d-topbar">
    <div>
      <div style="font-size:13px;color:var(--d-muted);margin-bottom:4px;">
        <a href="{{ route('supervisor.pacientes.show', $patient) }}" style="color:var(--d-brand);">{{ $patient->user?->name }}</a>
        › Nueva rutina
      </div>
      <h1 class="d-page-title">🏋️ Nueva Rutina</h1>
    </div>
  </div>

  <form action="{{ route('supervisor.pacientes.routines.store', $patient) }}" method="POST">
    @csrf

    <div class="rt-section">
      <div class="rt-section-title">📋 Información general</div>

      <div class="mb-3">
        <label class="d-label">Plantilla (opcional)</label>
        <select id="routineTemplate" class="d-select" style="max-width:520px;">
          <option value="">— Sin plantilla —</option>
          @foreach(($templates ?? []) as $tpl)
            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="grid-2 mb-3">
        <div>
          <label class="d-label">Título (opcional)</label>
          <input type="text" name="title" class="d-input" value="{{ old('title') }}" placeholder="Ej: Fuerza + movilidad">
        </div>
        <div>
          <label class="d-label">Objetivo (opcional)</label>
          <input type="text" name="goal" class="d-input" value="{{ old('goal') }}" placeholder="Ej: Aumentar fuerza, mejorar resistencia…">
        </div>
      </div>

      <div class="grid-2 mb-3">
        <div>
          <label class="d-label">Válida desde <span style="color:var(--d-danger);">*</span></label>
          <input type="date" name="valid_from" class="d-input" value="{{ old('valid_from', now()->format('Y-m-d')) }}" required>
        </div>
        <div>
          <label class="d-label">Válida hasta</label>
          <input type="date" name="valid_until" class="d-input" value="{{ old('valid_until') }}">
        </div>
      </div>

      <div>
        <label class="d-label">Notas (opcional)</label>
        <textarea name="notes" class="d-textarea" rows="3" style="resize:vertical;">{{ old('notes') }}</textarea>
      </div>
    </div>

    <div class="rt-section">
      <div class="rt-section-title">🧾 Ejercicios</div>
      <div id="items"></div>

      <button type="button" onclick="addItem()" class="d-btn d-btn-outline" style="font-size:13px;">
        <i data-lucide="plus" style="width:14px;"></i> Agregar ejercicio
      </button>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px;">
      <a href="{{ route('supervisor.pacientes.show', $patient) }}" class="d-btn d-btn-outline">Cancelar</a>
      <button type="submit" class="d-btn d-btn-primary">
        <i data-lucide="save"></i> Guardar rutina
      </button>
    </div>
  </form>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();
  const days = @json($days);
  const routineTemplates = @json($templatesJson ?? []);

  @php
    $existingRoutineItems = array_values(old('items', []));
  @endphp
  const existingRoutineItems = @json($existingRoutineItems);

  let itemIndex = 0;

  function dayOptions(selected) {
    return Object.entries(days).map(([k, v]) => {
      const sel = (selected != null && String(selected) === String(k)) ? 'selected' : '';
      return `<option value="${k}" ${sel}>${v}</option>`;
    }).join('');
  }

  function addItem(prefill = null) {
    const list = document.getElementById('items');
    const idx = itemIndex++;

    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
      <button type="button" class="btn-remove-item" onclick="removeItem(this)">×</button>

      <div style="display:grid;grid-template-columns:160px 1fr;gap:10px;">
        <div>
          <label class="d-label" style="font-size:11px;">Día *</label>
          <select name="items[${idx}][day]" class="d-select" required>
            ${dayOptions(prefill?.day)}
          </select>
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Ejercicio *</label>
          <input type="text" name="items[${idx}][exercise_name]" class="d-input" required placeholder="Ej: Sentadilla, Press banca, Caminata…">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:10px;">
        <div>
          <label class="d-label" style="font-size:11px;">Series</label>
          <input type="number" name="items[${idx}][sets]" class="d-input" min="1" max="50" placeholder="3">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Reps</label>
          <input type="text" name="items[${idx}][reps]" class="d-input" placeholder="8-12">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Descanso (seg)</label>
          <input type="number" name="items[${idx}][rest_seconds]" class="d-input" min="0" max="3600" placeholder="90">
        </div>
        <div>
          <label class="d-label" style="font-size:11px;">Nota</label>
          <input type="text" name="items[${idx}][notes]" class="d-input" placeholder="Opcional">
        </div>
      </div>
    `;

    list.appendChild(row);

    if (prefill) {
      if (prefill.exercise_name != null) row.querySelector(`[name="items[${idx}][exercise_name]"]`).value = prefill.exercise_name;
      if (prefill.sets != null) row.querySelector(`[name="items[${idx}][sets]"]`).value = prefill.sets;
      if (prefill.reps != null) row.querySelector(`[name="items[${idx}][reps]"]`).value = prefill.reps;
      if (prefill.rest_seconds != null) row.querySelector(`[name="items[${idx}][rest_seconds]"]`).value = prefill.rest_seconds;
      if (prefill.notes != null) row.querySelector(`[name="items[${idx}][notes]"]`).value = prefill.notes;
    }

    lucide.createIcons();
  }

  function removeItem(btn) {
    btn.closest('.item-row').remove();
  }

  function clearItems() {
    document.getElementById('items').innerHTML = '';
    itemIndex = 0;
  }

  // Restore old items after validation errors
  if (Array.isArray(existingRoutineItems) && existingRoutineItems.length) {
    existingRoutineItems.forEach((it) => addItem(it));
  }

  // Apply template
  const templateSelect = document.getElementById('routineTemplate');
  if (templateSelect) {
    templateSelect.addEventListener('change', () => {
      const id = templateSelect.value;
      if (!id) return;

      const tpl = routineTemplates[id];
      if (!tpl) return;

      const titleInput = document.querySelector('input[name="title"]');
      const goalInput = document.querySelector('input[name="goal"]');
      const notesInput = document.querySelector('textarea[name="notes"]');

      if (titleInput) titleInput.value = tpl.name ?? '';
      if (goalInput) goalInput.value = tpl.goal ?? '';
      if (notesInput) notesInput.value = tpl.notes ?? '';

      clearItems();
      if (Array.isArray(tpl.items)) {
        tpl.items.forEach((it) => addItem(it));
      }
    });
  }
</script>
@endpush
