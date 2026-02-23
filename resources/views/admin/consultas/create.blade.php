@extends('layouts.dashboard')

@section('title', 'Nueva Consulta - Vive Activo')
@section('page_title', 'Nueva Consulta')

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
      <span style="color:var(--d-text);font-weight:500;">Nueva consulta</span>
    </div>

    <div class="d-card">
      <h2 style="margin:0 0 24px;font-size:18px;font-weight:700;">
        <i data-lucide="calendar-plus" style="width:18px;vertical-align:-3px;"></i> Programar consulta
      </h2>

      @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.store' : 'supervisor.consultas.store') }}" method="POST">
        @csrf

        {{-- Paciente --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="patient_id">Paciente <span style="color:var(--d-danger);">*</span></label>
          <select id="patient_id" name="patient_id" class="d-select" required onchange="onPatientChange(this)">
            <option value="">— Seleccionar paciente —</option>
            @foreach($patients as $p)
              <option value="{{ $p->id }}"
                data-student="{{ $p->activeAssignment?->student?->user?->name ?? '' }}"
                @selected(old('patient_id', $selectedPatient?->id) == $p->id)>
                {{ $p->user?->name ?? '—' }} ({{ $p->user?->email ?? '—' }})
              </option>
            @endforeach
          </select>
          <p id="alumno-info" style="margin:6px 0 0;font-size:13px;color:var(--d-muted);"></p>
        </div>

        {{-- Tipo --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="type">Tipo de consulta</label>
          <input id="type" name="type" type="text" class="d-input"
            placeholder="Ej: Evaluación inicial, Revisión de exámenes…"
            value="{{ old('type') }}">
        </div>

        {{-- Modo --}}
        <div class="d-form-group mb-4">
          <label class="d-label">Modalidad <span style="color:var(--d-danger);">*</span></label>
          <div style="display:flex;gap:12px;flex-wrap:wrap;">
            @foreach(['presencial'=>'🏥 Presencial','zoom'=>'🎥 Zoom','meet'=>'📹 Google Meet'] as $val => $lbl)
              <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid var(--d-border);border-radius:10px;cursor:pointer;font-size:14px;"
                id="mode-label-{{ $val }}">
                <input type="radio" name="mode" value="{{ $val }}"
                  {{ old('mode', 'presencial') === $val ? 'checked' : '' }}
                  onchange="onModeChange('{{ $val }}')"
                  style="accent-color:var(--d-brand);">
                {{ $lbl }}
              </label>
            @endforeach
          </div>
        </div>

        {{-- URL (solo zoom/meet) --}}
        <div class="d-form-group mb-4" id="meeting-url-group" style="{{ in_array(old('mode','presencial'),['zoom','meet']) ? '' : 'display:none;' }}">
          <label class="d-label" for="meeting_url">URL de reunión <span style="color:var(--d-danger);">*</span></label>
          <input id="meeting_url" name="meeting_url" type="url" class="d-input"
            placeholder="https://zoom.us/j/... o https://meet.google.com/..."
            value="{{ old('meeting_url') }}">
          <p style="margin:4px 0 0;font-size:12px;color:var(--d-muted);">Pega el enlace de Zoom o Google Meet.</p>
        </div>

        {{-- Fecha y hora --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="scheduled_at">Fecha y hora <span style="color:var(--d-danger);">*</span></label>
          <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="d-input"
            value="{{ old('scheduled_at') }}" required>
        </div>

        {{-- Notas --}}
        <div class="d-form-group mb-5">
          <label class="d-label" for="notes">Notas (opcional)</label>
          <textarea id="notes" name="notes" class="d-input" rows="3"
            placeholder="Indicaciones ou observaciones previas…"
            style="resize:vertical;">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="{{ route(request()->routeIs('admin.*') ? 'admin.consultas.index' : 'supervisor.consultas.index') }}"
             class="d-btn d-btn-outline">Cancelar</a>
          <button type="submit" class="d-btn d-btn-primary">
            <i data-lucide="calendar-check"></i> Programar consulta
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

    // Al cargar, verificar si había paciente preseleccionado
    document.addEventListener('DOMContentLoaded', function () {
      const sel = document.getElementById('patient_id');
      if (sel && sel.value) onPatientChange(sel);
      onModeChange(document.querySelector('[name=mode]:checked')?.value || 'presencial');
    });

    function onPatientChange(sel) {
      const opt = sel.options[sel.selectedIndex];
      const student = opt.dataset.student;
      const info = document.getElementById('alumno-info');
      info.textContent = student ? `Alumno asignado: ${student}` : 'Sin alumno asignado actualmente.';
    }

    function onModeChange(mode) {
      const group = document.getElementById('meeting-url-group');
      const input = document.getElementById('meeting_url');
      const show  = mode === 'zoom' || mode === 'meet';
      group.style.display = show ? '' : 'none';
      input.required = show;
    }
  </script>
@endpush
