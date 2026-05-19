@extends('layouts.admin')

@section('title', 'Estado Operativo Cobradores')

@push('styles')
<style>
    .estado-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }

    .cobrador-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: border-color .15s;
    }
    .cobrador-card:hover { border-color: var(--border-light); }

    /* Estado color lateral */
    .cobrador-card.disponible  { border-left: 3px solid var(--success); }
    .cobrador-card.en_ruta     { border-left: 3px solid var(--accent); }
    .cobrador-card.pausado     { border-left: 3px solid var(--warning); }
    .cobrador-card.offline     { border-left: 3px solid var(--border); }
    .cobrador-card.sincronizando { border-left: 3px solid #a78bfa; }

    .card-top {
        padding: 16px 18px 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid var(--border);
    }

    .cobrador-avatar {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: var(--accent-glow);
        color: var(--accent);
        font-size: 17px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .cobrador-info { flex: 1; min-width: 0; }
    .cobrador-nombre { font-size: 14px; font-weight: 700; color: var(--text-1); }
    .cobrador-cn-sector { font-size: 11px; color: var(--text-2); margin-top: 2px; }

    .estado-badge {
        font-size: 10px; font-weight: 700; padding: 3px 8px;
        border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;
        flex-shrink: 0;
    }
    .badge-disponible   { background: var(--success-soft); color: var(--success); }
    .badge-en_ruta      { background: var(--accent-glow);  color: var(--accent); }
    .badge-pausado      { background: var(--warning-soft); color: var(--warning); }
    .badge-offline      { background: var(--bg-card-2);    color: var(--text-3); }
    .badge-sincronizando{ background: rgba(167,139,250,0.15); color: #a78bfa; }

    .card-body {
        padding: 14px 18px;
    }

    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    .metric-row:last-child { border-bottom: none; }
    .metric-label { color: var(--text-2); }
    .metric-value { font-weight: 700; color: var(--text-1); font-family: var(--font-mono); }
    .metric-value.success { color: var(--success); }
    .metric-value.danger  { color: var(--danger); }
    .metric-value.accent  { color: var(--accent); }

    .progress-bar-wrap {
        background: var(--bg-card-2);
        border-radius: 10px;
        height: 6px;
        overflow: hidden;
        margin-top: 4px;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, var(--accent), var(--accent-2));
        transition: width .6s ease;
    }

    .sync-dot {
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block; margin-right: 4px;
    }
    .dot-online  { background: var(--success); box-shadow: 0 0 6px var(--success); }
    .dot-offline { background: var(--text-3); }

    .empty-estado {
        grid-column: 1/-1;
        text-align: center;
        padding: 60px 20px;
        color: var(--text-3);
    }

    /* Filtro top */
    .filtro-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filtro-btn {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px; font-weight: 600;
        border: 1px solid var(--border);
        background: var(--bg-card-2);
        color: var(--text-2);
        cursor: pointer;
        transition: all .12s;
    }
    .filtro-btn:hover, .filtro-btn.active {
        background: var(--accent-glow);
        border-color: rgba(79,142,247,0.3);
        color: var(--accent);
    }

    .refresh-badge {
        font-size: 11px; color: var(--text-3);
        display: flex; align-items: center; gap: 6px;
    }
</style>
@endpush

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: var(--text-1); margin: 0;">Estado Operativo</h2>
        <p style="font-size: 12px; color: var(--text-2); margin: 4px 0 0;">
            Datos en tiempo real de la jornada de hoy · {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <span class="refresh-badge">
            <i class="fas fa-circle-notch fa-spin" id="spin" style="display:none;"></i>
            <span id="ultimo-refresh">Actualizado ahora</span>
        </span>
        <button onclick="recargar()" class="btn btn-secondary btn-sm">
            <i class="fas fa-rotate-right"></i> Actualizar
        </button>
    </div>
</div>

{{-- Filtros rápidos --}}
<div class="filtro-bar">
    <button class="filtro-btn active" onclick="filtrar(this, 'todos')">Todos ({{ count($cobradores) }})</button>
    <button class="filtro-btn" onclick="filtrar(this, 'en_ruta')">
        <span class="sync-dot dot-online"></span> En ruta
    </button>
    <button class="filtro-btn" onclick="filtrar(this, 'disponible')">Disponible</button>
    <button class="filtro-btn" onclick="filtrar(this, 'offline')">Offline</button>
    <button class="filtro-btn" onclick="filtrar(this, 'pausado')">Pausado</button>
</div>

{{-- Grid de cobradores --}}
<div class="estado-grid" id="cobradoresGrid">

    @forelse($cobradores as $c)
    <div class="cobrador-card {{ $c['estado_operativo'] }}" data-estado="{{ $c['estado_operativo'] }}">

        <div class="card-top">
            <div class="cobrador-avatar">
                {{ strtoupper(substr($c['nombre'], 0, 1)) }}
            </div>
            <div class="cobrador-info">
                <div class="cobrador-nombre">{{ $c['nombre'] }}</div>
                <div class="cobrador-cn-sector">
                    @if($c['cn'] !== '—') <strong>{{ $c['cn'] }}</strong> · @endif
                    {{ $c['sector'] }}
                    @if($c['ubicacion'])
                    · <i class="fas fa-location-dot" style="font-size:9px;"></i> {{ $c['ubicacion'] }}
                    @endif
                </div>
            </div>
            <span class="estado-badge badge-{{ $c['estado_operativo'] }}">
                <span class="sync-dot {{ $c['conectado'] ? 'dot-online' : 'dot-offline' }}"></span>
                {{ str_replace('_', ' ', $c['estado_operativo']) }}
            </span>
        </div>

        <div class="card-body">

            {{-- Progreso de meta --}}
            <div style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-2); margin-bottom: 4px;">
                    <span>Meta del día</span>
                    <span class="metric-value accent">{{ $c['porcentaje_meta'] }}%</span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: {{ $c['porcentaje_meta'] }}%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-3); margin-top: 4px;">
                    <span>Cobrado: ${{ number_format($c['total_cobrado_hoy'], 0, ',', '.') }}</span>
                    <span>Meta: ${{ number_format($c['meta_dia'] + $c['total_cobrado_hoy'], 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Progreso ruta --}}
            <div style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-2); margin-bottom: 4px;">
                    <span>Progreso ruta</span>
                    <span class="metric-value">{{ $c['progreso_ruta'] }}%</span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: {{ $c['progreso_ruta'] }}%; background: linear-gradient(90deg, var(--success), #34d399);"></div>
                </div>
            </div>

            {{-- Métricas --}}
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-users" style="width:14px;"></i> Clientes</span>
                <span class="metric-value">{{ $c['total_clientes'] }}</span>
            </div>
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-receipt" style="width:14px;"></i> Pagos hoy</span>
                <span class="metric-value success">{{ $c['total_pagos_hoy'] }}</span>
            </div>
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-money-bill-wave" style="width:14px;"></i> Cobrado hoy</span>
                <span class="metric-value success">${{ number_format($c['total_cobrado_hoy'], 0, ',', '.') }}</span>
            </div>
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-clock" style="width:14px;"></i> Pendientes hoy</span>
                <span class="metric-value {{ $c['cuotas_pendientes'] > 0 ? 'danger' : '' }}">
                    {{ $c['cuotas_pendientes'] }}
                </span>
            </div>
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-satellite-dish" style="width:14px;"></i> Última sync</span>
                <span class="metric-value" style="font-family: var(--font-main); font-size: 11px; color: var(--text-2);">
                    {{ $c['ultima_sync'] }}
                </span>
            </div>

            @if($c['caja_inicial'] > 0)
            <div class="metric-row">
                <span class="metric-label"><i class="fas fa-cash-register" style="width:14px;"></i> Caja inicial</span>
                <span class="metric-value">${{ number_format($c['caja_inicial'], 0, ',', '.') }}</span>
            </div>
            @endif

        </div>
    </div>
    @empty
    <div class="empty-estado">
        <i class="fas fa-satellite-dish" style="font-size: 40px; margin-bottom: 16px; display: block;"></i>
        <div style="font-size: 16px; font-weight: 600; color: var(--text-2);">Sin cobradores activos</div>
        <div style="font-size: 13px; margin-top: 6px;">No hay cobradores con estado registrado hoy.</div>
    </div>
    @endforelse

</div>

@endsection

@push('scripts')
<script>
function filtrar(btn, estado) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.cobrador-card').forEach(card => {
        if (estado === 'todos' || card.dataset.estado === estado) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function recargar() {
    const spin = document.getElementById('spin');
    const label = document.getElementById('ultimo-refresh');
    spin.style.display = 'inline-block';
    label.textContent = 'Actualizando...';

    // Reload suave de la página
    setTimeout(() => window.location.reload(), 300);
}

// Auto-refresh cada 60 segundos
let countdown = 60;
setInterval(() => {
    countdown--;
    if (countdown <= 0) {
        window.location.reload();
    }
    const label = document.getElementById('ultimo-refresh');
    if (label) label.textContent = `Actualiza en ${countdown}s`;
}, 1000);
</script>
@endpush