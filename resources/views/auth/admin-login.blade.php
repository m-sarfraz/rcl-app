<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — RCL</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary:#00e676; --gold:#ffd600; --dark:#0d1117; --surface:#161b22; --surface2:#21262d; --border:#30363d; --text:#e6edf3; --muted:#8b949e; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 60% 50% at 50% 0%, rgba(0,230,118,.05) 0%, transparent 70%); pointer-events:none; }
        .login-box { width:100%; max-width:420px; padding:1.5rem; }
        .login-header { text-align:center; margin-bottom:2rem; }
        .login-logo { width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--gold));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:#000;margin:0 auto 1rem;box-shadow:0 0 40px rgba(0,230,118,.3); }
        .login-title { font-size:1.5rem; font-weight:800; }
        .login-sub { color:var(--muted); font-size:.85rem; margin-top:.25rem; }
        .card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:2rem; }
        .form-group { margin-bottom:1.25rem; }
        label { display:block; font-size:.8rem; font-weight:600; color:var(--muted); margin-bottom:.4rem; }
        .input-wrap { position:relative; }
        .input-wrap i { position:absolute; left:.875rem; top:50%; transform:translateY(-50%); color:var(--muted); font-size:.95rem; }
        input { width:100%; background:var(--surface2); border:1px solid var(--border); color:var(--text); border-radius:8px; padding:.65rem .875rem .65rem 2.5rem; font-size:.9rem; font-family:inherit; transition:border-color .2s; }
        input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,230,118,.15); }
        input::placeholder { color:var(--muted); }
        .btn-login { width:100%; background:linear-gradient(135deg,var(--primary),var(--gold)); border:none; color:#000; font-weight:800; font-size:.95rem; padding:.75rem; border-radius:8px; cursor:pointer; transition:opacity .2s; font-family:inherit; margin-top:.5rem; }
        .btn-login:hover { opacity:.9; }
        .error-msg { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3); color:#ef4444; border-radius:8px; padding:.75rem 1rem; font-size:.85rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
        .divider { display:flex; align-items:center; gap:.75rem; margin:1.25rem 0; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:var(--border); }
        .divider span { font-size:.75rem; color:var(--muted); }
        .btn-google { width:100%; background:var(--surface2); border:1px solid var(--border); color:var(--text); font-weight:600; font-size:.9rem; padding:.65rem; border-radius:8px; cursor:pointer; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:.625rem; text-decoration:none; transition:border-color .2s; }
        .btn-google:hover { border-color:var(--primary); color:var(--primary); }
        .security-note { text-align:center; font-size:.72rem; color:var(--muted); margin-top:1.25rem; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-header">
        <div class="login-logo">RCL</div>
        <div class="login-title">Admin Panel</div>
        <div class="login-sub">Royal Champions League — VCC</div>
    </div>

    <div class="card">
        @if($errors->any())
            <div class="error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}</div>
        @endif
        @if(session('error'))
            <div class="error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit', ['hash' => $hash]) }}">
            @csrf
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@rcl.com" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-shield-lock-fill"></i> Sign In to Admin Panel
            </button>
        </form>

        <div class="divider"><span>or continue with</span></div>

        <a href="{{ route('auth.google.redirect') }}" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Sign in with Google
        </a>
    </div>

    <div class="security-note">
        <i class="bi bi-shield-check"></i> Secure encrypted session — RCL Admin v35
    </div>
</div>
</body>
</html>
