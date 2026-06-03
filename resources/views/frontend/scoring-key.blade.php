@extends('layouts.app')
@section('title','Scoring Console')

@section('content')
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;padding:2rem 1.5rem;text-align:center;">

    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--g));display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:1.25rem;box-shadow:var(--gp);">
        🔐
    </div>
    <div style="font-size:1.15rem;font-weight:900;color:var(--txt);margin-bottom:.4rem;">Scoring Console</div>
    <div style="font-size:.8rem;color:var(--mut);margin-bottom:1.75rem;max-width:280px;line-height:1.6;">
        Enter the secret key provided by your administrator to access live scoring.
    </div>

    @if(session('error'))
    <div style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.25);border-radius:12px;padding:.75rem 1rem;color:var(--red);font-size:.8rem;margin-bottom:1rem;width:100%;max-width:320px;">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('frontend.scoring.verify') }}" style="width:100%;max-width:320px;">
        @csrf
        <div style="position:relative;margin-bottom:1rem;">
            <input type="password" name="key" id="scoring-key" placeholder="Enter secret key…"
                   value="{{ old('key') }}"
                   autocomplete="off" autocorrect="off" spellcheck="false"
                   style="width:100%;padding:.75rem 3rem .75rem 1rem;border-radius:12px;border:1.5px solid var(--bd);background:var(--s1);font-size:.95rem;font-family:inherit;color:var(--txt);outline:none;transition:border-color .2s;"
                   onfocus="this.style.borderColor='var(--p)'" onblur="this.style.borderColor='var(--bd)'">
            <button type="button" onclick="togglePw()" title="Show/hide"
                    style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--mut);font-size:1rem;padding:0;">
                <i class="bi bi-eye" id="pw-eye"></i>
            </button>
        </div>
        <button type="submit"
                style="width:100%;padding:.75rem;border-radius:12px;border:none;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;font-size:.95rem;font-weight:800;font-family:inherit;cursor:pointer;box-shadow:var(--gp);">
            Unlock Scoring
        </button>
    </form>

    <div style="margin-top:1.5rem;font-size:.72rem;color:var(--mut);">
        Key is set by admin in <strong>Settings → Scoring Key</strong>.
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePw() {
    var inp = document.getElementById('scoring-key');
    var eye = document.getElementById('pw-eye');
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        eye.className = 'bi bi-eye';
    }
}
</script>
@endpush
