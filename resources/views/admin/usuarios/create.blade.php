@extends('layouts.admin')

@section('title', 'Nuevo Usuario')

@section('topbar-actions')
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
@endsection

@section('content')

<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-plus"></i> Crear nuevo usuario</div>
        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="flash-message flash-error" style="margin-bottom: 20px;">
                <i class="fas fa-circle-exclamation"></i>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf

                <div style="margin-bottom: 22px;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                        <i class="fas fa-user" style="margin-right: 6px; color: var(--accent);"></i> Datos personales
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre completo <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name') }}" required autofocus
                                placeholder="Nombre del usuario">
                            @error('name')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone') }}" placeholder="3001234567">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electrónico <span class="req">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                        @error('email')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom: 22px;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                        <i class="fas fa-lock" style="margin-right: 6px; color: var(--accent);"></i> Seguridad
                    </div>
                    <div class="form-grid form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Contraseña <span class="req">*</span></label>
                            <input type="password" name="password" class="form-control"
                                required placeholder="Mínimo 8 caracteres">
                            @error('password')<div class="form-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar contraseña <span class="req">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                required placeholder="Repetir contraseña">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 22px;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-3); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                        <i class="fas fa-id-badge" style="margin-right: 6px; color: var(--accent);"></i> Rol
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        @foreach(['cobrador' => ['fas fa-hand-holding-dollar', 'Cobrador', 'Gestiona cobros en campo'],
                                  'admin' => ['fas fa-shield-halved', 'Administrador', 'Acceso total al sistema']] as $val => $info)
                        <div>
                            <input type="radio" name="role" id="role_{{ $val }}" value="{{ $val }}"
                                class="role-opt" style="display: none;"
                                {{ old('role', 'cobrador') === $val ? 'checked' : '' }}>
                            <label for="role_{{ $val }}" class="role-card"
                                style="display: flex; flex-direction: column; align-items: center; gap: 8px;
                                    padding: 16px 12px; border-radius: 12px; border: 2px solid var(--border);
                                    background: var(--bg-card-2); cursor: pointer; transition: all .15s; text-align: center;">
                                <i class="{{ $info[0] }}" style="font-size: 22px; color: var(--text-2);"></i>
                                <div style="font-size: 13px; font-weight: 700; color: var(--text-1);">{{ $info[1] }}</div>
                                <div style="font-size: 11px; color: var(--text-2);">{{ $info[2] }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('role')<div class="form-error" style="margin-top: 8px;"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>@enderror
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border);">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check"></i> Crear usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Highlight selected role
    document.querySelectorAll('input[name="role"]').forEach(radio => {
        const label = document.querySelector(`label[for="${radio.id}"]`);
        if (radio.checked) {
            label.style.borderColor = 'var(--accent)';
            label.style.background = 'var(--accent-glow)';
        }
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name="role"]').forEach(r => {
                const l = document.querySelector(`label[for="${r.id}"]`);
                l.style.borderColor = 'var(--border)';
                l.style.background = 'var(--bg-card-2)';
            });
            label.style.borderColor = 'var(--accent)';
            label.style.background = 'var(--accent-glow)';
        });
    });
</script>
@endpush

@endsection