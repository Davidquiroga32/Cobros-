@extends('layouts.cobrador')

@section('title', 'Editar Cliente')

@section('topbar-actions')
    <a href="{{ route('cobrador.clientes.show', $cliente) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@push('styles')
<style>
    .section-divider {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 1px; color: var(--text-3);
        margin-bottom: 16px; padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 8px;
    }
    .section-divider i { color: var(--accent); font-size: 12px; }
    .req { color: var(--danger); margin-left: 2px; }

    @media (max-width: 640px) {
        .card-body { padding: 14px; }
        .card-header { padding: 12px 14px; }
        .form-actions { flex-direction: column; }
        .form-actions .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')

<div style="max-width: 700px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-pen"></i> Editar cliente: {{ $cliente->nombre }}
            </div>
            <span class="tag info">{{ $cliente->cedula ?? 'Sin documento' }}</span>
        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="flash-message flash-error" style="margin-bottom: 20px;">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('cobrador.clientes.update', $cliente) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-user"></i> Datos personales
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre completo <span class="req">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                value="{{ old('nombre', $cliente->nombre) }}"
                                placeholder="Ej: Carlos Lopez Gomez"
                                required autofocus>
                            @error('nombre')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cedula / Documento</label>
                            <input type="text" name="cedula" class="form-control"
                                value="{{ old('cedula', $cliente->cedula) }}"
                                placeholder="Ej: 10234567">
                            @error('cedula')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telefono principal <span class="req">*</span></label>
                            <input type="text" name="telefono" class="form-control"
                                value="{{ old('telefono', $cliente->telefono) }}"
                                placeholder="Ej: 3101234567"
                                required>
                            @error('telefono')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telefono alternativo</label>
                            <input type="text" name="telefono_alt" class="form-control"
                                value="{{ old('telefono_alt', $cliente->telefono_alt) }}"
                                placeholder="Opcional">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-location-dot"></i> Ubicacion
                    </div>
                    <div class="form-group">
                        <label class="form-label">Direccion <span class="req">*</span></label>
                        <input type="text" name="direccion" class="form-control"
                            value="{{ old('direccion', $cliente->direccion) }}"
                            placeholder="Ej: Cra 5 #23-10, Barrio Centro"
                            required>
                        @error('direccion')
                            <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Barrio</label>
                            <input type="text" name="barrio" class="form-control"
                                value="{{ old('barrio', $cliente->barrio) }}"
                                placeholder="Ej: La 40">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                value="{{ old('ciudad', $cliente->ciudad) }}"
                                placeholder="Ej: Villavicencio">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referencia de ubicacion</label>
                        <input type="text" name="referencia_ubicacion" class="form-control"
                            value="{{ old('referencia_ubicacion', $cliente->referencia_ubicacion) }}"
                            placeholder="Ej: Casa azul con puerta verde, frente al parque">
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-note-sticky"></i> Observaciones
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Notas internas</label>
                        <textarea name="notas" class="form-control" rows="3"
                            placeholder="Referencias, comportamiento de pago, notas de campo...">{{ old('notas', $cliente->notas) }}</textarea>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border);">
                    <a href="{{ route('cobrador.clientes.show', $cliente) }}" class="btn btn-secondary">
                        <i class="fas fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Guardar cambios
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
