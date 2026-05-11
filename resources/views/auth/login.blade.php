<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartPay — Iniciar sesión</title>

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

        .bg-mesh { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-mesh::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(79,142,247,0.12) 0%, transparent 70%);
            top: -200px; left: -100px;
            animation: float1 8s ease-in-out infinite;
        }
        .bg-mesh::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(124,92,191,0.10) 0%, transparent 70%);
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

        .login-wrap { width: 100%; max-width: 420px; }

        .brand {
            display: flex; align-items: center; gap: 12px;
            justify-content: center; margin-bottom: 32px;
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

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600;
            color: var(--text-2); margin-bottom: 8px;
            letter-spacing: 0.3px; text-transform: uppercase;
        }
        .form-label i { font-size: 11px; color: var(--text-3); }

        .input-wrap { position: relative; }

        .form-control {
            width: 100%; padding: 12px 14px 12px 42px;
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

        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; margin-top: -4px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-2); cursor: pointer; user-select: none;
        }
        .remember-label input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer;
        }
        .forgot-link {
            font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        .btn-submit {
            width: 100%; padding: 14px;
            background: var(--accent);
            color: white; font-family: var(--font-main);
            font-size: 15px; font-weight: 700;
            border: none; border-radius: 12px;
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 0 32px rgba(79,142,247,0.3);
        }
        .btn-submit:hover { background: #3a7af0; transform: translateY(-2px); box-shadow: 0 8px 40px rgba(79,142,247,0.4); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit.loading { pointer-events: none; opacity: 0.8; }

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
            margin-top: 6px; display: flex; align-items: center; gap: 4px;
        }
        .field-error i { font-size: 10px; }

        .register-link {
            text-align: center; margin-top: 20px;
            font-size: 13px; color: var(--text-2);
        }
        .register-link a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }

        .login-footer {
            text-align: center; margin-top: 28px;
            font-size: 12px; color: var(--text-3);
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.8s linear infinite; display: inline-block; }

        @media (max-width: 480px) {
            .card { padding: 28px 20px; }
            .brand-text { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>

<div class="page">
    <div class="login-wrap">

        <div class="brand">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            <div class="brand-text">Smart<span>Pay</span></div>
        </div>

        <div class="card">
            <div class="card-title">Bienvenido de nuevo</div>
            <div class="card-sub">Ingresa tus credenciales para continuar</div>

            @if (session('status'))
                <div class="alert-error" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2); color: #86efac;">
                    <i class="fas fa-circle-check" style="color: #22c55e;"></i>
                    {{ session('status') }}
                </div>
            @endif

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

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i> Correo electrónico
                    </label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-at"></i>
                        <input id="email" type="email" name="email" class="form-control"
                            value="{{ old('email') }}"
                            placeholder="usuario@ejemplo.com"
                            required autofocus autocomplete="username">
                    </div>
                    @error('email')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-wrap">
                        <i class="input-icon fas fa-lock"></i>
                        <input id="password" type="password" name="password" class="form-control"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                            style="padding-right: 42px;">
                        <button type="button" class="input-toggle" id="togglePassword" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-footer">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Recordarme
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Iniciar sesión
                </button>
            </form>

            @if (Route::has('register'))
            <div class="register-link">
                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
            </div>
            @endif
        </div>

        <div class="login-footer">
            SmartPay &copy; {{ date('Y') }} &nbsp;·&nbsp; Sistema de cobros
        </div>

    </div>
</div>

<script>
    const toggle = document.getElementById('togglePassword');
    const pwInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggle?.addEventListener('click', () => {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        toggleIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-circle-notch spin"></i> Verificando...';
    });
</script>

</body>
</html>