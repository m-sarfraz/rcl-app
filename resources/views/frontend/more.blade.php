@extends('layouts.app')
@section('title','More')

@section('content')
<div class="sec">
    <div class="grad" style="font-weight:900;font-size:1rem;margin-bottom:1rem;">Explore RCL</div>

    <div class="card" style="margin-bottom:.875rem;">
        <a href="{{ route('vcc') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(179,136,255,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--purple);">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">VCC Cabinet</div>
                <div style="font-size:.72rem;color:var(--mut);">Village Cricket Council leadership</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('stats') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(64,196,255,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--blue);">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Statistics</div>
                <div style="font-size:.72rem;color:var(--mut);">Batting, bowling &amp; fielding records</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('tournaments.index') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,214,0,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--g);">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">All Editions</div>
                <div style="font-size:.72rem;color:var(--mut);">Browse all tournament editions</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('captains') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(212,144,10,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--g);">
                <i class="bi bi-star-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Team Captains</div>
                <div style="font-size:.72rem;color:var(--mut);">Captains &amp; vice-captains by team</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('banned-bowlers') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--red);">
                <i class="bi bi-slash-circle-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Banned Bowlers</div>
                <div style="font-size:.72rem;color:var(--mut);">Bowling action bans &amp; restrictions</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('team-fines') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(212,144,10,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--g);">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Team Fines</div>
                <div style="font-size:.72rem;color:var(--mut);">Disciplinary fines by team</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('sponsors') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);border-bottom:1px solid var(--bd);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(212,144,10,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--g);">
                <i class="bi bi-award-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Sponsors</div>
                <div style="font-size:.72rem;color:var(--mut);">Our proud partners &amp; supporters</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
        <a href="{{ route('frontend.scoring') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--red);">
                <i class="bi bi-broadcast"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Live Scoring</div>
                <div style="font-size:.72rem;color:var(--mut);">Score matches live (key required)</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
    </div>

    @if(auth()->check())
    <div class="card" style="margin-bottom:.875rem;">
        <a href="{{ route('admin.dashboard') }}" style="display:flex;align-items:center;gap:.875rem;padding:.875rem;text-decoration:none;color:var(--txt);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,230,118,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--p);">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">Admin Panel</div>
                <div style="font-size:.72rem;color:var(--mut);">Manage matches, teams &amp; players</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);"></i>
        </a>
    </div>
    @endif

    <div class="card">
        <div style="padding:1.25rem;text-align:center;">
            <img src="/logo.jfif" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid var(--p);margin-bottom:.75rem;">
            <div style="font-weight:900;font-size:.95rem;" class="grad">Royal Champions League</div>
            <div style="font-size:.72rem;color:var(--mut);margin-top:.25rem;">Powered by Village Cricket Council (VCC)</div>
            <div style="font-size:.7rem;color:var(--mut);margin-top:.75rem;">
                @php $ed = \App\Models\Edition::where('is_current',true)->first(); @endphp
                {{ $ed ? $ed->edition_number.'th Edition' : 'RCL' }}
            </div>
        </div>
    </div>
</div>
@endsection
