@extends('layouts.dashboard')
@section('title', 'Editar Sesión - Vive Activo')
@section('page_title', 'Editar Sesión')

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
      <span style="color:var(--d-text);font-weight:500;">Editar sesión</span>
    </div>

    <div class="d-card">
      <h2 style="margin:0 0 8px;font-size:18px;font-weight:700;">
        <i data-lucide="pencil" style="width:16px;vertical-align:-2px;"></i>
        Sesión de {{ $session->patient?->user?->name ?? '—' }}
      </h2>
      <p style="margin:0 0 24px;font-size:13px;color:var(--d-muted);">Programada el {{ $session->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>

      @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route("{$prefix}.sesiones.update", $session) }}" method="POST">
        @csrf @method('PUT')

        {{-- Alumno --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="student_id">Alumno <span style="color:var(--d-danger);">*</span></label>
          <select id="student_id" name="student_id" class="d-select" required>
            <option value="">— Seleccionar alumno —</option>
            @foreach($students as $st)
              <option value="{{ $st->id }}" @selected(old('student_id', $session->student_id) == $st->id)>
                {{ $st->user?->name ?? '—' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Fecha --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="scheduled_at">Fecha y hora <span style="color:var(--d-danger);">*</span></label>
          <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="d-input"
            value="{{ old('scheduled_at', $session->scheduled_at?->format('Y-m-d\TH:i')) }}" required>
        </div>

        {{-- Estado --}}
        <div class="d-form-group mb-4">
          <label class="d-label" for="status">Estado <span style="color:var(--d-danger);">*</span></label>
          <select id="status" name="status" class="d-select" required>
            @foreach(\App\Models\PatientSession::STATUSES as $val => $lbl)
              <option value="{{ $val }}" @selected(old('status', $session->status) === $val)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        {{-- Descuenta --}}
        <div class="d-form-group mb-4">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
            <input type="hidden" name="deducts" value="0">
            <input type="checkbox" name="deducts" value="1" id="deducts"
              style="width:18px;height:18px;accent-color:var(--d-brand);"
              {{ old('deducts', $session->deducts) ? 'checked' : '' }}>
            <span><strong>Descuenta sesión</strong> — esta sesión se descuenta del plan</span>
          </label>
        </div>

        {{-- Notas --}}
        <div class="d-form-group mb-5">
          <label class="d-label" for="notes">Notas</label>
          <textarea id="notes" name="notes" class="d-input" rows="3"
            style="resize:vertical;">{{ old('notes', $session->notes) }}</textarea>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="{{ route("{$prefix}.sesiones.index") }}" class="d-btn d-btn-outline">Cancelar</a>
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
  <script>lucide.createIcons();</script>
@endpush
