@extends('layouts.cobrador')

@section('title', $cliente->nombre)

@section('topbar-actions')
    <a href="{{ route('cobrador.clientes.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    @if($creditoActivo)
    <a href="{{ route('cobrador.pagos.create', $creditoActivo->cuotas()->whereIn('estado',['pendiente','parcial','vencida'])->orderBy('fecha_vencimiento')->first() ?? $creditoActivo->cuotas->first()) }}"
       class="btn btn-primary btn-sm">
        <i class="fas fa-hand-holding-dollar"></i> Registrar pago
    </a>
    @endif
@endsection

@push('styles')
<style>
    .client-hero { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; display: flex; align-items: flex-start; gap: 24px; margin-bottom: 20px; position: relative; overflow: hidden; }
    .client-hero::before { content: ''; position: absolute; top: 0; right: 0; width: 200px; height: 100%; background: radial-gradient(ellipse at right center, var(--accent-glow), transparent 70%); pointer-events: none; }
    .hero-avatar { width: 72px; height: 72px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: white; flex-shrink: 0; background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 0 30px var(--accent-glow); }
    .hero-avatar.mora { background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 0 30px var(--danger-soft); }
    .hero-info { flex: 1; }
    .hero-name { font-size: 24px; font-weight: 700; color: var(--text-1); margin-bottom: 4px; }
    .hero-cedula { font-size: 13px; color: var(--text-2); margin-bottom: 12px; }
    .hero-meta { display: flex; flex-wrap: wrap; gap: 10px; }
    .hero-meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-2); }
    .hero-meta-item i { color: var(--accent); font-size: 12px; }

    .credito-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 20px; overflow: hidden; }
    .credito-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, rgba(79,142,247,0.05), transparent); }
    .credito-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
    .credito-stat { padding: 18px 20px; border-right: 1px solid var(--border); text-align: center; }
    .credito-stat:last-child { border-right: none; }
    .cs-stat-label { font-size: 11px; color: var(--text-3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .cs-stat-value { font-family: var(--font-mono); font-size: 20px; font-weight: 700; color: var(--text-1); }
    .credito-progress { padding: 16px 22px; border-top: 1px solid var(--border); }
    .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-2); margin-bottom: 8px; }
    .progress-track-lg { height: 10px; background: var(--bg-card-2); border-radius: 10px; overflow: hidden; }
    .progress-fill-lg { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--accent), var(--accent-2)); }

    .cuotas-table-wrap { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 20px; }

    .estado-dot { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .estado-dot::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .estado-pagada { color: var(--success); background: var(--success-soft); }
    .estado-pagada::before { background: var(--success); }
    .estado-pendiente { color: var(--text-2); background: var(--bg-card-2); }
    .estado-pendiente::before { background: var(--text-3); }
    .estado-vencida { color: var(--danger); background: var(--danger-soft); }
    .estado-vencida::before { background: var(--danger); }
    .estado-parcial { color: var(--warning); background: var(--warning-soft); }
    .estado-parcial::before { background: var(--warning); }

    .pago-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .pago-item:last-child { border-bottom: none; }
    .pago-icon { width: 36px; height: 36px; border-radius: 10px; background: var(--success-soft); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
    .pago-info { flex: 1; }
    .pago-recibo { font-size: 12px; font-weight: 600; color: var(--text-1); }
    .pago-fecha { font-size: 11px; color: var(--text-2); }
    .pago-monto { font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--success); }
    .metodo { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; padding: 2px 8px; border-radius: 6px; background: var(--bg-card-2); color: var(--text-2); border: 1px solid var(--border); }

    @media (max-width: 768px) {
        .client-hero { flex-direction: column; }
        .credito-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

<div class="client-hero">
    <div class="hero-avatar {{ $cliente->enMora() ? 'mora' : '' }}">
        {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
    </div>
    <div class="hero-info">
        <div class="hero-name">{{ $cliente->nombre }}</div>
        <div class="hero-cedula">CC {{ $cliente->cedula ?? 'Sin cédula' }}</div>
        <div class="hero-meta">
            @if($cliente->telefono)
            <div class="hero-meta-item">
                <i class="fas fa-phone"></i>
                <a href="tel:{{ $cliente->telefono }}" style="color: inherit;">{{ $cliente->telefono }}</a>
            </div>
            @endif
            @if($cliente->telefono_alt)
            <div class="hero-meta-item">
                <i class="fas fa-phone-volume"></i>
                {{ $cliente->telefono_alt }}
            </div>
            @endif
            @if($cliente->direccion)
            <div class="hero-meta-item">
                <i class="fas fa-location-dot"></i>
                {{ $cliente->direccion }}
                @if($cliente->barrio) — {{ $cliente->barrio }} @endif
            </div>
            @endif
        </div>
    </div>
    <div>
        @if($cliente->enMora())
            <span class="tag danger"><i class="fas fa-triangle-exclamation"></i> Cliente en mora</span>
        @elseif($creditoActivo)
            <span class="tag success"><i class="fas fa-circle-check"></i> Al día</span>
        @else
            <span class="tag" style="background: var(--bg-card-2); color: var(--text-2);">Sin crédito activo</span>
        @endif
    </div>
</div>

@if($creditoActivo)
<div class="credito-card">
    <div class="credito-header">
        <div>
            <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Crédito activo — {{ $creditoActivo->codigo }}</div>
            <div style="font-size: 12px; color: var(--text-2); margin-top: 4px;">
                Desde {{ $creditoActivo->fecha_inicio->format('d/m/Y') }}
                · Frecuencia {{ ucfirst($creditoActivo->frecuencia) }}
                · {{ $creditoActivo->tasa_interes }}% de interés
            </div>
        </div>
        <div>
            @if($creditoActivo->proxima_fecha_pago)
            <div style="text-align: right;">
                <div style="font-size: 11px; color: var(--text-3);">Próximo pago</div>
                <div style="font-size: 14px; font-weight: 700; color:
                    {{ $creditoActivo->proxima_fecha_pago->isPast() ? 'var(--danger)' : 'var(--text-1)' }}">
                    {{ $creditoActivo->proxima_fecha_pago->format('d/m/Y') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="credito-stats">
        <div class="credito-stat">
            <div class="cs-stat-label">Prestado</div>
            <div class="cs-stat-value" style="font-size:16px;">${{ number_format($creditoActivo->monto_prestado, 0, ',', '.') }}</div>
        </div>
        <div class="credito-stat">
            <div class="cs-stat-label">Total a pagar</div>
            <div class="cs-stat-value" style="font-size:16px;">${{ number_format($creditoActivo->total_a_pagar, 0, ',', '.') }}</div>
        </div>
        <div class="credito-stat">
            <div class="cs-stat-label">Saldo pendiente</div>
            <div class="cs-stat-value" style="font-size:16px; color: {{ $creditoActivo->estaEnMora() ? 'var(--danger)' : 'var(--accent)' }}">
                ${{ number_format($creditoActivo->saldo_pendiente, 0, ',', '.') }}
            </div>
        </div>
        <div class="credito-stat">
            <div class="cs-stat-label">Valor cuota</div>
            <div class="cs-stat-value" style="font-size:16px;">${{ number_format($creditoActivo->valor_cuota, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="credito-progress">
        <div class="progress-label">
            <span>{{ $creditoActivo->cuotasPagadas() }} de {{ $creditoActivo->num_cuotas }} cuotas pagadas</span>
            <span style="font-weight: 700; color: var(--accent);">{{ $creditoActivo->porcentajePagado() }}%</span>
        </div>
        <div class="progress-track-lg">
            <div class="progress-fill-lg" style="width: {{ $creditoActivo->porcentajePagado() }}%;
                {{ $creditoActivo->estaEnMora() ? 'background: var(--danger);' : '' }}"></div>
        </div>
    </div>
</div>

@if($cuotasProximas->count() > 0)
<div class="cuotas-table-wrap">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-calendar-week"></i> Próximas cuotas</div>
        <span class="tag info">{{ $cuotasProximas->count() }} pendientes</span>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Vencimiento</th>
                <th>Valor cuota</th>
                <th>Saldo</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotasProximas as $cuota)
            <tr>
                <td style="font-family: var(--font-mono); color: var(--text-2);">{{ $cuota->numero_cuota }}/{{ $creditoActivo->num_cuotas }}</td>
                <td>
                    <span style="color: {{ $cuota->fecha_vencimiento->isPast() ? 'var(--danger)' : 'var(--text-1)' }}; font-weight: 500;">
                        {{ $cuota->fecha_vencimiento->format('d/m/Y') }}
                    </span>
                    @if($cuota->fecha_vencimiento->isToday())
                        <span class="tag warning" style="font-size: 10px; margin-left: 6px;">Hoy</span>
                    @elseif($cuota->fecha_vencimiento->isPast())
                        <span class="tag danger" style="font-size: 10px; margin-left: 6px;">{{ $cuota->fecha_vencimiento->diffInDays(today()) }}d atraso</span>
                    @endif
                </td>
                <td style="font-family: var(--font-mono);">${{ number_format($cuota->valor_cuota, 0, ',', '.') }}</td>
                <td style="font-family: var(--font-mono); color: var(--accent); font-weight: 700;">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</td>
                <td><span class="estado-dot estado-{{ $cuota->estado }}">{{ ucfirst($cuota->estado) }}</span></td>
                <td>
                    <a href="{{ route('cobrador.pagos.create', $cuota) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-hand-holding-dollar"></i> Cobrar
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Historial de pagos</div>
        <span class="tag info">Últimos 10</span>
    </div>
    <div class="card-body">
        @forelse($historialPagos as $pago)
        <div class="pago-item">
            <div class="pago-icon"><i class="fas fa-receipt"></i></div>
            <div class="pago-info">
                <div class="pago-recibo">{{ $pago->recibo_numero }}</div>
                <div class="pago-fecha">
                    {{ $pago->fecha_pago->format('d/m/Y h:i A') }}
                    &nbsp;·&nbsp; <span class="metodo"><i class="fas fa-coins" style="font-size:9px;"></i> {{ ucfirst($pago->metodo_pago) }}</span>
                    @if($pago->es_pago_parcial) &nbsp;<span class="tag warning" style="font-size:10px;">Parcial</span>@endif
                </div>
            </div>
            <div class="pago-monto">${{ number_format($pago->monto_pagado, 0, ',', '.') }}</div>
        </div>
        @empty
        <div style="text-align: center; padding: 30px; color: var(--text-3);">
            <i class="fas fa-inbox" style="font-size: 28px; display: block; margin-bottom: 8px;"></i>
            Este cliente no tiene pagos registrados
        </div>
        @endforelse
    </div>
</div>

@endsection