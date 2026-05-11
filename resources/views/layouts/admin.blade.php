<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartPay Admin — @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    <style>
        :root {
            --bg-base:      #080910;
            --bg-card:      #10121a;
            --bg-card-2:    #181b28;
            --bg-input:     #1c1f30;
            --border:       #252840;
            --border-light: #31355a;
            --accent:       #7c5cbf;
            --accent-2:     #4f8ef7;
            --accent-glow:  rgba(124, 92, 191, 0.15);
            --success:      #22c55e;
            --success-soft: rgba(34, 197, 94, 0.12);
            --warning:      #f59e0b;
            --warning-soft: rgba(245, 158, 11, 0.12);
            --danger:       #ef4444;
            --danger-soft:  rgba(239, 68, 68, 0.12);
            --gold:         #f59e0b;
            --gold-soft:    rgba(245, 158, 11, 0.12);
            --text-1:       #eef0ff;
            --text-2:       #8b93c0;
            --text-3:       #525880;
            --font-main:    'DM Sans', sans-serif;
            --font-mono:    'Space Mono', monospace;
            --radius:       12px;
            --radius-lg:    18px;
            --shadow:       0 4px 24px rgba(0,0,0,0.5);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: var(--font-main); background: var(--bg-base); color: var(--text-1); font-size: 15px; line-height: 1.6; }

        /* ─── SIDEBAR ─────────────────────────────── */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 240px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 100;
            transition: transform .25s ease;
        }

        .sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
        .brand { display: flex; align-items: center; gap: 10px; }
        .logo-icon { width: 36px; height: 36px; background: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px var(--accent-glow); }
        .logo-icon i { color: white; font-size: 14px; }
        .brand-text { font-size: 18px; font-weight: 700; color: var(--text-1); letter-spacing: -0.5px; }
        .brand-text span { color: var(--accent); }
        .brand-badge { font-size: 9px; font-weight: 700; background: var(--accent-glow); color: var(--accent); border: 1px solid rgba(124,92,191,0.3); padding: 2px 6px; border-radius: 6px; letter-spacing: 1px; text-transform: uppercase; margin-left: 2px; }

        .sidebar-user { padding: 14px 16px; border-bottom: 1px solid var(--border); }
        .user-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--bg-card-2); border-radius: var(--radius); border: 1px solid var(--border); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-2)); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: white; flex-shrink: 0; }
        .user-info .name { font-size: 13px; font-weight: 600; color: var(--text-1); line-height: 1.2; }
        .user-info .role-badge { font-size: 10px; font-weight: 600; letter-spacing: 0.8px; color: var(--accent); text-transform: uppercase; }

        .sidebar-nav { padding: 12px; flex: 1; overflow-y: auto; }
        .nav-section-label { font-size: 10px; font-weight: 700; letter-spacing: 1.2px; color: var(--text-3); text-transform: uppercase; padding: 8px 8px 4px; margin-top: 8px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 10px; color: var(--text-2); font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; transition: all .15s; margin-bottom: 2px; border: 1px solid transparent; }
        .nav-item i { width: 18px; text-align: center; font-size: 14px; }
        .nav-item:hover { background: var(--bg-card-2); color: var(--text-1); border-color: var(--border); }
        .nav-item.active { background: var(--accent-glow); color: var(--accent); border-color: rgba(124,92,191,0.25); }
        .nav-item .badge { margin-left: auto; background: var(--danger); color: white; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 20px; line-height: 1.6; }

        .sidebar-footer { padding: 12px; border-top: 1px solid var(--border); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border-radius: 10px; background: none; border: 1px solid transparent; color: var(--text-2); font-size: 14px; font-family: var(--font-main); cursor: pointer; transition: all .15s; }
        .logout-btn:hover { background: var(--danger-soft); color: var(--danger); border-color: rgba(239,68,68,0.2); }

        /* ─── MAIN ─────────────────────────────────── */
        .main-wrap { margin-left: 240px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { position: sticky; top: 0; z-index: 50; background: rgba(8,9,16,0.88); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; }
        .topbar-title { font-size: 16px; font-weight: 600; color: var(--text-1); }
        .topbar-sub   { font-size: 12px; color: var(--text-2); margin-top: 1px; }
        .page-content { padding: 24px 28px; flex: 1; }

        /* ─── COMPONENTS ───────────────────────────── */
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
        .card-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 14px; font-weight: 600; color: var(--text-1); display: flex; align-items: center; gap: 8px; }
        .card-title i { color: var(--accent); }
        .card-body { padding: 22px; }

        .stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; position: relative; overflow: hidden; transition: border-color .2s; }
        .stat-card:hover { border-color: var(--border-light); }
        .stat-card::before { content: ''; position: absolute; inset: 0; background: var(--card-glow, transparent); pointer-events: none; }
        .stat-card.purple { --card-glow: radial-gradient(ellipse at top right, rgba(124,92,191,.10), transparent 60%); }
        .stat-card.blue   { --card-glow: radial-gradient(ellipse at top right, rgba(79,142,247,.08), transparent 60%); }
        .stat-card.green  { --card-glow: radial-gradient(ellipse at top right, rgba(34,197,94,.08),  transparent 60%); }
        .stat-card.amber  { --card-glow: radial-gradient(ellipse at top right, rgba(245,158,11,.08), transparent 60%); }
        .stat-card.red    { --card-glow: radial-gradient(ellipse at top right, rgba(239,68,68,.08),  transparent 60%); }
        .stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3); }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--text-1); font-family: var(--font-mono); letter-spacing: -1px; margin: 6px 0 4px; }
        .stat-value.money::before { content: '$'; font-size: 16px; color: var(--text-2); margin-right: 2px; font-family: var(--font-main); }
        .stat-meta { font-size: 12px; color: var(--text-2); }
        .stat-icon { position: absolute; top: 18px; right: 18px; font-size: 28px; opacity: 0.12; }

        .tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .tag.success { background: var(--success-soft); color: var(--success); }
        .tag.warning { background: var(--warning-soft); color: var(--warning); }
        .tag.danger  { background: var(--danger-soft);  color: var(--danger); }
        .tag.info    { background: var(--accent-glow);  color: var(--accent); }
        .tag.gold    { background: var(--gold-soft);    color: var(--gold); }

        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 10px; font-family: var(--font-main); font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; text-decoration: none; }
        .btn-primary { background: var(--accent); color: white; box-shadow: 0 0 20px rgba(124,92,191,0.25); }
        .btn-primary:hover { background: #6b4db0; transform: translateY(-1px); }
        .btn-secondary { background: var(--bg-card-2); color: var(--text-1); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--border-light); }
        .btn-success { background: var(--success-soft); color: var(--success); border: 1px solid rgba(34,197,94,0.3); }
        .btn-success:hover { background: rgba(34,197,94,0.2); }
        .btn-danger { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 8px; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-2); margin-bottom: 6px; letter-spacing: 0.3px; }
        .form-label .req { color: var(--danger); margin-left: 2px; }
        .form-control { width: 100%; padding: 10px 14px; background: var(--bg-input); border: 1.5px solid var(--border); border-radius: 10px; color: var(--text-1); font-family: var(--font-main); font-size: 14px; transition: border-color .15s; outline: none; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
        .form-control::placeholder { color: var(--text-3); }
        select.form-control option { background: var(--bg-card); }
        textarea.form-control { resize: vertical; }
        .form-error { color: var(--danger); font-size: 12px; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .form-hint  { color: var(--text-3); font-size: 12px; margin-top: 4px; }

        .form-grid { display: grid; gap: 16px; }
        .form-grid-2 { grid-template-columns: 1fr 1fr; }
        .form-grid-3 { grid-template-columns: 1fr 1fr 1fr; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-3); padding: 10px 16px; text-align: left; border-bottom: 1px solid var(--border); }
        .table td { padding: 13px 16px; border-bottom: 1px solid rgba(37,40,64,0.5); font-size: 14px; color: var(--text-1); }
        .table tr:last-child td { border-bottom: none; }
        .table tbody tr { transition: background .1s; }
        .table tbody tr:hover td { background: var(--bg-card-2); }

        .flash-message { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .flash-success { background: var(--success-soft); color: var(--success); border: 1px solid rgba(34,197,94,0.2); }
        .flash-error   { background: var(--danger-soft);  color: var(--danger);  border: 1px solid rgba(239,68,68,0.2); }

        .mobile-toggle { display: none; position: fixed; top: 14px; left: 14px; z-index: 200; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 8px 11px; cursor: pointer; color: var(--text-1); font-size: 16px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .mobile-toggle { display: flex; align-items: center; }
            .topbar { padding-left: 60px; }
            .page-content { padding: 18px 16px; }
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-light); border-radius: 10px; }

        .grid { display: grid; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .grid-5 { grid-template-columns: repeat(5, 1fr); gap: 14px; }

        @media (max-width: 1024px) { .grid-4, .grid-5 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .grid-2, .grid-3, .grid-4, .grid-5 { grid-template-columns: 1fr; } }

        .flex { display: flex; } .items-center { align-items: center; } .items-start { align-items: flex-start; }
        .gap-2 { gap: 8px; } .gap-3 { gap: 12px; } .gap-4 { gap: 16px; }
        .justify-between { justify-content: space-between; } .justify-end { justify-content: flex-end; }
        .ml-auto { margin-left: auto; } .flex-1 { flex: 1; }
        .mt-4 { margin-top: 16px; } .mt-6 { margin-top: 24px; }
        .mb-4 { margin-bottom: 16px; } .mb-6 { margin-bottom: 24px; }
        .text-sm { font-size: 13px; } .text-xs { font-size: 11px; }
        .font-mono { font-family: var(--font-mono); } .font-bold { font-weight: 700; }
        .w-full { width: 100%; } .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .text-accent { color: var(--accent); } .text-success { color: var(--success); }
        .text-danger { color: var(--danger); } .text-muted { color: var(--text-2); }

        .page-header { margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: var(--text-1); }
        .page-header p { font-size: 13px; color: var(--text-2); margin-top: 2px; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-3); }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 16px; }

        .pagination-wrap { margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .page-btn { padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-card-2); color: var(--text-2); text-decoration: none; transition: all .12s; }
        .page-btn:hover { border-color: var(--border-light); color: var(--text-1); }
        .page-btn.active { background: var(--accent-glow); border-color: rgba(124,92,191,0.3); color: var(--accent); }
        .page-btn.disabled { opacity: 0.4; pointer-events: none; cursor: default; }

        .progress-track { height: 6px; background: var(--bg-card-2); border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--accent), var(--accent-2)); }
    </style>

    @stack('styles')
