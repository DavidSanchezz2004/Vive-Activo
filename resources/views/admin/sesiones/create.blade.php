@extends('layouts.dashboard')
@section('title', 'Nueva Sesión - Vive Activo')
@section('page_title', 'Nueva Sesión')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container" style="max-width:700px;">

    @php $prefix = request()->routeIs('admin.*') ? 'admin' : 'supervisor'; @endphp

    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--d-muted);">
      <a href="{{ route("{$prefix}.sesiones.index") }}" style="color:var(--d-muted);text-decoration:none;">Sesiones</a>
      <i data-lucide="chevron-right" style="width:14px;"></i>
      <span style="color:var(--d-text);font-weight:500;">Nueva sesión</span>
    </div>

    <div class="d-card">
      <h2 style="margin:0 0 24px;font-size:18px;font-weight:700;">
        <i data-lucide="calendar-plus" style="width:18px;vertical-align:-3px;"></i> Programar sesión
      </h2>

      @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route("{$prefix}.sesiones.store") }}" method="POST">
        @csrf

        {{-- Paciente --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="patient_id">Paciente <span style="color:var(--d-danger);">*</span></label>
          <select id="patient_id" name="patient_id" class="d-select" required onchange="onPatientChange(this)">
            <option value="">— Seleccionar paciente —</option>
            @foreach($patients as $p)
              <option value="{{ $p->id }}"
                data-student-id="{{ $p->activeAssignment?->student_id ?? '' }}"
                data-student-name="{{ $p->activeAssignment?->student?->user?->name ?? '' }}"
                @selected(old('patient_id', $selectedPatient?->id) == $p->id)>
                {{ $p->user?->name ?? '—' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Alumno --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="student_id">Alumno <span style="color:var(--d-danger);">*</span></label>
          <select id="student_id" name="student_id" class="d-select" required>
            <option value="">— Seleccionar alumno —</option>
            @foreach($students as $st)
              <option value="{{ $st->id }}" @selected(old('student_id') == $st->id)>
                {{ $st->user?->name ?? '—' }}
              </option>
            @endforeach
          </select>
          <p id="student-info" style="margin:4px 0 0;font-size:12px;color:var(--d-muted);"></p>
        </div>

        {{-- Fecha y hora --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="scheduled_at">Fecha y hora <span style="color:var(--d-danger);">*</span></label>
          <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="d-input"
            value="{{ old('scheduled_at') }}" required>
        </div>

        {{-- Estado --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="status">Estado inicial</label>
          <select id="status" name="status" class="d-select">
            @foreach(\App\Models\PatientSession::STATUSES as $val => $lbl)
              <option value="{{ $val }}" @selected(old('status', 'pending') === $val)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        {{-- Descuenta --}}
        <div class="d-form-group mb-4">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
            <input type="hidden" name="deducts" value="0">
            <input type="checkbox" name="deducts" value="1" id="deducts"
              style="width:18px;height:18px;accent-color:var(--d-brand);"
              {{ old('deducts') ? 'checked' : '' }}>
            <span><strong>Descuenta sesión</strong> — marcar si esta sesión se descuenta del plan</span>
          </label>
        </div>

        {{-- Notas --}}
        <div class="d-form-group mb-5">
          <label class="d-label" for="notes">Notas (opcional)</label>
          <textarea id="notes" name="notes" class="d-input" rows="3"
            placeholder="Observaciones y detalles de la sesión…"
            style="resize:vertical;">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="{{ route("{$prefix}.sesiones.index") }}" class="d-btn d-btn-outline">Cancelar</a>
          <button type="submit" class="d-btn d-btn-primary">
            <i data-lucide="calendar-check"></i> Guardar sesión
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

    // Preseleccionar paciente desde URL
    document.addEventListener('DOMContentLoaded', function () {
      const sel = document.getElementById('patient_id');
      if (sel && sel.value) onPatientChange(sel);
    });

    function onPatientChange(sel) {
      const opt   = sel.options[sel.selectedIndex];
      const stId  = opt.dataset.studentId;
      const stName = opt.dataset.studentName;
      const stuSel = document.getElementById('student_id');
      const info   = document.getElementById('student-info');

      if (stId) {
        stuSel.value = stId;
        info.textContent = `Alumno asignado activo: ${stName}`;
      } else {
        info.textContent = 'Sin alumno asignado actualmente.';
      }
    }
  </script>
@endpush
