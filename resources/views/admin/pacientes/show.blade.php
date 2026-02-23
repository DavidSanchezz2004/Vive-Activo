@extends('layouts.dashboard')

@section('title', ($patient->user->name ?? 'Paciente') . ' - Vive Activo')
@section('page_title', 'Detalle del Paciente')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
  <style>
    /* ---------- TABS ---------- */
    .va-tabs { display:flex; gap:4px; border-bottom:2px solid var(--d-border); margin-bottom:24px; }
    .va-tab  {
      padding:10px 18px; font-size:14px; font-weight:500; cursor:pointer;
      border-radius:8px 8px 0 0; color:var(--d-muted); border:none;
      background:transparent; transition:color .15s, background .15s;
    }
    .va-tab:hover  { color:var(--d-text); }
    .va-tab.active { color:var(--d-brand); background:rgba(5,150,105,.08); border-bottom:2px solid var(--d-brand); margin-bottom:-2px; }
    .va-panel { display:none; }
    .va-panel.active { display:block; }

    /* ---------- MODAL ---------- */
    .va-overlay {
      display:none; position:fixed; inset:0; background:rgba(0,0,0,.6);
      z-index:1000; align-items:center; justify-content:center;
    }
    .va-overlay.open { display:flex; }
    .va-modal {
      background:#ffffff;
      border:1px solid #e5e7eb;
      border-radius:16px; width:100%; max-width:460px;
      padding:28px; box-shadow:0 24px 60px rgba(0,0,0,.2);
      animation:modalIn .2s ease;
    }
    html.dark .va-modal {
      background:#1e2a3a;
      border-color:rgba(255,255,255,.08);
      box-shadow:0 24px 60px rgba(0,0,0,.6);
    }
    @keyframes modalIn { from { transform:scale(.94); opacity:0; } to { transform:scale(1); opacity:1; } }
    .va-modal-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .va-modal-title { font-size:17px; font-weight:700; }

    /* ---------- HISTORIAL ---------- */
    .hist-row { display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid var(--d-border); }
    .hist-row:last-child { border-bottom:none; }
    .hist-dot {
      width:10px; height:10px; border-radius:50%; margin-top:5px; flex-shrink:0;
      background:var(--d-brand);
    }
    .hist-dot.closed { background:var(--d-muted); }
  </style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container" style="max-width:900px;">

    @if(session('ok'))
      <div class="mb-4" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;font-size:14px;font-weight:500;">
        {{ session('ok') }}
      </div>
    @endif

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--d-muted);">
      <a href="{{ route('admin.pacientes.index') }}" style="color:var(--d-muted);text-decoration:none;">Pacientes</a>
      <i data-lucide="chevron-right" style="width:14px;"></i>
      <span style="color:var(--d-text);font-weight:500;">{{ $patient->user->name ?? 'Detalle' }}</span>
    </div>

    {{-- Header card --}}
    @php
      $user     = $patient->user;
      $profile  = $user?->profile;
      $initials = collect(explode(' ', trim($user->name ?? 'P')))
                    ->filter()->take(2)->map(fn($w)=>mb_substr($w,0,1))->implode('');
      $assignment = $patient->activeAssignment;
    @endphp
    <div class="d-card mb-4" style="display:flex;align-items:center;gap:20px;">
      <div class="d-avatar" style="width:60px;height:60px;font-size:22px;">{{ $initials ?: 'P' }}</div>
      <div style="flex:1;">
        <div style="font-size:18px;font-weight:700;">{{ $user->name ?? '—' }}</div>
        <div style="font-size:13px;color:var(--d-muted);">{{ $user->email ?? '—' }}</div>
        @if($profile?->document_number)
          <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
            {{ $profile->document_type ?? 'DNI' }}: {{ $profile->document_number }}
            @if($profile->district) · {{ $profile->district }} @endif
          </div>
        @endif
      </div>
      <div style="display:flex;gap:8px;">
        <span class="d-badge {{ $patient->is_active ? 'd-badge-green' : 'd-badge-red' }}">
          {{ $patient->is_active ? 'Activo' : 'Inactivo' }}
        </span>
        <form action="{{ route('admin.pacientes.toggle', $patient) }}" method="POST">
          @csrf @method('PATCH')
          <button type="submit" class="d-btn d-btn-outline" style="font-size:12px;padding:4px 10px;">
            {{ $patient->is_active ? 'Desactivar' : 'Activar' }}
          </button>
        </form>
      </div>
    </div>

    {{-- TABS --}}
    <div class="va-tabs">
      <button class="va-tab active" onclick="switchTab('asignacion', this)">
        <i data-lucide="link" style="width:14px;vertical-align:-2px;"></i> Asignación
      </button>
      <button class="va-tab" onclick="switchTab('consultas', this)">
        <i data-lucide="file-text" style="width:14px;vertical-align:-2px;"></i> Consultas
      </button>
      <button class="va-tab" onclick="switchTab('sesiones', this)">
        <i data-lucide="calendar" style="width:14px;vertical-align:-2px;"></i> Sesiones
      </button>
      <button class="va-tab" onclick="switchTab('pagos', this)">
        <i data-lucide="credit-card" style="width:14px;vertical-align:-2px;"></i> Pagos
      </button>
      <button class="va-tab" onclick="switchTab('documentos', this)">
        <i data-lucide="paperclip" style="width:14px;vertical-align:-2px;"></i> Documentos
      </button>
      <button class="va-tab" onclick="switchTab('nutricion', this)">
        <i data-lucide="salad" style="width:14px;vertical-align:-2px;"></i> Nutrición
      </button>
    </div>

    {{-- ========== TAB: ASIGNACIÓN ========== --}}
    <div id="tab-asignacion" class="va-panel active">

      {{-- Alumno actual --}}
      <div class="d-card mb-4">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Alumno asignado</h3>
          <button class="d-btn d-btn-primary" onclick="openModal()">
            <i data-lucide="{{ $assignment ? 'refresh-cw' : 'plus' }}"></i>
            {{ $assignment ? 'Reasignar' : 'Asignar alumno' }}
          </button>
        </div>

        @if($assignment)
          @php $st = $assignment->student; @endphp
          <div style="display:flex;align-items:center;gap:16px;padding:16px;background:var(--d-bg);border-radius:12px;">
            <div class="d-avatar" style="width:48px;height:48px;font-size:16px;background:var(--d-info);">
              {{ mb_substr($st?->user?->name ?? 'A', 0, 1) }}
            </div>
            <div style="flex:1;">
              <div style="font-weight:700;">{{ $st?->user?->name ?? '—' }}</div>
              <div style="font-size:13px;color:var(--d-muted);">{{ $st?->user?->email ?? '—' }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                Asignado el {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '—' }}
                @if($assignment->reason)
                  · <em>{{ $assignment->reason }}</em>
                @endif
              </div>
            </div>
            <form action="{{ route('admin.pacientes.unassign', $patient) }}" method="POST"
                  onsubmit="return confirm('¿Desasignar al alumno actual?')">
              @csrf
              <button type="submit" class="d-btn d-btn-danger" style="font-size:13px;">
                <i data-lucide="user-x"></i> Desasignar
              </button>
            </form>
          </div>
        @else
          <div style="padding:24px;text-align:center;color:var(--d-muted);">
            <i data-lucide="user-x" style="width:28px;height:28px;display:block;margin:0 auto 8px;"></i>
            Este paciente no tiene alumno asignado.
          </div>
        @endif
      </div>

      {{-- Plan comercial --}}
      @php
        $activePlan = $patient->activePlan;
      @endphp
      <div class="d-card mb-4">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Plan comercial</h3>
          <a href="{{ route('admin.planes.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="package"></i> Ver catálogo
          </a>
        </div>

        @if($activePlan)
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:16px;background:var(--d-bg);border-radius:12px;">
            <div style="flex:1;min-width:240px;">
              <div style="font-weight:700;">{{ $activePlan->plan?->name ?? '—' }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                Vigencia: {{ $activePlan->starts_at?->format('d/m/Y') ?? '—' }} → {{ $activePlan->ends_at?->format('d/m/Y') ?? '—' }}
                · Estado: <strong>{{ $activePlan->statusLabel() }}</strong>
              </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
              <span class="d-badge" style="background:var(--d-brand-light);color:var(--d-brand-dark);">
                Consumidas: {{ $activePlan->sessions_used }}
              </span>
              <span class="d-badge" style="background:var(--d-info);color:#fff;">
                Total: {{ ($activePlan->plan?->sessions_total ?? 0) === 0 ? '∞' : $activePlan->plan?->sessions_total }}
              </span>
              <span class="d-badge" style="background:var(--d-warning);color:#111827;">
                Restantes: {{ $activePlan->sessionsRemaining() === null ? '∞' : $activePlan->sessionsRemaining() }}
              </span>
              <span class="d-badge" style="background:var(--d-muted);color:#fff;">
                Días: {{ $activePlan->daysLeft() }}
              </span>
            </div>

            <form action="{{ route('admin.pacientes.planes.cancel', [$patient, $activePlan]) }}" method="POST"
                  onsubmit="return confirm('¿Cancelar el plan comercial activo?');">
              @csrf @method('PATCH')
              <button type="submit" class="d-btn d-btn-danger" style="font-size:13px;">
                <i data-lucide="ban"></i> Cancelar plan
              </button>
            </form>
          </div>
        @else
          <div style="padding:16px;background:var(--d-bg);border-radius:12px;color:var(--d-muted);font-size:13px;">
            Este paciente no tiene un plan comercial activo.
          </div>
        @endif

        <div style="margin-top:14px;border-top:1px solid var(--d-border);padding-top:14px;">
          <h4 style="margin:0 0 10px;font-size:14px;font-weight:700;">Asignar nuevo plan</h4>
          <form method="POST" action="{{ route('admin.pacientes.planes.store', $patient) }}" style="display:grid;grid-template-columns:2fr 1fr;gap:12px;align-items:end;">
            @csrf
            <div>
              <label class="d-label">Plan</label>
              <select name="plan_id" class="d-select" required>
                <option value="">Selecciona un plan…</option>
                @foreach($plans as $plan)
                  <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                    {{ $plan->name }} — {{ $plan->sessions_total === 0 ? '∞ sesiones' : $plan->sessions_total . ' sesiones' }} / {{ $plan->duration_months }} mes(es)
                  </option>
                @endforeach
              </select>
              @error('plan_id')
                <div style="font-size:12px;color:var(--d-danger);margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <label class="d-label">Inicio</label>
              <input type="date" name="starts_at" class="d-input" value="{{ old('starts_at', now()->toDateString()) }}" required>
              @error('starts_at')
                <div style="font-size:12px;color:var(--d-danger);margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>

            <div style="grid-column:span 2;">
              <label class="d-label">Notas (opcional)</label>
              <textarea name="notes" class="d-textarea" rows="2" placeholder="Observaciones sobre el plan...">{{ old('notes') }}</textarea>
              @error('notes')
                <div style="font-size:12px;color:var(--d-danger);margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>

            <div style="grid-column:span 2;display:flex;justify-content:flex-end;">
              <button type="submit" class="d-btn d-btn-primary" style="font-size:13px;">
                <i data-lucide="plus"></i> Asignar plan
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Historial --}}
      <div class="d-card">
        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">Historial de asignaciones</h3>
        @forelse($patient->assignments as $a)
          <div class="hist-row">
            <div class="hist-dot {{ $a->is_active ? '' : 'closed' }}"></div>
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;">
                {{ $a->student?->user?->name ?? '—' }}
                @if($a->is_active)
                  <span class="d-badge d-badge-green" style="font-size:11px;">Activa</span>
                @else
                  <span class="d-badge" style="font-size:11px;background:var(--d-muted);color:#fff;">Cerrada</span>
                @endif
              </div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                Desde: {{ $a->assigned_at?->format('d/m/Y') ?? '—' }}
                @if($a->unassigned_at)
                  &nbsp;→&nbsp;Hasta: {{ $a->unassigned_at?->format('d/m/Y') }}
                @endif
                @if($a->reason)
                  &nbsp;· <em>{{ $a->reason }}</em>
                @endif
              </div>
            </div>
            <div style="font-size:12px;color:var(--d-muted);">
              Por: {{ $a->assignedBy?->name ?? '—' }}
            </div>
          </div>
        @empty
          <p style="color:var(--d-muted);text-align:center;margin:0;">Sin historial de asignaciones.</p>
        @endforelse
      </div>

      {{-- Historial de planes comerciales --}}
      <div class="d-card" style="margin-top:16px;">
        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">Historial de planes comerciales</h3>

        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:800px;">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Vigencia</th>
                <th style="text-align:center;">Sesiones</th>
                <th style="text-align:center;">Estado</th>
                <th>Asignado por</th>
              </tr>
            </thead>
            <tbody>
              @forelse($patient->patientPlans as $pp)
                <tr>
                  <td style="font-weight:600;">{{ $pp->plan?->name ?? '—' }}</td>
                  <td style="font-size:12px;color:var(--d-muted);">
                    {{ $pp->starts_at?->format('d/m/Y') ?? '—' }} → {{ $pp->ends_at?->format('d/m/Y') ?? '—' }}
                  </td>
                  <td style="text-align:center;">
                    {{ $pp->sessions_used }} / {{ ($pp->plan?->sessions_total ?? 0) === 0 ? '∞' : $pp->plan?->sessions_total }}
                  </td>
                  <td style="text-align:center;">
                    @php
                      $badgeClass = $pp->status === 'active' ? 'd-badge-green' : ($pp->status === 'cancelled' ? 'd-badge-red' : '');
                    @endphp
                    <span class="d-badge {{ $badgeClass }}">
                      {{ $pp->statusLabel() }}
                    </span>
                  </td>
                  <td style="font-size:12px;color:var(--d-muted);">{{ $pp->createdBy?->name ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" style="padding:16px;opacity:.8;">Sin planes asignados.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ========== TAB: CONSULTAS ========== --}}
    <div id="tab-consultas" class="va-panel">
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Consultas</h3>
          <a href="{{ route('admin.consultas.create', ['patient_id' => $patient->id]) }}" class="d-btn d-btn-primary" style="font-size:13px;">
            <i data-lucide="plus"></i> Nueva consulta
          </a>
        </div>
        @forelse($patient->consultations()->orderByDesc('scheduled_at')->get() as $c)
          @php
            $statusColors = ['pending_confirmation'=>'#b45309','confirmed'=>'#059669','completed'=>'#475569','cancelled'=>'#dc2626'];
            $statusBg = ['pending_confirmation'=>'rgba(245,158,11,.15)','confirmed'=>'rgba(16,185,129,.15)','completed'=>'rgba(100,116,139,.15)','cancelled'=>'rgba(239,68,68,.15)'];
            $modeIcons = ['presencial'=>'🏥','zoom'=>'🎥','meet'=>'📹'];
          @endphp
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--d-border);">
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;">{{ $c->type ?: 'Consulta nutricional' }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                {{ $modeIcons[$c->mode] ?? '' }} {{ $c->modeLabel() }}
                · {{ $c->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
              </div>
            </div>
            <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;color:{{ $statusColors[$c->status]??'#475569' }};background:{{ $statusBg[$c->status]??'rgba(100,116,139,.15)' }};">
              {{ $c->statusLabel() }}
            </span>
            <a href="{{ route('admin.consultas.edit', $c) }}" class="d-btn d-btn-outline action-btn" title="Editar">
              <i data-lucide="pencil"></i>
            </a>
          </div>
        @empty
          <p style="color:var(--d-muted);text-align:center;padding:24px 0;margin:0;">Sin consultas registradas.</p>
        @endforelse
      </div>
    </div>

    {{-- ========== TAB: SESIONES ========== --}}
    <div id="tab-sesiones" class="va-panel">
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Sesiones nutricionales</h3>
          <a href="{{ route('admin.sesiones.create', ['patient_id' => $patient->id]) }}" class="d-btn d-btn-primary" style="font-size:13px;">
            <i data-lucide="plus"></i> Nueva sesión
          </a>
        </div>
        @forelse($patient->patientSessions()->orderByDesc('scheduled_at')->with('student.user')->get() as $s)
          @php
            $sColors = ['pending'=>'#b45309','done'=>'#059669','no_show'=>'#dc2626','rescheduled'=>'#2563eb','cancelled'=>'#475569'];
            $sBg = ['pending'=>'rgba(245,158,11,.15)','done'=>'rgba(16,185,129,.15)','no_show'=>'rgba(239,68,68,.15)','rescheduled'=>'rgba(59,130,246,.15)','cancelled'=>'rgba(100,116,139,.15)'];
          @endphp
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--d-border);">
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;">Con {{ $s->student?->user?->name ?? '—' }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                {{ $s->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                @if($s->deducts) · <span style="color:var(--d-danger);">Descuenta</span> @endif
              </div>
            </div>
            <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;color:{{ $sColors[$s->status]??'#475569' }};background:{{ $sBg[$s->status]??'rgba(100,116,139,.15)' }};">
              {{ $s->statusLabel() }}
            </span>
            <a href="{{ route('admin.sesiones.edit', $s) }}" class="d-btn d-btn-outline action-btn" title="Editar">
              <i data-lucide="pencil"></i>
            </a>
          </div>
        @empty
          <p style="color:var(--d-muted);text-align:center;padding:24px 0;margin:0;">Sin sesiones registradas.</p>
        @endforelse
      </div>
    </div>

    {{-- ========== TAB: PAGOS ========== --}}
    <div id="tab-pagos" class="va-panel">

      {{-- Flash pagos --}}
      @if(session('ok_pagos'))
        <div class="mb-3" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 14px;border-radius:10px;font-size:14px;">{{ session('ok_pagos') }}</div>
      @endif

      {{-- Formulario nuevo pago --}}
      <div class="d-card mb-4">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;"><i data-lucide="plus-circle" style="width:15px;vertical-align:-2px;"></i> Registrar pago</h3>
        <form action="{{ route('admin.pacientes.pagos.store', $patient) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
              <label class="d-label">Concepto</label>
              <input type="text" name="concept" class="d-input" placeholder="Ej: Sesión mensual, Plan anual…">
            </div>
            <div>
              <label class="d-label">Monto <span style="color:var(--d-danger);">*</span></label>
              <input type="number" step="0.01" min="0.01" name="amount" class="d-input" placeholder="0.00" required>
            </div>
            <div>
              <label class="d-label">Moneda</label>
              <select name="currency" class="d-select">
                <option value="PEN">PEN (S/)</option>
                <option value="USD">USD ($)</option>
              </select>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;margin-bottom:14px;">
            <div>
              <label class="d-label">Estado</label>
              <select name="status" class="d-select">
                <option value="paid">Pagado</option>
                <option value="pending">Pendiente</option>
                <option value="cancelled">Cancelado</option>
              </select>
            </div>
            <div>
              <label class="d-label">Fecha de pago</label>
              <input type="date" name="paid_at" class="d-input" value="{{ date('Y-m-d') }}">
            </div>
            <div>
              <label class="d-label">Comprobante (PDF/imagen, máx 5 MB)</label>
              <input type="file" name="receipt" class="d-input" accept=".pdf,.jpg,.jpeg,.png" style="padding:6px;">
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end;">
            <button type="submit" class="d-btn d-btn-primary" style="font-size:13px;">
              <i data-lucide="save"></i> Guardar pago
            </button>
          </div>
        </form>
      </div>

      {{-- Listado de pagos --}}
      <div class="d-card">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;">Historial de pagos</h3>
        @php
          $pColors = ['paid'=>'#059669','pending'=>'#b45309','cancelled'=>'#dc2626'];
          $pBg     = ['paid'=>'rgba(16,185,129,.15)','pending'=>'rgba(245,158,11,.15)','cancelled'=>'rgba(239,68,68,.15)'];
        @endphp
        @forelse($patient->payments()->orderByDesc('paid_at')->get() as $pay)
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--d-border);">
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;">{{ $pay->concept ?: 'Sin concepto' }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                {{ $pay->paid_at?->format('d/m/Y') ?? $pay->created_at?->format('d/m/Y') }}
                · {{ $pay->currency }}
              </div>
            </div>
            <div style="font-weight:700;font-size:15px;">{{ $pay->currency === 'USD' ? '$' : 'S/' }} {{ number_format($pay->amount, 2) }}</div>
            <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;color:{{ $pColors[$pay->status]??'#475569' }};background:{{ $pBg[$pay->status]??'rgba(100,116,139,.15)' }};">
              {{ $pay->statusLabel() }}
            </span>
            @if($pay->receipt_path)
              <a href="{{ Storage::url($pay->receipt_path) }}" target="_blank" class="d-btn d-btn-outline action-btn" title="Ver comprobante" style="font-size:12px;">
                <i data-lucide="paperclip"></i>
              </a>
            @endif
            <form action="{{ route('admin.pacientes.pagos.destroy', [$patient, $pay]) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar este pago?')">
              @csrf @method('DELETE')
              <button type="submit" class="d-btn d-btn-danger action-btn" title="Eliminar" style="font-size:12px;">
                <i data-lucide="trash-2"></i>
              </button>
            </form>
          </div>
        @empty
          <p style="color:var(--d-muted);text-align:center;padding:24px 0;margin:0;">Sin pagos registrados.</p>
        @endforelse
      </div>
    </div>

    {{-- ========== TAB: DOCUMENTOS ========== --}}
    <div id="tab-documentos" class="va-panel">

      @if(session('ok_docs'))
        <div class="mb-3" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 14px;border-radius:10px;font-size:14px;">{{ session('ok_docs') }}</div>
      @endif

      {{-- Upload --}}
      <div class="d-card mb-4">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;"><i data-lucide="upload" style="width:15px;vertical-align:-2px;"></i> Subir documento</h3>
        <form action="{{ route('admin.pacientes.documentos.store', $patient) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div style="display:grid;grid-template-columns:1fr 2fr;gap:14px;align-items:end;margin-bottom:14px;">
            <div>
              <label class="d-label">Tipo de documento</label>
              <select name="type" class="d-select">
                <option value="">— Sin categoría —</option>
                @foreach(\App\Models\Document::TYPES as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="d-label">Archivo <span style="color:var(--d-danger);">*</span> (PDF, imagen, Word, Excel — máx 10 MB)</label>
              <input type="file" name="file" class="d-input" required
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                style="padding:6px;">
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end;">
            <button type="submit" class="d-btn d-btn-primary" style="font-size:13px;">
              <i data-lucide="upload-cloud"></i> Subir
            </button>
          </div>
        </form>
      </div>

      {{-- Listado documentos --}}
      <div class="d-card">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;">Documentos subidos</h3>
        @forelse($patient->documents()->orderByDesc('created_at')->get() as $doc)
          <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--d-border);">
            <div style="font-size:24px;">
              @if($doc->isImage()) 🖼️
              @elseif(str_contains($doc->mime_type??'','pdf')) 📄
              @elseif(str_contains($doc->mime_type??'','word')) 📝
              @elseif(str_contains($doc->mime_type??'','excel') || str_contains($doc->mime_type??'','spreadsheet')) 📊
              @else 📎
              @endif
            </div>
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;">{{ $doc->original_name }}</div>
              <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                {{ $doc->typeLabel() }} · {{ $doc->humanSize() }}
                · Subido el {{ $doc->created_at?->format('d/m/Y') }}
                @if($doc->uploadedBy) por {{ $doc->uploadedBy->name }} @endif
              </div>
            </div>
            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="d-btn d-btn-outline action-btn" title="Descargar">
              <i data-lucide="download"></i>
            </a>
            <form action="{{ route('admin.pacientes.documentos.destroy', [$patient, $doc]) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar este documento?')">
              @csrf @method('DELETE')
              <button type="submit" class="d-btn d-btn-danger action-btn" title="Eliminar">
                <i data-lucide="trash-2"></i>
              </button>
            </form>
          </div>
        @empty
          <p style="color:var(--d-muted);text-align:center;padding:24px 0;margin:0;">Sin documentos subidos.</p>
        @endforelse
      </div>
    </div>

    {{-- ========== TAB: NUTRICIÓN ========== --}}
    <div id="tab-nutricion" class="va-panel">
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">🥗 Plan Nutricional</h3>
          <a href="{{ route('admin.pacientes.nutrition_plans.create', $patient) }}" class="d-btn d-btn-primary" style="font-size:13px;">
            <i data-lucide="plus"></i> Nuevo plan
          </a>
        </div>

        @if($nutritionPlans->isEmpty())
          <div style="text-align:center;padding:40px;">
            <div style="font-size:48px;margin-bottom:12px;">🥗</div>
            <p style="color:var(--d-muted);font-size:14px;margin:0;">Sin planes nutricionales registrados.<br>Crea el primero para este paciente.</p>
          </div>
        @else
          @foreach($nutritionPlans as $np)
            <div style="background:var(--d-bg);border-radius:12px;padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;">
              <div style="width:40px;height:40px;border-radius:10px;background:{{ $np->is_active ? 'rgba(5,150,105,.15)' : 'rgba(100,116,139,.12)' }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">🥗</div>
              <div style="flex:1;">
                <div style="font-size:14px;font-weight:700;">{{ $np->phase ?? 'Plan Nutricional' }}</div>
                <div style="font-size:12px;color:var(--d-muted);margin-top:2px;">
                  {{ $np->valid_from?->format('d/m/Y') }} {{ $np->valid_until ? '– '.$np->valid_until->format('d/m/Y') : '' }}
                  @if($np->kcal_target) · {{ number_format($np->kcal_target) }} kcal/día @endif
                </div>
              </div>
              @if($np->is_active)
                <span class="d-badge d-badge-green" style="font-size:11px;">Activo</span>
              @else
                <span class="d-badge" style="font-size:11px;background:var(--d-bg);color:var(--d-muted);">Archivado</span>
              @endif
              <a href="{{ route('admin.pacientes.nutrition_plans.show', [$patient, $np]) }}" class="d-btn d-btn-outline" style="font-size:13px;padding:6px 12px;">
                Ver plan
              </a>
            </div>
          @endforeach
        @endif
      </div>
    </div>

  </div>
</div>

{{-- ========== MODAL ASIGNACIÓN ========== --}}
<div class="va-overlay" id="modal-asignacion" onclick="closeModalOutside(event)">
  <div class="va-modal" role="dialog" aria-modal="true" style="max-width:520px;">
    <div class="va-modal-head">
      <div class="va-modal-title">
        <i data-lucide="{{ $assignment ? 'refresh-cw' : 'user-plus' }}" style="width:18px;vertical-align:-3px;"></i>
        {{ $assignment ? 'Reasignar alumno' : 'Asignar alumno' }}
      </div>
      <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--d-muted);">
        <i data-lucide="x" style="width:20px;"></i>
      </button>
    </div>

    {{-- Alumno actual --}}
    @if($assignment)
      <div style="background:var(--d-bg);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px;">
        <div class="d-avatar" style="width:32px;height:32px;font-size:12px;background:var(--d-info);flex-shrink:0;">
          {{ mb_substr($assignment->student?->user?->name ?? 'A', 0, 1) }}
        </div>
        <div>
          <div style="font-weight:700;">{{ $assignment->student?->user?->name ?? '—' }}</div>
          <div style="font-size:12px;color:var(--d-muted);">Alumno actual · Asignado {{ $assignment->assigned_at?->format('d/m/Y') }}</div>
        </div>
      </div>
    @endif

    {{-- ⚠️ WARNING de conflictos --}}
    @if($conflicts->isNotEmpty())
      <div id="conflict-warning" style="background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.35);border-radius:12px;padding:14px 16px;margin-bottom:18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;font-weight:700;font-size:14px;color:#dc2626;">
          <i data-lucide="alert-triangle" style="width:16px;flex-shrink:0;"></i>
          Hay compromisos activos con el alumno actual
        </div>
        <ul style="margin:0;padding-left:18px;font-size:13px;color:#7f1d1d;line-height:1.7;">
          @foreach($conflicts as $conflict)
            <li>
              <strong>{{ $conflict['date'] }}</strong> — {{ $conflict['desc'] }}
              <span style="font-size:11px;background:{{ $conflict['type']==='sesion' ? 'rgba(245,158,11,.2)' : 'rgba(59,130,246,.2)' }};color:{{ $conflict['type']==='sesion' ? '#b45309' : '#1d4ed8' }};padding:1px 6px;border-radius:10px;margin-left:4px;">
                {{ $conflict['type'] === 'sesion' ? 'Sesión' : 'Consulta' }}
              </span>
            </li>
          @endforeach
        </ul>
        <div style="margin-top:12px;font-size:12px;color:#991b1b;opacity:.8;">
          Reasignar no cancela automáticamente estos compromisos. Deberás gestionarlos manualmente después.
        </div>
      </div>
    @endif

    {{-- Errores de validación --}}
    @if($errors->any())
      <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#dc2626;">
        <ul style="margin:0;padding-left:16px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('admin.pacientes.assign', $patient) }}" method="POST" id="form-asignacion">
      @csrf

      {{-- Campo oculto: indica al backend si había conflictos --}}
      <input type="hidden" name="has_conflicts" value="{{ $conflicts->isNotEmpty() ? '1' : '0' }}">

      <div class="d-form-group mb-3">
        <label class="d-label" for="student_id">Nuevo alumno <span style="color:var(--d-danger);">*</span></label>
        <select id="student_id" name="student_id" class="d-select" required>
          <option value="">— Seleccionar alumno activo —</option>
          @foreach($students as $st)
            <option value="{{ $st->id }}"
              @if($assignment && $assignment->student_id === $st->id) disabled @endif
              @selected(old('student_id') == $st->id)>
              {{ $st->user?->name ?? '—' }} ({{ $st->user?->email ?? '—' }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="d-form-group mb-3">
        <label class="d-label" for="reason">
          Motivo
          @if($conflicts->isNotEmpty())
            <span style="color:var(--d-danger);">* (obligatorio por conflictos)</span>
          @else
            <span style="color:var(--d-muted);font-weight:400;">(opcional)</span>
          @endif
        </label>
        <textarea id="reason" name="reason" class="d-input" rows="2"
          placeholder="Ej: Reasignación por cambio de turno, rotación semestral..."
          style="resize:vertical;min-height:64px;"
          {{ $conflicts->isNotEmpty() ? 'required' : '' }}>{{ old('reason') }}</textarea>
      </div>

      {{-- Checkbox de confirmación (solo si hay conflictos) --}}
      @if($conflicts->isNotEmpty())
        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.4);border-radius:10px;padding:12px 14px;margin-bottom:16px;">
          <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;color:var(--d-text);">
            <input type="checkbox" name="confirm_conflicts" value="1"
              id="chk-confirm"
              style="margin-top:2px;width:16px;height:16px;accent-color:var(--d-brand);flex-shrink:0;"
              onchange="document.getElementById('btn-asignar').disabled = !this.checked;"
              {{ old('confirm_conflicts') ? 'checked' : '' }}>
            <span>
              <strong>Entiendo y deseo reasignar igualmente.</strong><br>
              <span style="color:var(--d-muted);font-size:12px;">Reconozco que hay compromisos activos y me haré responsable de gestionarlos.</span>
            </span>
          </label>
        </div>
      @endif

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closeModal()" class="d-btn d-btn-outline">Cancelar</button>
        <button type="submit" id="btn-asignar" class="d-btn d-btn-primary"
          {{ $conflicts->isNotEmpty() && !old('confirm_conflicts') ? 'disabled' : '' }}>
          <i data-lucide="{{ $assignment ? 'refresh-cw' : 'user-plus' }}"></i>
          {{ $assignment ? 'Reasignar' : 'Asignar' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    lucide.createIcons();

    /* ---------- TABS ---------- */
    function switchTab(name, btn) {
      document.querySelectorAll('.va-panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.va-tab').forEach(b => b.classList.remove('active'));
      document.getElementById('tab-' + name).classList.add('active');
      btn.classList.add('active');
    }

    /* ---------- MODAL ---------- */
    function openModal()  { document.getElementById('modal-asignacion').classList.add('open'); }
    function closeModal() { document.getElementById('modal-asignacion').classList.remove('open'); }
    function closeModalOutside(e) { if (e.target === e.currentTarget) closeModal(); }

    /* Cerrar con ESC */
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    /* Abrir modal automáticamente si hay errores de validación del assign --}}*/
    @if($errors->any())
      openModal();
    @endif
  </script>
@endpush
