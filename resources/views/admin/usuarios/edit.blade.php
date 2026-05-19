@extends('layouts.admin')

@section('title', 'Editar Usuario')

@push('styles')
<style>
    @media (max-width: 800px) {
        .edit-usuario-layout { grid-template-columns: 1fr !important; }
        .edit-usuario-layout > div:last-child { position: static !important; }
    }
</style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')

<div style="max-width: 700px;">
    <div class="grid edit-usuario-layout" style="grid-template-columns: 1fr 280px; gap: 16px; align-items: start;">

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-pen"></i> Editar — {{ $usuario->name }}</div>
                <span class="tag {{ $usuario->active ? 'success' : 'danger' }}">
                    {{ $usuario->active ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="flash-message flash-error" style="margin-bottom: 20px;">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                    @csrf @method('PUT')

                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-user" style="margin-right: 6px; color: var(--accent);"></i> Datos personales
                        </div>
                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Nombre <span class="req">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $usuario->name) }}" required>
                                @error('name')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $usuario->phone) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $usuario->email) }}" required>
                            @error('email')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-lock" style="margin-right: 6px; color: var(--accent);"></i> Nueva contraseña (opcional)
                        </div>
                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Dejar vacío para mantener">
                                @error('password')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Repetir nueva contraseña">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-sliders" style="margin-right: 6px; color: var(--accent);"></i> Configuracion
                        </div>
                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Rol <span class="req">*</span></label>
                                <select name="role" class="form-control" id="roleSelect" required>
                                    <option value="cobrador" {{ old('role', $usuario->role) === 'cobrador' ? 'selected' : '' }}>Cobrador</option>
                                    <option value="admin"    {{ old('role', $usuario->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--bg-card-2); border: 1px solid var(--border); border-radius: 10px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--text-1);">
                                        <input type="checkbox" name="active" value="1"
                                            {{ old('active', $usuario->active) ? 'checked' : '' }}
                                            style="accent-color: var(--success); width: 16px; height: 16px;">
                                        Usuario activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="cobradorFields" style="margin-bottom: 20px; {{ old('role', $usuario->role) !== 'cobrador' ? 'display:none;' : '' }}">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-route" style="margin-right: 6px; color: var(--accent);"></i> Datos de cobrador
                        </div>
                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Codigo CN</label>
                                <input type="text" name="cn" class="form-control"
                                    value="{{ old('cn', $usuario->cn) }}" placeholder="Ej: CN-001">
                                @error('cn')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sector</label>
                                <select name="sector_id" class="form-control">
                                    <option value="">Sin sector asignado</option>
                                    @foreach($sectores as $sector)
                                    <option value="{{ $sector->id }}" {{ old('sector_id', $usuario->sector_id) == $sector->id ? 'selected' : '' }}>
                                        {{ $sector->nombre }} ({{ $sector->codigo }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('sector_id')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border);">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-xmark"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- STATS DEL USUARIO --}}
        <div style="position: sticky; top: 80px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-bar"></i> Estadísticas</div>
                </div>
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 16px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px;
                            background: {{ $usuario->role === 'admin' ? 'linear-gradient(135deg,var(--accent),var(--accent-2))' : 'linear-gradient(135deg,var(--success),#16a34a)' }};
                            display: flex; align-items: center; justify-content: center;
                            font-size: 22px; font-weight: 700; color: white;">
                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--text-1);">{{ $usuario->name }}</div>
                        <div style="font-size: 12px; color: var(--text-2); margin-top: 2px;">{{ $usuario->email }}</div>
                    </div>

                    @php
                        $totalClientes = $usuario->clientes()->count();
                        $totalPagos    = $usuario->pagos()->count();
                        $totalCobrado  = $usuario->pagos()->sum('monto_pagado');
                        $cobradoMes    = $usuario->pagos()->whereMonth('fecha_pago', now()->month)->sum('monto_pagado');
                    @endphp

                    <div style="border-top: 1px solid var(--border); padding-top: 14px;">
                        @foreach([
                            ['Clientes asignados', $totalClientes, 'fas fa-users', 'var(--accent)'],
                            ['Pagos registrados', $totalPagos, 'fas fa-receipt', 'var(--success)'],
                            ['Cobrado este mes', '$'.number_format($cobradoMes,0,',','.'), 'fas fa-calendar-check', 'var(--warning)'],
                            ['Cobrado total', '$'.number_format($totalCobrado,0,',','.'), 'fas fa-coins', 'var(--success)'],
                        ] as [$label, $val, $icon, $color])
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-2);">
                                <i class="{{ $icon }}" style="color: {{ $color }}; font-size: 11px; width: 14px;"></i>
                                {{ $label }}
                            </div>
                            <div style="font-family: var(--font-mono); font-size: 13px; font-weight: 700; color: var(--text-1);">{{ $val }}</div>
                        </div>
                        @endforeach
                    </div>

                    <div style="margin-top: 14px; font-size: 11px; color: var(--text-3); text-align: center;">
                        Miembro desde {{ $usuario->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    const cobradorFields = document.getElementById('cobradorFields');
    roleSelect.addEventListener('change', () => {
        cobradorFields.style.display = roleSelect.value === 'cobrador' ? '' : 'none';
    });
</script>
@endpush

@endsection