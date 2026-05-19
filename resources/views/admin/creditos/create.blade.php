@extends('layouts.admin')

@section('title', 'Nuevo Crédito')

@section('topbar-actions')
    <a href="{{ route('admin.creditos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@push('styles')
<style>
    .simulador-card {
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        margin-top: 16px;
    }

    .sim-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0; border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .sim-row:last-child { border-bottom: none; }
    .sim-key { color: var(--text-2); }
    .sim-val { font-family: var(--font-mono); font-weight: 700; color: var(--text-1); }
    .sim-val.accent { color: var(--accent); }
    .sim-val.success { color: var(--success); }

    .freq-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .freq-opt { display: none; }
    .freq-label {
        display: flex; flex-direction: column; align-items: center; gap: 5px;
        padding: 12px 8px; border-radius: 10px;
        border: 2px solid var(--border); background: var(--bg-card-2);
        cursor: pointer; transition: all .15s; text-align: center;
    }
    .freq-opt:checked + .freq-label {
        border-color: var(--accent); background: var(--accent-glow); color: var(--accent);
    }
    .freq-label i { font-size: 18px; color: var(--text-2); }
    .freq-opt:checked + .freq-label i { color: var(--accent); }
    .freq-label .freq-name { font-size: 12px; font-weight: 700; color: var(--text-1); }
    .freq-opt:checked + .freq-label .freq-name { color: var(--accent); }
    .freq-label .freq-desc { font-size: 10px; color: var(--text-2); }

    @media (max-width: 900px) {
        .create-credito-layout { grid-template-columns: 1fr !important; }
        .create-credito-layout > div:last-child { position: static !important; }
    }
    @media (max-width: 500px) {
        .freq-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

<div style="max-width: 900px;">
    <div class="grid create-credito-layout" style="grid-template-columns: 1fr 320px; gap: 20px; align-items: start;">

        {{-- FORMULARIO --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-file-circle-plus"></i> Nuevo crédito</div>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="flash-message flash-error" style="margin-bottom: 20px;">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.creditos.store') }}" id="creditoForm">
                    @csrf

                    {{-- Cobrador + Cliente --}}
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-users" style="margin-right: 6px; color: var(--accent);"></i> Asignación
                        </div>

                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Cobrador <span class="req">*</span></label>
                                <select name="cobrador_id" id="cobradorSelect" class="form-control" required>
                                    <option value="">— Seleccionar —</option>
                                    @foreach($cobradores as $c)
                                        <option value="{{ $c->id }}"
                                            {{ old('cobrador_id', $clienteSeleccionado?->cobrador_id) == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cobrador_id')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cliente <span class="req">*</span></label>
                                <select name="cliente_id" id="clienteSelect" class="form-control" required>
                                    <option value="">— Primero selecciona cobrador —</option>
                                    @if($clienteSeleccionado)
                                        @foreach($clientes as $cl)
                                            <option value="{{ $cl->id }}"
                                                {{ old('cliente_id', $clienteSeleccionado->id) == $cl->id ? 'selected' : '' }}>
                                                {{ $cl->nombre }} @if($cl->cedula) — {{ $cl->cedula }} @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('cliente_id')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Montos --}}
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                            <i class="fas fa-coins" style="margin-right: 6px; color: var(--accent);"></i> Términos del crédito
                        </div>

                        <div class="form-grid form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Monto prestado <span class="req">*</span></label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-2); font-weight: 700;">$</span>
                                    <input type="number" name="monto_prestado" id="montoPrestado"
                                        class="form-control" style="padding-left: 24px;"
                                        value="{{ old('monto_prestado') }}"
                                        min="10000" step="1000" required
                                        placeholder="500000">
                                </div>
                                @error('monto_prestado')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tasa de interés (%) <span class="req">*</span></label>
                                <div style="position: relative;">
                                    <input type="number" name="tasa_interes" id="tasaInteres"
                                        class="form-control" style="padding-right: 32px;"
                                        value="{{ old('tasa_interes', 5) }}"
                                        min="0" max="100" step="0.5" required>
                                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-2); font-size: 13px;">%</span>
                                </div>
                                @error('tasa_interes')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Número de cuotas <span class="req">*</span></label>
                                <input type="number" name="num_cuotas" id="numCuotas"
                                    class="form-control"
                                    value="{{ old('num_cuotas', 12) }}"
                                    min="1" max="120" required>
                                @error('num_cuotas')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Fecha de inicio <span class="req">*</span></label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio', today()->format('Y-m-d')) }}" required>
                                @error('fecha_inicio')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Frecuencia --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label">Frecuencia de pago <span class="req">*</span></label>
                        <div class="freq-grid">
                            @foreach(['diaria' => ['fas fa-calendar-day', 'Diaria', 'Cada día'],
                                      'semanal' => ['fas fa-calendar-week', 'Semanal', 'Cada semana'],
                                      'quincenal' => ['fas fa-calendar-alt', 'Quincenal', 'Cada 15 días'],
                                      'mensual' => ['fas fa-calendar', 'Mensual', 'Cada mes']] as $val => $info)
                            <div>
                                <input type="radio" name="frecuencia" id="freq_{{ $val }}"
                                    class="freq-opt" value="{{ $val }}"
                                    {{ old('frecuencia', 'semanal') === $val ? 'checked' : '' }}>
                                <label for="freq_{{ $val }}" class="freq-label">
                                    <i class="{{ $info[0] }}"></i>
                                    <span class="freq-name">{{ $info[1] }}</span>
                                    <span class="freq-desc">{{ $info[2] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('frecuencia')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas internas (opcional)</label>
                        <textarea name="notas" class="form-control" rows="3"
                            placeholder="Condiciones especiales, garantías, referencias...">{{ old('notas') }}</textarea>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border);">
                        <a href="{{ route('admin.creditos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-xmark"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-circle-check"></i> Crear crédito y generar cuotas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- SIMULADOR --}}
        <div style="position: sticky; top: 80px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-calculator"></i> Simulador</div>
                </div>
                <div class="card-body">
                    <div class="simulador-card">
                        <div class="sim-row">
                            <span class="sim-key">Monto prestado</span>
                            <span class="sim-val accent" id="simMonto">$0</span>
                        </div>
                        <div class="sim-row">
                            <span class="sim-key">Interés</span>
                            <span class="sim-val" id="simInteres">$0</span>
                        </div>
                        <div class="sim-row">
                            <span class="sim-key">Total a pagar</span>
                            <span class="sim-val success" id="simTotal">$0</span>
                        </div>
                        <div class="sim-row">
                            <span class="sim-key">Valor por cuota</span>
                            <span class="sim-val accent" id="simCuota">$0</span>
                        </div>
                        <div class="sim-row">
                            <span class="sim-key">Número de cuotas</span>
                            <span class="sim-val" id="simNumCuotas">0</span>
                        </div>
                    </div>

                    <div style="margin-top: 16px; padding: 14px; background: var(--warning-soft); border: 1px solid rgba(245,158,11,0.2); border-radius: var(--radius); font-size: 12px; color: var(--warning);">
                        <i class="fas fa-info-circle"></i>
                        El valor de cuota se redondea al entero más cercano. Las diferencias por redondeo se ajustan en la última cuota.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Simulador en tiempo real
    const fields = ['montoPrestado', 'tasaInteres', 'numCuotas'];

    function calcular() {
        const monto = parseFloat(document.getElementById('montoPrestado').value) || 0;
        const tasa  = parseFloat(document.getElementById('tasaInteres').value) || 0;
        const cuotas = parseInt(document.getElementById('numCuotas').value) || 0;

        const total = monto * (1 + tasa / 100);
        const valorCuota = cuotas > 0 ? Math.round(total / cuotas) : 0;
        const interes = total - monto;

        const fmt = n => '$' + Math.round(n).toLocaleString('es-CO');

        document.getElementById('simMonto').textContent = fmt(monto);
        document.getElementById('simInteres').textContent = fmt(interes);
        document.getElementById('simTotal').textContent = fmt(total);
        document.getElementById('simCuota').textContent = fmt(valorCuota);
        document.getElementById('simNumCuotas').textContent = cuotas;
    }

    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', calcular);
    });

    calcular();

    // AJAX: cargar clientes por cobrador
    const cobradorSelect = document.getElementById('cobradorSelect');
    const clienteSelect  = document.getElementById('clienteSelect');
    const clientePresel  = {{ $clienteSeleccionado ? $clienteSeleccionado->id : 'null' }};

    cobradorSelect.addEventListener('change', async function () {
        const cobradorId = this.value;
        clienteSelect.innerHTML = '<option value="">Cargando clientes...</option>';

        if (!cobradorId) {
            clienteSelect.innerHTML = '<option value="">— Primero selecciona cobrador —</option>';
            return;
        }

        try {
            const res = await fetch(`/admin/api/cobrador/${cobradorId}/clientes`);
            const clientes = await res.json();

            if (clientes.length === 0) {
                clienteSelect.innerHTML = '<option value="">Sin clientes activos</option>';
                return;
            }

            clienteSelect.innerHTML = '<option value="">— Seleccionar cliente —</option>';
            clientes.forEach(cl => {
                const opt = document.createElement('option');
                opt.value = cl.id;
                opt.textContent = cl.nombre + (cl.cedula ? ` — ${cl.cedula}` : '');
                if (cl.id === clientePresel) opt.selected = true;
                clienteSelect.appendChild(opt);
            });
        } catch (e) {
            clienteSelect.innerHTML = '<option value="">Error al cargar clientes</option>';
        }
    });

    // Si ya hay cobrador seleccionado al cargar (cliente preseleccionado)
    if (cobradorSelect.value) {
        // Trigger only if we have pre-selected clientes already rendered
        // (server already loaded them via $clientes)
    }
</script>
@endpush