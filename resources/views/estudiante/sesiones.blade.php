@extends('layouts.dashboard')

@section('title', 'Mis Sesiones - Vive Activo')

@push('styles')
<style>
  .sesh-tabs { display:flex; gap:6px; margin-bottom:20px; }
  .sesh-tab { padding:8px 18px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid var(--d-border); background:var(--d-surface); color:var(--d-muted); }
  .sesh-tab.active { background:var(--d-brand); color:#fff; border-color:var(--d-brand); }
  .sesh-panel { display:none; }
  .sesh-panel.active { display:block; }
  .sesh-row { display:flex; align-items:center; gap:12px; padding:14px 0; border-bottom:1px solid var(--d-border); }
  body.sidebar-dark .sesh-row { border-bottom-color:#24324b; }

  body.sidebar-dark .sesh-tab { background:#111c31; border-color:#24324b; color:#94a3b8; }
  body.sidebar-dark .sesh-tab.active { background:var(--d-brand); border-color:var(--d-brand); color:#fff; }

  /* Modal de atención */
  .at-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9998; display:none; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
  .at-overlay.open { display:flex; }
  .at-modal { border-radius:20px; padding:28px 30px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; }

  .at-paciente-info { background: var(--d-bg); border:1px solid var(--d-border); border-radius:10px; padding:10px 14px; margin-bottom:18px; font-size:13px; }
  body.sidebar-dark .at-paciente-info { background:#0f172a; border-color:#24324b; }
  .st-status-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
  .dot-done{background:var(--d-success);} .dot-no_show{background:var(--d-danger);} .dot-pending{background:var(--d-warning);} .dot-rescheduled{background:var(--d-info);} .dot-cancelled{background:var(--d-muted);}

  .sesh-pill { display:inline-flex; align-items:center; gap:6px; border-radius:20px; padding:1px 7px; font-size:11px; }
  .sesh-pill.danger { background: var(--d-danger); color:#fff; }
  body.sidebar-dark .sesh-pill.danger { background: var(--d-danger); color:#fff; }

  .sesh-avatar { width:38px; height:38px; font-size:14px; flex-shrink:0; }
  .sesh-avatar.tone-warning { background: color-mix(in srgb, var(--d-warning) 16%, transparent); color: var(--d-warning); }
  body.sidebar-dark .sesh-avatar.tone-warning { background: rgba(255,255,255,.06); color: #e2e8f0; }

  .sesh-status-badge { font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; border:1px solid transparent; }
  .sesh-status-badge.is-done { color: var(--d-success); background: color-mix(in srgb, var(--d-success) 14%, transparent); border-color: color-mix(in srgb, var(--d-success) 35%, transparent); }
  .sesh-status-badge.is-no_show { color: var(--d-danger); background: color-mix(in srgb, var(--d-danger) 12%, transparent); border-color: color-mix(in srgb, var(--d-danger) 35%, transparent); }
  .sesh-status-badge.is-rescheduled { color: var(--d-info); background: color-mix(in srgb, var(--d-info) 12%, transparent); border-color: color-mix(in srgb, var(--d-info) 35%, transparent); }
  .sesh-status-badge.is-cancelled { color: var(--d-muted); background: color-mix(in srgb, var(--d-muted) 14%, transparent); border-color: color-mix(in srgb, var(--d-muted) 35%, transparent); }
  .sesh-status-badge.is-pending { color: var(--d-warning); background: color-mix(in srgb, var(--d-warning) 14%, transparent); border-color: color-mix(in srgb, var(--d-warning) 35%, transparent); }

  body.sidebar-dark .sesh-status-badge.is-done,
  body.sidebar-dark .sesh-status-badge.is-no_show,
  body.sidebar-dark .sesh-status-badge.is-rescheduled,
  body.sidebar-dark .sesh-status-badge.is-cancelled,
  body.sidebar-dark .sesh-status-badge.is-pending {
    background: rgba(255,255,255,.06);
    border-color: rgba(255,255,255,.10);
  }

  .sesh-alert-ok { background: color-mix(in srgb, var(--d-success) 12%, transparent); border: 1px solid color-mix(in srgb, var(--d-success) 35%, transparent); color: var(--d-text); padding:12px 16px; border-radius:12px; font-size:14px; }
  body.sidebar-dark .sesh-alert-ok { background: rgba(255,255,255,.06); border-color:#24324b; color:#e2e8f0; }
</style>
@endpush

@section('content')
<div class="d-topbar">
  <div>
    <h1 class="d-page-title">Mis Sesiones</h1>
    <p style="color:var(--d-muted);font-size:14px;margin:2px 0 0;">Cola de atención y historial</p>
  </div>
</div>

@if(session('ok_sesion'))
  <div class="mb-4 sesh-alert-ok">
    ✅ {{ session('ok_sesion') }}
  </div>
@endif

{{-- Tabs --}}
<div class="sesh-tabs">
  <button class="sesh-tab active" onclick="switchSeshTab('pendientes', this)">
    <i data-lucide="clock" style="width:13px;vertical-align:-2px;"></i>
    Pendientes
    @if($pendientes->total() > 0)
      <span class="sesh-pill danger" style="margin-left:4px;">{{ $pendientes->total() }}</span>
    @endif
  </button>
  <button class="sesh-tab" onclick="switchSeshTab('historial', this)">
    <i data-lucide="history" style="width:13px;vertical-align:-2px;"></i>
    Historial
  </button>
</div>

{{-- TAB: PENDIENTES --}}
<div id="tab-pendientes" class="sesh-panel active">
  <div class="d-card">
    @forelse($pendientes as $s)
      <div class="sesh-row">
        <div class="d-avatar sesh-avatar tone-warning">
          {{ mb_substr($s->patient?->user?->name ?? 'P', 0, 1) }}
        </div>
        <div style="flex:1;">
          <div style="font-size:14px;font-weight:700;">{{ $s->patient?->user?->name ?? '—' }}</div>
          <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
            📅 {{ $s->scheduled_at?->isoFormat('dddd D [de] MMMM · H:mm') ?? '—' }}
            @if($s->deducts) · <span style="color:var(--d-warning);">Descuenta sesión</span> @endif
          </div>
          @if($s->notes)
            <div style="font-size:12px;color:var(--d-muted);margin-top:3px;font-style:italic;">📝 {{ Str::limit($s->notes, 80) }}</div>
          @endif
        </div>
        <button onclick="openAtencion({{ $s->id }}, '{{ addslashes($s->patient?->user?->name ?? '') }}', '{{ $s->scheduled_at?->format('d/m/Y H:i') }}')"
          class="d-btn d-btn-primary" style="font-size:13px;white-space:nowrap;">
          <i data-lucide="clipboard-check" style="width:14px;"></i> Registrar
        </button>
      </div>
    @empty
      <div style="text-align:center;padding:40px;">
        <div style="font-size:48px;margin-bottom:12px;">🎉</div>
        <p style="color:var(--d-muted);font-size:14px;margin:0;">Sin sesiones pendientes. ¡Todo al día!</p>
      </div>
    @endforelse
  </div>
  @if($pendientes->hasPages())
    <div style="margin-top:16px;">{{ $pendientes->withQueryString()->links() }}</div>
  @endif
</div>

{{-- TAB: HISTORIAL --}}
<div id="tab-historial" class="sesh-panel">
  <div class="d-card">
    @php
      $dotMap = ['done'=>'dot-done','no_show'=>'dot-no_show','rescheduled'=>'dot-rescheduled','cancelled'=>'dot-cancelled','pending'=>'dot-pending'];
    @endphp
    @forelse($historial as $s)
      <div class="sesh-row">
        <div class="st-status-dot {{ $dotMap[$s->status] ?? 'dot-cancelled' }}"></div>
        <div style="flex:1;">
          <div style="font-size:14px;font-weight:600;">{{ $s->patient?->user?->name ?? '—' }}</div>
          <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
            {{ $s->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
            @if($s->weight_kg) · ⚖️ {{ $s->weight_kg }} kg @endif
            @if($s->rpe) · RPE {{ $s->rpe }}/10 @endif
          </div>
          @if($s->notes)
            <div style="font-size:12px;color:var(--d-muted);margin-top:3px;font-style:italic;">{{ Str::limit($s->notes, 60) }}</div>
          @endif
        </div>
        <span class="sesh-status-badge is-{{ $s->status }}">
          {{ $s->statusLabel() }}
        </span>
      </div>
    @empty
      <p style="color:var(--d-muted);text-align:center;padding:32px;margin:0;">Sin historial de sesiones.</p>
    @endforelse
  </div>
  @if($historial->hasPages())
    <div style="margin-top:16px;">{{ $historial->appends(['page' => $pendientes->currentPage()])->links('pagination::default', ['pageName' => 'hist_page']) }}</div>
  @endif
</div>
@endsection

{{-- ========== MODAL DE REGISTRO DE ATENCIÓN ========== --}}
<div class="at-overlay" id="modal-atencion" onclick="if(event.target===this)closeAtencion()">
  <div class="d-card at-modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h3 style="margin:0;font-size:16px;font-weight:700;">📋 Registrar atención</h3>
      <button onclick="closeAtencion()" style="background:none;border:none;cursor:pointer;color:var(--d-muted);">
        <i data-lucide="x" style="width:20px;"></i>
      </button>
    </div>

    <div id="modal-paciente-info" class="at-paciente-info">
      <div id="modal-paciente-nombre" style="font-weight:700;"></div>
      <div id="modal-sesion-fecha" style="color:var(--d-muted);font-size:12px;margin-top:2px;"></div>
    </div>

    <form id="form-atencion" method="POST" action="">
      @csrf @method('PATCH')

      <div class="d-form-group mb-3">
        <label class="d-label">Estado <span style="color:var(--d-danger);">*</span></label>
        <select name="status" class="d-select" required onchange="toggleReschedule(this.value)">
          <option value="done">✅ Sesión realizada</option>
          <option value="no_show">❌ No asistió (no show)</option>
          <option value="rescheduled">📅 Reprogramar</option>
          <option value="cancelled">🚫 Cancelar</option>
        </select>
      </div>

      <div id="rescheduled-row" class="d-form-group mb-3" style="display:none;">
        <label class="d-label">Nueva fecha <span style="color:var(--d-danger);">*</span></label>
        <input type="datetime-local" name="rescheduled_at" class="d-input">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label class="d-label">Peso registrado (kg)</label>
          <input type="number" name="weight_kg" class="d-input" step="0.1" min="20" max="300" placeholder="Ej: 75.5">
        </div>
        <div>
          <label class="d-label">RPE (1=fácil · 10=máximo)</label>
          <input type="number" name="rpe" class="d-input" min="1" max="10" placeholder="1–10">
        </div>
      </div>

      <div class="d-form-group mb-4">
        <label class="d-label">Notas de la sesión</label>
        <textarea name="notes" class="d-input" rows="3" placeholder="Observaciones clínicas, evolución, indicaciones…" style="resize:vertical;min-height:72px;"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closeAtencion()" class="d-btn d-btn-outline">Cancelar</button>
        <button type="submit" class="d-btn d-btn-primary">
          <i data-lucide="save"></i> Guardar
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();

  function switchSeshTab(name, btn) {
    document.querySelectorAll('.sesh-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sesh-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
  }

  function openAtencion(id, nombre, fecha) {
    const base = '{{ route('estudiante.sesiones') }}';
    document.getElementById('form-atencion').action = `/estudiante/sesiones/${id}/atencion`;
    document.getElementById('modal-paciente-nombre').textContent = nombre;
    document.getElementById('modal-sesion-fecha').textContent = '📅 ' + fecha;
    document.getElementById('modal-atencion').classList.add('open');
  }

  function closeAtencion() {
    document.getElementById('modal-atencion').classList.remove('open');
  }

  function toggleReschedule(val) {
    document.getElementById('rescheduled-row').style.display = val === 'rescheduled' ? 'block' : 'none';
    document.querySelector('[name="rescheduled_at"]').required = val === 'rescheduled';
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAtencion(); });
</script>
@endpush
