@extends('layouts.admin')

@section('title', 'Editar Credito')

@section('topbar-actions')
    <a href="{{ route('admin.creditos.show', $credito) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')
<div style="max-width: 700px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Editar credito: {{ $credito->codigo }}</div>
            <span class="tag info">{{ $credito->cliente->nombre }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.creditos.update', $credito) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Codigo</label>
                        <input type="text" class="form-control" value="{{ $credito->codigo }}" disabled
                            style="opacity: 0.6; cursor: not-allowed;">
                        <span class="form-hint">El codigo no se puede modificar.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estado *</label>
                        <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                            <option value="activo" {{ old('estado', $credito->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="al_dia" {{ old('estado', $credito->estado) === 'al_dia' ? 'selected' : '' }}>Al dia</option>
                            <option value="mora" {{ old('estado', $credito->estado) === 'mora' ? 'selected' : '' }}>En mora</option>
                            <option value="pagado" {{ old('estado', $credito->estado) === 'pagado' ? 'selected' : '' }}>Pagado</option>
                            <option value="cancelado" {{ old('estado', $credito->estado) === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('estado') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Cobrador *</label>
                        <select name="cobrador_id" class="form-control @error('cobrador_id') is-invalid @enderror" required>
                            @foreach($cobradores as $c)
                            <option value="{{ $c->id }}" {{ old('cobrador_id', $credito->cobrador_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('cobrador_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cliente *</label>
                        <select name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ old('cliente_id', $credito->cliente_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }} {{ $c->cedula ? '- ' . $c->cedula : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('cliente_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Monto prestado</label>
                        <input type="text" class="form-control"
                            value="${{ number_format($credito->monto_prestado, 0, ',', '.') }}" disabled
                            style="opacity: 0.6; cursor: not-allowed;">
                        <span class="form-hint">No se puede modificar el monto.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tasa de interes (%) *</label>
                        <input type="number" name="tasa_interes"
                            class="form-control @error('tasa_interes') is-invalid @enderror"
                            value="{{ old('tasa_interes', $credito->tasa_interes) }}" min="0" max="100" step="0.1" required>
                        @error('tasa_interes') <span class="form-error">{{ $message }}</span> @enderror
                        <span class="form-hint">Modificar la tasa recalcula el total a pagar y el saldo.</span>
                    </div>
                </div>

                <div style="padding: 14px 16px; background: var(--bg-card-2); border-radius: 10px; margin-bottom: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
                    <div><span style="color: var(--text-2);">Cuotas:</span> <strong style="font-family: var(--font-mono);">{{ $credito->cuotasPagadas() }} / {{ $credito->num_cuotas }}</strong></div>
                    <div><span style="color: var(--text-2);">Frecuencia:</span> <strong>{{ ucfirst($credito->frecuencia) }}</strong></div>
                    <div><span style="color: var(--text-2);">Total a pagar:</span> <strong style="font-family: var(--font-mono);">${{ number_format($credito->total_a_pagar, 0, ',', '.') }}</strong></div>
                    <div><span style="color: var(--text-2);">Saldo pendiente:</span> <strong style="font-family: var(--font-mono); color: var(--danger);">${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}</strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones del credito...">{{ old('notas', $credito->notas) }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Guardar cambios
                    </button>
                    <a href="{{ route('admin.creditos.show', $credito) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
