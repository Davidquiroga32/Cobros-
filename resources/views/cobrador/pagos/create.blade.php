@extends('layouts.cobrador')

@section('title', 'Registrar Pago')

@section('topbar-actions')
    <a href="{{ route('cobrador.agenda') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver a agenda
    </a>
@endsection

@push('styles')
<style>
    .pago-layout { display: grid; grid-template-columns: 1fr 380px; gap: 20px; align-items: start; }

    /* Resumen cuota */
    .cuota-summary {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden; position: sticky; top: 80px;
    }

    .summary-header {
        padding: 20px 22px; border-bottom: 1px solid var(--border);
        background: linear-gradient(135deg, rgba(79,142,247,0.08), transparent);
    }

    .summary-client {
        display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
    }

    .summary-avatar {
        width: 48px; height: 48px; border-radius: 14px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 700; color: white; flex-shrink: 0;
    }

    .summary-name { font-size: 16px; font-weight: 700; color: var(--text-1); }
    .summary-cedula { font-size: 12px; color: var(--text-2); }

    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--border);
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-key { font-size: 12px; color: var(--text-2); font-weight: 500; }
    .summary-val { font-size: 13px; font-weight: 700; color: var(--text-1); }
    .summary-val.accent { color: var(--accent); font-family: var(--font-mono); }
    .summary-val.warning { color: var(--warning); font-family: var(--font-mono); }
    .summary-val.danger  { color: var(--danger); font-family: var(--font-mono); }

    /* Big amount display */
    .amount-display {
        text-align: center; padding: 20px;
        background: var(--bg-card-2); border-top: 1px solid var(--border);
    }

    .amount-label { font-size: 11px; color: var(--text-3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
    .amount-big { font-family: var(--font-mono); font-size: 36px; font-weight: 700; color: var(--accent); line-height: 1; }
    .amount-sub { font-size: 12px; color: var(--text-2); margin-top: 4px; }

    /* Form card */
    .form-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius-lg); overflow: hidden;
    }

    .form-card-header {
        padding: 20px 24px; border-bottom: 1px solid var(--border);
    }

    .form-card-body { padding: 24px; }

    /* Monto input special */
    .monto-wrap { position: relative; }
    .monto-prefix {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        font-size: 18px; font-weight: 700; color: var(--text-2);
        font-family: var(--font-mono);
    }

    .monto-input {
        width: 100%; padding: 14px 14px 14px 30px;
        background: var(--bg-input); border: 2px solid var(--border);
        border-radius: 12px; color: var(--text-1);
        font-family: var(--font-mono); font-size: 26px; font-weight: 700;
        transition: border-color .15s; outline: none;
    }

    .monto-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-glow); }
    .monto-input.valid { border-color: var(--success); }

    /* Quick amounts */
    .quick-amounts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .quick-btn {
        padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
        border: 1px solid var(--border); background: var(--bg-card-2);
        color: var(--text-2); cursor: pointer; transition: all .12s; font-family: var(--font-mono);
    }
    .quick-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
    .quick-btn.full-pay { border-color: rgba(34,197,94,0.3); color: var(--success); background: var(--success-soft); }

    /* Método pago */
    .metodo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    @media (max-width: 480px) {
        .metodo-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .metodo-opt { display: none; }

    .metodo-label {
        display: flex; flex-direction: column; align-items: center;
        padding: 14px 10px; border-radius: 12px;
        border: 2px solid var(--border); background: var(--bg-card-2);
        cursor: pointer; transition: all .15s; text-align: center;
    }

    .metodo-opt:checked + .metodo-label {
        border-color: var(--accent); background: var(--accent-glow); color: var(--accent);
    }

    .metodo-label i { font-size: 20px; margin-bottom: 6px; color: var(--text-2); }
    .metodo-opt:checked + .metodo-label i { color: var(--accent); }
    .metodo-label span { font-size: 11px; font-weight: 600; color: var(--text-2); }
    .metodo-opt:checked + .metodo-label span { color: var(--accent); }

    /* Submit btn */
    .submit-btn {
        width: 100%; padding: 16px; border-radius: 12px;
        background: var(--success); color: white;
        font-size: 16px; font-weight: 700; font-family: var(--font-main);
        border: none; cursor: pointer; transition: all .15s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow: 0 0 24px rgba(34,197,94,0.25);
    }
    .submit-btn:hover { background: #16a34a; transform: translateY(-1px); box-shadow: 0 4px 32px rgba(34,197,94,0.35); }
    .submit-btn:active { transform: none; }

    /* GPS */
    .gps-status {
        display: flex; align-items: center; gap: 8px;
        font-size: 12px; color: var(--text-3); margin-top: 10px;
    }
    .gps-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--text-3); }
    .gps-dot.active { background: var(--success); box-shadow: 0 0 8px var(--success); }

    @media (max-width: 1024px) {
        .pago-layout { grid-template-columns: 1fr; }
        .cuota-summary { position: static; }
    }
