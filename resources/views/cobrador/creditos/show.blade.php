@extends('layouts.cobrador')

@section('title', 'Credito ' . $credito->codigo)

@section('topbar-actions')
    <a href="{{ route('cobrador.creditos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@push('styles')
<style>
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .detail-item { }
    .detail-label { font-size: 10px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3); margin-bottom: 2px; }
    .detail-value { font-size: 15px; font-weight: 600; color: var(--text-1); }
    .detail-value.money { font-family: var(--font-mono); }
    .cuota-item {
        display: flex; align-items: center; gap: 14px;
        flex-wrap: wrap;
        padding: 10px 16px; border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .cuota-num {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; font-family: var(--font-mono);
        background: var(--bg-card-2); color: var(--text-2);
        flex-shrink: 0;
    }
    .cuota-num.pagada { background: var(--success-soft); color: var(--success); }
    .cuota-num.vencida { background: var(--danger-soft); color: var(--danger); }
    .cuota-num.parcial { background: var(--warning-soft); color: var(--warning); }
    @media (max-width: 640px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div class="page-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
    <div>
        <div style="font-size: 22px; font-weight: 700; color: var(--text-1); display: flex; align-items: center; gap: 10px;">
            Credito {{ $credito->codigo }}
            <span class="tag {{ match($credito->estado) { 'activo', 'al_dia' => 'info', 'mora' => 'danger', 'pagado' => 'success', default => 'info' } }}">
                {{ match($credito->estado) {
                    'activo' => 'Activo',
                    'al_dia' => 'Al dia',
                    'mora' => 'En mora',
                    'pagado' => 'Pagado',
                    'cancelado' => 'Cancelado',
                    default => $credito->estado,
                } }}
            </span>
        </div>
        <p style="font-size: 13px; color: var(--text-2); margin-top: 2px;">
            Cliente: <a href="{{ route('cobrador.clientes.show', $credito->cliente) }}" style="color: var(--accent); text-decoration: none;">{{ $credito->cliente->nombre }}</a>
        </p>
    </div>
    <div style="text-align: right;">
        <div style="font-size: 10px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3);">Progreso</div>
        <div style="font-size: 22px; font-weight: 700; color: var(--accent); font-family: var(--font-mono);">{{ $credito->porcentajePagado() }}%</div>
    </div>
</div>

<div class="grid grid-2 mb-6">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-info-circle"></i> Detalles del credito</div>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Monto prestado</div>
                    <div class="detail-value money">${{ number_format($credito->monto_prestado, 0, ',', '.') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tasa de interes</div>
                    <div class="detail-value">{{ $credito->tasa_interes }}%</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Total a pagar</div>
                    <div class="detail-value money">${{ number_format($credito->total_a_pagar, 0, ',', '.') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Saldo pendiente</div>
                    <div class="detail-value money" style="color: {{ $credito->saldo_pendiente > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        ${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Cuotas</div>
                    <div class="detail-value">{{ $credito->cuotasPagadas() }} / {{ $credito->num_cuotas }} pagadas</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Valor por cuota</div>
                    <div class="detail-value money">${{ number_format($credito->valor_cuota, 0, ',', '.') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Frecuencia</div>
                    <div class="detail-value">{{ ucfirst($credito->frecuencia) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fecha inicio</div>
                    <div class="detail-value">{{ $credito->fecha_inicio->format('d/m/Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fecha vencimiento</div>
                    <div class="detail-value">{{ $credito->fecha_vencimiento->format('d/m/Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Prox. fecha pago</div>
                    <div class="detail-value">{{ $credito->proxima_fecha_pago?->format('d/m/Y') ?? '--' }}</div>
                </div>
            </div>
            @if($credito->notas)
            <div style="margin-top: 16px; padding: 12px; background: var(--bg-card-2); border-radius: 10px; font-size: 13px; color: var(--text-2);">
                <strong style="color: var(--text-1);">Notas:</strong> {{ $credito->notas }}
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-list-ol"></i> Cuotas</div>
            <span class="tag info">{{ $credito->cuotas->count() }} cuotas</span>
        </div>
        <div class="card-body" style="padding: 0; max-height: 420px; overflow-y: auto;">
            @forelse($credito->cuotas as $cuota)
            <div class="cuota-item">
                <div class="cuota-num {{ $cuota->estado }}">{{ $cuota->numero_cuota }}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: var(--text-1);">
                        Vence: {{ $cuota->fecha_vencimiento->format('d/m/Y') }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-2);">
                        @if($cuota->estado === 'pagada')
                            Pagado el {{ $cuota->fecha_pago?->format('d/m/Y') ?? '--' }}
                        @elseif($cuota->estado === 'parcial')
                            ${{ number_format($cuota->valor_pagado, 0, ',', '.') }} pagado · falta ${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}
                        @elseif($cuota->estaVencida())
                            <span style="color: var(--danger);">Vencida ({{ $cuota->calcularDiasMora() }} dias)</span>
                        @else
                            Pendiente
                        @endif
                    </div>
                </div>
                <div style="font-family: var(--font-mono); font-size: 13px; font-weight: 700; color: var(--text-1);">
                    ${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}
                </div>
                @if(in_array($cuota->estado, ['pendiente', 'parcial', 'vencida']))
                <div style="flex-shrink: 0;">
                    <a href="{{ route('cobrador.pagos.create', $cuota) }}" class="btn btn-success btn-sm">
                        Cobrar
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div style="padding: 30px; text-align: center; color: var(--text-3);">Sin cuotas registradas.</div>
            @endforelse
        </div>
    </div>
</div>

@if($credito->pagos->count() > 0)
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-receipt"></i> Historial de pagos</div>
        <span class="tag info">{{ $credito->pagos->count() }} pagos</span>
    </div>
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table">
            <thead>
                <tr>
                    <th>Recibo</th>
                    <th>Cuota</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Metodo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($credito->pagos as $pago)
                <tr>
                    <td style="font-family: var(--font-mono); font-size: 12px;">{{ $pago->recibo_numero }}</td>
                    <td>#{{ $pago->cuota?->numero_cuota ?? '--' }}</td>
                    <td>{{ $pago->fecha_pago->format('d/m/Y h:i A') }}</td>
                    <td style="font-family: var(--font-mono); font-weight: 700; color: var(--success);">
                        ${{ number_format($pago->monto_pagado, 0, ',', '.') }}
                    </td>
                    <td>{{ ucfirst($pago->metodo_pago) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
