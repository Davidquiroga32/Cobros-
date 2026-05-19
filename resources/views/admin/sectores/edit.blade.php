{{-- resources/views/admin/sectores/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Editar Sector')
@section('topbar-actions')
    <a href="{{ route('admin.sectores.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')
<div style="max-width: 560px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-map-location-dot"></i> Editar sector: {{ $sector->nombre }}</div>
            <span class="tag info">{{ $sector->codigo }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sectores.update', $sector) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nombre del sector *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $sector->nombre) }}" required placeholder="Ej: Norte, Centro, Sur-Oriente">
                    @error('nombre') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Código</label>
                    <input type="text" class="form-control" value="{{ $sector->codigo }}" disabled
                        style="opacity: 0.6; cursor: not-allowed;">
                    <span class="form-hint">El código no se puede modificar.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad *</label>
                    <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror"
                        value="{{ old('ciudad', $sector->ciudad) }}" required>
                    @error('ciudad') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripcion (opcional)</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                        placeholder="Barrios o zonas que cubre este sector...">{{ old('descripcion', $sector->descripcion) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" id="activo"
                            {{ old('activo', $sector->activo) ? 'checked' : '' }}
                            style="width: 18px; height: 18px; accent-color: var(--accent);">
                        <label for="activo" style="font-size: 14px; cursor: pointer;">Sector activo</label>
                    </div>
                    <span class="form-hint">Desmarcar para desactivar este sector.</span>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Guardar cambios
                    </button>
                    <a href="{{ route('admin.sectores.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
