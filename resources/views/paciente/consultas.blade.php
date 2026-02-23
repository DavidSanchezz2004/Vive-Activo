@extends('layouts.dashboard')

@section('title', 'Mis Consultas - Vive Activo')
@section('page_title', 'Mis Consultas')

@push('styles')
<style>
  .upcoming-card { border-radius:16px; padding:20px 22px; color:#fff; margin-bottom:12px; background:linear-gradient(135deg,#2563eb,#7c3aed); position:relative; overflow:hidden; }
  .upcoming-card::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,.1); }
  .upcoming-card > * { position:relative; z-index:1; }
  .mode-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; background:rgba(255,255,255,.25); color:#fff; margin-top:8px; }
  .join-btn { display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:rgba(255,255,255,.9);color:#1e40af;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;margin-top:14px;transition:all .2s; }
  .join-btn:hover { background:#fff; transform:translateY(-1px); }
  .hist-row { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--d-border); color: var(--d-text); }
  .hist-row:last-child { border-bottom:none; }
  .status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .dot-completed { background:#059669; }
  .dot-cancelled  { background:#64748b; }
  .dot-no_show    { background:#dc2626; }
</style>
@endpush

@section('content')
<div class="d-content">
  <div class="d-container">

    {{-- Próximas consultas --}}
    <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--d-text);">📅 Próximas Consultas</h3>
    @forelse($proximas as $c)
      <div class="upcoming-card">
        <div style="font-size:13px;opacity:.8;">{{ $c->scheduled_at?->isoFormat('dddd D [de] MMMM · HH:mm') }}</div>
        <div style="font-size:18px;font-weight:800;margin-top:4px;">{{ $c->title ?? 'Consulta' }}</div>
        @if($c->notes)
          <div style="font-size:13px;opacity:.8;margin-top:4px;">{{ Str::limit($c->notes, 80) }}</div>
        @endif
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;align-items:center;">
          <span class="mode-pill">
            @if($c->mode === 'zoom') 📹 Zoom
            @elseif($c->mode === 'presencial') 🏢 Presencial
            @else 📞 {{ $c->mode }}
            @endif
          </span>
          <span style="background:rgba(255,255,255,.2);padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;color:#fff;">
            {{ $c->status === 'confirmed' ? '✅ Confirmada' : '⏳ Pendiente confirmación' }}
          </span>
        </div>
        @if($c->mode === 'zoom' && $c->meeting_url)
          <a href="{{ $c->meeting_url }}" target="_blank" class="join-btn" style="color:#1e40af;">
            <i data-lucide="video" style="width:14px;"></i> Unirme a la reunión
          </a>
        @endif
      </div>
    @empty
      <div class="d-card" style="text-align:center;padding:36px;margin-bottom:20px;">
        <p style="opacity:.7;margin:0;color:var(--d-text);">No tienes consultas programadas próximamente.</p>
      </div>
    @endforelse

    {{-- Historial --}}
    <h3 style="font-size:16px;font-weight:700;margin:24px 0 14px;color:var(--d-text);">🗂️ Historial</h3>
    <div class="d-card">
      @forelse($historial as $c)
        <div class="hist-row">
          <div class="status-dot dot-{{ $c->status }}"></div>
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;">{{ $c->title ?? 'Consulta' }}</div>
            <div style="font-size:12px;opacity:.8;color:var(--d-text);">
              {{ $c->scheduled_at?->format('d/m/Y H:i') }}
              @if($c->mode) · {{ $c->mode }} @endif
            </div>
          </div>
          <span class="d-badge
            @if($c->status === 'completed') d-badge-green
            @elseif($c->status === 'cancelled') d-badge-red
            @else d-badge-yellow @endif"
            style="font-size:11px;">
            @if($c->status === 'completed') Completada
            @elseif($c->status === 'cancelled') Cancelada
            @else No asistió @endif
          </span>
        </div>
      @empty
        <p style="opacity:.6;text-align:center;padding:24px;margin:0;color:var(--d-text);">Sin historial de consultas aún.</p>
      @endforelse
    </div>

    @if($historial->hasPages())
      <div style="margin-top:16px;">{{ $historial->links() }}</div>
    @endif

  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@endpush
