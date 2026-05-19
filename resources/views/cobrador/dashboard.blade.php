@extends('layouts.cobrador')

@section('title', 'Mi Dashboard')

@section('topbar-actions')
    <a href="{{ route('cobrador.agenda') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-route"></i> Ver ruta de hoy
    </a>
@endsection

@push('styles')
<style>
    .progress-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
    .ring-label {
        position: absolute; text-align: center;
        font-family: var(--font-mono); font-size: 22px; font-weight: 700; color: var(--text-1);
        line-height: 1;
    }
    .ring-label small { font-family: var(--font-main); font-size: 11px; font-weight: 500; color: var(--text-2); display: block; }

    .cobrado-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 10px;
        border: 1px solid var(--border);
        margin-bottom: 8px; background: var(--bg-card-2);
        transition: border-color .15s;
    }
    .cobrado-item:hover { border-color: var(--border-light); }

    .client-dot {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700;
        background: var(--accent-glow); color: var(--accent);
        flex-shrink: 0;
    }

    .mora-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 14px; border-radius: 10px;
        background: rgba(239,68,68,0.05);
        border: 1px solid rgba(239,68,68,0.15);
        margin-bottom: 6px;
    }

    .day-bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 80px; }
    .day-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .day-bar {
        width: 100%; background: var(--bg-card-2); border-radius: 6px 6px 0 0;
        position: relative; overflow: hidden; min-height: 4px;
        transition: height .6s cubic-bezier(.4,0,.2,1);
    }
    .day-bar-fill {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: linear-gradient(to top, var(--accent), rgba(79,142,247,0.4));
        border-radius: 6px 6px 0 0;
        transition: height 1s cubic-bezier(.4,0,.2,1);
    }
    .day-bar-label { font-size: 10px; color: var(--text-3); font-weight: 600; }
    .day-bar.today .day-bar-fill { background: linear-gradient(to top, var(--success), rgba(34,197,94,0.4)); }

    .proxima-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid var(--border);
    }
    .proxima-row:last-child { border-bottom: none; }

    canvas { max-width: 100%; }
    .progress-ring svg { max-width: 100%; height: auto; }

    @media (max-width: 900px) {
        .dashboard-grid-row { grid-template-columns: 1fr !important; }
    }

    .fecha-badge {
        min-width: 42px; text-align: center;
        background: var(--bg-card-2); border: 1px solid var(--border);
        border-radius: 8px; padding: 4px 8px;
        font-size: 10px; font-weight: 700; color: var(--text-2); line-height: 1.3;
    }
    .fecha-badge .day { font-size: 18px; font-family: var(--font-mono); font-weight: 700; color: var(--text-1); }

    @media (max-width: 900px) {
        .dashboard-grid-row { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

{{-- ── STAT CARDS ─────────────────────────────────────────── --}}
<div class="grid grid-4 mb-6">

    <div class="stat-card blue">
        <div class="stat-label">Cobrado hoy</div>
        <div class="stat-value money font-mono">{{ number_format($totalCobradoHoy, 0, ',', '.') }}</div>
        <div class="stat-meta">
            <i class="fas fa-arrow-trend-up" style="color: var(--success); font-size: 11px;"></i>
            De ${{ number_format($metaDiaria, 0, ',', '.') }} esperados
        </div>
        <div class="stat-icon"><i class="fas fa-coins" style="color: var(--accent);"></i></div>
    </div>

    <div class="stat-card green">
        <div class="stat-label">Visitas completadas</div>
        <div class="stat-value font-mono">{{ $visitasCompletadas }}<span style="font-size:16px; color: var(--text-2);">/{{ $totalVisitasHoy }}</span></div>
        <div class="stat-meta">
            <i class="fas fa-location-dot" style="color: var(--success); font-size: 11px;"></i>
            Clientes visitados hoy
        </div>
        <div class="stat-icon"><i class="fas fa-route" style="color: var(--success);"></i></div>
    </div>

    <div class="stat-card amber">
        <div class="stat-label">Pendientes hoy</div>
        <div class="stat-value font-mono">{{ $cuotasHoy->count() }}</div>
        <div class="stat-meta">
            <i class="fas fa-clock" style="color: var(--warning); font-size: 11px;"></i>
            Cuotas por cobrar
        </div>
        <div class="stat-icon"><i class="fas fa-hourglass-half" style="color: var(--warning);"></i></div>
    </div>

    <div class="stat-card red">
        <div class="stat-label">Clientes en mora</div>
        <div class="stat-value font-mono" style="color: var(--danger);">{{ $clientesEnMora->count() }}</div>
        <div class="stat-meta">
            <i class="fas fa-triangle-exclamation" style="color: var(--danger); font-size: 11px;"></i>
            Requieren atención
        </div>
        <div class="stat-icon"><i class="fas fa-circle-exclamation" style="color: var(--danger);"></i></div>
    </div>

</div>

{{-- ── FILA 2: PROGRESO + BARRAS + COBROS ──────────────────── --}}
<div class="grid dashboard-grid-row" style="grid-template-columns: 300px 1fr 1fr; gap: 16px; margin-bottom: 16px;">

    {{-- Anillo de meta --}}
    <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 28px;">
        <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3); margin-bottom: 20px;">Meta del día</div>

        <div class="progress-ring">
            <svg width="160" height="160" style="transform: rotate(-90deg);">
                <circle cx="80" cy="80" r="68" fill="none" stroke="var(--bg-card-2)" stroke-width="12"/>
                <circle cx="80" cy="80" r="68" fill="none"
                    stroke="url(#ringGradient)" stroke-width="12"
                    stroke-linecap="round"
                    stroke-dasharray="{{ round(2 * M_PI * 68, 1) }}"
                    stroke-dashoffset="{{ round(2 * M_PI * 68 * (1 - $porcentajeMeta / 100), 1) }}"
                    style="transition: stroke-dashoffset 1s ease;"/>
                <defs>
                    <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#4f8ef7"/>
                        <stop offset="100%" stop-color="#7c5cbf"/>
                    </linearGradient>
                </defs>
            </svg>
            <div class="ring-label">
                {{ $porcentajeMeta }}%
                <small>completado</small>
            </div>
        </div>

        <div style="margin-top: 18px; text-align: center;">
            <div style="font-size: 13px; color: var(--text-2);">
                ${{ number_format($totalCobradoHoy, 0, ',', '.') }}
                <span style="color: var(--text-3);">/ ${{ number_format($metaDiaria, 0, ',', '.') }}</span>
            </div>
            <div style="font-size: 11px; color: var(--text-3); margin-top: 2px;">cobrado / meta</div>
        </div>
    </div>

    {{-- Gráfica semanal --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar"></i> Cobros últimos 7 días</div>
        </div>
        <div class="card-body">
            <canvas id="weekChart" height="120" style="max-width: 100%;"></canvas>
        </div>
    </div>

    {{-- Cobros de hoy --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-circle-check"></i> Cobros de hoy</div>
            <span class="tag success">{{ $cobradosHoy->count() }} pagos</span>
        </div>
        <div class="card-body" style="max-height: 220px; overflow-y: auto;">
            @forelse($cobradosHoy as $pago)
                <div class="cobrado-item">
                    <div class="client-dot">{{ strtoupper(substr($pago->cliente->nombre, 0, 1)) }}</div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-1);" class="truncate">
                            {{ $pago->cliente->nombre }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-2);">
                            {{ $pago->fecha_pago->format('h:i A') }}
                        </div>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 14px; color: var(--success); font-weight: 700;">
                        ${{ number_format($pago->monto_pagado, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 30px 0; color: var(--text-3);">
                    <i class="fas fa-inbox" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                    Aún no hay cobros registrados hoy
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ── FILA 3: MORA + PRÓXIMAS CUOTAS ──────────────────────── --}}
<div class="grid grid-2">

    {{-- Clientes en mora --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i> Clientes en mora</div>
            <a href="{{ route('cobrador.clientes.index', ['filtro' => 'mora']) }}" class="btn btn-secondary btn-sm">Ver todos</a>
        </div>
        <div class="card-body">
            @forelse($clientesEnMora as $cliente)
                @php $credito = $cliente->creditoActivo(); @endphp
                <div class="mora-item">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(239,68,68,0.15); display: flex; align-items: center; justify-content: center; color: var(--danger); font-size: 13px; font-weight: 700; flex-shrink: 0;">
                        {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 13px; font-weight: 600;" class="truncate">{{ $cliente->nombre }}</div>
                        <div style="font-size: 11px; color: var(--danger);">
                            {{ $credito ? $credito->dias_mora . ' días de mora' : '—' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-family: var(--font-mono); font-size: 13px; color: var(--danger); font-weight: 700;">
                            ${{ $credito ? number_format($credito->saldo_pendiente, 0, ',', '.') : '0' }}
                        </div>
                        <a href="{{ route('cobrador.clientes.show', $cliente) }}" class="tag danger" style="font-size: 10px; text-decoration: none;">
                            Ver
                        </a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 20px; color: var(--text-3);">
                    <i class="fas fa-party-horn" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                    ¡Sin clientes en mora!
                </div>
            @endforelse
        </div>
    </div>

    {{-- Próximas cuotas --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-calendar-days"></i> Próximos cobros</div>
            <span style="font-size: 11px; color: var(--text-2);">Mañana y pasado</span>
        </div>
        <div class="card-body">
            @forelse($proximasCuotas as $cuota)
                <div class="proxima-row">
                    <div class="fecha-badge">
                        <div class="day">{{ $cuota->fecha_vencimiento->format('d') }}</div>
                        {{ $cuota->fecha_vencimiento->isoFormat('MMM') }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 13px; font-weight: 600;" class="truncate">{{ $cuota->cliente->nombre }}</div>
                        <div style="font-size: 11px; color: var(--text-2);">Cuota #{{ $cuota->numero_cuota }}</div>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 14px; font-weight: 700; color: var(--text-1);">
                        ${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 20px; color: var(--text-3);">
                    No hay cobros pendientes para los próximos días
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Gráfica semanal
    const weekData = @json($cobrosSemana);
    const maxVal   = Math.max(...weekData.map(d => d.total), 1);
    const todayIdx = weekData.length - 1;

    const ctx = document.getElementById('weekChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: weekData.map(d => d.fecha),
            datasets: [{
                data: weekData.map(d => d.total),
                backgroundColor: weekData.map((d, i) =>
                    i === todayIdx
                        ? 'rgba(34,197,94,0.8)'
                        : 'rgba(79,142,247,0.5)'
                ),
                borderColor: weekData.map((d, i) =>
                    i === todayIdx ? '#22c55e' : '#4f8ef7'
                ),
                borderWidth: 1.5,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#12141c',
                    borderColor: '#2a2e42',
                    borderWidth: 1,
                    titleColor: '#8b92b3',
                    bodyColor: '#f0f2ff',
                    callbacks: {
                        label: ctx => '$' + ctx.raw.toLocaleString('es-CO')
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#565d7e', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(42,46,66,0.5)', drawBorder: false },
                    ticks: {
                        color: '#565d7e', font: { size: 11 },
                        callback: v => '$' + (v/1000).toFixed(0) + 'k'
                    }
                }
            }
        }
    });
</script>
@endpush