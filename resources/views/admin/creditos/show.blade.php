@extends('layouts.admin')

@section('title', 'Crédito ' . $credito->codigo)

@section('topbar-actions')
    <a href="{{ route('admin.creditos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Creditos
    </a>
    <a href="{{ route('admin.creditos.edit', $credito) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-pen"></i> Editar
    </a>
    @if(in_array($credito->estado, ['activo','al_dia','mora']))
    <form action="{{ route('admin.creditos.destroy', $credito) }}" method="POST"
        onsubmit="return confirm('¿Eliminar este crédito? Esta acción no se puede deshacer.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Eliminar
        </button>
    </form>
    @endif
@endsection

@push('styles')
<style>
    .credito-hero {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 28px;
        display: flex; align-items: flex-start; gap: 24px;
        margin-bottom: 20px; position: relative; overflow: hidden;
    }
    .credito-hero::before {
        content: ''; position: absolute; top: 0; right: 0;
        width: 260px; height: 100%;
        background: radial-gradient(ellipse at right center, var(--accent-glow), transparent 70%);
        pointer-events: none;
    }
    .hero-icon {
        width: 68px; height: 68px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; flex-shrink: 0;
        background: var(--accent-glow);
        border: 1px solid rgba(124,92,191,0.3);
        color: var(--accent);
    }
    .hero-icon.mora { background: var(--danger-soft); border-color: rgba(239,68,68,0.3); color: var(--danger); }
    .hero-code { font-family: var(--font-mono); font-size: 22px; font-weight: 700; color: var(--text-1); margin-bottom: 4px; }
    .hero-sub { font-size: 13px; color: var(--text-2); margin-bottom: 12px; }

    .stats-band {
        display: grid; grid-template-columns: repeat(4, 1fr);
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 20px;
    }
    .band-item {
        padding: 18px 20px; border-right: 1px solid var(--border); text-align: center;
    }
    .band-item:last-child { border-right: none; }
    .band-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-3); margin-bottom: 6px; }
    .band-val { font-family: var(--font-mono); font-size: 20px; font-weight: 700; color: var(--text-1); }

    .progress-section {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 20px;
    }

    .estado-dot {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px;
    }
    .estado-dot::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .estado-pagada { color: var(--success); background: var(--success-soft); }
    .estado-pagada::before { background: var(--success); }
    .estado-pendiente { color: var(--text-2); background: var(--bg-card-2); }
    .estado-pendiente::before { background: var(--text-3); }
    .estado-vencida { color: var(--danger); background: var(--danger-soft); }
    .estado-vencida::before { background: var(--danger); }
    .estado-parcial { color: var(--warning); background: var(--warning-soft); }
    .estado-parcial::before { background: var(--warning); }

    @media (max-width: 768px) {
        .stats-band { grid-template-columns: repeat(2, 1fr); }
        .credito-hero { flex-direction: column; }
        .card-body { padding: 14px; }
        .card-header { padding: 12px 14px; }
    }
</style>
@endpush

@section('content')

{{-- HERO --}}
<div class="credito-hero">
    <div class="hero-icon {{ $credito->estado === 'mora' ? 'mora' : '' }}">
        <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div style="flex: 1;">
        <div class="hero-code">{{ $credito->codigo }}</div>
        <div class="hero-sub">
            Cliente: <strong style="color: var(--text-1);">{{ $credito->cliente->nombre }}</strong>
            &nbsp;·&nbsp; Cobrador: {{ $credito->cobrador->name ?? '—' }}
            &nbsp;·&nbsp; Creado: {{ $credito->created_at->format('d/m/Y') }}
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; font-size: 13px; color: var(--text-2);">
            <span><i class="fas fa-calendar-plus" style="color: var(--accent); font-size: 11px;"></i> Inicio: {{ $credito->fecha_inicio->format('d/m/Y') }}</span>
            <span><i class="fas fa-calendar-xmark" style="color: var(--danger); font-size: 11px;"></i> Vence: {{ $credito->fecha_vencimiento->format('d/m/Y') }}</span>
            <span><i class="fas fa-rotate" style="color: var(--accent); font-size: 11px;"></i> {{ ucfirst($credito->frecuencia) }}</span>
            <span><i class="fas fa-percent" style="font-size: 11px;"></i> {{ $credito->tasa_interes }}% interés</span>
        </div>
    </div>
    <div>
        @php $colors = ['activo'=>'info','al_dia'=>'success','mora'=>'danger','pagado'=>'success','cancelado'=>'warning']; @endphp
        <span class="tag {{ $colors[$credito->estado] ?? 'info' }}">{{ ucfirst($credito->estado) }}</span>
    </div>
</div>

