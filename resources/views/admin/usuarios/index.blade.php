@extends('layouts.admin')

@section('title', 'Usuarios')

@section('topbar-actions')
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus"></i> Nuevo usuario
    </a>
@endsection

@push('styles')
<style>
    .usuario-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 20px; border-bottom: 1px solid rgba(37,40,64,0.5);
        transition: background .1s;
    }
    .usuario-row:hover { background: var(--bg-card-2); }
    .usuario-row:last-child { border-bottom: none; }

    .u-avatar {
        width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; font-weight: 700; color: white;
    }
    .u-avatar.admin { background: linear-gradient(135deg, var(--accent), var(--accent-2)); }
    .u-avatar.cobrador { background: linear-gradient(135deg, var(--success), #16a34a); }
    .u-avatar.inactivo { background: linear-gradient(135deg, var(--text-3), #3a3a4a); }

    .u-info { flex: 1; min-width: 0; }
    .u-name { font-size: 14px; font-weight: 600; color: var(--text-1); margin-bottom: 2px; }
    .u-meta { font-size: 12px; color: var(--text-2); display: flex; gap: 10px; flex-wrap: wrap; }
    .u-meta span { display: flex; align-items: center; gap: 4px; }

    .u-stats { text-align: right; min-width: 120px; flex-shrink: 0; }
    .u-cobrado { font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--success); }
    .u-clientes { font-size: 11px; color: var(--text-2); }

    .u-actions { display: flex; gap: 6px; flex-shrink: 0; }
</style>
@endpush

@section('content')

<div class="grid grid-3 mb-6">
    <div class="stat-card purple">
        <div class="stat-label">Cobradores activos</div>
        <div class="stat-value">{{ $totalCobradores }}</div>
        <div class="stat-icon"><i class="fas fa-hand-holding-dollar" style="color: var(--accent);"></i></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Administradores</div>
        <div class="stat-value" style="color: var(--accent-2);">{{ $totalAdmins }}</div>
        <div class="stat-icon"><i class="fas fa-shield-halved" style="color: var(--accent-2);"></i></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Cobrado hoy (total)</div>
        <div class="stat-value money" style="font-size: 20px;">{{ number_format($cobradoHoy, 0, ',', '.') }}</div>
        <div class="stat-icon"><i class="fas fa-coins" style="color: var(--success);"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users-gear"></i> Usuarios del sistema</div>
        <span class="tag info">{{ $usuarios->total() }} registros</span>
    </div>

    {{-- Filtros --}}
    <div style="padding: 14px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" class="flex items-center gap-2" style="flex-wrap: wrap;">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Buscar nombre o email..."
                class="form-control" style="width: 240px;">
            <select name="rol" class="form-control" style="width: 150px;">
                <option value="">Todos los roles</option>
                <option value="admin" {{ request('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="cobrador" {{ request('rol') === 'cobrador' ? 'selected' : '' }}>Cobrador</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            @if(request()->hasAny(['buscar','rol']))
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i></a>
            @endif
        </form>
    </div>

    @if($usuarios->count() > 0)
    <div>
        @foreach($usuarios as $usuario)
        @php
            $esActivo = $usuario->active;
            $esAdmin  = $usuario->role === 'admin';
            $avatarClass = !$esActivo ? 'inactivo' : ($esAdmin ? 'admin' : 'cobrador');
        @endphp
        <div class="usuario-row">
            <div class="u-avatar {{ $avatarClass }}">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>
            <div class="u-info">
                <div class="u-name">
                    {{ $usuario->name }}
                    @if(!$esActivo) <span style="font-size: 10px; color: var(--danger); margin-left: 6px;">(Inactivo)</span> @endif
                </div>
                <div class="u-meta">
                    <span><i class="fas fa-envelope" style="font-size: 10px;"></i> {{ $usuario->email }}</span>
                    @if($usuario->phone) <span><i class="fas fa-phone" style="font-size: 10px;"></i> {{ $usuario->phone }}</span> @endif
                    <span><i class="fas fa-users" style="font-size: 10px;"></i> {{ $usuario->clientes_count }} clientes</span>
                </div>
            </div>
            <div>
                <span class="tag {{ $esAdmin ? 'info' : 'success' }}" style="font-size: 10px;">
                    <i class="fas fa-{{ $esAdmin ? 'shield-halved' : 'hand-holding-dollar' }}"></i>
                    {{ $esAdmin ? 'Admin' : 'Cobrador' }}
                </span>
            </div>
            <div class="u-stats">
                <div class="u-cobrado">${{ number_format($usuario->pagos_sum_monto_pagado ?? 0, 0, ',', '.') }}</div>
                <div class="u-clientes">{{ $usuario->pagos_count }} pagos totales</div>
            </div>
            <div class="u-actions">
                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-pen"></i>
                </a>
                @if($usuario->id !== auth()->id())
                <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="btn btn-sm {{ $esActivo ? 'btn-danger' : 'btn-success' }}"
                        title="{{ $esActivo ? 'Desactivar' : 'Activar' }}">
                        <i class="fas fa-{{ $esActivo ? 'ban' : 'check' }}"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($usuarios->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
        <div class="pagination-wrap" style="margin-top: 0;">
            @if($usuarios->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $usuarios->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a>
            @endif
            <span style="font-size: 13px; color: var(--text-2);">Página {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}</span>
            @if($usuarios->hasMorePages())
                <a href="{{ $usuarios->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-users-slash" style="color: var(--text-3);"></i>
        <p style="font-size: 16px; font-weight: 600; color: var(--text-2); margin-bottom: 6px;">No se encontraron usuarios</p>
    </div>
    @endif
</div>

@endsection