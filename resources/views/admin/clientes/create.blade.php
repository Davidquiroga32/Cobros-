@extends('layouts.admin')

@section('title', 'Nuevo Cliente')

@section('topbar-actions')
    <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')

<div style="max-width: 760px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-plus"></i> Registrar nuevo cliente</div>
        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="flash-message flash-error" style="margin-bottom:20px;">
                <i class="fas fa-circle-exclamation"></i>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.clientes.store') }}">
                @csrf

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-user" style="margin-right:6px; color:var(--accent);"></i> Datos personales
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre completo <span class="req">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}"
                                placeholder="Ej: Carlos López Gómez" required autofocus>
                            @error('nombre')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cédula / Documento</label>
                            <input type="text" name="cedula" class="form-control" value="{{ old('cedula') }}"
                                placeholder="Ej: 10234567">
                            @error('cedula')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono principal <span class="req">*</span></label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}"
                                placeholder="Ej: 3101234567" required>
                            @error('telefono')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono alternativo</label>
                            <input type="text" name="telefono_alt" class="form-control" value="{{ old('telefono_alt') }}"
                                placeholder="Opcional">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-location-dot" style="margin-right:6px; color:var(--accent);"></i> Ubicación
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección <span class="req">*</span></label>
                        <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}"
                            placeholder="Ej: Cra 5 #23-10, Barrio Centro" required>
                        @error('direccion')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Barrio</label>
                            <input type="text" name="barrio" class="form-control" value="{{ old('barrio') }}"
                                placeholder="Ej: La 40">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', 'Villavicencio') }}"
                                placeholder="Ej: Villavicencio">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referencia de ubicación</label>
                        <input type="text" name="referencia_ubicacion" class="form-control" value="{{ old('referencia_ubicacion') }}"
                            placeholder="Ej: Casa azul con puerta verde, frente al parque">
                        <div class="form-hint">Ayuda al cobrador a encontrar la dirección fácilmente.</div>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--text-3); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        <i class="fas fa-user-tie" style="margin-right:6px; color:var(--accent);"></i> Asignación
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cobrador asignado <span class="req">*</span></label>
                        <select name="cobrador_id" class="form-control" required>
                            <option value="">— Seleccionar cobrador —</option>
                            @foreach($cobradores as $c)
                                <option value="{{ $c->id }}" {{ old('cobrador_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} @if($c->phone) — {{ $c->phone }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('cobrador_id')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas internas</label>
                        <textarea name="notas" class="form-control" rows="3"
                            placeholder="Observaciones sobre el cliente, historial, referencias...">{{ old('notas') }}</textarea>
                    </div>
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:16px; border-top:1px solid var(--border);">
                    <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check"></i> Guardar cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection