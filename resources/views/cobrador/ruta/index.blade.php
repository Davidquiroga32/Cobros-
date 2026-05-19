@extends('layouts.cobrador')

@section('title', 'Mi Ruta de Hoy')

@section('topbar-actions')
    <a href="{{ route('cobrador.agenda') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-calendar-check"></i> Agenda
    </a>
@endsection

@push('styles')
<style>
    .ruta-header {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .progreso-wrap { display: flex; align-items: center; gap: 16px; flex: 1; min-width: 200px; }
    .progreso-circulo {
        position: relative; width: 80px; height: 80px; flex-shrink: 0;
    }
    .progreso-label {
        position: absolute; inset: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .progreso-num { font-size: 20px; font-weight: 800; color: var(--text-1); font-family: var(--font-mono); }
    .progreso-sub { font-size: 9px; color: var(--text-2); font-weight: 600; }

    .ruta-stat { text-align: center; flex: 1; min-width: 80px; }
    .ruta-stat-val { font-size: 22px; font-weight: 800; color: var(--text-1); font-family: var(--font-mono); }
    .ruta-stat-lab { font-size: 11px; color: var(--text-2); margin-top: 2px; }

    /* Paradas */
    .parada-list { display: flex; flex-direction: column; gap: 8px; }

    .parada-item {
        display: flex; align-items: center; gap: 12px;
        flex-wrap: wrap;
        padding: 14px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        transition: border-color .12s;
    }
    .parada-item:hover { border-color: var(--border-light); }
    .parada-item.visitado  { opacity: 0.6; border-color: var(--success); background: rgba(34,197,94,0.04); }
    .parada-item.no_encontrado { opacity: 0.5; }

    .parada-orden {
        width: 30px; height: 30px; border-radius: 8px;
        background: var(--bg-card-2);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: var(--text-2);
        flex-shrink: 0;
    }

    .parada-info { flex: 1; min-width: 0; }
    .parada-nombre { font-size: 14px; font-weight: 600; color: var(--text-1); }
    .parada-meta { font-size: 11px; color: var(--text-2); display: flex; gap: 8px; flex-wrap: wrap; margin-top: 2px; }

    .parada-monto {
        font-family: var(--font-mono); font-size: 15px;
        font-weight: 700; color: var(--text-1); flex-shrink: 0;
    }

    .parada-actions { display: flex; gap: 6px; flex-shrink: 0; }

    .section-sep {
        font-size: 11px; font-weight: 700; color: var(--text-3);
        text-transform: uppercase; letter-spacing: 0.8px;
        padding: 12px 0 8px;
        display: flex; align-items: center; gap: 8px;
    }
    .section-sep::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* Modal visita */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.5);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000; padding: 20px;
        opacity: 0; pointer-events: none; transition: opacity .2s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal-box {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 24px;
        width: calc(100% - 32px); max-width: 420px;
    }
    .modal-title { font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--text-1); }
</style>
@endpush

@section('content')

{{-- Header con progreso --}}
<div class="ruta-header">
    <div class="progreso-wrap">
        <div class="progreso-circulo">
            <svg width="80" height="80" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="32" fill="none" stroke="var(--bg-card-2)" stroke-width="8"/>
                <circle cx="40" cy="40" r="32" fill="none"
                    stroke="var(--accent)" stroke-width="8"
                    stroke-linecap="round"
                    stroke-dasharray="{{ round(2 * M_PI * 32, 1) }}"
                    stroke-dashoffset="{{ round(2 * M_PI * 32 * (1 - $progreso / 100), 1) }}"
                    transform="rotate(-90 40 40)"
                    style="transition: stroke-dashoffset 1s ease;"/>
            </svg>
            <div class="progreso-label">
                <span class="progreso-num">{{ $progreso }}%</span>
                <span class="progreso-sub">avance</span>
            </div>
        </div>
        <div>
            <div style="font-size: 15px; font-weight: 700; color: var(--text-1);">Ruta del día</div>
            <div style="font-size: 12px; color: var(--text-2); margin-top: 2px;">
                {{ now()->isoFormat('dddd, D [de] MMMM') }}
            </div>
            @if($caja)
            <div style="font-size: 11px; color: var(--success); margin-top: 4px;">
                <i class="fas fa-cash-register"></i> Caja abierta · ${{ number_format($caja->monto_inicial, 0, ',', '.') }}
            </div>
            @else
            <div style="font-size: 11px; color: var(--warning); margin-top: 4px;">
                <i class="fas fa-triangle-exclamation"></i>
                <a href="{{ route('cobrador.caja.index') }}" style="color: var(--warning);">Abrir caja primero</a>
            </div>
            @endif
        </div>
    </div>

    <div class="ruta-stat">
        <div class="ruta-stat-val">{{ $ruta->total_paradas }}</div>
        <div class="ruta-stat-lab">Total paradas</div>
    </div>
    <div class="ruta-stat">
        <div class="ruta-stat-val" style="color: var(--success);">{{ $paradasCompletadas->count() }}</div>
        <div class="ruta-stat-lab">Completadas</div>
    </div>
    <div class="ruta-stat">
        <div class="ruta-stat-val" style="color: var(--warning);">{{ $paradasPendientes->count() }}</div>
        <div class="ruta-stat-lab">Pendientes</div>
    </div>
    <div class="ruta-stat">
        <div class="ruta-stat-val" style="color: var(--danger);">{{ $paradasNoEncontradas->count() }}</div>
        <div class="ruta-stat-lab">No encontrados</div>
    </div>
</div>

{{-- Paradas pendientes --}}
@if($paradasPendientes->count())
<div class="section-sep">
    <i class="fas fa-map-pin" style="color: var(--warning);"></i>
    Por visitar ({{ $paradasPendientes->count() }})
</div>
<div class="parada-list">
    @foreach($paradasPendientes as $parada)
    <div class="parada-item" id="parada-{{ $parada->id }}">
        <div class="parada-orden">{{ $parada->orden }}</div>
        <div class="parada-info">
            <div class="parada-nombre">{{ $parada->cliente->nombre }}</div>
            <div class="parada-meta">
                <span><i class="fas fa-map-marker-alt" style="font-size:9px;"></i> {{ $parada->cliente->direccion }}</span>
                @if($parada->cuota)
                <span>Cuota #{{ $parada->cuota->numero_cuota }}</span>
                @endif
            </div>
        </div>
        <div class="parada-monto">${{ number_format($parada->monto_esperado, 0, ',', '.') }}</div>
        <div class="parada-actions">
            <button onclick="abrirModal({{ $parada->id }}, '{{ $parada->cliente->nombre }}', {{ $parada->monto_esperado }})"
                class="btn btn-primary btn-sm">
                <i class="fas fa-check"></i>
            </button>
            <button onclick="marcarNoEncontrado({{ $parada->id }})" class="btn btn-secondary btn-sm" title="No encontrado">
                <i class="fas fa-user-xmark"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Paradas completadas --}}
@if($paradasCompletadas->count())
<div class="section-sep" style="margin-top: 20px;">
    <i class="fas fa-circle-check" style="color: var(--success);"></i>
    Completadas ({{ $paradasCompletadas->count() }})
</div>
<div class="parada-list">
    @foreach($paradasCompletadas as $parada)
    <div class="parada-item visitado">
        <div class="parada-orden" style="background: var(--success-soft); color: var(--success);">
            <i class="fas fa-check" style="font-size:11px;"></i>
        </div>
        <div class="parada-info">
            <div class="parada-nombre">{{ $parada->cliente->nombre }}</div>
            <div class="parada-meta">
                <span>{{ $parada->hora_visita?->format('H:i') }}</span>
                @if($parada->observaciones)
                <span>{{ $parada->observaciones }}</span>
                @endif
            </div>
        </div>
        <div class="parada-monto" style="color: var(--success);">
            ${{ number_format($parada->monto_cobrado, 0, ',', '.') }}
        </div>
    </div>
    @endforeach
</div>
@endif

@if($ruta->total_paradas === 0)
<div style="text-align: center; padding: 60px 20px; color: var(--text-3);">
    <i class="fas fa-route" style="font-size: 40px; display: block; margin-bottom: 16px;"></i>
    <div style="font-size: 16px; font-weight: 600;">Sin paradas para hoy</div>
    <div style="font-size: 13px; margin-top: 6px;">No tienes cuotas pendientes para cobrar hoy.</div>
</div>
@endif

{{-- Modal visita --}}
<div class="modal-overlay" id="modalVisita">
    <div class="modal-box">
        <div class="modal-title" id="modalTitulo">Registrar visita</div>
        <form id="formVisita">
            @csrf
            @method('PATCH')
            <input type="hidden" name="estado" value="visitado">
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Monto cobrado</label>
                <input type="number" name="monto_cobrado" id="montoInput"
                    class="form-control" step="100" min="0" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Observaciones (opcional)</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Ej: Pagó en efectivo, cliente en casa..."></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-check"></i> Confirmar visita
                </button>
                <button type="button" onclick="cerrarModal()" class="btn btn-secondary">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
let paradaActual = null;

function abrirModal(paradaId, nombre, monto) {
    paradaActual = paradaId;
    document.getElementById('modalTitulo').textContent = `Visita: ${nombre}`;
    document.getElementById('montoInput').value = monto;
    document.getElementById('modalVisita').classList.add('open');
}

function cerrarModal() {
    document.getElementById('modalVisita').classList.remove('open');
    paradaActual = null;
}

document.getElementById('formVisita').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!paradaActual) return;

    const form = new FormData(this);
    const url  = `/cobrador/ruta/paradas/${paradaActual}`;

    try {
        const res = await fetch(url, {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                             || '{{ csrf_token() }}',
                'Accept'      : 'application/json',
            },
            body: form,
        });

        if (res.ok) {
            cerrarModal();
            window.location.reload();
        }
    } catch(err) {
        console.error(err);
    }
});

function marcarNoEncontrado(paradaId) {
    if (!confirm('¿Marcar como no encontrado?')) return;

    fetch(`/cobrador/ruta/paradas/${paradaId}`, {
        method : 'POST',
        headers: {
            'Content-Type' : 'application/json',
            'X-CSRF-TOKEN' : '{{ csrf_token() }}',
            'Accept'       : 'application/json',
        },
        body: JSON.stringify({ estado: 'no_encontrado', _method: 'PATCH' }),
    }).then(() => window.location.reload());
}
</script>
@endpush