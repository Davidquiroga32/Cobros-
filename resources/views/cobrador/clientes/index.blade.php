@extends('layouts.cobrador')

@section('title', 'Mis Clientes')

@section('topbar-actions')
    <form method="GET" action="{{ route('cobrador.clientes.index') }}" class="flex items-center gap-2">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Buscar cliente..." class="form-control"
            style="width: 200px; padding: 6px 12px; font-size:13px;">
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
        @if(request('buscar') || request('filtro'))
            <a href="{{ route('cobrador.clientes.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-xmark"></i>
            </a>
        @endif
    </form>
    <a href="{{ route('cobrador.clientes.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus"></i> Nuevo cliente
    </a>
@endsection

@push('styles')
<style>
    .client-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
    .cs-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
    .cs-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .cs-icon.blue { background: var(--accent-glow); color: var(--accent); }
    .cs-icon.red { background: var(--danger-soft); color: var(--danger); }
    .cs-icon.gold { background: rgba(245,158,11,0.12); color: var(--warning); }
    .cs-label { font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.6px; }
    .cs-value { font-size: 22px; font-weight: 700; font-family: var(--font-mono); color: var(--text-1); }

    .filtros { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filtro-btn { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-card-2); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; }
    .filtro-btn:hover { border-color: var(--border-light); color: var(--text-1); }
    .filtro-btn.active { background: var(--accent-glow); border-color: rgba(79,142,247,0.3); color: var(--accent); }
    .filtro-btn.mora.active { background: var(--danger-soft); border-color: rgba(239,68,68,0.3); color: var(--danger); }

    .clientes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; }

    .cliente-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; transition: all .18s; text-decoration: none; display: block; position: relative; overflow: hidden; }
    .cliente-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--accent); opacity: 0; transition: opacity .15s; }
    .cliente-card:hover { border-color: var(--border-light); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
    .cliente-card:hover::before { opacity: 1; }
    .cliente-card.mora-card::before { opacity: 1; background: var(--danger); }
    .cliente-card.mora-card { border-color: rgba(239,68,68,0.2); }

    .card-header-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .cliente-avatar-lg { width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: white; }
    .cliente-name { font-size: 15px; font-weight: 700; color: var(--text-1); margin-bottom: 2px; }
    .cliente-cedula { font-size: 12px; color: var(--text-2); }
    .client-divider { height: 1px; background: var(--border); margin-bottom: 14px; }
    .client-details { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px; }
    .detail-label { font-size: 10px; font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.5px; }
    .detail-value { font-size: 13px; color: var(--text-1); font-weight: 500; margin-top: 1px; }
    .detail-value.money { font-family: var(--font-mono); color: var(--accent); }
    .detail-value.danger { font-family: var(--font-mono); color: var(--danger); }
    .card-footer-row { display: flex; align-items: center; justify-content: space-between; }
    .progress-mini { height: 4px; background: var(--bg-card-2); border-radius: 4px; overflow: hidden; flex: 1; margin: 0 10px; }
    .progress-mini-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--accent-2)); }

    .pagination-wrap { margin-top: 24px; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .page-btn { padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-card-2); color: var(--text-2); text-decoration: none; transition: all .12s; cursor: pointer; }
    .page-btn:hover { border-color: var(--border-light); color: var(--text-1); }
    .page-btn.active { background: var(--accent-glow); border-color: rgba(79,142,247,0.3); color: var(--accent); }
    .page-btn.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-3); }
    .empty-state i { font-size: 48px; margin-bottom: 16px; display: block; }

    @media (max-width: 768px) {
        .client-stats { grid-template-columns: 1fr; }
        .clientes-grid { grid-template-columns: 1fr; }
    }

    .avatar-blue   { background: linear-gradient(135deg, #4f8ef7, #7c5cbf); }
    .avatar-green  { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .avatar-orange { background: linear-gradient(135deg, #f59e0b, #ef4444); }
    .avatar-pink   { background: linear-gradient(135deg, #ec4899, #8b5cf6); }
    .avatar-teal   { background: linear-gradient(135deg, #14b8a6, #3b82f6); }
    .avatar-red    { background: linear-gradient(135deg, #ef4444, #b91c1c); }
</style>
@endpush

@section('content')

<div class="client-stats">
    <div class="cs-card">
        <div class="cs-icon blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="cs-label">Total clientes</div>
            <div class="cs-value">{{ $totalClientes }}</div>
        </div>
    </div>
    <div class="cs-card">
        <div class="cs-icon red"><i class="fas fa-circle-exclamation"></i></div>
        <div>
            <div class="cs-label">En mora</div>
            <div class="cs-value" style="color: var(--danger);">{{ $clientesEnMora }}</div>
        </div>
    </div>
    <div class="cs-card">
        <div class="cs-icon gold"><i class="fas fa-coins"></i></div>
        <div>
            <div class="cs-label">Cartera total</div>
            <div class="cs-value" style="font-size:16px;">${{ number_format($saldoTotal, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="filtros">
    <span style="font-size: 12px; color: var(--text-3); font-weight: 600;">FILTRAR:</span>
    <a href="{{ route('cobrador.clientes.index', array_merge(request()->except('filtro'), [])) }}"
       class="filtro-btn {{ !request('filtro') ? 'active' : '' }}">
        Todos ({{ $totalClientes }})
    </a>
    <a href="{{ route('cobrador.clientes.index', array_merge(request()->all(), ['filtro' => 'mora'])) }}"
       class="filtro-btn mora {{ request('filtro') === 'mora' ? 'active' : '' }}">
        <i class="fas fa-triangle-exclamation" style="font-size: 10px;"></i> En mora ({{ $clientesEnMora }})
    </a>
</div>

@if($clientes->count() > 0)
<div class="clientes-grid">
    @php
    $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-orange', 'avatar-pink', 'avatar-teal', 'avatar-red'];
    @endphp

    @foreach($clientes as $i => $cliente)
    @php
        $creditoActivo = $cliente->creditos->first();
        $enMora = $cliente->enMora();
        $avatarClass = $avatarColors[$i % count($avatarColors)];
        $porcentaje = $creditoActivo ? $creditoActivo->porcentajePagado() : 0;
    @endphp
    <a href="{{ route('cobrador.clientes.show', $cliente) }}"
       class="cliente-card {{ $enMora ? 'mora-card' : '' }}">

        <div class="card-header-row">
            <div class="cliente-avatar-lg {{ $enMora ? 'avatar-red' : $avatarClass }}">
                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div class="cliente-name truncate">{{ $cliente->nombre }}</div>
                <div class="cliente-cedula">{{ $cliente->cedula ?? 'Sin cédula' }}</div>
            </div>
            <div>
                @if($enMora)
                    <span class="tag danger" style="font-size:10px;"><i class="fas fa-triangle-exclamation"></i> Mora</span>
                @elseif($creditoActivo)
                    <span class="tag info" style="font-size:10px;"><i class="fas fa-circle-check"></i> Activo</span>
                @else
                    <span class="tag" style="font-size:10px; background:var(--bg-card-2); color:var(--text-2);">Sin crédito</span>
                @endif
            </div>
        </div>

        <div class="client-divider"></div>

        <div class="client-details">
            <div class="detail-item">
                <div class="detail-label">Teléfono</div>
                <div class="detail-value">{{ $cliente->telefono }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Barrio</div>
                <div class="detail-value">{{ $cliente->barrio ?? '—' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Saldo pendiente</div>
                <div class="detail-value {{ $enMora ? 'danger' : 'money' }}">
                    ${{ $creditoActivo ? number_format($creditoActivo->saldo_pendiente, 0, ',', '.') : '0' }}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Cuota</div>
                <div class="detail-value">
                    ${{ $creditoActivo ? number_format($creditoActivo->valor_cuota, 0, ',', '.') : '—' }}
                </div>
            </div>
        </div>

        @if($creditoActivo)
        <div class="card-footer-row">
            <span style="font-size: 11px; color: var(--text-3);">{{ round($porcentaje) }}% pagado</span>
            <div class="progress-mini">
                <div class="progress-mini-fill" style="width: {{ $porcentaje }}%; {{ $enMora ? 'background: var(--danger);' : '' }}"></div>
            </div>
            <span style="font-size: 11px; color: var(--text-3);">{{ $creditoActivo->cuotasPagadas() }}/{{ $creditoActivo->num_cuotas }}</span>
        </div>
        @endif
    </a>
    @endforeach
</div>

@if($clientes->hasPages())
<div class="pagination-wrap">
    @if($clientes->onFirstPage())
        <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
    @else
        <a href="{{ $clientes->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
    @endif

    @foreach($clientes->getUrlRange(1, $clientes->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-btn {{ $page == $clientes->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if($clientes->hasMorePages())
        <a href="{{ $clientes->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
    @else
        <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
    @endif
</div>
@endif

@else
<div class="card">
    <div class="empty-state">
        <i class="fas fa-users-slash" style="color: var(--text-3);"></i>
        <p style="font-size: 16px; font-weight: 600; color: var(--text-2); margin-bottom: 6px;">
            @if(request('buscar')) No se encontraron clientes para "{{ request('buscar') }}"
            @elseif(request('filtro') === 'mora') No tienes clientes en mora
            @else No tienes clientes asignados
            @endif
        </p>
        <p style="margin-bottom: 16px;">
            @if(request('buscar') || request('filtro'))
                <a href="{{ route('cobrador.clientes.index') }}" style="color: var(--accent);">Limpiar filtros</a>
            @endif
        </p>
        <a href="{{ route('cobrador.clientes.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Registrar primer cliente
        </a>
    </div>
</div>
@endif

@endsection