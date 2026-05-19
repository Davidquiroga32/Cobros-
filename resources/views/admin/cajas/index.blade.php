@extends('layouts.admin')

@section('title', 'Cajas')

@section('topbar-actions')
    <a href="{{ route('admin.cajas.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Abrir caja
    </a>
@endsection

@push('styles')
<style>
    .filters-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    .caja-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(37,40,64,0.5);
        transition: background .1s;
    }
    .caja-row:hover { background: var(--bg-card-2); }
    .caja-row:last-child { border-bottom: none; }

    .caja-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .caja-icon.abierta  { background: var(--success-soft); color: var(--success); }
    .caja-icon.cerrada  { background: var(--bg-card-2);    color: var(--text-3);  }
    .caja-icon.cuadrada { background: var(--accent-glow);  color: var(--accent);  }

    .caja-info  { flex: 1; min-width: 0; }
    .caja-codigo { font-size: 13px; font-weight: 700; color: var(--text-1); }
    .caja-meta  {
        font-size: 11px; color: var(--text-2); margin-top: 2px;
        display: flex; gap: 10px; flex-wrap: wrap;
    }

    .monto-col  { text-align: right; min-width: 110px; flex-shrink: 0; }
    .monto-big  { font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--success); }
    .monto-sub  { font-size: 11px; color: var(--text-2); }

    .estado-pill {
        font-size: 10px; font-weight: 700; padding: 3px 9px;
        border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px;
        flex-shrink: 0;
    }
    .pill-abierta  { background: var(--success-soft); color: var(--success); }
    .pill-cerrada  { background: var(--bg-card-2);    color: var(--text-3);  }
    .pill-cuadrada { background: var(--accent-glow);  color: var(--accent);  }
</style>
@endpush

@section('content')

{{-- ── KPIs del día ────────────────────────────────────────────────────── --}}
<div class="grid grid-3 mb-6">
    <div class="stat-card green">
        <div class="stat-label">Cajas abiertas hoy</div>
        <div class="stat-value">{{ $totalAbierto }}</div>
        <div class="stat-meta">En campo ahora</div>
        <div class="stat-icon"><i class="fas fa-lock-open" style="color:var(--success);"></i></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Total cobrado</div>
        <div class="stat-value" style="font-size:22px;">${{ number_format($totalCobrado, 0, ',', '.') }}</div>
        <div class="stat-meta">Todas las cajas del día</div>
        <div class="stat-icon"><i class="fas fa-dollar-sign" style="color:var(--accent);"></i></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Capital en campo</div>
        <div class="stat-value" style="font-size:22px;">${{ number_format($totalInicial, 0, ',', '.') }}</div>
        <div class="stat-meta">Suma de cajas iniciales</div>
        <div class="stat-icon"><i class="fas fa-vault" style="color:var(--accent-2);"></i></div>
    </div>
</div>

