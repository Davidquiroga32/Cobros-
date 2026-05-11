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
                <i class="fas fa-party-horn" style="font-size:24px; display:block; margin-bottom:8px;"></i>
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

@endsection

@push('scripts')
<script>
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