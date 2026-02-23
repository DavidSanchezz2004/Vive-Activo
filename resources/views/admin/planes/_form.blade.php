@php
  /** @var \App\Models\Plan|null $plan */
  $isEdit = isset($plan) && $plan;
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
  <div style="grid-column:span 2;">
    <label class="d-label">Nombre</label>
    <input name="name" class="d-input" value="{{ old('name', $plan->name ?? '') }}" required>
  </div>

  <div style="grid-column:span 2;">
    <label class="d-label">Descripción</label>
    <textarea name="description" class="d-textarea" rows="3">{{ old('description', $plan->description ?? '') }}</textarea>
  </div>

  <div>
    <label class="d-label">Slug (opcional)</label>
    <input name="slug" class="d-input" value="{{ old('slug', $plan->slug ?? '') }}" placeholder="plan-basico">
  </div>

  <div>
    <label class="d-label">Sesiones incluidas (0 = ilimitado)</label>
    <input type="number" min="0" name="sessions_total" class="d-input" value="{{ old('sessions_total', $plan->sessions_total ?? 0) }}" required>
  </div>

  <div>
    <label class="d-label">Duración (meses)</label>
    <input type="number" min="1" max="60" name="duration_months" class="d-input" value="{{ old('duration_months', $plan->duration_months ?? 1) }}" required>
  </div>

  <div>
    <label class="d-label">Precio</label>
    <input type="number" step="0.01" min="0" name="price" class="d-input" value="{{ old('price', $plan->price ?? 0) }}" required>
  </div>

  <div>
    <label class="d-label">Moneda</label>
    <select name="currency" class="d-select" required>
      @php $cur = old('currency', $plan->currency ?? 'PEN'); @endphp
      <option value="PEN" @selected($cur==='PEN')>PEN</option>
      <option value="USD" @selected($cur==='USD')>USD</option>
    </select>
  </div>

  <div style="display:flex;align-items:center;gap:10px;grid-column:span 2;">
    @php $active = old('is_active', $plan->is_active ?? true); @endphp
    <input id="is_active" type="checkbox" name="is_active" value="1" @checked($active)>
    <label for="is_active" style="font-size:14px;color:var(--d-text);font-weight:600;">Plan activo</label>
  </div>
</div>
