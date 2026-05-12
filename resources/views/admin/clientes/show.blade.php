@extends('layouts.admin')

@section('title', $cliente->nombre)

@section('topbar-actions')
    <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Clientes</a>
    <a href="{{ route('admin.creditos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo crédito
    </a>
    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-secondary btn-sm"><i class="fas fa-pen"></i> Editar</a>
@endsection

@section('content')

{{-- Hero --}}
<div class="card" style="margin-bottom:20px; padding:28px; display:flex; align-items:flex-start; gap:24px; position:relative; overflow:hidden;">
    <div style="position:absolute; top:0; right:0; width:200px; height:100%; background:radial-gradient(ellipse at right center, var(--accent-glow), transparent 70%); pointer-events:none;"></div>
    <div style="width:72px; height:72px; border-radius:20px; background:{{ $cliente->enMora() ? 'linear-gradient(135deg,#ef4444,#b91c1c)' : 'linear-gradient(135deg,var(--accent),var(--accent-2))' }}; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:white; flex-shrink:0; box-shadow:0 0 30px var(--accent-glow);">
        {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
    </div>
    <div style="flex:1;">
        <div style="font-size:24px; font-weight:700; color:var(--text-1); margin-bottom:4px;">{{ $cliente->nombre }}</div>
        <div style="font-size:13px; color:var(--text-2); margin-bottom:12px;">CC {{ $cliente->cedula ?? 'Sin cédula' }} · Cobrador: {{ $cliente->cobrador->name ?? '—' }}</div>
        <div style="display:flex; flex-wrap:wrap; gap:12px;">
            @if($cliente->telefono)
            <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-2);">
                <i class="fas fa-phone" style="color:var(--accent);"></i>
                <a href="tel:{{ $cliente->telefono }}" style="color:inherit;">{{ $cliente->telefono }}</a>
            </div>
            @endif
            @if($cliente->direccion)
            <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-2);">
                <i class="fas fa-location-dot" style="color:var(--accent);"></i> {{ $cliente->direccion }}
                @if($cliente->barrio) — {{ $cliente->barrio }} @endif
            </div>
            @endif
        </div>
    </div>
    <div>
        @if($cliente->enMora()) <span class="tag danger"><i class="fas fa-triangle-exclamation"></i> En mora</span>
        @else <span class="tag {{ $cliente->estado === 'activo' ? 'success' : 'warning' }}">{{ ucfirst($cliente->estado) }}</span>
        @endif
    </div>
</div>

{{-- Créditos --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Créditos ({{ $cliente->creditos->count() }})</div>
        <a href="{{ route('admin.creditos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Nuevo crédito</a>
    </div>
    @if($cliente->creditos->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Monto</th>
                <th>Cuotas</th>
                <th>Saldo</th>
                <th>Frecuencia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cliente->creditos as $credito)
            @php $colors = ['activo'=>'info','al_dia'=>'success','mora'=>'danger','pagado'=>'success','cancelado'=>'warning']; @endphp
            <tr>
                <td style="font-family:var(--font-mono); color:var(--text-2);">{{ $credito->codigo }}</td>
                <td style="font-family:var(--font-mono);">${{ number_format($credito->monto_prestado, 0, ',', '.') }}</td>
                <td>{{ $credito->cuotasPagadas() }}/{{ $credito->num_cuotas }}</td>
                <td style="font-family:var(--font-mono); color:{{ $credito->estaEnMora() ? 'var(--danger)' : 'var(--accent)' }}; font-weight:700;">
                    ${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}
                </td>
                <td>{{ ucfirst($credito->frecuencia) }}</td>
                <td><span class="tag {{ $colors[$credito->estado] ?? 'info' }}" style="font-size:10px;">{{ ucfirst($credito->estado) }}</span></td>
                <td>
                    <a href="{{ route('admin.creditos.show', $credito) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state" style="padding:30px;">
        <i class="fas fa-file-circle-xmark" style="font-size:28px; color:var(--text-3); display:block; margin-bottom:8px;"></i>
        Sin créditos. <a href="{{ route('admin.creditos.create', ['cliente_id' => $cliente->id]) }}" style="color:var(--accent);">Crear uno ahora</a>
    </div>
    @endif
</div>

{{-- Pagos recientes --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-receipt"></i> Últimos pagos</div>
        <span class="tag info">{{ $cliente->pagos->count() }} registros</span>
    </div>
    <div class="card-body">
        @forelse($cliente->pagos as $pago)
        <div style="display:flex; align-items:center; gap:14px; padding:10px 0; border-bottom:1px solid var(--border);">
            <div style="width:36px; height:36px; border-radius:10px; background:var(--success-soft); color:var(--success); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0;">
                <i class="fas fa-receipt"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:13px; font-weight:600;">{{ $pago->recibo_numero }}</div>
                <div style="font-size:11px; color:var(--text-2);">{{ $pago->fecha_pago->format('d/m/Y h:i A') }} · {{ ucfirst($pago->metodo_pago) }}</div>
            </div>
            <div style="font-family:var(--font-mono); font-size:15px; font-weight:700; color:var(--success);">${{ number_format($pago->monto_pagado, 0, ',', '.') }}</div>
        </div>
        @empty
        <div style="text-align:center; padding:20px; color:var(--text-3);">Sin pagos registrados</div>
        @endforelse
    </div>
</div>

@endsection