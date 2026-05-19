@extends('layouts.cobrador')

@section('title', 'Nuevo Credito')

@section('topbar-actions')
    <a href="{{ route('cobrador.creditos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@push('styles')
<style>
    .credit-summary {
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        margin-top: 20px;
    }
    .summary-row {
        display: flex; justify-content: space-between;
        flex-wrap: wrap; gap: 4px;
        padding: 6px 0; font-size: 13px;
    }
    .summary-label { color: var(--text-2); }
    .summary-value { font-family: var(--font-mono); font-weight: 700; color: var(--text-1); }

    @media (max-width: 640px) {
        .card-body { padding: 14px; }
        .card-header { padding: 12px 14px; }
    }
</style>
@endpush

@section('content')
<div style="max-width: 700px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Nuevo credito</div>
            @if($clienteSeleccionado)
            <span class="tag info">{{ $clienteSeleccionado->nombre }}</span>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('cobrador.creditos.store') }}" method="POST" id="creditoForm">
                @csrf
                <input type="hidden" id="tasa_calculada" value="0">

                <div class="form-group">
                    <label class="form-label">Cliente *</label>
                    <select name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                        <option value="">Selecciona un cliente...</option>
                        @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $clienteSeleccionado?->id) == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombre }} {{ $cliente->cedula ? '- ' . $cliente->cedula : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="form-error">{{ $message }}</span> @enderror
                    @if($clientes->isEmpty())
                    <span class="form-hint" style="color: var(--warning);">
                        No tienes clientes activos. <a href="{{ route('cobrador.clientes.create') }}" style="color: var(--accent);">Crear uno nuevo</a>
                    </span>
                    @endif
                </div>

                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Monto prestado ($) *</label>
                        <input type="number" name="monto_prestado" id="monto_prestado"
                            class="form-control @error('monto_prestado') is-invalid @enderror"
                            value="{{ old('monto_prestado') }}" min="10000" step="10000" required
                            placeholder="Ej: 500000">
                        @error('monto_prestado') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tasa de interes (%)</label>
                        <input type="number" name="tasa_interes" id="tasa_interes"
                            class="form-control @error('tasa_interes') is-invalid @enderror"
                            value="{{ old('tasa_interes', 5) }}" min="0" max="100" step="0.1">
                        @error('tasa_interes') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Numero de cuotas *</label>
                        <input type="number" name="num_cuotas" id="num_cuotas"
                            class="form-control @error('num_cuotas') is-invalid @enderror"
                            value="{{ old('num_cuotas') }}" min="1" max="120" required
                            placeholder="Ej: 12">
                        @error('num_cuotas') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Frecuencia *</label>
                        <select name="frecuencia" class="form-control @error('frecuencia') is-invalid @enderror" required>
                            <option value="">Selecciona...</option>
                            <option value="diaria" {{ old('frecuencia') === 'diaria' ? 'selected' : '' }}>Diaria</option>
                            <option value="semanal" {{ old('frecuencia', 'semanal') === 'semanal' ? 'selected' : '' }}>Semanal</option>
                            <option value="quincenal" {{ old('frecuencia') === 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                            <option value="mensual" {{ old('frecuencia') === 'mensual' ? 'selected' : '' }}>Mensual</option>
                        </select>
                        @error('frecuencia') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha de inicio *</label>
                    <input type="date" name="fecha_inicio"
                        class="form-control @error('fecha_inicio') is-invalid @enderror"
                        value="{{ old('fecha_inicio', today()->format('Y-m-d')) }}" required>
                    @error('fecha_inicio') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Notas (opcional)</label>
                    <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones del credito...">{{ old('notas') }}</textarea>
                </div>

                <div class="credit-summary" id="summary" style="display: none;">
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3); margin-bottom: 10px;">
                        Resumen del credito
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Total a pagar</span>
                        <span class="summary-value" id="sum_total">$0</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Valor por cuota</span>
                        <span class="summary-value" id="sum_cuota">$0</span>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Crear credito
                    </button>
                    <a href="{{ route('cobrador.creditos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function recalcular() {
        const monto = parseFloat(document.getElementById('monto_prestado').value) || 0;
        const tasa  = parseFloat(document.getElementById('tasa_interes').value) || 0;
        const cuotas = parseInt(document.getElementById('num_cuotas').value) || 0;
        const summary = document.getElementById('summary');

        if (monto > 0 && cuotas > 0) {
            const total = monto * (1 + tasa / 100);
            const valorCuota = Math.round(total / cuotas);
            document.getElementById('sum_total').textContent = '$' + total.toLocaleString('es-CO');
            document.getElementById('sum_cuota').textContent = '$' + valorCuota.toLocaleString('es-CO');
            summary.style.display = 'block';
        } else {
            summary.style.display = 'none';
        }
    }

    document.getElementById('monto_prestado').addEventListener('input', recalcular);
    document.getElementById('tasa_interes').addEventListener('input', recalcular);
    document.getElementById('num_cuotas').addEventListener('input', recalcular);
    recalcular();
</script>
@endpush

@endsection
