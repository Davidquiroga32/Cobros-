@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('topbar-actions')
    <a href="{{ route('admin.creditos.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo crédito
    </a>
    <a href="{{ route('admin.clientes.create') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-user-plus"></i> Nuevo cliente
    </a>
@endsection

@push('styles')
<style>
    /* Estilos existentes optimizados */
    .cobrador-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .cobrador-row:last-child { border-bottom: none; }
    .cobrador-avatar { width: 38px; height: 38px; border-radius: 10px; background: var(--accent-glow); border: 1px solid rgba(124,92,191,0.3); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: var(--accent); flex-shrink: 0; }
    .cobrador-info { flex: 1; min-width: 0; }
    .cobrador-name { font-size: 13px; font-weight: 600; color: var(--text-1); }
    .cobrador-meta { font-size: 11px; color: var(--text-2); }
    .cobrador-stat { text-align: right; flex-shrink: 0; }
    .cobrador-monto { font-family: var(--font-mono); font-size: 14px; font-weight: 700; color: var(--success); }
    .cobrador-pagos { font-size: 11px; color: var(--text-2); }

    .vencida-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(37,40,64,0.5); }
    .vencida-row:last-child { border-bottom: none; }

    .credito-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(37,40,64,0.5); }
    .credito-row:last-child { border-bottom: none; }

    /* --- AJUSTES PARA LA TABLA RESPONSIVA --- */
    .table-responsive-container {
        width: 100%;
        overflow-x: auto; /* Scroll horizontal si es necesario */
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px; /* Evita que las 14 columnas se aplasten */
    }

    .table th {
        font-size: 11px;
        color: var(--text-2);
        text-align: left;
        padding: 12px;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 12px;
        border-top: 1px solid rgba(37,40,64,0.5);
        font-size: 12px;
        white-space: nowrap; /* Evita saltos de línea molestos */
        color: var(--text-1);
        vertical-align: middle;
    }

    /* Columna de nombre fija un poco más ancha */
    .table td:first-child {
        font-weight: 600;
        min-width: 160px;
    }

    .online-dot, .offline-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    .online-dot { background: #00d26a; box-shadow: 0 0 8px #00d26a; }
    .offline-dot { background: #ff4d4d; box-shadow: 0 0 8px #ff4d4d; }

    .progress-mini {
        width: 80px;
        height: 6px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }

    .progress-mini-bar {
        height: 100%;
        background: linear-gradient(90deg,#7c5cbf,#4da3ff);
    }
</style>
@endpush

@section('content')

{{-- KPI ROW --}}
<div class="grid grid-4 mb-6">
    <div class="stat-card purple">
        <div class="stat-label">Cartera total</div>
        <div class="stat-value money" style="font-size:22px;">{{ number_format($carteraTotal, 0, ',', '.') }}</div>
        <div class="stat-meta"><i class="fas fa-chart-line" style="font-size:10px; color:var(--accent);"></i> {{ $creditosActivos }} créditos activos</div>
        <div class="stat-icon"><i class="fas fa-vault" style="color:var(--accent);"></i></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Cobrado hoy</div>
        <div class="stat-value money" style="font-size:22px; color:var(--success);">{{ number_format($cobradoHoy, 0, ',', '.') }}</div>
        <div class="stat-meta"><i class="fas fa-calendar" style="font-size:10px;"></i> Mes: ${{ number_format($cobradoMes, 0, ',', '.') }}</div>
        <div class="stat-icon"><i class="fas fa-coins" style="color:var(--success);"></i></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Clientes en mora</div>
        <div class="stat-value" style="color:var(--danger);">{{ $clientesEnMora }}</div>
        <div class="stat-meta"><i class="fas fa-triangle-exclamation" style="font-size:10px;"></i> {{ $cuotasVencidas }} cuotas vencidas</div>
        <div class="stat-icon"><i class="fas fa-circle-exclamation" style="color:var(--danger);"></i></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Clientes / Cobradores</div>
        <div class="stat-value">{{ $totalClientes }}<span style="font-size:16px; color:var(--text-2);"> / {{ $totalCobradores }}</span></div>
        <div class="stat-meta"><i class="fas fa-users" style="font-size:10px;"></i> Activos en el sistema</div>
        <div class="stat-icon"><i class="fas fa-users" style="color:var(--accent-2);"></i></div>
    </div>
</div>

{{-- CHARTS + COBROS --}}
<div class="grid" style="grid-template-columns: 1fr 360px; gap: 16px; margin-bottom: 16px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-area"></i> Cobros últimos 30 días</div>
        </div>
        <div class="card-body">
            <canvas id="chart30" height="100"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-ranking-star"></i> Cobradores — hoy</div>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">Ver todos</a>
        </div>
        <div class="card-body">
            @forelse($cobradores as $c)
            <div class="cobrador-row">
                <div class="cobrador-avatar">{{ strtoupper(substr($c->name, 0, 1)) }}</div>
                <div class="cobrador-info">
                    <div class="cobrador-name truncate">{{ $c->name }}</div>
                    <div class="cobrador-meta">{{ $c->total_clientes }} clientes @if($c->en_mora > 0) · <span style="color:var(--danger);">{{ $c->en_mora }} mora</span>@endif</div>
                </div>
                <div class="cobrador-stat">
                    <div class="cobrador-monto">${{ number_format($c->cobrado_hoy, 0, ',', '.') }}</div>
                    <div class="cobrador-pagos">{{ $c->pagos_hoy }} pagos</div>
                </div>
            </div>
            @empty
            <div style="text-align:center; padding:20px; color:var(--text-3);">
                <i class="fas fa-users" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                Sin cobradores activos
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- VENCIDAS + RECIENTES --}}
<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock-rotate-left" style="color:var(--danger);"></i> Cuotas vencidas</div>
            <span class="tag danger">{{ $cuotasVencidas }} total</span>
        </div>
        <div class="card-body">
            @forelse($cuotasVencidasTop as $cuota)
            <div class="vencida-row">
                <div style="min-width:40px; text-align:center;">
                    <div style="font-size:10px; font-weight:700; color:var(--danger);">{{ $cuota->calcularDiasMora() }}d</div>
                    <div style="font-size:9px; color:var(--text-3);">atraso</div>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600;" class="truncate">{{ $cuota->cliente->nombre }}</div>
                    <div style="font-size:11px; color:var(--text-2);">{{ $cuota->credito->cobrador->name ?? '—' }} · Cuota #{{ $cuota->numero_cuota }}</div>
                </div>
                <div style="font-family:var(--font-mono); font-size:13px; color:var(--danger); font-weight:700;">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</div>
            </div>
            @empty
            <div style="text-align:center; padding:20px; color:var(--text-3);">
                <i class="fas fa-check-circle" style="font-size:24px; display:block; margin-bottom:8px; color:var(--success);"></i>
                ¡Sin cuotas vencidas!
            </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-file-circle-plus"></i> Créditos recientes</div>
            <a href="{{ route('admin.creditos.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo</a>
        </div>
        <div class="card-body">
            @foreach($creditosRecientes as $credito)
            <div class="credito-row">
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600;" class="truncate">{{ $credito->cliente->nombre }}</div>
                    <div style="font-size:11px; color:var(--text-2);">{{ $credito->codigo }} · {{ ucfirst($credito->frecuencia) }}</div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-family:var(--font-mono); font-size:13px; font-weight:700; color:var(--accent);">${{ number_format($credito->monto_prestado, 0, ',', '.') }}</div>
                    @php
                        $colors = ['activo'=>'info','al_dia'=>'success','mora'=>'danger','pagado'=>'success','cancelado'=>'warning'];
                    @endphp
                    <span class="tag {{ $colors[$credito->estado] ?? 'info' }}" style="font-size:9px;">{{ ucfirst($credito->estado) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- TABLA OPERATIVA - AJUSTADA --}}
<div class="card mt-6">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-satellite-dish"></i>
            Estado operativo cobradores
        </div>
        <span class="tag info pulse">Tiempo real</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive-container">
            <table class="table" id="tabla-cobradores-operativos">
                <thead>
                    <tr>
                        <th>Cobrador</th>
                        <th>CN</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Score</th>
                        <th>Caja</th>
                        <th>Fecha Caja</th>
                        <th>Inicial</th>
                        <th>Final</th>
                        <th>Ruta</th>
                        <th>Sync</th>
                        <th>PIN</th>
                        <th>Versión</th>
                        <th style="text-align:center;">Online</th>
                    </tr>
                </thead>
                <tbody id="estado-body">
                    @foreach($cobradoresEstado as $estado)
                    <tr>
                        <td>{{ $estado->cobrador->name }}</td>
                        <td><span class="badge-subtle">{{ $estado->cn }}</span></td>
                        <td><i class="fas fa-location-dot" style="font-size:10px; color:var(--text-3);"></i> {{ $estado->ubicacion_actual ?? 'No disponible' }}</td>
                        <td>
                            @php
                                $estadoColors = [
                                    'disponible' => 'success',
                                    'en_ruta' => 'info',
                                    'pausado' => 'warning',
                                    'offline' => 'danger',
                                    'sincronizando' => 'purple'
                                ];
                            @endphp
                            <span class="tag {{ $estadoColors[$estado->estado] ?? 'info' }}">
                                {{ ucfirst($estado->estado) }}
                            </span>
                        </td>
                        <td><strong>{{ $estado->score }}</strong></td>
                        <td>{{ $estado->caja_actual }}</td>
                        <td>{{ optional($estado->fecha_caja)->format('d/m/Y') }}</td>
                        <td>${{ number_format($estado->caja_inicial,0,',','.') }}</td>
                        <td>${{ number_format($estado->caja_final,0,',','.') }}</td>
                        <td>
                            <div class="progress-mini">
                                <div class="progress-mini-bar" style="width: {{ $estado->progreso_ruta }}%"></div>
                            </div>
                            <small>{{ $estado->progreso_ruta }}%</small>
                        </td>
                        <td>{{ optional($estado->ultima_sincronizacion)?->diffForHumans() ?? '—' }}</td>
                        <td><small style="font-family:var(--font-mono);">{{ $estado->pin_dispositivo }}</small></td>
                        <td>v{{ $estado->version_app }}</td>
                        <td style="text-align:center;">
                            @if($estado->conectado)
                                <span class="online-dot"></span>
                            @else
                                <span class="offline-dot"></span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Tu lógica de JS permanece igual, ya que solo cambiamos clases de estilo
    async function cargarEstadosCobradores() {
        try {
            const response = await fetch("{{ route('admin.cobradores.estado') }}");
            const data = await response.json();
            // Aquí podrías actualizar el DOM dinámicamente si lo deseas
        } catch (e) { console.error("Error cargando estados"); }
    }

    setInterval(() => { cargarEstadosCobradores(); }, 15000);

    // Gráfico Chart.js
    const data30 = @json($cobros30);
    const ctx = document.getElementById('chart30').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data30.map(d => d.fecha),
            datasets: [{
                data: data30.map(d => d.total),
                borderColor: '#7c5cbf',
                backgroundColor: 'rgba(124,92,191,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#10121a', borderColor: '#252840', borderWidth: 1,
                titleColor: '#8b93c0', bodyColor: '#eef0ff',
                callbacks: { label: ctx => '$' + ctx.raw.toLocaleString('es-CO') }
            }},
            scales: {
                x: { grid: { display: false }, ticks: { color: '#525880', font: { size: 10 }, maxTicksLimit: 10 } },
                y: { grid: { color: 'rgba(37,40,64,0.5)' }, ticks: { color: '#525880', font: { size: 10 }, callback: v => '$' + (v/1000).toFixed(0) + 'k' } }
            }
        }
    });
</script>
@endpush