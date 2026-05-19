@extends('layouts.cobrador')

@section('title', 'Nuevo Cliente')

@section('topbar-actions')
    <a href="{{ route('cobrador.clientes.index') }}" class="btn btn-secondary btn-sm">
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
                <i class="fas fa-user-plus"></i> Registrar nuevo cliente
            </div>
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

            <form method="POST" action="{{ route('cobrador.clientes.store') }}">
                @csrf

                {{-- Datos personales --}}
                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-user"></i> Datos personales
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre completo <span class="req">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                value="{{ old('nombre') }}"
                                placeholder="Ej: Carlos López Gómez"
                                required autofocus>
                            @error('nombre')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cédula / Documento</label>
                            <input type="text" name="cedula" class="form-control"
                                value="{{ old('cedula') }}"
                                placeholder="Ej: 10234567">
                            @error('cedula')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono principal <span class="req">*</span></label>
                            <input type="text" name="telefono" class="form-control"
                                value="{{ old('telefono') }}"
                                placeholder="Ej: 3101234567"
                                required>
                            @error('telefono')
                                <div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono alternativo</label>
                            <input type="text" name="telefono_alt" class="form-control"
                                value="{{ old('telefono_alt') }}"
                                placeholder="Opcional">
                        </div>
                    </div>
                </div>

                {{-- Ubicación --}}
                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-location-dot"></i> Ubicación
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección <span class="req">*</span></label>
                        <input type="text" name="direccion" class="form-control"
                            value="{{ old('direccion') }}"
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
                                value="{{ old('barrio') }}"
                                placeholder="Ej: La 40">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                value="{{ old('ciudad', 'Villavicencio') }}"
                                placeholder="Ej: Villavicencio">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Referencia de ubicación</label>
                        <input type="text" name="referencia_ubicacion" class="form-control"
                            value="{{ old('referencia_ubicacion') }}"
                            placeholder="Ej: Casa azul con puerta verde, frente al parque">
                        <div class="form-hint">Ayuda a encontrar la dirección fácilmente.</div>
                    </div>
                </div>

                {{-- Notas --}}
                <div style="margin-bottom: 24px;">
                    <div class="section-divider">
                        <i class="fas fa-note-sticky"></i> Observaciones
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Notas internas</label>
                        <textarea name="notas" class="form-control" rows="3"
                            placeholder="Referencias, comportamiento de pago, notas de campo...">{{ old('notas') }}</textarea>
                    </div>
                </div>

                <div class="form-actions" style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border);">
                    <a href="{{ route('cobrador.clientes.index') }}" class="btn btn-secondary">
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