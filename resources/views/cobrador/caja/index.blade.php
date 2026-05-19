@extends('layouts.cobrador')

@section('title', 'Mi Caja')

@push('styles')
<style>
    .caja-layout { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }
    @media (max-width: 768px) { .caja-layout { grid-template-columns: 1fr; } }

    .caja-estado {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .caja-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }

    .caja-abierta-badge {
        padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        background: var(--success-soft); color: var(--success);
    }
    .caja-cerrada-badge {
        padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        background: var(--bg-card-2); color: var(--text-3);
    }

    .caja-metric { padding: 14px 22px; border-bottom: 1px solid var(--border); }
    .caja-metric:last-child { border-bottom: none; }
    .metric-name { font-size: 12px; color: var(--text-2); margin-bottom: 4px; }
    .metric-big { font-size: 26px; font-weight: 800; font-family: var(--font-mono); color: var(--text-1); }
    .metric-big.success { color: var(--success); }

    .historial-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .historial-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

<div class="caja-layout">

    {{-- Columna izquierda: estado actual + formularios --}}
    <div>

        @if($cajaHoy)
        {{-- Caja abierta --}}
        <div class="caja-estado" style="margin-bottom: 20px;">
            <div class="caja-header">
                <div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--text-1);">Caja de hoy</div>
                    <div style="font-size: 12px; color: var(--text-2); margin-top: 2px;">{{ $cajaHoy->codigo }}</div>
                </div>
                <span class="{{ $cajaHoy->estaAbierta() ? 'caja-abierta-badge' : 'caja-cerrada-badge' }}">
                    {{ $cajaHoy->estaAbierta() ? '🟢 Abierta' : '🔴 Cerrada' }}
                </span>
            </div>

            <div class="caja-metric">
                <div class="metric-name">Monto inicial</div>
                <div class="metric-big">${{ number_format($cajaHoy->monto_inicial, 0, ',', '.') }}</div>
            </div>
            <div class="caja-metric">
                <div class="metric-name">Total cobrado hoy</div>
                <div class="metric-big success">${{ number_format($cajaHoy->monto_cobrado, 0, ',', '.') }}</div>
            </div>
            <div class="caja-metric">
                <div class="metric-name">Efectivo esperado en mano</div>
                <div class="metric-big">${{ number_format($cajaHoy->monto_inicial + $cajaHoy->monto_cobrado, 0, ',', '.') }}</div>
            </div>
            @if($cajaHoy->sector)
            <div class="caja-metric">
                <div class="metric-name">Sector</div>
                <div style="font-weight: 600; color: var(--text-1);">{{ $cajaHoy->sector->nombre }}</div>
            </div>
            @endif
        </div>

        {{-- Formulario cierre --}}
        @if($cajaHoy->estaAbierta())
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-cash-register"></i> Cerrar caja del día</div>
            </div>
            <div class="card-body">
                <form action="{{ route('cobrador.caja.cerrar') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Gastos del día (opcional)</label>
                        <input type="number" name="monto_gastos" class="form-control"
                            step="100" min="0" value="0" placeholder="0">
                        <span class="form-hint">Combustible, viáticos, etc.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas de cierre</label>
                        <textarea name="notas_cierre" class="form-control" rows="3"
                            placeholder="Incidencias del día, observaciones..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('¿Cerrar la caja del día?')">
                        <i class="fas fa-lock"></i> Cerrar caja
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 30px; color: var(--text-2);">
                <i class="fas fa-lock" style="font-size: 28px; margin-bottom: 12px; display: block;"></i>
                Caja cerrada el día de hoy.
            </div>
        </div>
        @endif

        @else
        {{-- Sin caja: formulario de apertura --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-cash-register"></i> Abrir caja del día</div>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-2); margin-bottom: 16px;">
                    Antes de iniciar tu ruta, registra el efectivo con el que sales.
                </p>
                <form action="{{ route('cobrador.caja.abrir') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Monto inicial *</label>
                        <input type="number" name="monto_inicial" class="form-control @error('monto_inicial') is-invalid @enderror"
                            step="1000" min="0" required placeholder="0">
                        @error('monto_inicial')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas (opcional)</label>
                        <textarea name="notas_apertura" class="form-control" rows="2"
                            placeholder="Observaciones de inicio de jornada..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-unlock"></i> Abrir caja
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- Columna derecha: historial --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Historial</div>
        </div>
        @forelse($historial as $caja)
        <div class="historial-item">
            <div style="flex:1; min-width:0;">
                <div style="font-weight: 600; color: var(--text-1);">{{ $caja->fecha_jornada->format('d/m/Y') }}</div>
                <div style="font-size: 11px; color: var(--text-2);">{{ $caja->codigo }}</div>
                @if($caja->sector) <div style="font-size: 11px; color: var(--text-3);">{{ $caja->sector->nombre }}</div> @endif
            </div>
            <div style="text-align: right; flex-shrink:0;">
                <div style="font-family: var(--font-mono); font-size: 14px; font-weight: 700; color: var(--success);">
                    ${{ number_format($caja->monto_cobrado, 0, ',', '.') }}
                </div>
                <span style="font-size: 10px; color: {{ $caja->estado === 'abierta' ? 'var(--success)' : 'var(--text-3)' }};">
                    {{ $caja->estado }}
                </span>
            </div>
        </div>
        @empty
        <div style="padding: 24px; text-align: center; color: var(--text-3); font-size: 13px;">
            Sin historial de cajas.
        </div>
        @endforelse
    </div>

</div>

@endsection