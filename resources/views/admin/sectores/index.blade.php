@extends('layouts.admin')

@section('title', 'Sectores')

@section('topbar-actions')
    <a href="{{ route('admin.sectores.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo sector
    </a>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-map"></i> Sectores de cobranza</div>
        <span class="tag info">{{ $sectores->total() }} registros</span>
    </div>

    {{-- Buscador --}}
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Buscar por nombre o código..."
                class="form-control" style="max-width: 300px;">
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Buscar
            </button>
            @if(request('buscar'))
            <a href="{{ route('admin.sectores.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
            @endif
        </form>
    </div>

    <div>
        @forelse($sectores as $sector)
        <div style="display: flex; align-items: center; gap: 14px; padding: 14px 20px;
                    border-bottom: 1px solid var(--border); transition: background .1s;"
             class="hover-row">

            <div style="width: 40px; height: 40px; border-radius: 10px;
                        background: var(--accent-glow); color: var(--accent);
                        display: flex; align-items: center; justify-content: center;
                        font-size: 16px; flex-shrink:0;">
                <i class="fas fa-map-location-dot"></i>
            </div>

            <div style="flex:1; min-width:0;">
                <div style="font-weight: 700; color: var(--text-1); font-size: 14px;">{{ $sector->nombre }}</div>
                <div style="font-size: 11px; color: var(--text-2);">
                    <strong>{{ $sector->codigo }}</strong> · {{ $sector->ciudad }}
                    @if($sector->descripcion) · {{ Str::limit($sector->descripcion, 60) }} @endif
                </div>
            </div>

            <div style="text-align: center; min-width: 70px;">
                <div style="font-size: 18px; font-weight: 800; color: var(--text-1); font-family: var(--font-mono);">
                    {{ $sector->total_cobradores }}
                </div>
                <div style="font-size: 10px; color: var(--text-3);">Cobradores</div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-shrink:0;">
                <span class="tag {{ $sector->activo ? 'success' : '' }}" style="{{ !$sector->activo ? 'color:var(--text-3);' : '' }}">
                    {{ $sector->activo ? 'Activo' : 'Inactivo' }}
                </span>
                <a href="{{ route('admin.sectores.edit', $sector) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-pen"></i>
                </a>
                <form action="{{ route('admin.sectores.toggle', $sector) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-secondary btn-sm" title="{{ $sector->activo ? 'Desactivar' : 'Activar' }}">
                        <i class="fas fa-power-off"></i>
                    </button>
                </form>
                @if($sector->total_cobradores == 0)
                <form action="{{ route('admin.sectores.destroy', $sector) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar sector {{ $sector->nombre }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div style="padding: 40px; text-align: center; color: var(--text-3);">
            <i class="fas fa-map" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
            No hay sectores creados.
            <br>
            <a href="{{ route('admin.sectores.create') }}" style="color: var(--accent); margin-top: 8px; display:inline-block;">
                Crear el primero →
            </a>
        </div>
        @endforelse
    </div>

    @if($sectores->hasPages())
    <div class="pagination-wrap">
        {{ $sectores->links() }}
    </div>
    @endif
</div>

@endsection