{{-- ── Tabla principal ────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-cash-register"></i> Cajas</div>
        <span class="tag info">{{ $cajas->total() }} registros</span>
    </div>

    {{-- Filtros --}}
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" class="filters-bar">

            <select name="cobrador_id" class="form-control" style="width:200px;">
                <option value="">Todos los cobradores</option>
                @foreach($cobradores as $c)
                    <option value="{{ $c->id }}" {{ request('cobrador_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->cn ? ' · ' . $c->cn : '' }}
                    </option>
                @endforeach
            </select>

            <select name="estado" class="form-control" style="width:140px;">
                <option value="">Todo estado</option>
                <option value="abierta"  {{ request('estado') === 'abierta'  ? 'selected' : '' }}>Abierta</option>
                <option value="cerrada"  {{ request('estado') === 'cerrada'  ? 'selected' : '' }}>Cerrada</option>
                <option value="cuadrada" {{ request('estado') === 'cuadrada' ? 'selected' : '' }}>Cuadrada</option>
            </select>

            <input type="date" name="fecha" class="form-control" style="width:160px;"
                value="{{ request('fecha', today()->format('Y-m-d')) }}">

            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-filter"></i> Filtrar
            </button>

            @if(request()->hasAny(['cobrador_id', 'estado', 'fecha']))
                <a href="{{ route('admin.cajas.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-xmark"></i> Limpiar
                </a>
            @endif

        </form>
    </div>

    {{-- ── Listado ──────────────────────────────────────────────────────── --}}
    @if($cajas->count())
    <div>
        @foreach($cajas as $caja)
        <div class="caja-row">

            {{-- Icono estado --}}
            <div class="caja-icon {{ $caja->estado }}">
                <i class="fas fa-{{ $caja->estado === 'abierta' ? 'lock-open' : 'lock' }}"></i>
            </div>

            {{-- Info principal --}}
            <div class="caja-info">
                <div class="caja-codigo">{{ $caja->codigo }}</div>
                <div class="caja-meta">
                    <span>
                        <i class="fas fa-user" style="font-size:9px;"></i>
                        {{ $caja->cobrador->name }}
                    </span>
                    @if($caja->cobrador->cn)
                        <span style="color:var(--accent); font-family:var(--font-mono);">
                            {{ $caja->cobrador->cn }}
                        </span>
                    @endif
                    @if($caja->sector)
                        <span>
                            <i class="fas fa-map" style="font-size:9px;"></i>
                            {{ $caja->sector->nombre }}
                        </span>
                    @endif
                    <span>
                        <i class="fas fa-calendar" style="font-size:9px;"></i>
                        {{ $caja->fecha_jornada->format('d/m/Y') }}
                    </span>
                    <span>Apertura: {{ $caja->fecha_apertura->format('H:i') }}</span>
                    @if($caja->fecha_cierre)
                        <span>· Cierre: {{ $caja->fecha_cierre->format('H:i') }}</span>
                    @endif
                </div>
            </div>

            {{-- Monto inicial --}}
            <div style="text-align:center; min-width:90px; flex-shrink:0;">
                <div style="font-family:var(--font-mono); font-size:13px; font-weight:700; color:var(--text-2);">
                    ${{ number_format($caja->monto_inicial, 0, ',', '.') }}
                </div>
                <div style="font-size:10px; color:var(--text-3);">Inicial</div>
            </div>

            {{-- Total cobrado --}}
            <div class="monto-col">
                <div class="monto-big">${{ number_format($caja->monto_cobrado, 0, ',', '.') }}</div>
                <div class="monto-sub">Cobrado</div>
            </div>

            {{-- Pill estado --}}
            <span class="estado-pill pill-{{ $caja->estado }}">{{ $caja->estado }}</span>

            {{-- Acciones --}}
            <div style="display:flex; gap:6px; flex-shrink:0;">
                <a href="{{ route('admin.cajas.show', $caja) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye"></i>
                </a>
                @if($caja->estaAbierta())
                    <a href="{{ route('admin.cajas.show', $caja) }}#cerrar"
                       class="btn btn-secondary btn-sm"
                       style="color:var(--danger);"
                       title="Cerrar caja">
                        <i class="fas fa-lock"></i>
                    </a>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    @if($cajas->hasPages())
    <div class="pagination-wrap">
        @if($cajas->onFirstPage())
            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $cajas->previousPageUrl() }}" class="page-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach($cajas->getUrlRange(1, $cajas->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-btn {{ $page == $cajas->currentPage() ? 'active' : '' }}">
                {{ $page }}
            </a>
        @endforeach

        @if($cajas->hasMorePages())
            <a href="{{ $cajas->nextPageUrl() }}" class="page-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
    @endif

    @else
    {{-- Estado vacío --}}
    <div class="empty-state">
        <i class="fas fa-cash-register"></i>
        <p style="font-size:16px; font-weight:600; color:var(--text-2); margin-bottom:6px;">
            Sin cajas para los filtros seleccionados
        </p>
        <p style="font-size:13px; margin-bottom:16px;">
            Prueba cambiando la fecha o los filtros, o abre una nueva caja.
        </p>
        <a href="{{ route('admin.cajas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Abrir caja
        </a>
    </div>
    @endif

</div>

@endsection