</style>
@endpush

@section('content')

<div class="pago-layout">

    {{-- ── FORMULARIO ──────────────────────────────────── --}}
    <div>
        <div class="form-card">
            <div class="form-card-header">
                <div class="card-title"><i class="fas fa-hand-holding-dollar"></i> Registrar pago</div>
                <p style="font-size: 13px; color: var(--text-2); margin-top: 6px;">
                    Ingresa los datos del cobro para la cuota #{{ $cuota->numero_cuota }}
                </p>
            </div>
            <div class="form-card-body">
                @if($errors->any())
                <div class="flash-message flash-error" style="margin-bottom: 20px;">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('cobrador.pagos.store', $cuota) }}" id="pagoForm">
                    @csrf

                    {{-- Monto --}}
                    <div class="form-group">
                        <label class="form-label">Monto recibido</label>
                        <div class="monto-wrap">
                            <span class="monto-prefix">$</span>
                            <input type="number" name="monto_pagado" id="montoInput"
                                class="monto-input {{ $errors->has('monto_pagado') ? '' : '' }}"
                                value="{{ old('monto_pagado', (int) $cuota->saldo_cuota) }}"
                                min="500" max="{{ (int) $cuota->saldo_cuota }}"
                                step="500" required
                                placeholder="0">
                        </div>
                        @error('monto_pagado')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        {{-- Quick amounts --}}
                        <div class="quick-amounts">
                            <button type="button" class="quick-btn full-pay"
                                onclick="setMonto({{ $cuota->saldo_cuota }})">
                                <i class="fas fa-circle-check" style="font-size:10px;"></i>
                                ${{ number_format($cuota->saldo_cuota, 0, ',', '.') }} (completo)
                            </button>
                            @foreach([50000, 100000, 150000, 200000] as $amt)
                                @if($amt < $cuota->saldo_cuota)
                                <button type="button" class="quick-btn" onclick="setMonto({{ $amt }})">
                                    ${{ number_format($amt, 0, ',', '.') }}
                                </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Método de pago --}}
                    <div class="form-group">
                        <label class="form-label">Método de pago</label>
                        <div class="metodo-grid">
                            @foreach(['efectivo' => ['fas fa-money-bill-wave', 'Efectivo'],
                                      'transferencia' => ['fas fa-building-columns', 'Transferencia'],
                                      'nequi' => ['fas fa-mobile-screen', 'Nequi'],
                                      'daviplata' => ['fas fa-credit-card', 'Daviplata'],
                                      'otro' => ['fas fa-ellipsis', 'Otro']] as $val => $info)
                            <div>
                                <input type="radio" name="metodo_pago" id="metodo_{{ $val }}"
                                    class="metodo-opt" value="{{ $val }}"
                                    {{ old('metodo_pago', 'efectivo') === $val ? 'checked' : '' }}>
                                <label for="metodo_{{ $val }}" class="metodo-label">
                                    <i class="{{ $info[0] }}"></i>
                                    <span>{{ $info[1] }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('metodo_pago')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="form-group">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea name="observaciones" class="form-control" rows="3"
                            placeholder="Notas adicionales sobre el pago...">{{ old('observaciones') }}</textarea>
                    </div>

                    {{-- GPS oculto --}}
                    <input type="hidden" name="latitud" id="latInput">
                    <input type="hidden" name="longitud" id="lonInput">

                    <div class="gps-status">
                        <div class="gps-dot" id="gpsDot"></div>
                        <span id="gpsText">Obteniendo ubicación GPS...</span>
                    </div>

                    <div style="margin-top: 24px;">
                        <button type="submit" class="submit-btn" id="submitBtn">
                            <i class="fas fa-circle-check" style="font-size: 20px;"></i>
                            Confirmar pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RESUMEN LATERAL ─────────────────────────────── --}}
    <div>
        <div class="cuota-summary">
            <div class="summary-header">
                <div class="summary-client">
                    <div class="summary-avatar">{{ strtoupper(substr($cuota->cliente->nombre, 0, 1)) }}</div>
                    <div>
                        <div class="summary-name">{{ $cuota->cliente->nombre }}</div>
                        <div class="summary-cedula">CC {{ $cuota->cliente->cedula ?? 'Sin cédula' }}</div>
                    </div>
                </div>

                <div>
                    <div class="summary-row">
                        <span class="summary-key">Crédito</span>
                        <span class="summary-val">{{ $cuota->credito->codigo }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Cuota</span>
                        <span class="summary-val">#{{ $cuota->numero_cuota }} de {{ $cuota->credito->num_cuotas }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Vencimiento</span>
                        <span class="summary-val {{ $cuota->fecha_vencimiento->isPast() ? 'danger' : '' }}">
                            {{ $cuota->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-key">Valor cuota</span>
                        <span class="summary-val accent">${{ number_format($cuota->valor_cuota, 0, ',', '.') }}</span>
                    </div>
                    @if($cuota->valor_pagado > 0)
                    <div class="summary-row">
                        <span class="summary-key">Ya pagado</span>
                        <span class="summary-val" style="color: var(--success); font-family: var(--font-mono);">
                            ${{ number_format($cuota->valor_pagado, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif
                    <div class="summary-row">
                        <span class="summary-key">Saldo pendiente</span>
                        <span class="summary-val warning">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="amount-display">
                <div class="amount-label">Recibirás</div>
                <div class="amount-big" id="amountPreview">${{ number_format($cuota->saldo_cuota, 0, ',', '.') }}</div>
                <div class="amount-sub" id="amountSub">Pago completo</div>
            </div>

            {{-- Saldo crédito --}}
            <div style="padding: 16px 22px; border-top: 1px solid var(--border);">
                <div class="summary-row">
                    <span class="summary-key">Saldo crédito actual</span>
                    <span class="summary-val danger">${{ number_format($cuota->credito->saldo_pendiente, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-key">Saldo después del pago</span>
                    <span class="summary-val" id="saldoAfter" style="color: var(--success); font-family: var(--font-mono);">
                        ${{ number_format($cuota->credito->saldo_pendiente - $cuota->saldo_cuota, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    const saldoCuota = {{ $cuota->saldo_cuota }};
    const saldoCredito = {{ $cuota->credito->saldo_pendiente }};
    const montoInput = document.getElementById('montoInput');
    const amountPreview = document.getElementById('amountPreview');
    const amountSub = document.getElementById('amountSub');
    const saldoAfter = document.getElementById('saldoAfter');

    function formatMoney(n) {
        return '$' + Math.round(n).toLocaleString('es-CO');
    }

    function setMonto(val) {
        montoInput.value = val;
        updatePreview();
    }

    function updatePreview() {
        const val = parseFloat(montoInput.value) || 0;
        amountPreview.textContent = formatMoney(val);

        if (val >= saldoCuota) {
            amountSub.textContent = '✓ Pago completo';
            amountSub.style.color = 'var(--success)';
            amountPreview.style.color = 'var(--success)';
        } else if (val > 0) {
            const restante = saldoCuota - val;
            amountSub.textContent = 'Pago parcial — queda ' + formatMoney(restante);
            amountSub.style.color = 'var(--warning)';
            amountPreview.style.color = 'var(--warning)';
        } else {
            amountSub.textContent = 'Ingresa el monto';
            amountSub.style.color = 'var(--text-3)';
            amountPreview.style.color = 'var(--text-3)';
        }

        const nuevoSaldo = Math.max(0, saldoCredito - val);
        saldoAfter.textContent = formatMoney(nuevoSaldo);
        saldoAfter.style.color = nuevoSaldo <= 0 ? 'var(--success)' : 'var(--accent)';

        montoInput.classList.toggle('valid', val > 0 && val <= saldoCuota);
    }

    montoInput.addEventListener('input', updatePreview);
    updatePreview();

    // GPS
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('latInput').value = pos.coords.latitude;
                document.getElementById('lonInput').value = pos.coords.longitude;
                document.getElementById('gpsDot').classList.add('active');
                document.getElementById('gpsText').textContent = 'Ubicación GPS obtenida ✓';
                document.getElementById('gpsText').style.color = 'var(--success)';
            },
            () => {
                document.getElementById('gpsText').textContent = 'GPS no disponible (opcional)';
            }
        );
    }

    // Submit confirm
    document.getElementById('pagoForm').addEventListener('submit', function(e) {
        const monto = parseFloat(montoInput.value) || 0;
        if (monto <= 0) { e.preventDefault(); alert('Ingresa un monto válido'); return; }
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    });
</script>
@endpush