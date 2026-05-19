@extends('layouts.admin')

@section('title', 'Créditos')

@section('topbar-actions')
    <a href="{{ route('admin.creditos.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Nuevo crédito
    </a>
@endsection

@section('content')

<div class="grid grid-4 mb-6">
    <div class="stat-card purple">
        <div class="stat-label">Total créditos</div>
        <div class="stat-value">{{ $totalCreditos }}</div>
        <div class="stat-icon"><i class="fas fa-file-invoice-dollar" style="color:var(--accent);"></i></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Activos</div>
        <div class="stat-value" style="color:var(--accent-2);">{{ $creditosActivos }}</div>
        <div class="stat-icon"><i class="fas fa-circle-play" style="color:var(--accent-2);"></i></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">En mora</div>
        <div class="stat-value" style="color:var(--danger);">{{ $creditosMora }}</div>
        <div class="stat-icon"><i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Cartera activa</div>
        <div class="stat-value money" style="font-size:20px;">{{ number_format($carteraTotal, 0, ',', '.') }}</div>
        <div class="stat-icon"><i class="fas fa-vault" style="color:var(--success);"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Listado de créditos</div>
        <span class="tag info">{{ $creditos->total() }} registros</span>
    </div>

    <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
        <form method="GET" class="flex items-center gap-2" style="flex-wrap:wrap;">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Código, cliente..."
                class="form-control" style="width:220px;">
            <select name="estado" class="form-control" style="width:140px;">
                <option value="">Todo estado</option>
                @foreach(['activo','al_dia','mora','pagado','cancelado'] as $est)
                    <option value="{{ $est }}" {{ request('estado') === $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                @endforeach
            </select>
            <select name="cobrador_id" class="form-control" style="width:180px;">
                <option value="">Todos los cobradores</option>
                @foreach($cobradores as $c)
                    <option value="{{ $c->id }}" {{ request('cobrador_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            @if(request()->hasAny(['buscar','estado','cobrador_id']))
                <a href="{{ route('admin.creditos.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i></a>
            @endif
        </form>
    </div>

    @if($creditos->count() > 0)
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Cobrador</th>
                <th>Monto</th>
                <th>Cuotas</th>
                <th>Saldo</th>
                <th>Frecuencia</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($creditos as $credito)
            @php $colors = ['activo'=>'info','al_dia'=>'success','mora'=>'danger','pagado'=>'success','cancelado'=>'warning']; @endphp
            <tr>
                <td style="font-family:var(--font-mono); font-size:12px; color:var(--text-2);">{{ $credito->codigo }}</td>
                <td style="font-weight:600;">{{ $credito->cliente->nombre }}</td>
                <td style="font-size:13px; color:var(--text-2);">{{ $credito->cobrador->name ?? '—' }}</td>
                <td style="font-family:var(--font-mono);">${{ number_format($credito->monto_prestado, 0, ',', '.') }}</td>
                <td style="color:var(--text-2);">{{ $credito->cuotasPagadas() }}/{{ $credito->num_cuotas }}</td>
                <td style="font-family:var(--font-mono); font-weight:700; color:{{ $credito->estado === 'mora' ? 'var(--danger)' : 'var(--accent)' }};">
                    ${{ number_format($credito->saldo_pendiente, 0, ',', '.') }}
                </td>
                <td style="font-size:12px; color:var(--text-2);">{{ ucfirst($credito->frecuencia) }}</td>
                <td><span class="tag {{ $colors[$credito->estado] ?? 'info' }}" style="font-size:10px;">{{ ucfirst($credito->estado) }}</span></td>
                <td>
                    <a href="{{ route('admin.creditos.show', $credito) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    @if($creditos->hasPages())
    <div style="padding:16px 20px; border-top:1px solid var(--border);">
        <div class="pagination-wrap" style="margin-top:0;">
            @if($creditos->onFirstPage()) <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
            @else <a href="{{ $creditos->previousPageUrl() }}" class="page-btn"><i class="fas fa-chevron-left"></i></a> @endif
            <span style="font-size:13px; color:var(--text-2);">Página {{ $creditos->currentPage() }} de {{ $creditos->lastPage() }}</span>
            @if($creditos->hasMorePages()) <a href="{{ $creditos->nextPageUrl() }}" class="page-btn"><i class="fas fa-chevron-right"></i></a>
            @else <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span> @endif
        </div>
    </div>
    @endif

    @else
    <div class="empty-state">
        <i class="fas fa-file-circle-xmark" style="color:var(--text-3);"></i>
        <p style="font-size:16px; font-weight:600; color:var(--text-2); margin-bottom:6px;">No se encontraron créditos</p>
        <a href="{{ route('admin.creditos.create') }}" class="btn btn-primary" style="margin-top:12px;"><i class="fas fa-plus"></i> Crear primer crédito</a>
    </div>
    @endif
</div>

@endsection