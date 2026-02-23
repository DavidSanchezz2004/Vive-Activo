@extends('layouts.dashboard')

@section('title', 'Mis Sesiones - Vive Activo')
@section('page_title', 'Mis Sesiones')

@push('styles')
<style>
  .ses-upcoming { border-radius:14px; padding:18px 20px; background:linear-gradient(135deg,#059669,#0891b2); color:#fff; margin-bottom:10px; position:relative; overflow:hidden; }
  .ses-upcoming::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,.08); }
  .ses-upcoming > * { position:relative; z-index:1; }
  .ses-pill  { display:inline-block;padding:3px 10px;border-radius:14px;font-size:11px;font-weight:700;background:rgba(255,255,255,.22);color:#fff;margin-top:6px; }
  .hist-row  { display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--d-border); color: var(--d-text); }
  .hist-row:last-child { border-bottom:none; }
  .sh-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
  .dot-done { background:#059669; } .dot-no_show { background:#dc2626; }
  .dot-rescheduled { background:#2563eb; } .dot-cancelled { background:#64748b; }
  .metrics { display:flex;gap:6px;flex-wrap:wrap;margin-top:4px; }
  .metric { font-size:11px;padding:2px 7px;border-radius:8px;background:rgba(255,255,255,.2);color:#fff; }
</style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    @if (session('success'))
      <div class="d-card" style="margin-bottom:14px;">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="d-card" style="margin-bottom:14px;border-color:#fecaca;">
        {{ session('error') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="d-card" style="margin-bottom:14px;border-color:#fecaca;">
        <div style="font-weight:700;margin-bottom:6px;color:var(--d-text);">Revisa los datos</div>
        <ul style="margin:0;padding-left:18px;color:var(--d-text);opacity:.9;">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Próximas sesiones --}}
    <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--d-text);">🏃 Próximas Sesiones</h3>
    @forelse($proximas as $s)
      <div class="ses-upcoming">
        <div style="font-size:13px;opacity:.75;">{{ $s->scheduled_at?->isoFormat('dddd D [de] MMMM · HH:mm') }}</div>
        <div style="font-size:17px;font-weight:800;margin-top:2px;">
          Sesión con {{ $s->student?->user?->name ?? '—' }}
        </div>
        @if($s->notes)
          <div style="font-size:13px;opacity:.8;margin-top:4px;">{{ Str::limit($s->notes, 80) }}</div>
        @endif
        <span class="ses-pill">⏳ Pendiente</span>
      </div>
    @empty
      <div class="d-card" style="text-align:center;padding:32px;margin-bottom:20px;">
        <p style="opacity:.7;margin:0;color:var(--d-text);">No tienes sesiones pendientes próximamente.</p>
      </div>
    @endforelse

    {{-- Historial --}}
    <h3 style="font-size:16px;font-weight:700;margin:24px 0 14px;color:var(--d-text);">📊 Historial de Sesiones</h3>
    <div class="d-card">
      @php $oldSessionId = old('session_id'); @endphp
      @forelse($historial as $s)
        <div class="hist-row">
          <div class="sh-dot dot-{{ $s->status }}"></div>
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;">{{ $s->scheduled_at?->format('d/m/Y H:i') }}</div>
            <div style="font-size:12px;opacity:.8;color:var(--d-text);">Con {{ $s->student?->user?->name ?? '—' }}</div>
            @if($s->weight_kg || $s->rpe)
              <div style="display:flex;gap:8px;margin-top:4px;flex-wrap:wrap;">
                @if($s->weight_kg)
                  <span style="font-size:11px;background:var(--d-bg);border:1px solid var(--d-border);border-radius:8px;padding:2px 8px;">⚖️ {{ $s->weight_kg }} kg</span>
                @endif
                @if($s->rpe)
                  <span style="font-size:11px;background:var(--d-bg);border:1px solid var(--d-border);border-radius:8px;padding:2px 8px;">💪 RPE {{ $s->rpe }}/10</span>
                @endif
              </div>
            @endif
            @if($s->notes)
              <div style="font-size:12px;opacity:.7;font-style:italic;margin-top:2px;color:var(--d-text);">{{ Str::limit($s->notes, 70) }}</div>
            @endif

            @if($s->status === 'done')
              <div style="margin-top:8px;">
                @if($s->review)
                  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <span class="d-badge d-badge-blue">⭐ {{ $s->review->rating }}/5</span>
                    @if($s->review->comment)
                      <span style="font-size:12px;opacity:.75;color:var(--d-text);">{{ Str::limit($s->review->comment, 70) }}</span>
                    @endif
                  </div>
                @endif

                <details style="margin-top:10px;">
                  <summary style="cursor:pointer;font-size:12px;font-weight:700;color:var(--d-brand);">
                    {{ $s->review ? 'Editar calificación' : 'Calificar al estudiante' }}
                  </summary>

                  <form method="POST" action="{{ route('paciente.sesiones.review', $s) }}" style="margin-top:10px;display:grid;gap:10px;">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $s->id }}">

                    @php
                      $ratingValue = ((string)$oldSessionId === (string)$s->id)
                        ? old('rating')
                        : ($s->review->rating ?? '');
                      $commentValue = ((string)$oldSessionId === (string)$s->id)
                        ? old('comment')
                        : ($s->review->comment ?? '');
                    @endphp

                    <div style="display:grid;grid-template-columns:140px 1fr;gap:10px;align-items:center;">
                      <label class="d-label" style="margin:0;">Rating</label>
                      <select name="rating" class="d-select" required>
                        @for($i=1; $i<=5; $i++)
                          <option value="{{ $i }}" @selected((string)$ratingValue === (string)$i)>{{ $i }}</option>
                        @endfor
                      </select>
                    </div>

                    <div>
                      <label class="d-label">Comentario (opcional)</label>
                      <textarea name="comment" class="d-textarea" rows="2" placeholder="¿Cómo fue la sesión?">{{ $commentValue }}</textarea>
                    </div>

                    <div style="display:flex;justify-content:flex-end;">
                      <button type="submit" class="d-btn d-btn-primary" style="font-size:12px;padding:8px 14px;">Guardar</button>
                    </div>
                  </form>
                </details>
              </div>
            @endif
          </div>
          <span class="d-badge
            @if($s->status === 'done') d-badge-green
            @elseif($s->status === 'no_show') d-badge-red
            @elseif($s->status === 'rescheduled') d-badge-blue
            @else @endif"
            style="font-size:11px;flex-shrink:0;">
            @switch($s->status)
              @case('done') ✅ Completada @break
              @case('no_show') ❌ No asistió @break
              @case('rescheduled') 🔄 Reprogramada @break
              @case('cancelled') Cancelada @break
              @default {{ $s->status }}
            @endswitch
          </span>
        </div>
      @empty
        <p style="opacity:.6;text-align:center;padding:24px;margin:0;color:var(--d-text);">Sin historial de sesiones aún.</p>
      @endforelse
    </div>

    @if($historial->hasPages())
      <div style="margin-top:16px;">{{ $historial->links() }}</div>
    @endif

  </div>
</div>
@endsection
