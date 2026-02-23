@extends('layouts.dashboard')

@section('title', 'Editar Consulta - Vive Activo')
@section('page_title', 'Editar Consulta')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container" style="max-width:700px;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--d-muted);">
      <a href="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.index' : 'supervisor.consultas.index') }}" style="color:var(--d-muted);text-decoration:none;">Consultas</a>
      <i data-lucide="chevron-right" style="width:14px;"></i>
      <span style="color:var(--d-text);font-weight:500;">Editar</span>
    </div>

    <div class="d-card">
      <h2 style="margin:0 0 24px;font-size:18px;font-weight:700;">
        <i data-lucide="pencil" style="width:16px;vertical-align:-2px;"></i>
        Consulta con {{ $consultation->patient?->user?->name ?? '—' }}
      </h2>

      @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      @php $prefix = request()->routeIs('admin.*') ? 'admin' : 'supervisor'; @endphp

      <form action="{{ route("{$prefix}.consultas.update", $consultation) }}" method="POST">
        @csrf @method('PUT')

        {{-- Alumno (editable) --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="student_id">Alumno asignado</label>
          <select id="student_id" name="student_id" class="d-select">
            <option value="">— Sin alumno —</option>
            @foreach($students as $st)
              <option value="{{ $st->id }}" @selected(old('student_id', $consultation->student_id) == $st->id)>
                {{ $st->user?->name ?? '—' }} ({{ $st->user?->email ?? '—' }})
              </option>
            @endforeach
          </select>
        </div>

        {{-- Tipo --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="type">Tipo de consulta</label>
          <input id="type" name="type" type="text" class="d-input"
            placeholder="Ej: Evaluación inicial…"
            value="{{ old('type', $consultation->type) }}">
        </div>

        {{-- Estado --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="status">Estado <span style="color:var(--d-danger);">*</span></label>
          <select id="status" name="status" class="d-select" required>
            @foreach($statuses as $val => $lbl)
              <option value="{{ $val }}" @selected(old('status', $consultation->status) === $val)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        {{-- Modo --}}
        <div class="d-form-group mb-4">
          <label class="d-label">Modalidad <span style="color:var(--d-danger);">*</span></label>
          <div style="display:flex;gap:12px;flex-wrap:wrap;">
            @foreach(['presencial'=>'🏥 Presencial','zoom'=>'🎥 Zoom','meet'=>'📹 Google Meet'] as $val => $lbl)
              <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid var(--d-border);border-radius:10px;cursor:pointer;font-size:14px;">
                <input type="radio" name="mode" value="{{ $val }}"
                  {{ old('mode', $consultation->mode) === $val ? 'checked' : '' }}
                  onchange="onModeChange('{{ $val }}')"
                  style="accent-color:var(--d-brand);">
                {{ $lbl }}
              </label>
            @endforeach
          </div>
        </div>

        {{-- URL reunión --}}
        <div class="d-form-group mb-4" id="meeting-url-group"
          style="{{ in_array(old('mode', $consultation->mode), ['zoom','meet']) ? '' : 'display:none;' }}">
          <label class="d-label" for="meeting_url">URL de reunión</label>
          <input id="meeting_url" name="meeting_url" type="url" class="d-input"
            placeholder="https://zoom.us/j/... o https://meet.google.com/..."
            value="{{ old('meeting_url', $consultation->meeting_url) }}">
        </div>

        {{-- Fecha --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="scheduled_at">Fecha y hora <span style="color:var(--d-danger);">*</span></label>
          <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="d-input"
            value="{{ old('scheduled_at', $consultation->scheduled_at?->format('Y-m-d\TH:i')) }}" required>
        </div>

        {{-- Notas --}}
        <div class="d-form-group mb-5">
          <label class="d-label" for="notes">Notas</label>
          <textarea id="notes" name="notes" class="d-input" rows="3"
            style="resize:vertical;">{{ old('notes', $consultation->notes) }}</textarea>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="{{ route("{$prefix}.consultas.index") }}" class="d-btn d-btn-outline">Cancelar</a>
          <button type="submit" class="d-btn d-btn-primary">
            <i data-lucide="save"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    lucide.createIcons();

    function onModeChange(mode) {
      const group = document.getElementById('meeting-url-group');
      const input = document.getElementById('meeting_url');
      const show  = mode === 'zoom' || mode === 'meet';
      group.style.display = show ? '' : 'none';
      input.required = show;
    }
  </script>
@endpush
