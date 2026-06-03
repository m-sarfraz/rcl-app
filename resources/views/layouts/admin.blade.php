<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') — RCL Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --rcl-primary: #00e676;
            --rcl-gold: #ffd600;
            --rcl-dark: #0d1117;
            --rcl-surface: #161b22;
            --rcl-surface2: #21262d;
            --rcl-border: #30363d;
            --rcl-text: #e6edf3;
            --rcl-muted: #8b949e;
            --sidebar-w: 260px;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--rcl-dark); color: var(--rcl-text); margin: 0; }

        /* Sidebar */
        #sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar-w); height: 100vh;
            background: var(--rcl-surface); border-right: 1px solid var(--rcl-border);
            overflow-y: auto; z-index: 1000; transition: transform .3s ease;
            display: flex; flex-direction: column;
        }
        #sidebar .brand {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--rcl-border);
            display: flex; align-items: center; gap: .75rem;
        }
        #sidebar .brand .logo-circle {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #000; font-size: .85rem; flex-shrink: 0;
        }
        #sidebar .brand span { font-weight: 700; font-size: 1rem; color: var(--rcl-text); }
        #sidebar .brand small { font-size: .7rem; color: var(--rcl-muted); display: block; margin-top: -2px; }

        .nav-section-label {
            padding: .5rem 1.5rem .25rem; font-size: .65rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase; color: var(--rcl-muted);
        }
        #sidebar .nav-link {
            display: flex; align-items: center; gap: .625rem; padding: .5rem 1.5rem;
            color: var(--rcl-muted); border-radius: 0; transition: all .15s;
            font-size: .875rem; font-weight: 500; text-decoration: none;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            color: var(--rcl-primary); background: rgba(0,230,118,.08);
        }
        #sidebar .nav-link i { font-size: 1rem; width: 20px; text-align: center; }

        /* Topbar */
        #topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0; height: 56px;
            background: var(--rcl-surface); border-bottom: 1px solid var(--rcl-border);
            display: flex; align-items: center; padding: 0 1.5rem; gap: 1rem; z-index: 999;
        }
        #topbar .page-title { font-weight: 700; font-size: 1rem; flex: 1; }
        #topbar .topbar-btn {
            background: var(--rcl-surface2); border: 1px solid var(--rcl-border);
            color: var(--rcl-text); padding: .375rem .75rem; border-radius: 6px;
            font-size: .8rem; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: .4rem;
        }
        #topbar .topbar-btn:hover { border-color: var(--rcl-primary); color: var(--rcl-primary); }

        /* Main content */
        #main-content { margin-left: var(--sidebar-w); padding-top: 56px; min-height: 100vh; }
        .content-area { padding: 1.5rem; }

        /* Cards */
        .rcl-card {
            background: var(--rcl-surface); border: 1px solid var(--rcl-border);
            border-radius: 10px; overflow: hidden;
        }
        .rcl-card-header {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--rcl-border);
            font-weight: 600; font-size: .9rem; display: flex; align-items: center; justify-content: space-between;
        }
        .rcl-card-body { padding: 1.25rem; }

        /* Stat cards */
        .stat-card {
            background: var(--rcl-surface); border: 1px solid var(--rcl-border);
            border-radius: 10px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;
        }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }

        /* Table */
        .rcl-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .rcl-table thead th {
            background: var(--rcl-surface2); padding: .75rem 1rem;
            font-weight: 600; font-size: .75rem; text-transform: uppercase;
            letter-spacing: .05em; color: var(--rcl-muted); white-space: nowrap;
            border-bottom: 1px solid var(--rcl-border);
        }
        .rcl-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--rcl-border); vertical-align: middle; }
        .rcl-table tbody tr:hover { background: var(--rcl-surface2); }
        .rcl-table tbody tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge-rcl { padding: .3em .65em; border-radius: 5px; font-size: .72rem; font-weight: 600; }
        .badge-live { background: rgba(0,230,118,.15); color: var(--rcl-primary); }
        .badge-upcoming { background: rgba(255,214,0,.15); color: var(--rcl-gold); }
        .badge-completed { background: rgba(100,116,139,.15); color: var(--rcl-muted); }
        .badge-paid { background: rgba(0,230,118,.15); color: var(--rcl-primary); }
        .badge-unpaid { background: rgba(239,68,68,.15); color: #ef4444; }

        /* Forms */
        .form-label { font-size: .8rem; font-weight: 600; color: var(--rcl-muted); margin-bottom: .35rem; }
        .form-control, .form-select {
            background: var(--rcl-surface2); border: 1px solid var(--rcl-border);
            color: var(--rcl-text); border-radius: 6px; padding: .5rem .75rem; font-size: .875rem;
        }
        .form-control:focus, .form-select:focus {
            background: var(--rcl-surface2); color: var(--rcl-text);
            border-color: var(--rcl-primary); box-shadow: 0 0 0 3px rgba(0,230,118,.15); outline: none;
        }
        .form-control::placeholder { color: var(--rcl-muted); }
        .form-select option { background: var(--rcl-surface2); }

        /* Buttons */
        .btn-rcl-primary { background: var(--rcl-primary); border: none; color: #000; font-weight: 700; border-radius: 6px; padding: .5rem 1.25rem; }
        .btn-rcl-primary:hover { background: #00c853; color: #000; }
        .btn-rcl-danger { background: rgba(239,68,68,.15); border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; padding: .375rem .75rem; font-size: .8rem; }
        .btn-rcl-danger:hover { background: #ef4444; color: #fff; }
        .btn-rcl-secondary { background: var(--rcl-surface2); border: 1px solid var(--rcl-border); color: var(--rcl-text); border-radius: 6px; padding: .5rem 1.25rem; }
        .btn-rcl-secondary:hover { border-color: var(--rcl-primary); color: var(--rcl-primary); }

        /* Alert */
        .alert-rcl-success { background: rgba(0,230,118,.1); border: 1px solid rgba(0,230,118,.3); color: var(--rcl-primary); border-radius: 8px; padding: .75rem 1rem; }
        .alert-rcl-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #ef4444; border-radius: 8px; padding: .75rem 1rem; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--rcl-dark); }
        ::-webkit-scrollbar-thumb { background: var(--rcl-border); border-radius: 3px; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #topbar, #main-content { margin-left: 0; left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div id="sidebar">
    <div class="brand">
        <div class="logo-circle">RCL</div>
        <div>
            <span>RCL Admin</span>
            <small>Village Cricket Council</small>
        </div>
    </div>

    <nav class="py-2 flex-1">
        <div class="nav-section-label">Main</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section-label mt-2">Tournament</div>
        <a href="{{ route('admin.editions.index') }}" class="nav-link {{ request()->routeIs('admin.editions.*') ? 'active' : '' }}">
            <i class="bi bi-trophy"></i> Editions
        </a>
        <a href="{{ route('admin.matches.index') }}" class="nav-link {{ request()->routeIs('admin.matches.*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-event"></i> Matches
        </a>
        <a href="{{ route('admin.polls.index') }}" class="nav-link {{ request()->routeIs('admin.polls.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i> Polls
        </a>

        <div class="nav-section-label mt-2">Roster</div>
        <a href="{{ route('admin.teams.index') }}" class="nav-link {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
            <i class="bi bi-shield-fill"></i> Teams
        </a>
        <a href="{{ route('admin.players.index') }}" class="nav-link {{ request()->routeIs('admin.players.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Players
        </a>
        <a href="{{ route('admin.captains.index') }}" class="nav-link {{ request()->routeIs('admin.captains.*') ? 'active' : '' }}">
            <i class="bi bi-star-fill"></i> Captains
        </a>

        <div class="nav-section-label mt-2">Disciplinary</div>
        <a href="{{ route('admin.fines.index') }}" class="nav-link {{ request()->routeIs('admin.fines.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle-fill"></i> Fines
        </a>
        <a href="{{ route('admin.banned-bowlers.index') }}" class="nav-link {{ request()->routeIs('admin.banned-bowlers.*') ? 'active' : '' }}">
            <i class="bi bi-slash-circle-fill"></i> Banned Bowlers
        </a>
        <a href="{{ route('admin.finance.index') }}" class="nav-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Finance
        </a>

        <div class="nav-section-label mt-2">VCC</div>
        <a href="{{ route('admin.vcc.index') }}" class="nav-link {{ request()->routeIs('admin.vcc.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge-fill"></i> VCC Cabinet
        </a>
        <a href="{{ route('admin.sponsors.index') }}" class="nav-link {{ request()->routeIs('admin.sponsors.*') ? 'active' : '' }}">
            <i class="bi bi-award-fill"></i> Sponsors
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <i class="bi bi-megaphone-fill"></i> Notifications
        </a>
        <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
            <i class="bi bi-image-fill"></i> Banners
        </a>

        <div class="nav-section-label mt-2">Settings</div>
        <a href="{{ route('admin.settings.meeting') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-megaphone-fill"></i> Meeting Notice
        </a>

        <div class="nav-section-label mt-2">System</div>
        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock-fill"></i> Roles & Users
        </a>
        <a href="/" target="_blank" class="nav-link">
            <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>
    </nav>

    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--rcl-border);">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--rcl-primary),var(--rcl-gold));display:flex;align-items:center;justify-content:center;font-weight:700;color:#000;font-size:.75rem;">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
            </div>
            <div style="flex:1;overflow:hidden;">
                <div style="font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div style="font-size:.7rem;color:var(--rcl-muted);">{{ auth()->user()->role?->name ?? 'Super Admin' }}</div>
            </div>
            <a href="{{ route('admin.logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="color:var(--rcl-muted);font-size:1rem;" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
</div>

<div id="topbar">
    <button class="btn p-0 d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')" style="color:var(--rcl-text);background:none;border:none;font-size:1.25rem;">
        <i class="bi bi-list"></i>
    </button>
    <div class="page-title">@yield('page-title','Dashboard')</div>
    @yield('topbar-actions')
</div>

<div id="main-content">
    <div class="content-area">
        @if(session('success'))
            <div class="alert-rcl-success mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-rcl-error mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-rcl-error mb-3">
                <i class="bi bi-exclamation-circle-fill"></i>
                <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>
@stack('scripts')
</body>
</html>
