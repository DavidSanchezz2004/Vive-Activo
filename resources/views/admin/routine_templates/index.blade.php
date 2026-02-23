@extends('layouts.dashboard')

@section('content')
<div class="d-card" style="margin-bottom: 16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div>
            <h2 style="margin:0;">Plantillas de rutinas</h2>
            <p style="margin:6px 0 0;color:var(--d-muted);">Crea y administra plantillas reutilizables.</p>
        </div>
        <a class="d-btn d-btn-primary" href="{{ route('admin.routine_templates.create') }}">Nueva plantilla</a>
    </div>
</div>

@if(session('success'))
    <div class="d-card" style="margin-bottom: 12px; border-left: 4px solid var(--d-success);">
        {{ session('success') }}
    </div>
@endif

<div class="d-card">
    <div style="overflow:auto;">
        <table class="d-table" style="min-width: 760px;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Objetivo</th>
                    <th>Items</th>
                    <th>Activa</th>
                    <th style="width: 220px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tpl)
                    <tr>
                        <td style="font-weight:600;">{{ $tpl->name }}</td>
                        <td style="color:var(--d-muted);">{{ $tpl->goal ?? '—' }}</td>
                        <td>{{ $tpl->items_count }}</td>
                        <td>
                            @if($tpl->is_active)
                                <span style="color:var(--d-success);font-weight:600;">Sí</span>
                            @else
                                <span style="color:var(--d-muted);">No</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <a class="d-btn d-btn-outline" href="{{ route('admin.routine_templates.edit', $tpl) }}">Editar</a>
                                <form method="POST" action="{{ route('admin.routine_templates.destroy', $tpl) }}" onsubmit="return confirm('¿Eliminar esta plantilla?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="d-btn d-btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="color:var(--d-muted);">No hay plantillas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 12px;">
        {{ $templates->links() }}
    </div>
</div>
@endsection
