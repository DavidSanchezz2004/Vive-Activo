@extends('layouts.dashboard')

@section('title', 'Mi Portal - Vive Activo')
@section('page_title', 'Inicio')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/agregar.css') }}">
<style>
  /* ─── KPI grid ─── */
  .portal-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
  @media(max-width:900px){ .portal-kpis{ grid-template-columns:1fr 1fr; } }

  /* ─── Next card ─── */
  .next-card { border-radius:16px; padding:24px; color:#fff; position:relative; overflow:hidden; }
  .next-card::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,.12); }
  .next-card > * { position:relative; z-index:1; }
  .nc-consult { background:linear-gradient(135deg,#2563eb,#7c3aed); }
  .nc-session  { background:linear-gradient(135deg,#059669,#0891b2); }

  /* ─── Badge modes ─── */
  .mode-pill { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(255,255,255,.25);color:#fff; }

  /* ─── Session history ─── */
  .sh-row { display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--d-border); color: var(--d-text); }
  .sh-row:last-child { border-bottom:none; }
  .sh-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
  .dot-done       { background:#059669; }
  .dot-no_show    { background:#dc2626; }
  .dot-rescheduled{ background:#2563eb; }
  .dot-cancelled  { background:#64748b; }

  /* ─── Join button ─── */
  .join-btn { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:rgba(255,255,255,.9);color:#1e40af;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;margin-top:16px;transition:all .2s; }
  .join-btn:hover { background:#fff;transform:translateY(-1px); }
  html.dark .join-btn { background:rgba(255,255,255,.15);color:#fff; }
</style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- Saludo --}}
    <div style="margin-bottom:24px;">
      <h2 style="margin:0;font-size:22px;font-weight:800;color:var(--d-text);">
        ¡Hola, {{ Str::words(auth()->user()->name, 1, '') }}! 👋
      </h2>
      <p style="margin:4px 0 0;opacity:.8;font-size:14px;color:var(--d-text);">
        {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}
      </p>
    </div>

    {{-- KPIs --}}
    <div class="portal-kpis">
      <div class="d-card" style="text-align:center;">
        <div style="font-size:32px;font-weight:800;color:var(--d-brand);">{{ $sessionsDone }}</div>
        <div style="font-size:13px;opacity:.8;margin-top:4px;color:var(--d-text);">Sesiones completadas</div>
      </div>
      <div class="d-card" style="text-align:center;">
        <div style="font-size:32px;font-weight:800;color:var(--d-success);">{{ $consultationsDone }}</div>
        <div style="font-size:13px;opacity:.8;margin-top:4px;color:var(--d-text);">Consultas realizadas</div>
      </div>
      <div class="d-card" style="text-align:center;">
        @if($activePlan && $activePlan->plan)
          @php
            $total = (int) ($activePlan->plan->sessions_total ?? 0);
            $used  = (int) ($activePlan->sessions_used ?? 0);
          @endphp
          <div style="font-size:32px;font-weight:800;color:var(--d-info);">
            {{ $used }}
          </div>
          <div style="font-size:13px;opacity:.8;margin-top:4px;color:var(--d-text);">Sesiones consumidas</div>
          <div style="font-size:12px;opacity:.7;margin-top:2px;color:var(--d-text);">
            de {{ $total === 0 ? '∞' : $total }}
          </div>
        @else
          <div style="font-size:13px;opacity:.8;padding:8px 0;color:var(--d-text);">Sin plan activo</div>
        @endif
      </div>
      <div class="d-card" style="text-align:center;">
        @php
          $alumno = $patient->activeAssignment?->student?->user;
        @endphp
        @if($alumno)
          <div class="d-avatar" style="width:40px;height:40px;margin:0 auto 6px;font-size:16px;background:var(--d-info);">
            {{ mb_substr($alumno->name, 0, 1) }}
          </div>
          <div style="font-size:14px;font-weight:700;color:var(--d-text);">{{ Str::words($alumno->name, 2, '') }}</div>
          <div style="font-size:12px;opacity:.8;color:var(--d-text);">Tu alumno asignado</div>
        @else
          <div style="font-size:13px;opacity:.8;padding:8px 0;color:var(--d-text);">Sin alumno asignado</div>
        @endif
      </div>
    </div>

    {{-- Grid principal --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      {{-- ─── Próxima Consulta ─── --}}
      <div>
        <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.05em;color:var(--d-text);">
          Próxima Consulta
        </h3>
        @if($nextConsultation)
          <div class="next-card nc-consult">
            <div style="font-size:13px;opacity:.8;margin-bottom:6px;">
              {{ $nextConsultation->scheduled_at?->isoFormat('dddd D [de] MMMM') }}
              · {{ $nextConsultation->scheduled_at?->format('H:i') }}
            </div>
            <div style="font-size:20px;font-weight:800;margin-bottom:8px;">
              {{ $nextConsultation->type ?: 'Consulta nutricional' }}
            </div>
            <span class="mode-pill">
              @php $modeIcons = ['presencial'=>'🏥','zoom'=>'🎥','meet'=>'📹']; @endphp
              {{ $modeIcons[$nextConsultation->mode] ?? '' }}
              {{ $nextConsultation->modeLabel() }}
            </span>

            @if($nextConsultation->isConfirmed() && $nextConsultation->isMeetingMode() && $nextConsultation->meeting_url)
              <br>
              <a href="{{ $nextConsultation->meeting_url }}" target="_blank" class="join-btn">
                <i data-lucide="video" style="width:16px;"></i> Unirme a la reunión
              </a>
            @elseif(!$nextConsultation->isConfirmed())
              <div style="margin-top:14px;font-size:13px;opacity:.8;">
                ⏳ Pendiente de confirmación
              </div>
            @endif
          </div>
        @else
          <div class="d-card" style="padding:32px;text-align:center;color:var(--d-text);opacity:.7;">
            <i data-lucide="calendar-x" style="width:36px;height:36px;display:block;margin:0 auto 10px;opacity:.4;"></i>
            <div style="font-size:14px;">No tienes consultas programadas próximamente.</div>
          </div>
        @endif
      </div>

      {{-- ─── Próxima Sesión ─── --}}
      <div>
        <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.05em;color:var(--d-text);">
          Próxima Sesión
        </h3>
        @if($nextSession)
          <div class="next-card nc-session">
            <div style="font-size:13px;opacity:.8;margin-bottom:6px;">
              {{ $nextSession->scheduled_at?->isoFormat('dddd D [de] MMMM') }}
              · {{ $nextSession->scheduled_at?->format('H:i') }}
            </div>
            <div style="font-size:20px;font-weight:800;margin-bottom:8px;">
              Sesión con {{ Str::words($nextSession->student?->user?->name ?? 'tu alumno', 2, '') }}
            </div>
            @if($nextSession->notes)
              <div style="font-size:13px;opacity:.8;margin-top:4px;font-style:italic;">
                "{{ Str::limit($nextSession->notes, 80) }}"
              </div>
            @endif
            @if($nextSession->deducts)
              <div style="margin-top:12px;font-size:12px;opacity:.7;">
                📋 Esta sesión descuenta de tu plan
              </div>
            @endif
          </div>
        @else
          <div class="d-card" style="padding:32px;text-align:center;color:var(--d-text);opacity:.7;">
            <i data-lucide="clock" style="width:36px;height:36px;display:block;margin:0 auto 10px;opacity:.4;"></i>
            <div style="font-size:14px;">No tienes sesiones pendientes próximamente.</div>
          </div>
        @endif
      </div>

    </div>{{-- /grid --}}

    {{-- ─── Historial de sesiones recientes ─── --}}
    <div class="d-card" style="margin-top:24px;">
      <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;color:var(--d-text);">
        <i data-lucide="history" style="width:16px;vertical-align:-3px;"></i> Historial reciente
      </h3>

      @if($recentSessions->isEmpty())
        <div style="padding:24px;text-align:center;opacity:.7;font-size:14px;color:var(--d-text);">
          Aún no hay sesiones en tu historial.
        </div>
      @else
        @php
          $statusColors = [
            'done'        => 'dot-done',
            'no_show'     => 'dot-no_show',
            'rescheduled' => 'dot-rescheduled',
            'cancelled'   => 'dot-cancelled',
          ];
        @endphp
        @foreach($recentSessions as $s)
          <div class="sh-row">
            <div class="sh-dot {{ $statusColors[$s->status] ?? '' }}"></div>
            <div style="flex:1;">
              <div style="font-weight:600;font-size:14px;">
                Sesión con {{ Str::words($s->student?->user?->name ?? '—', 2, '') }}
              </div>
              <div style="font-size:12px;opacity:.8;color:var(--d-text);">
                {{ $s->scheduled_at?->isoFormat('D MMM YYYY · H:mm') ?? '—' }}
              </div>
            </div>
            <div>
              @php
                $statusBadge = [
                  'done'        => ['Completada', '#059669', 'rgba(16,185,129,.15)'],
                  'no_show'     => ['No asistí', '#dc2626', 'rgba(239,68,68,.15)'],
                  'rescheduled' => ['Reprogramada', '#2563eb', 'rgba(59,130,246,.15)'],
                  'cancelled'   => ['Cancelada', '#475569', 'rgba(100,116,139,.15)'],
                ][$s->status] ?? [$s->statusLabel(), '#475569', 'rgba(100,116,139,.1)'];
              @endphp
              <span style="display:inline-block;font-size:12px;font-weight:600;color:{{ $statusBadge[0] }};padding:3px 10px;border-radius:20px;background:{{ $statusBadge[2] }};">
                {{ $statusBadge[0] }}
              </span>
            </div>
          </div>
        @endforeach
      @endif
    </div>

  </div>{{-- /container --}}
</div>{{-- /content --}}
@endsection

@push('scripts')
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
@endpush