{{-- STATS --}}
<div class="stats-band">
    <div class="band-item">
        <div class="band-label">Prestado</div>
        <div class="band-val" style="font-size: 17px;">${{ number_format($credito->monto_prestado, 0, ',', '.') }}</div>
    </div>
    <div class="band-item">
        <div class="band-label">Total a pagar</div>
        <div class="band-val" style="font-size: 17px;">${{ number_format($credito->total_a_pagar, 0, ',', '.') }}</div>
    </div>
    <div class="band-item">
        <div class="band-label">Saldo pendiente</div>
        <div class="band-val" style="font-size: 17px; color: {{ $credito->estado === 'mora' ? 'var(--danger)' : 'var(--accent)' }};">
            ${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}
        </div>
    </div>
    <div class="band-item">
        <div class="band-label">Cuota</div>
        <div class="band-val" style="font-size: 17px; color: var(--success);">${{ number_format($credito->valor_cuota, 0, ',', '.') }}</div>
    </div>
</div>

{{-- PROGRESO --}}
<div class="progress-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div style="font-size: 13px; color: var(--text-2);">
            <strong style="color: var(--text-1);">{{ $credito->cuotasPagadas() }}</strong> de <strong style="color: var(--text-1);">{{ $credito->num_cuotas }}</strong> cuotas pagadas
        </div>
        <div style="font-size: 16px; font-weight: 700; color: var(--accent);">{{ $credito->porcentajePagado() }}%</div>
    </div>
    <div class="progress-track" style="height: 12px;">
        <div class="progress-fill" style="width: {{ $credito->porcentajePagado() }}%;
            {{ $credito->estado === 'mora' ? 'background: var(--danger);' : '' }}"></div>
    </div>
    @if($credito->proxima_fecha_pago)
    <div style="margin-top: 10px; font-size: 12px; color: var(--text-2);">
        <i class="fas fa-calendar-check" style="color: var(--accent); font-size: 10px;"></i>
        Próximo pago:
        <strong style="color: {{ $credito->proxima_fecha_pago->isPast() ? 'var(--danger)' : 'var(--text-1)' }};">
            {{ $credito->proxima_fecha_pago->format('d/m/Y') }}
        </strong>
    </div>
    @endif
</div>

{{-- CUOTAS --}}
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list-ol"></i> Plan de cuotas ({{ $credito->num_cuotas }})</div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="tag success">{{ $credito->cuotasPagadas() }} pagadas</span>
            <span class="tag danger">{{ $credito->cuotas->where('estado','vencida')->count() }} vencidas</span>
            <span class="tag info">{{ $credito->cuotasPendientes() }} pendientes</span>
        </div>
    </div>
    <div style="max-height: 400px; overflow-y: auto;">
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vencimiento</th>
                    <th>Valor cuota</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Fecha pago</th>
                </tr>
            </thead>
            <tbody>
                @foreach($credito->cuotas->sortBy('numero_cuota') as $cuota)
                <tr>
                    <td style="font-family: var(--font-mono); color: var(--text-2); font-size: 12px;">{{ $cuota->numero_cuota }}</td>
                    <td style="color: {{ $cuota->fecha_vencimiento->isPast() && $cuota->estado !== 'pagada' ? 'var(--danger)' : 'var(--text-1)' }};">
                        {{ $cuota->fecha_vencimiento->format('d/m/Y') }}
                    </td>
                    <td style="font-family: var(--font-mono);">${{ number_format($cuota->valor_cuota, 0, ',', '.') }}</td>
                    <td style="font-family: var(--font-mono); color: var(--success);">
                        ${{ number_format($cuota->valor_pagado, 0, ',', '.') }}
                    </td>
                    <td style="font-family: var(--font-mono); color: var(--accent);">
                        ${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}
                    </td>
                    <td><span class="estado-dot estado-{{ $cuota->estado }}">{{ ucfirst($cuota->estado) }}</span></td>
                    <td style="color: var(--text-2); font-size: 12px;">
                        {{ $cuota->fecha_pago ? $cuota->fecha_pago->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- PAGOS --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-receipt"></i> Pagos registrados</div>
        <span class="tag info">{{ $credito->pagos->count() }} pagos</span>
    </div>
    @if($credito->pagos->count() > 0)
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table class="table">
        <thead>
            <tr>
                <th>Recibo</th>
                <th>Cobrador</th>
                <th>Fecha</th>
                <th>Método</th>
                <th>Monto</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credito->pagos->sortByDesc('fecha_pago') as $pago)
            <tr>
                <td style="font-family: var(--font-mono); font-size: 12px; color: var(--text-2);">{{ $pago->recibo_numero }}</td>
                <td style="font-size: 13px;">{{ $pago->cobrador->name ?? '—' }}</td>
                <td style="color: var(--text-2); font-size: 13px;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                <td><span class="tag info" style="font-size: 10px;">{{ ucfirst($pago->metodo_pago) }}</span></td>
                <td style="font-family: var(--font-mono); font-weight: 700; color: var(--success);">
                    ${{ number_format($pago->monto_pagado, 0, ',', '.') }}
                </td>
                <td>
                    @if($pago->es_pago_parcial)
                        <span class="tag warning" style="font-size: 10px;">Parcial</span>
                    @else
                        <span class="tag success" style="font-size: 10px;">Completo</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <div class="empty-state" style="padding: 30px;">
        <i class="fas fa-inbox" style="font-size: 28px; color: var(--text-3); display: block; margin-bottom: 8px;"></i>
        Sin pagos registrados aún
    </div>
    @endif
</div>

@endsection