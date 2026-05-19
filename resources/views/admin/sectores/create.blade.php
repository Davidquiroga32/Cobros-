{{-- resources/views/admin/sectores/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Nuevo Sector')
@section('topbar-actions')
    <a href="{{ route('admin.sectores.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')
<div style="max-width: 560px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-map-location-dot"></i> Crear sector</div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sectores.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nombre del sector *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre') }}" required placeholder="Ej: Norte, Centro, Sur-Oriente">
                    @error('nombre') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad *</label>
                    <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror"
                        value="{{ old('ciudad', 'Villavicencio') }}" required>
                    @error('ciudad') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción (opcional)</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                        placeholder="Barrios o zonas que cubre este sector...">{{ old('descripcion') }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Crear sector
                    </button>
                    <a href="{{ route('admin.sectores.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection