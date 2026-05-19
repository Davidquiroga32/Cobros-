@extends('layouts.cobrador')

@section('title', 'Mis Creditos')

@section('topbar-actions')
    <a href="{{ route('cobrador.creditos.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo credito
    </a>
@endsection

@push('styles')
<style>
    .credito-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        transition: background .1s;
    }
    .credito-row:hover { background: var(--bg-card-2); }

    .credito-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
        background: var(--accent-glow); color: var(--accent);
    }
    .credito-icon.mora { background: var(--danger-soft); color: var(--danger); }
    .credito-icon.pagado { background: var(--success-soft); color: var(--success); }

    .progress-mini { height: 6px; background: var(--bg-card-2); border-radius: 10px; overflow: hidden; margin-top: 4px; }
    .progress-mini-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--accent), var(--accent-2)); }
    .progress-mini-fill.danger { background: linear-gradient(90deg, var(--danger), #f87171); }
    .progress-mini-fill.success { background: linear-gradient(90deg, var(--success), #4ade80); }
</style>
@endpush

@section('content')

<div class="grid grid-4 mb-6">
    <div class="stat-card blue">
        <div class="stat-label">Creditos activos</div>
        <div class="stat-value font-mono">{{ $totalActivos }}</div>
        <div class="stat-icon"><i class="fas fa-file-invoice-dollar" style="color: var(--accent);"></i></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">En mora</div>
        <div class="stat-value font-mono" style="color: var(--danger);">{{ $enMora }}</div>
        <div class="stat-icon"><i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i></div>
    </div>
    <div class="stat-card purple" style="--card-glow: radial-gradient(ellipse at top right, rgba(124,92,191,.08), transparent 60%);">
        <div class="stat-label">Cartera total</div>
        <div class="stat-value money font-mono">{{ number_format($carteraTotal, 0, ',', '.') }}</div>
        <div class="stat-icon"><i class="fas fa-landmark" style="color: var(--accent-2);"></i></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Cobrado hoy</div>
        <div class="stat-value money font-mono">{{ number_format($cobradoHoy, 0, ',', '.') }}</div>
        <div class="stat-icon"><i class="fas fa-coins" style="color: var(--success);"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Mis creditos</div>
        <span class="tag info">{{ $creditos->total() }} registros</span>
    </div>

    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Buscar por codigo o cliente..."
                class="form-control" style="max-width: 280px;">
            <select name="estado" class="form-control" style="width: auto;">
                <option value="">Todos los estados</option>
                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="al_dia" {{ request('estado') === 'al_dia' ? 'selected' : '' }}>Al dia</option>
                <option value="mora" {{ request('estado') === 'mora' ? 'selected' : '' }}>En mora</option>
                <option value="pagado" {{ request('estado') === 'pagado' ? 'selected' : '' }}>Pagado</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Filtrar
            </button>
            @if(request('buscar') || request('estado'))
            <a href="{{ route('cobrador.creditos.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
            @endif
        </form>
    </div>

    <div>
        @forelse($creditos as $credito)
        <a href="{{ route('cobrador.creditos.show', $credito) }}" style="text-decoration: none; color: inherit;">
        <div class="credito-row">
            <div class="credito-icon {{ $credito->estado === 'mora' ? 'mora' : '' }} {{ $credito->estado === 'pagado' ? 'pagado' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>

            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: var(--text-1); font-size: 14px; display: flex; align-items: center; gap: 6px;">
                    {{ $credito->cliente->nombre }}
                    <span class="tag {{ $credito->estado === 'mora' ? 'danger' : ($credito->estado === 'pagado' ? 'success' : 'info') }}" style="font-size: 10px;">
                        {{ match($credito->estado) {
                            'activo' => 'Activo',
                            'al_dia' => 'Al dia',
                            'mora' => 'En mora',
                            'pagado' => 'Pagado',
                            default => $credito->estado,
                        } }}
                    </span>
                </div>
                <div style="font-size: 11px; color: var(--text-2);">
                    <strong>{{ $credito->codigo }}</strong> · {{ $credito->cuotas_count }} cuotas · {{ $credito->frecuencia }}
                </div>
                <div class="progress-mini">
                    <div class="progress-mini-fill {{ $credito->estado === 'mora' ? 'danger' : ($credito->estado === 'pagado' ? 'success' : '') }}"
                        style="width: {{ min(100, $credito->porcentajePagado()) }}%"></div>
                </div>
            </div>

            <div style="text-align: right; flex-shrink: 0;">
                <div style="font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--text-1);">
                    ${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--text-2);">
                    {{ $credito->porcentajePagado() }}% pagado
                </div>
            </div>

            <div style="flex-shrink: 0;">
                <i class="fas fa-chevron-right" style="color: var(--text-3); font-size: 13px;"></i>
            </div>
        </div>
        </a>
        @empty
        <div style="padding: 40px; text-align: center; color: var(--text-3);">
            <i class="fas fa-file-invoice-dollar" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
            No tienes creditos registrados.
            <br>
            <a href="{{ route('cobrador.creditos.create') }}" style="color: var(--accent); margin-top: 8px; display: inline-block;">
                Crear el primero ->
            </a>
        </div>
        @endforelse
    </div>

    @if($creditos->hasPages())
    <div class="pagination-wrap">
        {{ $creditos->links() }}
    </div>
    @endif
</div>

@endsection