</head>
<body>

<button class="mobile-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="brand">
            <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
            <div class="brand-text">Smart<span>Pay</span> <span class="brand-badge">Admin</span></div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="name">{{ auth()->user()->name }}</div>
                <div class="role-badge">Administrador</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>

        <div class="nav-section-label">Gestión</div>
        <a href="{{ route('admin.clientes.index') }}" class="nav-item {{ request()->routeIs('admin.clientes.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="{{ route('admin.creditos.index') }}" class="nav-item {{ request()->routeIs('admin.creditos.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Créditos
        </a>

        <div class="nav-section-label">Administración</div>
        <a href="{{ route('admin.usuarios.index') }}" class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> Usuarios
        </a>

        <div class="nav-section-label">Mi cuenta</div>
        <a href="{{ route('cobrador.dashboard') }}" class="nav-item">
            <i class="fas fa-arrow-right-arrow-left"></i> Vista cobrador
        </a>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-arrow-right-from-bracket"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div>
            <div class="topbar-title">@yield('title', 'Dashboard')</div>
            <div class="topbar-sub">{{ now()->isoFormat('dddd, D [de] MMMM YYYY') }}</div>
        </div>
        <div class="flex items-center gap-3">
            @yield('topbar-actions')
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="flash-message flash-success"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-message flash-error"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    toggle?.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) sidebar.classList.remove('open');
    });
    setTimeout(() => {
        document.querySelectorAll('.flash-message').forEach(el => {
            el.style.opacity = '0'; el.style.transition = 'opacity 0.5s';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>

@stack('scripts')
</body>
</html>