<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartPay — Crear cuenta</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg-base:      #0a0b10;
            --bg-card:      #12141c;
            --bg-card-2:    #1a1d2a;
            --bg-input:     #1e2130;
            --border:       #2a2e42;
            --border-light: #363b52;
            --accent:       #4f8ef7;
            --accent-glow:  rgba(79, 142, 247, 0.15);
            --accent-2:     #7c5cbf;
            --success:      #22c55e;
            --danger:       #ef4444;
            --danger-soft:  rgba(239, 68, 68, 0.12);
            --warning:      #f59e0b;
            --warning-soft: rgba(245,158,11,0.12);
            --text-1:       #f0f2ff;
            --text-2:       #8b92b3;
            --text-3:       #565d7e;
            --font-main:    'DM Sans', sans-serif;
            --font-mono:    'Space Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: var(--font-main);
            background: var(--bg-base);
            color: var(--text-1);
            font-size: 15px;
            line-height: 1.6;
        }

        .bg-mesh {
            position: fixed; inset: 0; z-index: 0; overflow: hidden;
        }
        .bg-mesh::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(79,142,247,0.10) 0%, transparent 70%);
            top: -200px; left: -100px;
            animation: float1 8s ease-in-out infinite;
        }
        .bg-mesh::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(124,92,191,0.08) 0%, transparent 70%);
            bottom: -150px; right: -100px;
            animation: float2 10s ease-in-out infinite;
        }
        @keyframes float1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(40px,30px)} }
        @keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-30px,-40px)} }

        .page {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }

        .register-wrap { width: 100%; max-width: 460px; }

        .brand {
            display: flex; align-items: center; gap: 12px;
            justify-content: center; margin-bottom: 28px;
        }
        .logo-icon {
            width: 44px; height: 44px; background: var(--accent);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 32px rgba(79,142,247,0.4);
        }
        .logo-icon i { color: white; font-size: 18px; }
        .brand-text { font-size: 26px; font-weight: 700; color: var(--text-1); letter-spacing: -0.5px; }
        .brand-text span { color: var(--accent); }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            position: relative; overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(79,142,247,0.4), transparent);
        }

        .card-title { font-size: 20px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; }
        .card-sub { font-size: 13px; color: var(--text-2); margin-bottom: 28px; }

        .form-group { margin-bottom: 16px; }

        .form-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600;
            color: var(--text-2); margin-bottom: 7px;
            letter-spacing: 0.3px; text-transform: uppercase;
        }

        .input-wrap { position: relative; }

        .form-control {
            width: 100%; padding: 11px 14px 11px 42px;
            background: var(--bg-input);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            color: var(--text-1);
            font-family: var(--font-main); font-size: 14px;
            transition: all .2s; outline: none;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(79,142,247,0.12);
            background: #1a1d2e;
        }
        .form-control::placeholder { color: var(--text-3); }
        .form-control.no-icon { padding-left: 14px; }

        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-3); font-size: 14px; pointer-events: none;
            transition: color .2s;
        }
        .input-wrap:focus-within .input-icon { color: var(--accent); }

        .input-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text-3); font-size: 14px; cursor: pointer;
            background: none; border: none; padding: 4px; transition: color .15s;
        }
        .input-toggle:hover { color: var(--text-2); }

        /* Grid para campos en 2 columnas */
        .form-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }

        /* Role selector */
        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 2px; }
        .role-opt { display: none; }
        .role-label {
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            padding: 14px 12px; border-radius: 12px;
            border: 2px solid var(--border); background: var(--bg-card-2);
            cursor: pointer; transition: all .15s; text-align: center;
        }
        .role-opt:checked + .role-label {
            border-color: var(--accent); background: var(--accent-glow); color: var(--accent);
        }
        .role-opt:checked + .role-label i { color: var(--accent); }
        .role-label i { font-size: 22px; color: var(--text-2); }
        .role-label .role-name { font-size: 13px; font-weight: 700; color: var(--text-1); }
        .role-label .role-desc { font-size: 11px; color: var(--text-2); }
        .role-opt:checked + .role-label .role-name,
        .role-opt:checked + .role-label .role-desc { color: var(--accent); }

        /* Admin password field (hidden by default) */
        .admin-field {
            display: none;
            background: var(--warning-soft);
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .admin-field.visible { display: block; }
        .admin-field-label {
            font-size: 12px; font-weight: 700; color: var(--warning);
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex; align-items: center; gap: 6px;
        }

        /* Errors */
        .alert-error {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px;
            background: var(--danger-soft);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            color: #fca5a5; font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-error i { font-size: 14px; color: var(--danger); margin-top: 1px; flex-shrink: 0; }
        .field-error {
            font-size: 12px; color: #fca5a5;
            margin-top: 5px;
            display: flex; align-items: center; gap: 4px;
        }
        .field-error i { font-size: 10px; }

        /* Submit */
        .btn-submit {
            width: 100%; padding: 14px;
            background: var(--accent);
            color: white; font-family: var(--font-main);
            font-size: 15px; font-weight: 700;
            border: none; border-radius: 12px;
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 0 32px rgba(79,142,247,0.3);
            margin-top: 6px;
        }
        .btn-submit:hover { background: #3a7af0; transform: translateY(-2px); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit.loading { pointer-events: none; opacity: 0.8; }

        .login-link {
            text-align: center; margin-top: 20px;
            font-size: 13px; color: var(--text-2);
        }
        .login-link a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        .login-footer {
            text-align: center; margin-top: 24px;
            font-size: 12px; color: var(--text-3);
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.8s linear infinite; display: inline-block; }

        @media (max-width: 520px) {
            .card { padding: 28px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .brand-text { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>

<div class="page">
    <div class="register-wrap">

        {{-- Logo --}}
        <div class="brand">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            <div class="brand-text">Smart<span>Pay</span></div>
        </div>

        <div class="card">
            <div class="card-title">Crear cuenta</div>
            <div class="card-sub">Completa los datos para registrarte en el sistema</div>

            @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                {{-- Nombre y Teléfono --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">
                            <i class="fas fa-user" style="font-size:11px;"></i> Nombre completo
                        </label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input id="name" type="text" name="name" class="form-control"
                                value="{{ old('name') }}" required autofocus
                                placeholder="Tu nombre">
                        </div>
                        @error('name')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone" style="font-size:11px;"></i> Teléfono
                        </label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-phone"></i>
                            <input id="phone" type="text" name="phone" class="form-control"
                                value="{{ old('phone') }}"
                                placeholder="3001234567">
                        </div>
                        @error('phone')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope" style="font-size:11px;"></i> Correo electrónico
                    </label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-at"></i>
                        <input id="email" type="email" name="email" class="form-control"
                            value="{{ old('email') }}" required
                            placeholder="correo@ejemplo.com"
                            autocomplete="username">
                    </div>
                    @error('email')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock" style="font-size:11px;"></i> Contraseña
                        </label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input id="password" type="password" name="password" class="form-control"
                                required autocomplete="new-password"
                                placeholder="••••••••"
                                style="padding-right: 42px;">
                            <button type="button" class="input-toggle" id="togglePassword" tabindex="-1">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">
                            <i class="fas fa-lock" style="font-size:11px;"></i> Confirmar
                        </label>
                        <div class="input-wrap">
                            <i class="input-icon fas fa-shield-check"></i>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                class="form-control" required autocomplete="new-password"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                {{-- Rol --}}
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-id-badge" style="font-size:11px;"></i> Tipo de cuenta
                    </label>
                    <div class="role-grid">
                        <div>
                            <input type="radio" name="role" id="role_cobrador" class="role-opt"
                                value="cobrador" {{ old('role', 'cobrador') === 'cobrador' ? 'checked' : '' }}>
                            <label for="role_cobrador" class="role-label">
                                <i class="fas fa-hand-holding-dollar"></i>
                                <div class="role-name">Cobrador</div>
                                <div class="role-desc">Gestiona cobros en campo</div>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="role" id="role_admin" class="role-opt"
                                value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                            <label for="role_admin" class="role-label">
                                <i class="fas fa-shield-halved"></i>
                                <div class="role-name">Administrador</div>
                                <div class="role-desc">Acceso total al sistema</div>
                            </label>
                        </div>
                    </div>
                    @error('role')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo contraseña maestra (solo visible si elige admin) --}}
                <div class="admin-field {{ old('role') === 'admin' ? 'visible' : '' }}" id="adminField">
                    <div class="admin-field-label">
                        <i class="fas fa-triangle-exclamation"></i>
                        Verificación de administrador
                    </div>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-key" style="color: var(--warning);"></i>
                        <input type="password" name="admin_password" id="adminPassword"
                            class="form-control"
                            placeholder="Contraseña de administrador"
                            autocomplete="off">
                    </div>
                    @error('admin_password')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                    <div style="font-size: 11px; color: var(--warning); margin-top: 8px; opacity: 0.8;">
                        <i class="fas fa-info-circle"></i>
                        Se requiere la clave maestra del sistema para crear cuentas de administrador.
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    Crear cuenta
                </button>
            </form>

            <div class="login-link">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>

        <div class="login-footer">
            SmartPay &copy; {{ date('Y') }} &nbsp;·&nbsp; Sistema de cobros
        </div>

    </div>
</div>

<script>
    // Toggle contraseña
    const toggle = document.getElementById('togglePassword');
    const pwInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    toggle?.addEventListener('click', () => {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        toggleIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    // Mostrar/ocultar campo de contraseña admin
    const roleOpts = document.querySelectorAll('input[name="role"]');
    const adminField = document.getElementById('adminField');
    const adminPassword = document.getElementById('adminPassword');

    roleOpts.forEach(opt => {
        opt.addEventListener('change', () => {
            if (opt.value === 'admin' && opt.checked) {
                adminField.classList.add('visible');
                adminPassword.required = true;
                adminPassword.focus();
            } else {
                adminField.classList.remove('visible');
                adminPassword.required = false;
                adminPassword.value = '';
            }
        });
    });

    // Si ya estaba seleccionado admin (al volver con error)
    if (document.getElementById('role_admin').checked) {
        adminPassword.required = true;
    }

    // Loading state
    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-circle-notch spin"></i> Creando cuenta...';
    });
</script>

</body>
</html>