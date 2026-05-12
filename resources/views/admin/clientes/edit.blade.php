@extends('layouts.admin')

@section('title', 'Editar Cliente')

@section('topbar-actions')
    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')

<div style="max-width: 760px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-pen"></i> Editar — {{ $cliente->nombre }}</div>
            @if($cliente->enMora())
                <span class="tag danger"><i class="fas fa-triangle-exclamation"></i> En mora</span>
            @endif
        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="flash-message flash-error" style="margin-bottom:20px;">
                <i class="fas fa-circle-exclamation"></i>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.clientes.update', $cliente) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-user" style="margin-right:6px; color:var(--accent);"></i> Datos personales
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre completo <span class="req">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre) }}" required>
                            @error('nombre')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cédula / Documento</label>
                            <input type="text" name="cedula" class="form-control" value="{{ old('cedula', $cliente->cedula) }}">
                            @error('cedula')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono principal <span class="req">*</span></label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono alternativo</label>
                            <input type="text" name="telefono_alt" class="form-control" value="{{ old('telefono_alt', $cliente->telefono_alt) }}">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-location-dot" style="margin-right:6px; color:var(--accent);"></i> Ubicación
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección <span class="req">*</span></label>
                        <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $cliente->direccion) }}" required>
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Barrio</label>
                            <input type="text" name="barrio" class="form-control" value="{{ old('barrio', $cliente->barrio) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $cliente->ciudad) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referencia de ubicación</label>
                        <input type="text" name="referencia_ubicacion" class="form-control" value="{{ old('referencia_ubicacion', $cliente->referencia_ubicacion) }}">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-sliders" style="margin-right:6px; color:var(--accent);"></i> Configuración
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Cobrador asignado <span class="req">*</span></label>
                            <select name="cobrador_id" class="form-control" required>
                                @foreach($cobradores as $c)
                                    <option value="{{ $c->id }}" {{ old('cobrador_id', $cliente->cobrador_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado del cliente <span class="req">*</span></label>
                            <select name="estado" class="form-control" required>
                                <option value="activo"   {{ old('estado', $cliente->estado) === 'activo'   ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $cliente->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="bloqueado"{{ old('estado', $cliente->estado) === 'bloqueado'? 'selected' : '' }}>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas internas</label>
                        <textarea name="notas" class="form-control" rows="3">{{ old('notas', $cliente->notas) }}</textarea>
                    </div>
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:16px; border-top:1px solid var(--border);">
                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-secondary">
                        <i class="fas fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-floppy-disk"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection