@extends('layouts.admin')

@section('title', 'Abrir Caja')

@section('topbar-actions')
    <a href="{{ route('admin.cajas.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver a cajas
    </a>
@endsection

@push('styles')
<style>
    .create-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .create-layout { grid-template-columns: 1fr; }
    }

    .info-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: sticky;
        top: 80px;
    }

    .cobrador-preview {
        padding: 20px;
        border-bottom: 1px solid var(--border);
        text-align: center;
        background: linear-gradient(135deg, var(--accent-glow), transparent);
    }

    .preview-avatar {
        width: 56px; height: 56px; border-radius: 16px;
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700; color: white;
        margin: 0 auto 12px;
    }

    .preview-name { font-size: 15px; font-weight: 700; color: var(--text-1); }
    .preview-cn   { font-size: 12px; color: var(--accent); margin-top: 2px; font-family: var(--font-mono); }
    .preview-sector { font-size: 12px; color: var(--text-2); margin-top: 4px; }

    .info-row {
        padding: 12px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; justify-content: space-between; align-items: center;
        font-size: 13px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-key { color: var(--text-2); }
    .info-val { font-weight: 700; color: var(--text-1); font-family: var(--font-mono); }
    .info-val.danger { color: var(--danger); }

    .alerta-caja {
        margin-bottom: 16px;
        padding: 12px 16px;
        background: var(--warning-soft);
        border: 1px solid rgba(245,158,11,0.25);
        border-radius: var(--radius);
        font-size: 13px;
        color: var(--warning);
        display: flex; gap: 10px; align-items: flex-start;
    }
</style>
@endpush

@section('content')

<div class="create-layout">

    {{-- Formulario --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-cash-register"></i> Abrir caja para cobrador
            </div>
        </div>
        <div class="card-body">

            @if(session('error'))
            <div class="flash-message flash-error" style="margin-bottom:16px;">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.cajas.store') }}">
                @csrf

                {{-- Cobrador --}}
                <div class="form-group">
                    <label class="form-label">Cobrador *</label>
                    <select name="cobrador_id" id="selectCobrador"
                        class="form-control @error('cobrador_id') is-invalid @enderror"
                        required onchange="actualizarPreview(this)">
                        <option value="">— Seleccionar cobrador —</option>
                        @foreach($cobradores as $c)
                        <option value="{{ $c->id }}"
                            data-nombre="{{ $c->name }}"
                            data-cn="{{ $c->cn ?? '—' }}"
                            data-sector="{{ $c->sector?->nombre ?? 'Sin sector' }}"
                            data-clientes="{{ $c->clientes_count ?? 0 }}"
                            {{ old('cobrador_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}{{ $c->cn ? ' · ' . $c->cn : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('cobrador_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Sector (opcional, se hereda del cobrador si no se especifica) --}}
                <div class="form-group">
                    <label class="form-label">Sector <span style="color:var(--text-3); font-weight:400;">(opcional — hereda el del cobrador)</span></label>
                    <select name="sector_id" class="form-control @error('sector_id') is-invalid @enderror">
                        <option value="">— Heredar del cobrador —</option>
                        @foreach($sectores as $s)
                        <option value="{{ $s->id }}" {{ old('sector_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }} · {{ $s->codigo }}
                        </option>
                        @endforeach
                    </select>
                    @error('sector_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Monto inicial --}}
                <div class="form-group">
                    <label class="form-label">Monto inicial *</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%);
                                     color:var(--text-2); font-weight:700; pointer-events:none;">$</span>
                        <input type="number" name="monto_inicial" id="montoInicial"
                            class="form-control @error('monto_inicial') is-invalid @enderror"
                            style="padding-left:28px;"
                            value="{{ old('monto_inicial', 0) }}"
                            min="0" step="1000" required
                            onchange="actualizarMonto(this.value)">
                    </div>
                    <span class="form-hint">Efectivo con el que sale el cobrador a campo.</span>
                    @error('monto_inicial')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Montos rápidos --}}
                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;">
                    @foreach([50000, 100000, 150000, 200000, 300000] as $monto)
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="setMonto({{ $monto }})">
                        ${{ number_format($monto, 0, ',', '.') }}
                    </button>
                    @endforeach
                </div>

                {{-- Notas --}}
                <div class="form-group">
                    <label class="form-label">Notas de apertura <span style="color:var(--text-3); font-weight:400;">(opcional)</span></label>
                    <textarea name="notas_apertura" class="form-control" rows="3"
                        placeholder="Observaciones iniciales, instrucciones especiales...">{{ old('notas_apertura') }}</textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-unlock"></i> Abrir caja
                    </button>
                    <a href="{{ route('admin.cajas.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel derecho: preview del cobrador --}}
    <div>
        <div class="info-card">
            <div class="cobrador-preview" id="previewBox">
                <div class="preview-avatar" id="previewAvatar" style="background: var(--bg-card-2);">
                    <i class="fas fa-user" style="color:var(--text-3); font-size:20px;"></i>
                </div>
                <div class="preview-name" id="previewNombre" style="color:var(--text-3);">Selecciona un cobrador</div>
                <div class="preview-cn"   id="previewCn"></div>
                <div class="preview-sector" id="previewSector"></div>
            </div>

            <div class="info-row">
                <span class="info-key">Monto inicial</span>
                <span class="info-val success" id="previewMonto">$0</span>
            </div>
            <div class="info-row">
                <span class="info-key">Fecha jornada</span>
                <span class="info-val">{{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Hora apertura</span>
                <span class="info-val" id="previewHora">{{ now()->format('H:i') }}</span>
            </div>
        </div>

        <div class="alerta-caja" style="margin-top:14px;">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Un cobrador solo puede tener <strong>una caja abierta</strong> por jornada. Si ya tiene una, se mostrará un error.</span>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const GRADIENTS = [
    'linear-gradient(135deg,#7c5cbf,#4f8ef7)',
    'linear-gradient(135deg,#22c55e,#15803d)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#ef4444,#b91c1c)',
    'linear-gradient(135deg,#06b6d4,#0284c7)',
];

function actualizarPreview(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) {
        document.getElementById('previewAvatar').innerHTML = '<i class="fas fa-user" style="color:var(--text-3); font-size:20px;"></i>';
        document.getElementById('previewAvatar').style.background = 'var(--bg-card-2)';
        document.getElementById('previewNombre').textContent = 'Selecciona un cobrador';
        document.getElementById('previewNombre').style.color = 'var(--text-3)';
        document.getElementById('previewCn').textContent = '';
        document.getElementById('previewSector').textContent = '';
        return;
    }

    const nombre = opt.dataset.nombre;
    const idx    = parseInt(opt.value) % GRADIENTS.length;

    document.getElementById('previewAvatar').innerHTML = nombre.charAt(0).toUpperCase();
    document.getElementById('previewAvatar').style.background = GRADIENTS[idx];
    document.getElementById('previewNombre').textContent = nombre;
    document.getElementById('previewNombre').style.color = 'var(--text-1)';
    document.getElementById('previewCn').textContent     = opt.dataset.cn !== '—' ? opt.dataset.cn : '';
    document.getElementById('previewSector').textContent = opt.dataset.sector;
}

function setMonto(val) {
    document.getElementById('montoInicial').value = val;
    actualizarMonto(val);
}

function actualizarMonto(val) {
    const formatted = '$' + Number(val).toLocaleString('es-CO');
    document.getElementById('previewMonto').textContent = formatted;
}

// Actualizar hora cada minuto
setInterval(() => {
    const now = new Date();
    document.getElementById('previewHora').textContent =
        now.getHours().toString().padStart(2,'0') + ':' +
        now.getMinutes().toString().padStart(2,'0');
}, 60000);
</script>
@endpush