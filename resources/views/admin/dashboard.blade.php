@extends('layouts.dashboard')

@section('title', 'Admin - Panel General')
@section('page_title', 'Panel General')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- KPIs --}}
    <section class="d-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));margin-bottom:24px;">
      <article class="d-card kpi-card-admin kpi-users">
        <span class="d-kpi-label">Pacientes activos</span>
        <strong class="d-kpi-value">{{ $kpis['patients_active'] }}</strong>
        <span class="kpi-foot">Con cuenta habilitada</span>
      </article>
      <article class="d-card kpi-card-admin kpi-clients">
        <span class="d-kpi-label">Con plan activo</span>
        <strong class="d-kpi-value">{{ $kpis['patients_with_plan'] }}</strong>
        <span class="kpi-foot">Instancia de plan vigente</span>
      </article>
      <article class="d-card kpi-card-admin kpi-admins">
        <span class="d-kpi-label">Sin plan</span>
        <strong class="d-kpi-value">{{ $kpis['patients_without_plan'] }}</strong>
        <span class="kpi-foot">Pendientes de asignación</span>
      </article>
      <article class="d-card kpi-card-admin kpi-supervisors">
        <span class="d-kpi-label">Planes disponibles</span>
        <strong class="d-kpi-value">{{ $kpis['plans_active'] }}</strong>
        <span class="kpi-foot">Catálogo activo</span>
      </article>
    </section>

    {{-- A1: Pacientes por plan / distrito --}}
    <section class="d-grid" style="grid-template-columns:1fr 1fr;gap:24px;">
      <div class="d-card">
        <div class="flex-between mb-4">
          <h3 style="margin:0;font-size:16px;font-weight:700;">Pacientes activos por plan</h3>
          <a href="{{ route('admin.planes.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">
            <i data-lucide="package"></i> Gestionar planes
          </a>
        </div>
        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:520px;">
            <thead>
              <tr>
                <th>Plan</th>
                <th style="text-align:center;">Activos</th>
              </tr>
            </thead>
            <tbody>
              @forelse($patientsByPlan as $plan)
                <tr>
                  <td style="font-weight:600;">{{ $plan->name }}</td>
                  <td style="text-align:center;">
                    <span class="d-badge {{ $plan->active_patients_count > 0 ? 'd-badge-green' : 'd-badge-yellow' }}">
                      {{ $plan->active_patients_count }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" style="padding:16px;opacity:.8;">Sin planes activos.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-card">
        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">Pacientes activos por distrito</h3>
        <div class="d-table-wrapper">
          <table class="d-table" style="min-width:520px;">
            <thead>
              <tr>
                <th>Distrito</th>
                <th style="text-align:center;">Activos</th>
              </tr>
            </thead>
            <tbody>
              @forelse($patientsByDistrict as $row)
                <tr>
                  <td style="font-weight:600;">{{ $row->district }}</td>
                  <td style="text-align:center;">
                    <span class="d-badge d-badge-blue">{{ $row->total }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" style="padding:16px;opacity:.8;">Sin datos.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>

    {{-- A4: Filtros de pacientes (según datos actuales: plan + distrito + búsqueda) --}}
    <section class="d-card" style="margin-top:24px;">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Búsqueda de pacientes</h3>
        <a href="{{ route('admin.pacientes.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">
          Ver directorio completo
        </a>
      </div>

      <form method="GET" action="{{ route('admin.dashboard') }}">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end;">
          <div>
            <label class="d-label" for="q">Buscar</label>
            <div class="input-icon-wrap">
              <i data-lucide="search" class="input-icon"></i>
              <input id="q" name="q" type="search" class="d-input" placeholder="Nombre, email o DNI" value="{{ request('q') }}">
            </div>
          </div>

          <div>
            <label class="d-label" for="district">Distrito</label>
            <input id="district" name="district" type="text" class="d-input" placeholder="Ej: Miraflores" value="{{ request('district') }}">
          </div>

          <div>
            <label class="d-label" for="plan_id">Plan</label>
            <select id="plan_id" name="plan_id" class="d-select">
              <option value="">Todos</option>
              @foreach($plansForFilter as $p)
                <option value="{{ $p->id }}" @selected((string)request('plan_id') === (string)$p->id)>{{ $p->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="filter-actions">
            <button type="submit" class="d-btn d-btn-primary">Buscar</button>
            <a href="{{ route('admin.dashboard') }}" class="d-btn d-btn-outline">Limpiar</a>
          </div>
        </div>
      </form>

      <div class="d-table-wrapper" style="margin-top:16px;">
        <table class="d-table" style="min-width:980px;">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Distrito</th>
              <th>Plan activo</th>
              <th>Alumno asignado</th>
              <th style="text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($patients as $patient)
              @php
                $user = $patient->user;
                $profile = $user?->profile;
                $assignment = $patient->activeAssignment;
                $activePlan = $patient->activePlan;
                $initials = collect(explode(' ', trim($user->name ?? 'P')))
                  ->filter()->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('');
              @endphp
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ $initials ?: 'P' }}</div>
                    <div>
                      <div class="user-name">{{ $user->name ?? '—' }}</div>
                      <div class="user-email">{{ $user->email ?? '—' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $profile?->district ?? '—' }}</td>
                <td>
                  @if($activePlan?->plan)
                    <span class="d-badge d-badge-green">{{ $activePlan->plan->name }}</span>
                  @else
                    <span class="d-badge d-badge-yellow">Sin plan</span>
                  @endif
                </td>
                <td>
                  @if($assignment)
                    <div style="font-size:13px;font-weight:600;">{{ $assignment->student?->user?->name ?? '—' }}</div>
                    <div class="user-email">Desde {{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}</div>
                  @else
                    <span class="d-badge d-badge-yellow">Sin asignar</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  <a href="{{ route('admin.pacientes.show', $patient) }}" class="d-btn d-btn-outline action-btn" title="Ver detalle">
                    <i data-lucide="eye"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="padding:16px;opacity:.8;">Sin resultados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $patients->appends(request()->query())->links() }}
      </div>
    </section>

    {{-- A2 + A5: Cumplimiento de alumnos (mes actual) + perfil --}}
    <section class="d-card" style="margin-top:24px;">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Cumplimiento de alumnos ({{ $monthLabel }})</h3>
        <a href="{{ route('admin.alumnos.index') }}" class="d-btn d-btn-outline" style="font-size:13px;">Ver alumnos</a>
      </div>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:1100px;">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Distrito</th>
              <th>Carrera</th>
              <th style="text-align:center;">Edad</th>
              <th style="text-align:center;">Sexo</th>
              <th style="text-align:center;">Done</th>
              <th style="text-align:center;">No show</th>
              <th style="text-align:center;">Cumplimiento</th>
            </tr>
          </thead>
          <tbody>
            @forelse($students as $student)
              @php
                $done = (int) ($student->done_month ?? 0);
                $noShow = (int) ($student->no_show_month ?? 0);
                $den = $done + $noShow;
                $pct = $den > 0 ? round(($done / $den) * 100) : 100;
              @endphp
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="d-avatar avatar-mini">{{ mb_substr($student->user?->name ?? 'A', 0, 1) }}</div>
                    <div>
                      <div class="user-name">{{ $student->user?->name ?? '—' }}</div>
                      <div class="user-email">{{ $student->user?->email ?? '—' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ $student->district?->name ?? '—' }}</td>
                <td>{{ $student->career?->name ?? '—' }}</td>
                <td style="text-align:center;">{{ $student->age() ?? '—' }}</td>
                <td style="text-align:center;">{{ $student->sex ?? '—' }}</td>
                <td style="text-align:center;"><span class="d-badge d-badge-green">{{ $done }}</span></td>
                <td style="text-align:center;"><span class="d-badge d-badge-red">{{ $noShow }}</span></td>
                <td style="text-align:center;">
                  <span class="d-badge {{ $pct >= 80 ? 'd-badge-green' : ($pct >= 60 ? 'd-badge-yellow' : 'd-badge-red') }}">
                    {{ $pct }}%
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="padding:16px;opacity:.8;">Sin alumnos activos.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $students->appends(request()->query())->links() }}
      </div>
    </section>

    {{-- Fase 11 (A3): Alertas de riesgo --}}
    <section class="d-card" style="margin-top:24px;">
      <div class="flex-between mb-4">
        <h3 style="margin:0;font-size:16px;font-weight:700;">Alertas de riesgo</h3>
        <span style="font-size:12px;color:var(--d-muted);">Calculado en vivo</span>
      </div>

      <div class="d-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:16px;">
        @foreach($riskAlerts as $a)
          @php
            $badge = $a['severity'] === 'danger' ? 'd-badge-red' : ($a['severity'] === 'warning' ? 'd-badge-yellow' : 'd-badge-blue');
          @endphp
          <div class="d-card" style="padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
              <div style="font-size:13px;font-weight:700;">{{ $a['title'] }}</div>
              <span class="d-badge {{ $badge }}">{{ $a['count'] }}</span>
            </div>
            <div style="margin-top:10px;display:grid;gap:8px;">
              @forelse($a['items'] as $item)
                @if(isset($item->patient_id))
                  <a href="{{ route('admin.pacientes.show', $item->patient_id) }}" style="text-decoration:none;color:var(--d-text);">
                    <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;padding:10px 12px;border:1px solid var(--d-border);border-radius:12px;background:var(--d-surface);">
                      <div style="min-width:0;">
                        <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item->patient_name ?? 'Paciente' }}</div>
                        @if(isset($item->last_done_at) && $item->last_done_at)
                          <div style="font-size:12px;color:var(--d-muted);">Última asistencia: {{ \Carbon\Carbon::parse($item->last_done_at)->format('d/m/Y') }}</div>
                        @elseif(isset($item->ends_at) && $item->ends_at)
                          <div style="font-size:12px;color:var(--d-muted);">Vence: {{ \Carbon\Carbon::parse($item->ends_at)->format('d/m/Y') }} · {{ $item->plan_name ?? '' }}</div>
                        @elseif(isset($item->no_show_count))
                          <div style="font-size:12px;color:var(--d-muted);">No show: {{ $item->no_show_count }}</div>
                        @else
                          <div style="font-size:12px;color:var(--d-muted);">Ver detalle</div>
                        @endif
                      </div>
                      <i data-lucide="chevron-right" style="width:16px;"></i>
                    </div>
                  </a>
                @elseif(isset($item->student_id))
                  <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;padding:10px 12px;border:1px solid var(--d-border);border-radius:12px;background:var(--d-surface);">
                    <div style="min-width:0;">
                      <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item->student_name ?? 'Alumno' }}</div>
                      <div style="font-size:12px;color:var(--d-muted);">Cumplimiento: {{ $item->compliance_pct ?? '—' }}% ({{ $item->done_count ?? 0 }}/{{ ($item->done_count ?? 0) + ($item->no_show_count ?? 0) }})</div>
                    </div>
                    <span class="d-badge d-badge-red">{{ $item->compliance_pct ?? 0 }}%</span>
                  </div>
                @endif
              @empty
                <div style="font-size:12px;color:var(--d-muted);padding:10px 0;">Sin casos.</div>
              @endforelse
            </div>
          </div>
        @endforeach
      </div>
    </section>

    {{-- A6: Top sesiones calificadas --}}
    <section class="d-card" style="margin-top:24px;">
      <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">Top sesiones calificadas</h3>

      <div class="d-table-wrapper">
        <table class="d-table" style="min-width:1100px;">
          <thead>
            <tr>
              <th style="text-align:center;">Rating</th>
              <th>Paciente</th>
              <th>Alumno</th>
              <th>Fecha sesión</th>
              <th>Comentario</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topSessionReviews as $r)
              <tr>
                <td style="text-align:center;">
                  <span class="d-badge d-badge-blue">{{ $r->rating }}/5</span>
                </td>
                <td>{{ $r->patient?->user?->name ?? '—' }}</td>
                <td>{{ $r->session?->student?->user?->name ?? '—' }}</td>
                <td style="font-size:12px;color:var(--d-muted);">
                  {{ $r->session?->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                </td>
                <td style="font-size:12px;color:var(--d-muted);">
                  {{ $r->comment ? Str::limit($r->comment, 90) : '—' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="padding:16px;opacity:.8;">Sin calificaciones registradas.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

  </div>
</div>
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush