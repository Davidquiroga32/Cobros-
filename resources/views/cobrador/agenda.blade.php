@extends('layouts.cobrador')

@section('title', 'Agenda de Cobro')

@section('topbar-actions')
    <form method="GET" action="{{ route('cobrador.agenda') }}" class="flex items-center gap-2">
        <input type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}"
            class="form-control" style="width:auto; padding: 6px 12px; font-size:13px;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>
    <a href="{{ route('cobrador.agenda', ['fecha' => today()->format('Y-m-d')]) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-calendar-day"></i> Hoy
    </a>
@endsection

@push('styles')
<style>
    .agenda-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }

    .fecha-display {
        display: flex; align-items: center; gap: 16px;
    }

    .fecha-icon {
        width: 52px; height: 52px; border-radius: 14px;
        background: var(--accent-glow);
        border: 1px solid rgba(79,142,247,0.3);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        font-family: var(--font-mono);
    }

    .fecha-icon .day { font-size: 22px; font-weight: 700; color: var(--accent); line-height: 1; }
    .fecha-icon .month { font-size: 10px; color: var(--text-2); font-weight: 600; text-transform: uppercase; }

    .fecha-info h2 { font-size: 20px; font-weight: 700; color: var(--text-1); }
    .fecha-info p { font-size: 13px; color: var(--text-2); }

    /* Resumen stats */
    .resumen-strip {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 12px; margin-bottom: 24px;
    }

    .resumen-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        display: flex; align-items: center; gap: 14px;
    }

    .resumen-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }

    .resumen-icon.blue  { background: var(--accent-glow); color: var(--accent); }
    .resumen-icon.green { background: var(--success-soft); color: var(--success); }
    .resumen-icon.red   { background: var(--danger-soft); color: var(--danger); }

    .resumen-label { font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.6px; }
    .resumen-value { font-size: 20px; font-weight: 700; font-family: var(--font-mono); color: var(--text-1); }

    /* Cuota card */
    .cuota-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        display: flex; align-items: center; gap: 16px;
        margin-bottom: 10px;
        transition: all .15s;
        cursor: default;
    }

    .cuota-card:hover { border-color: var(--border-light); transform: translateY(-1px); }

    .cuota-card.pagada {
        opacity: 0.6;
        border-color: rgba(34,197,94,0.2);
        background: rgba(34,197,94,0.03);
    }

    .cuota-card.vencida { border-color: rgba(239,68,68,0.2); }
    .cuota-card.parcial { border-color: rgba(245,158,11,0.2); }

    .cuota-avatar {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; font-weight: 700; flex-shrink: 0;
        background: var(--accent-glow); color: var(--accent);
    }

    .cuota-avatar.pagada { background: var(--success-soft); color: var(--success); }
    .cuota-avatar.vencida { background: var(--danger-soft); color: var(--danger); }
    .cuota-avatar.parcial { background: var(--warning-soft); color: var(--warning); }

    .cuota-info { flex: 1; min-width: 0; }
    .cuota-nombre { font-size: 14px; font-weight: 600; color: var(--text-1); margin-bottom: 2px; }
    .cuota-meta { font-size: 12px; color: var(--text-2); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .cuota-meta span { display: flex; align-items: center; gap: 4px; }

    .cuota-monto { text-align: right; flex-shrink: 0; }
    .cuota-monto .amount { font-family: var(--font-mono); font-size: 18px; font-weight: 700; color: var(--text-1); }
    .cuota-monto .sub { font-size: 11px; color: var(--text-2); }

    .cuota-action { flex-shrink: 0; }

    /* Section titles */
    .section-title {
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; font-weight: 700; color: var(--text-2);
        text-transform: uppercase; letter-spacing: 0.8px;
        margin-bottom: 14px; margin-top: 4px;
    }

    .section-title::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
    }

    /* Atrasadas section */
    .atrasadas-toggle {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px;
        background: var(--danger-soft);
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: var(--radius);
        cursor: pointer;
        margin-bottom: 12px;
        font-size: 13px; font-weight: 600; color: var(--danger);
        transition: all .15s;
    }

    .atrasadas-toggle:hover { background: rgba(239,68,68,0.12); }

    .cuotas-list { }

    .empty-state {
        display: flex; flex-direction: column; align-items: center;
        padding: 40px 20px; text-align: center;
        color: var(--text-3);
    }

    .empty-state i { font-size: 36px; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: 14px; }

    @media (max-width: 768px) {
        .resumen-strip { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ── HEADER DE FECHA ─────────────────────────────────── --}}
