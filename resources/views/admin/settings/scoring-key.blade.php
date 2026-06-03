@extends('layouts.admin')
@section('title','Scoring Secret Key')
@section('page-title','Scoring Console — Secret Key')

@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-header" style="display:flex;align-items:center;gap:.625rem;">
        <i class="bi bi-shield-lock-fill" style="color:var(--rcl-primary);"></i>
        Frontend Scoring Console — Secret Key
    </div>
    <div class="rcl-card-body">
        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <p style="font-size:.82rem;color:var(--rcl-muted);margin-bottom:1.25rem;line-height:1.6;">
            This key protects the mobile scoring console at <code>/score</code>. Anyone who enters this key can
            record balls live. Choose something strong (min 6 chars). Change it anytime to lock out existing scorers.
        </p>

        <form method="POST" action="{{ route('admin.settings.scoring-key.save') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8rem;font-weight:700;margin-bottom:.4rem;">Secret Key</label>
                <div style="position:relative;">
                    <input type="password" name="key" id="key-input"
                           value="{{ old('key', $key) }}"
                           placeholder="Enter key (min 6 characters)…"
                           minlength="6" maxlength="64" required
                           style="width:100%;padding:.65rem 3rem .65rem .875rem;border:1px solid #d1d5db;border-radius:8px;font-size:.88rem;">
                    <button type="button" onclick="togglePw()"
                            style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:.95rem;color:#9ca3af;">
                        <i class="bi bi-eye" id="pw-eye"></i>
                    </button>
                </div>
                @error('key')<div style="color:red;font-size:.78rem;margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            @if($key)
            <div style="background:rgba(27,138,78,.06);border:1px solid rgba(27,138,78,.2);border-radius:8px;padding:.75rem;margin-bottom:1rem;font-size:.82rem;color:#166534;">
                <i class="bi bi-check-circle-fill"></i> A key is currently set.
                Scorers who entered the old key will be <strong>locked out</strong> on next browser session after you change it.
            </div>
            @endif

            <div style="display:flex;gap:.75rem;align-items:center;">
                <button type="submit" class="btn btn-rcl-primary">
                    <i class="bi bi-save"></i> Save Key
                </button>
                <a href="{{ route('admin.settings.meeting') }}" class="btn btn-rcl-secondary">
                    Back to Settings
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePw() {
    var inp = document.getElementById('key-input');
    var eye = document.getElementById('pw-eye');
    if (inp.type === 'password') { inp.type = 'text'; eye.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; eye.className = 'bi bi-eye'; }
}
</script>
@endpush
