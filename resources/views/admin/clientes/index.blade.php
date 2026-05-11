@extends('layouts.admin')

@section('title', 'Clientes')

@section('topbar-actions')
    <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus"></i> Nuevo cliente
    </a>
@endsection

@push('styles')
<style>
    .filters-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .cliente-row { display: flex; align-items: center; gap: 14px; padding: 14px 20px; border-bottom: 1px solid rgba(37,40,64,0.5); transition: background .1s; }
    .cliente-row:hover { background: var(--bg-card-2); }
    .cliente-row:last-child { border-bottom: none; }
    .cl-avatar { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: white; flex-shrink: 0; background: linear-gradient(135deg, var(--accent), var(--accent-2)); }
    .cl-avatar.mora { background: linear-gradient(135deg, #ef4444, #b91c1c); }
    .cl-info { flex: 1; min-width: 0; }
    .cl-name { font-size: 14px; font-weight: 600; color: var(--text-1); margin-bottom: 2px; }
    .cl-meta { font-size: 12px; color: var(--text-2); display: flex; gap: 10px; flex-wrap: wrap; }
    .cl-meta span { display: flex; align-items: center; gap: 4px; }
    .cl-saldo { font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--accent); text-align: right; flex-shrink: 0; min-width: 100px; }
    .cl-saldo.mora { color: var(--danger); }
    .cl-actions { display: flex; gap: 6px; flex-shrink: 0; }
</style>
@endpush

@section('content')

<div class="grid grid-3 mb-6">
    <div class="stat-card purple">
        <div class="stat-label">Total clientes</div>
        <div class="stat-value">{{ $totalClientes }}</div>
        <div class="stat-icon"><i class="fas fa-users" style="color:var(--accent);"></i></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Activos</div>
        <div class="stat-value" style="color:var(--success);">{{ $clientesActivos }}</div>
        <div class="stat-icon"><i class="fas fa-circle-check" style="color:var(--success);"></i></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">En mora</div>
        <div class="stat-value" style="color:var(--danger);">{{ $clientesEnMora }}</div>
        <div class="stat-icon"><i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users"></i> Listado de clientes</div>
        <span class="tag info">{{ $clientes->total() }} registros</span>
    </div>

    {{-- Filtros --}}
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" class="filters-bar">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Buscar nombre, cédula, teléfono..."
                class="form-control" style="width:260px;">
            <select name="cobrador_id" class="form-control" style="width:180px;">
                <option value="">Todos los cobradores</option>
                @foreach($cobradores as $c)
                    <option value="{{ $c->id }}" {{ request('cobrador_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="estado" class="form-control" style="width:140px;">
                <option value="">Todo estado</option>
                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                <option value="bloqueado" {{ request('estado') === 'bloqueado' ? 'selected' : '' }}>Bloqueado</option>
            </select>
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-2); cursor:pointer;">
                <input type="checkbox" name="mora" value="1" {{ request('mora') ? 'checked' : '' }} style="accent-color:var(--danger);">
                Solo mora
            </label>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            @if(request()->hasAny(['buscar','cobrador_id','estado','mora']))
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i></a>
            @endif
        </form>
    </div>

    @if($clientes->count() > 0)
    <div>
        @foreach($clientes as $cliente)
        @php $enMora = $cliente->enMora(); $credito = $cliente->creditos->first(); @endphp
        <div class="cliente-row">
            <div class="cl-avatar {{ $enMora ? 'mora' : '' }}">{{ strtoupper(substr($cliente->nombre, 0, 1)) }}</div>
            <div class="cl-info">
                <div class="cl-name">{{ $cliente->nombre }}</div>
                <div class="cl-meta">
                    @if($cliente->cedula) <span><i class="fas fa-id-card" style="font-size:10px;"></i> {{ $cliente->cedula }}</span> @endif
                    <span><i class="fas fa-phone" style="font-size:10px;"></i> {{ $cliente->telefono }}</span>
                    @if($cliente->barrio) <span><i class="fas fa-location-dot" style="font-size:10px;"></i> {{ $cliente->barrio }}</span> @endif
                    <span><i class="fas fa-user" style="font-size:10px;"></i> {{ $cliente->cobrador->name ?? '—' }}</span>
                </div>
            </div>
            <div>
                @if($enMora)
                    <span class="tag danger" style="font-size:10px;">Mora</span>
                @elseif($credito)
                    <span class="tag {{ $credito->estado === 'pagado' ? 'success' : 'info' }}" style="font-size:10px;">{{ ucfirst($credito->estado) }}</span>
                @else
                    <span class="tag" style="font-size:10px; background:var(--bg-card-2); color:var(--text-2);">Sin crédito</span>
                @endif
            </div>
            <div class="cl-saldo {{ $enMora ? 'mora' : '' }}">
                ${{ $credito ? number_format($credito->saldo_pendiente, 0, ',', '.') : '0' }}
            </div>
            <div class="cl-actions">
                <a href="{{ route('admin.creditos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-success btn-sm" title="Nuevo crédito">
                    <i class="fas fa-plus"></i>
                </a>
                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-pen"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    @if($clientes->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
        <div class="pagination-wrap" style="margin-top:0;">
            @if($clientes->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $clientes->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
            @endif
            <span style="font-size:13px; color:var(--text-2);">Página {{ $clientes->currentPage() }} de {{ $clientes->lastPage() }}</span>
            @if($clientes->hasMorePages())
                <a href="{{ $clientes->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-users-slash" style="color:var(--text-3);"></i>
        <p style="font-size:16px; font-weight:600; color:var(--text-2); margin-bottom:6px;">No se encontraron clientes</p>
        <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary" style="margin-top:12px;"><i class="fas fa-user-plus"></i> Crear primer cliente</a>
    </div>
    @endif
</div>

@endsection