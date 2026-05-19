@extends('layouts.cobrador')

@section('title', 'Historial de Pagos')

@section('topbar-actions')
    <form method="GET" action="{{ route('cobrador.pagos.index') }}" class="flex items-center gap-2">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Buscar cliente..." class="form-control"
            style="width: 180px; padding: 6px 12px; font-size:13px;">
        <input type="date" name="fecha" value="{{ request('fecha') }}"
            class="form-control" style="width:auto; padding: 6px 12px; font-size:13px;">
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
        @if(request('buscar') || request('fecha'))
            <a href="{{ route('cobrador.pagos.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i></a>
        @endif
    </form>
@endsection

@push('styles')
<style>
    .pagos-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }

    .ps-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 16px 20px;
        display: flex; align-items: center; gap: 14px;
    }
    .ps-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .ps-icon.green { background: var(--success-soft); color: var(--success); }
    .ps-icon.blue  { background: var(--accent-glow); color: var(--accent); }
    .ps-icon.gold  { background: rgba(245,158,11,0.12); color: var(--warning); }
    .ps-label { font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.6px; }
    .ps-value { font-size: 20px; font-weight: 700; font-family: var(--font-mono); color: var(--text-1); }

    /* Pagos table */
    .pagos-table-wrap {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden;
    }

    .pago-row { display: flex; align-items: center; gap: 16px; padding: 14px 20px; border-bottom: 1px solid rgba(42,46,66,0.5); transition: background .1s; flex-wrap: wrap; }
    .pago-row:last-child { border-bottom: none; }
    .pago-row:hover { background: var(--bg-card-2); }

    .pago-recibo-badge {
        font-family: var(--font-mono); font-size: 11px; font-weight: 700;
        color: var(--text-3); min-width: 90px;
    }

    .pago-avatar { width: 38px; height: 38px; border-radius: 10px; background: var(--success-soft); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }

    .pago-cliente { flex: 1; min-width: 0; }
    .pago-nombre { font-size: 14px; font-weight: 600; color: var(--text-1); margin-bottom: 2px; }
    .pago-detail { font-size: 11px; color: var(--text-2); display: flex; gap: 10px; flex-wrap: wrap; }

    .pago-monto-col { text-align: right; flex-shrink: 0; min-width: 100px; }
    .pago-amount { font-family: var(--font-mono); font-size: 16px; font-weight: 700; color: var(--success); }
    .pago-fecha-col { font-size: 12px; color: var(--text-2); text-align: right; min-width: 90px; }

    .metodo-chip {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10px; font-weight: 600; padding: 2px 7px;
        border-radius: 6px; background: var(--bg-card-2);
        color: var(--text-2); border: 1px solid var(--border);
    }

    /* Pagination */
    .pagination-wrap { margin-top: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px; }
    .page-btn { padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-card-2); color: var(--text-2); text-decoration: none; transition: all .12s; }
    .page-btn:hover { border-color: var(--border-light); color: var(--text-1); }
    .page-btn.active { background: var(--accent-glow); border-color: rgba(79,142,247,0.3); color: var(--accent); }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-3); }
    .empty-state i { font-size: 48px; display: block; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .pagos-stats { grid-template-columns: 1fr; }
        .pago-recibo-badge { display: none; }
        .pago-fecha-col { display: none; }
    }
</style>
@endpush

@section('content')

{{-- ── STATS ────────────────────────────────────────────── --}}
<div class="pagos-stats">
    <div class="ps-card">
        <div class="ps-icon green"><i class="fas fa-coins"></i></div>
        <div>
            <div class="ps-label">Cobrado hoy</div>
            <div class="ps-value">${{ number_format($totalHoy, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="ps-card">
        <div class="ps-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div>
            <div class="ps-label">Cobrado este mes</div>
            <div class="ps-value">${{ number_format($totalMes, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="ps-card">
        <div class="ps-icon gold"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="ps-label">Pagos hoy</div>
            <div class="ps-value">{{ $totalPagos }}</div>
        </div>
    </div>
</div>

{{-- ── TABLA DE PAGOS ───────────────────────────────────── --}}
<div class="pagos-table-wrap">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list-check"></i> Historial de cobros</div>
        <span class="tag info">{{ $pagos->total() }} registros</span>
    </div>

    @if($pagos->count() > 0)
    <div>
        @foreach($pagos as $pago)
        <div class="pago-row">
            <div class="pago-recibo-badge">{{ $pago->recibo_numero }}</div>

            <div class="pago-avatar">{{ strtoupper(substr($pago->cliente->nombre, 0, 1)) }}</div>

            <div class="pago-cliente">
                <div class="pago-nombre">{{ $pago->cliente->nombre }}</div>
                <div class="pago-detail">
                    @if($pago->cuota)
                    <span><i class="fas fa-hashtag" style="font-size:9px;"></i> Cuota #{{ $pago->cuota->numero_cuota }}</span>
                    @endif
                    <span class="metodo-chip">
                        @php
                        $iconos = ['efectivo' => 'fas fa-money-bill-wave', 'transferencia' => 'fas fa-building-columns', 'nequi' => 'fas fa-mobile-screen', 'daviplata' => 'fas fa-credit-card', 'otro' => 'fas fa-ellipsis'];
                        @endphp
                        <i class="{{ $iconos[$pago->metodo_pago] ?? 'fas fa-coins' }}" style="font-size:9px;"></i>
                        {{ ucfirst($pago->metodo_pago) }}
                    </span>
                    @if($pago->es_pago_parcial)
                    <span class="tag warning" style="font-size:10px; padding: 1px 6px;">Parcial</span>
                    @endif
                    @if($pago->observaciones)
                    <span style="color: var(--text-3); font-style: italic;" title="{{ $pago->observaciones }}">
                        <i class="fas fa-comment" style="font-size:9px;"></i> Nota
                    </span>
                    @endif
                </div>
            </div>

            <div class="pago-monto-col">
                <div class="pago-amount">${{ number_format($pago->monto_pagado, 0, ',', '.') }}</div>
            </div>

            <div class="pago-fecha-col">
                <div style="font-weight: 600; color: var(--text-1);">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                <div>{{ $pago->fecha_pago->format('h:i A') }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    @if($pagos->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
        <div class="pagination-wrap" style="margin-top: 0;">
            @if($pagos->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $pagos->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
            @endif

            <span style="font-size: 13px; color: var(--text-2);">
                Página {{ $pagos->currentPage() }} de {{ $pagos->lastPage() }}
            </span>

            @if($pagos->hasMorePages())
                <a href="{{ $pagos->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-inbox" style="color: var(--text-3);"></i>
        <p style="font-size: 16px; font-weight: 600; color: var(--text-2); margin-bottom: 6px;">
            @if(request('buscar') || request('fecha')) No se encontraron pagos
            @else Aún no hay pagos registrados
            @endif
        </p>
        <p>
            @if(request('buscar') || request('fecha'))
            <a href="{{ route('cobrador.pagos.index') }}" style="color: var(--accent);">Limpiar filtros</a>
            @endif
        </p>
    </div>
    @endif
</div>

@endsection