<div class="agenda-header">
    <div class="fecha-display">
        <div class="fecha-icon">
            <div class="day">{{ $fecha->format('d') }}</div>
            <div class="month">{{ $fecha->isoFormat('MMM') }}</div>
        </div>
        <div class="fecha-info">
            <h2>Agenda — {{ $fecha->isoFormat('dddd, D [de] MMMM') }}</h2>
            <p>
                @if($fecha->isToday()) <span style="color: var(--success);">• Hoy</span>
                @elseif($fecha->isTomorrow()) Mañana
                @elseif($fecha->isYesterday()) Ayer
                @else {{ $fecha->diffForHumans() }}
                @endif
                · {{ $cuotas->count() }} cuotas programadas
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('cobrador.agenda', ['fecha' => $fecha->copy()->subDay()->format('Y-m-d')]) }}"
           class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
        <a href="{{ route('cobrador.agenda', ['fecha' => $fecha->copy()->addDay()->format('Y-m-d')]) }}"
           class="btn btn-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
    </div>
</div>

{{-- ── RESUMEN ──────────────────────────────────────────── --}}
<div class="resumen-strip">
    <div class="resumen-card">
        <div class="resumen-icon blue"><i class="fas fa-money-bill-wave"></i></div>
        <div>
            <div class="resumen-label">Esperado hoy</div>
            <div class="resumen-value">${{ number_format($totalEsperado, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="resumen-card">
        <div class="resumen-icon green"><i class="fas fa-circle-check"></i></div>
        <div>
            <div class="resumen-label">Cobrado hoy</div>
            <div class="resumen-value" style="color: var(--success);">${{ number_format($totalCobrado, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="resumen-card">
        <div class="resumen-icon red"><i class="fas fa-triangle-exclamation"></i></div>
        <div>
            <div class="resumen-label">En atraso</div>
            <div class="resumen-value" style="color: var(--danger);">${{ number_format($totalAtrasado, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- ── CUOTAS ATRASADAS ─────────────────────────────────── --}}
@if($cuotasAtrasadas->count() > 0)
<div x-data="{ open: false }">
    <div class="atrasadas-toggle" @click="open = !open">
        <i class="fas fa-clock-rotate-left"></i>
        <span>{{ $cuotasAtrasadas->count() }} cuotas atrasadas — ${{ number_format($totalAtrasado, 0, ',', '.') }}</span>
        <i class="fas fa-chevron-down ml-auto" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </div>
    <div x-show="open" x-transition style="display:none;">
        @foreach($cuotasAtrasadas as $cuota)
        <div class="cuota-card vencida">
            <div class="cuota-avatar vencida">{{ strtoupper(substr($cuota->cliente->nombre, 0, 1)) }}</div>
            <div class="cuota-info">
                <div class="cuota-nombre">{{ $cuota->cliente->nombre }}</div>
                <div class="cuota-meta">
                    <span><i class="fas fa-hashtag" style="font-size:10px;"></i> Cuota {{ $cuota->numero_cuota }}/{{ $cuota->credito->num_cuotas }}</span>
                    <span><i class="fas fa-calendar-xmark" style="font-size:10px; color: var(--danger);"></i>
                        {{ $cuota->fecha_vencimiento->diffInDays(today()) }} días de atraso
                    </span>
                    <span>{{ $cuota->fecha_vencimiento->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="cuota-monto">
                <div class="amount" style="color: var(--danger);">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</div>
                <div class="sub">saldo pendiente</div>
            </div>
            <div class="cuota-action">
                <a href="{{ route('cobrador.pagos.create', $cuota) }}" class="btn btn-sm" style="background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(239,68,68,0.3);">
                    <i class="fas fa-hand-holding-dollar"></i> Cobrar
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── PENDIENTES DEL DÍA ───────────────────────────────── --}}
@if($pendientes->count() > 0)
<div class="section-title">
    <i class="fas fa-circle-half-stroke" style="color: var(--warning);"></i>
    Pendientes ({{ $pendientes->count() }})
</div>

<div class="cuotas-list">
    @foreach($pendientes as $cuota)
    @php $estado = $cuota->estado; @endphp
    <div class="cuota-card {{ $estado }}">
        <div class="cuota-avatar {{ $estado }}">{{ strtoupper(substr($cuota->cliente->nombre, 0, 1)) }}</div>
        <div class="cuota-info">
            <div class="cuota-nombre">{{ $cuota->cliente->nombre }}</div>
            <div class="cuota-meta">
                <span><i class="fas fa-hashtag" style="font-size:10px;"></i> Cuota {{ $cuota->numero_cuota }}</span>
                <span><i class="fas fa-phone" style="font-size:10px;"></i> {{ $cuota->cliente->telefono }}</span>
                @if($cuota->cliente->barrio)
                <span><i class="fas fa-location-dot" style="font-size:10px;"></i> {{ $cuota->cliente->barrio }}</span>
                @endif
                @if($estado === 'parcial')
                <span class="tag warning" style="font-size:10px;">Pago parcial — ${{ number_format($cuota->valor_pagado, 0, ',', '.') }} pagado</span>
                @endif
            </div>
        </div>
        <div class="cuota-monto">
            <div class="amount">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</div>
            <div class="sub">
                @if($estado === 'parcial') saldo restante
                @else cuota completa
                @endif
            </div>
        </div>
        <div class="cuota-action">
            <a href="{{ route('cobrador.pagos.create', $cuota) }}" class="btn btn-success btn-sm">
                <i class="fas fa-hand-holding-dollar"></i> Cobrar
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── COBRADOS ─────────────────────────────────────────── --}}
@if($cobrados->count() > 0)
<div class="section-title" style="margin-top: 24px;">
    <i class="fas fa-circle-check" style="color: var(--success);"></i>
    Cobrados hoy ({{ $cobrados->count() }})
</div>

<div class="cuotas-list">
    @foreach($cobrados as $cuota)
    <div class="cuota-card pagada">
        <div class="cuota-avatar pagada">{{ strtoupper(substr($cuota->cliente->nombre, 0, 1)) }}</div>
        <div class="cuota-info">
            <div class="cuota-nombre">{{ $cuota->cliente->nombre }}</div>
            <div class="cuota-meta">
                <span><i class="fas fa-hashtag" style="font-size:10px;"></i> Cuota {{ $cuota->numero_cuota }}</span>
                <span><i class="fas fa-circle-check" style="font-size:10px; color: var(--success);"></i> Pagado</span>
            </div>
        </div>
        <div class="cuota-monto">
            <div class="amount" style="color: var(--success);">${{ number_format($cuota->valor_cuota, 0, ',', '.') }}</div>
            <div class="sub">cobrado</div>
        </div>
        <div class="cuota-action">
            <span class="tag success"><i class="fas fa-check"></i> Listo</span>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── EMPTY STATE ──────────────────────────────────────── --}}
@if($cuotas->count() === 0 && $cuotasAtrasadas->count() === 0)
<div class="card">
    <div class="empty-state">
        <i class="fas fa-calendar-xmark" style="color: var(--text-3);"></i>
        <p style="font-size: 16px; font-weight: 600; color: var(--text-2); margin-bottom: 6px;">Sin cuotas para este día</p>
        <p>No hay cobros programados para el {{ $fecha->isoFormat('D [de] MMMM') }}.</p>
    </div>
</div>
@endif

@endsection