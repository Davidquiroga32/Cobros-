@extends('layouts.admin')

@section('title', 'Caja — ' . $caja->codigo)

@section('topbar-actions')
    <a href="{{ route('admin.cajas.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    @if($caja->estaAbierta())
    <button onclick="document.getElementById('modalCerrar').classList.add('open')" class="btn btn-primary btn-sm">
        <i class="fas fa-lock"></i> Cerrar caja
    </button>
    @endif
@endsection

@push('styles')
<style>
    .show-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 1024px) { .show-layout { grid-template-columns: 1fr; } }

    .hero-caja {
        padding: 24px 26px;
        display: flex;
        align-items: flex-start;
        gap: 20px; flex-wrap: wrap;
        border-bottom: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }
    .hero-caja::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse at top right, var(--accent-glow), transparent 60%);
        pointer-events: none;
    }

    .hero-icon {
        width: 56px; height: 56px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .icon-abierta  { background: var(--success-soft); color: var(--success); }
    .icon-cerrada  { background: var(--bg-card-2); color: var(--text-3); }
    .icon-cuadrada { background: var(--accent-glow); color: var(--accent); }

    .hero-info { flex: 1; }
    .hero-codigo { font-size: 20px; font-weight: 800; color: var(--text-1); }
    .hero-meta   { font-size: 12px; color: var(--text-2); margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap; }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        background: var(--border);
        border-bottom: 1px solid var(--border);
    }
    .metric-box {
        background: var(--bg-card);
        padding: 18px 20px;
        text-align: center;
    }
    .metric-box-val { font-family: var(--font-mono); font-size: 22px; font-weight: 800; color: var(--text-1); }
    .metric-box-val.success { color: var(--success); }
    .metric-box-val.danger  { color: var(--danger); }
    .metric-box-val.accent  { color: var(--accent); }
    .metric-box-lbl { font-size: 11px; color: var(--text-3); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.6px; }

    .pago-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 20px; border-bottom: 1px solid rgba(37,40,64,0.4);
        transition: background .1s;
    }
    .pago-row:hover { background: var(--bg-card-2); }
    .pago-row:last-child { border-bottom: none; }

    .pago-avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: var(--success-soft); color: var(--success);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; flex-shrink: 0;
    }

    /* Modal cierre */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.6);
        display: flex; align-items: center; justify-content: center;
        z-index: 999; padding: 20px;
        opacity: 0; pointer-events: none;
        transition: opacity .2s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }

    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 28px;
        width: 100%; max-width: 460px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .modal-title { font-size: 17px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; }
    .modal-sub   { font-size: 13px; color: var(--text-2); margin-bottom: 20px; }

    .diferencia-preview {
        padding: 14px 16px;
        border-radius: var(--radius);
        margin-top: 12px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .diferencia-ok      { background: var(--success-soft); color: var(--success); border: 1px solid rgba(34,197,94,0.2); }
    .diferencia-faltante{ background: var(--danger-soft);  color: var(--danger);  border: 1px solid rgba(239,68,68,0.2); }

    /* Resumen lateral */
    .resumen-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 18px; border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .resumen-row:last-child { border-bottom: none; }
    .resumen-key { color: var(--text-2); }
    .resumen-val { font-weight: 700; color: var(--text-1); font-family: var(--font-mono); }

    @media (max-width: 640px) {
        .metrics-grid { grid-template-columns: 1fr; }
        .hero-caja { flex-wrap: wrap; }
        .card-body { padding: 14px; }
        .card-header { padding: 12px 14px; }
    }
</style>
@endpush

@section('content')

<div class="show-layout">

    {{-- Columna principal --}}
    <div>

        {{-- Hero --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="hero-caja">
                <div class="hero-icon icon-{{ $caja->estado }}">
                    <i class="fas fa-{{ $caja->estado === 'abierta' ? 'lock-open' : 'lock' }}"></i>
                </div>
                <div class="hero-info">
                    <div class="hero-codigo">{{ $caja->codigo }}</div>
                    <div class="hero-meta">
                        <span><i class="fas fa-user" style="font-size:10px;"></i> {{ $caja->cobrador->name }}
                            @if($caja->cobrador->cn) · <strong style="color:var(--accent);">{{ $caja->cobrador->cn }}</strong> @endif
                        </span>
                        @if($caja->sector)
                        <span><i class="fas fa-map" style="font-size:10px;"></i> {{ $caja->sector->nombre }}</span>
                        @endif
                        <span><i class="fas fa-calendar" style="font-size:10px;"></i> {{ $caja->fecha_jornada->isoFormat('dddd, D [de] MMMM YYYY') }}</span>
                    </div>
                </div>
                <span class="tag {{ $caja->estado === 'abierta' ? 'success' : '' }}"
                      style="{{ $caja->estado !== 'abierta' ? 'color:var(--text-3);' : '' }}">
                    <i class="fas fa-circle" style="font-size:7px;"></i>
                    {{ ucfirst($caja->estado) }}
                </span>
            </div>

            {{-- Métricas grandes --}}
            <div class="metrics-grid">
                <div class="metric-box">
                    <div class="metric-box-val">${{ number_format($caja->monto_inicial, 0, ',', '.') }}</div>
                    <div class="metric-box-lbl">Monto inicial</div>
                </div>
                <div class="metric-box">
                    <div class="metric-box-val success">${{ number_format($caja->monto_cobrado, 0, ',', '.') }}</div>
                    <div class="metric-box-lbl">Total cobrado</div>
                </div>
                <div class="metric-box">
                    @php $esperado = $caja->monto_inicial + $caja->monto_cobrado - $caja->monto_gastos; @endphp
                    <div class="metric-box-val accent">${{ number_format($esperado, 0, ',', '.') }}</div>
                    <div class="metric-box-lbl">Efectivo esperado</div>
                </div>
            </div>

            @if($caja->monto_final > 0)
            <div style="padding:14px 20px; background:var(--bg-card-2); border-bottom:1px solid var(--border);">
                @php $diferencia = $caja->monto_final - $esperado; @endphp
                <div class="diferencia-preview {{ $diferencia >= 0 ? 'diferencia-ok' : 'diferencia-faltante' }}">
                    <span>{{ $diferencia >= 0 ? '✅ Cuadra correctamente' : '⚠️ Faltante detectado' }}</span>
                    <span>${{ number_format(abs($diferencia), 0, ',', '.') }}</span>
                </div>
            </div>
            @endif

            @if($caja->notas_apertura || $caja->notas_cierre)
            <div style="padding:16px 20px; font-size:13px; color:var(--text-2);">
                @if($caja->notas_apertura)
                    <div style="margin-bottom:6px;">
                        <strong style="color:var(--text-1);">Notas apertura:</strong> {{ $caja->notas_apertura }}
                    </div>
                @endif
                @if($caja->notas_cierre)
                    <div>
                        <strong style="color:var(--text-1);">Notas cierre:</strong> {{ $caja->notas_cierre }}
                    </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Pagos de la jornada --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list-check"></i> Pagos de la jornada</div>
                <span class="tag info">{{ $pagos->count() }} pagos</span>
            </div>

            @forelse($pagos as $pago)
            <div class="pago-row">
                <div class="pago-avatar">{{ strtoupper(substr($pago->cliente->nombre, 0, 1)) }}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:var(--text-1);">{{ $pago->cliente->nombre }}</div>
                    <div style="font-size:11px; color:var(--text-2); display:flex; gap:8px; flex-wrap:wrap; margin-top:2px;">
                        <span>{{ $pago->recibo_numero }}</span>
                        @if($pago->cuota) <span>Cuota #{{ $pago->cuota->numero_cuota }}</span> @endif
                        <span>{{ ucfirst($pago->metodo_pago) }}</span>
                        <span>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('H:i') }}</span>
                    </div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-family:var(--font-mono); font-size:14px; font-weight:700; color:var(--success);">
                        ${{ number_format($pago->monto_pagado, 0, ',', '.') }}
                    </div>
                    @if($pago->es_pago_parcial)
                    <span class="tag warning" style="font-size:10px; padding:1px 6px;">Parcial</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:40px; text-align:center; color:var(--text-3);">
                <i class="fas fa-receipt" style="font-size:28px; display:block; margin-bottom:12px;"></i>
                Sin pagos registrados en esta jornada.
            </div>
            @endforelse
        </div>

    </div>

    {{-- Columna lateral: resumen + acciones --}}
    <div>

        {{-- Resumen auditoria --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-clipboard-check"></i> Resumen</div>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Apertura</span>
                <span class="resumen-val" style="font-family:var(--font-main); font-size:12px;">
                    {{ $caja->fecha_apertura->format('d/m/Y H:i') }}
                </span>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Cierre</span>
                <span class="resumen-val" style="font-family:var(--font-main); font-size:12px; color:{{ $caja->fecha_cierre ? 'var(--text-1)' : 'var(--text-3)' }}">
                    {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'Pendiente' }}
                </span>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Abierta por</span>
                <span style="font-size:13px; font-weight:600; color:var(--text-1);">{{ $caja->abiertaPor->name }}</span>
            </div>
            @if($caja->cerradaPor)
            <div class="resumen-row">
                <span class="resumen-key">Cerrada por</span>
                <span style="font-size:13px; font-weight:600; color:var(--text-1);">{{ $caja->cerradaPor->name }}</span>
            </div>
            @endif
            <div class="resumen-row">
                <span class="resumen-key">Total pagos</span>
                <span class="resumen-val">{{ $pagos->count() }}</span>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Monto inicial</span>
                <span class="resumen-val">${{ number_format($caja->monto_inicial, 0, ',', '.') }}</span>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Cobrado</span>
                <span class="resumen-val success">${{ number_format($caja->monto_cobrado, 0, ',', '.') }}</span>
            </div>
            <div class="resumen-row">
                <span class="resumen-key">Gastos</span>
                <span class="resumen-val" style="color:var(--warning);">${{ number_format($caja->monto_gastos, 0, ',', '.') }}</span>
            </div>
            @if($caja->monto_final > 0)
            <div class="resumen-row">
                <span class="resumen-key">Declarado al cierre</span>
                <span class="resumen-val accent">${{ number_format($caja->monto_final, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        {{-- Botón cierre (si está abierta) --}}
        @if($caja->estaAbierta())
        <div class="card" id="cerrar">
            <div class="card-header">
                <div class="card-title" style="color:var(--danger);"><i class="fas fa-lock"></i> Cerrar caja</div>
            </div>
            <div class="card-body">
                <p style="font-size:13px; color:var(--text-2); margin-bottom:16px;">
                    Al cerrar, se calculará la diferencia entre el efectivo declarado y el cobrado en el sistema.
                </p>
                <form method="POST" action="{{ route('admin.cajas.cerrar', $caja) }}" id="formCerrar">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Gastos del día</label>
                        <input type="number" name="monto_gastos" id="gastos"
                            class="form-control" step="1000" min="0" value="0"
                            onchange="calcDiferencia()">
                        <span class="form-hint">Combustible, viáticos, etc.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas de cierre</label>
                        <textarea name="notas_cierre" class="form-control" rows="3"
                            placeholder="Incidencias, observaciones..."></textarea>
                    </div>

                    {{-- Preview diferencia --}}
                    <div class="diferencia-preview diferencia-ok" id="prevDif" style="margin-bottom:16px;">
                        <span id="dif-label">Cuadre estimado</span>
                        <span id="dif-val" style="font-family:var(--font-mono);">$0</span>
                    </div>

                    <button type="submit" class="btn btn-danger w-full"
                        onclick="return confirm('¿Confirmar cierre de caja?')">
                        <i class="fas fa-lock"></i> Confirmar cierre
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection

@push('scripts')
<script>
const montoInicial  = {{ $caja->monto_inicial }};
const montoCobrado  = {{ $caja->monto_cobrado }};

function calcDiferencia() {
    const gastos   = parseFloat(document.getElementById('gastos')?.value || 0);
    const esperado = montoInicial + montoCobrado - gastos;
    const dif      = 0 - (esperado - esperado); // sin monto declarado, diferencia = 0 estimado
    const box      = document.getElementById('prevDif');
    const lbl      = document.getElementById('dif-label');
    const val      = document.getElementById('dif-val');

    // Mostrar monto esperado en caja
    lbl.textContent = 'Esperado en caja';
    val.textContent = '$' + esperado.toLocaleString('es-CO');
    box.className   = 'diferencia-preview diferencia-ok';
}

// Inicializar
document.addEventListener('DOMContentLoaded', calcDiferencia);
</script>
@endpush