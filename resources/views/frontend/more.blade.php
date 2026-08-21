@extends('layouts.app')
@section('title','More')

@section('content')
<div class="sec">
    <div style="font-size:.68rem;color:var(--mut);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:1rem;">Explore RCL</div>

    <div class="card" style="overflow:hidden;margin-bottom:.75rem;">
        @php
        $links = [
            ['route'=>'vcc',           'icon'=>'bi-person-badge-fill', 'color'=>'rgba(206,147,216,.12)', 'ic'=>'var(--pur)', 'title'=>'VCC Cabinet',     'sub'=>'Village Cricket Council leadership'],
            ['route'=>'stats',         'icon'=>'bi-bar-chart-fill',    'color'=>'rgba(64,196,255,.12)',  'ic'=>'var(--blue)','title'=>'Statistics',       'sub'=>'Batting, bowling & fielding records'],
            ['route'=>'tournaments.index','icon'=>'bi-trophy-fill',    'color'=>'rgba(255,202,40,.12)', 'ic'=>'var(--g)',   'title'=>'All Editions',     'sub'=>'Browse all tournament editions'],
            ['route'=>'captains',      'icon'=>'bi-star-fill',         'color'=>'rgba(255,202,40,.12)', 'ic'=>'var(--g)',   'title'=>'Team Captains',    'sub'=>'Captains & vice-captains by team'],
            ['route'=>'banned-bowlers','icon'=>'bi-slash-circle-fill', 'color'=>'rgba(255,82,82,.12)',  'ic'=>'var(--red)', 'title'=>'Banned Bowlers',   'sub'=>'Bowling action bans & restrictions'],
            ['route'=>'team-fines',    'icon'=>'bi-cash-stack',        'color'=>'rgba(255,202,40,.12)', 'ic'=>'var(--g)',   'title'=>'Team Fines',       'sub'=>'Disciplinary fines by team'],
            ['route'=>'sponsors',      'icon'=>'bi-award-fill',        'color'=>'rgba(255,202,40,.12)', 'ic'=>'var(--g)',   'title'=>'Sponsors',         'sub'=>'Our proud partners & supporters'],
        ];
        @endphp

        @foreach($links as $i => $link)
        <a href="{{ route($link['route']) }}"
           style="display:flex;align-items:center;gap:.875rem;padding:.875rem 1rem;text-decoration:none;color:var(--txt);
                  {{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,.05);' : '' }}
                  transition:background .15s;"
           onmouseover="this.style.background='rgba(255,255,255,.03)'" onmouseout="this.style.background=''">
            <div style="width:40px;height:40px;border-radius:12px;background:{{ $link['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="bi {{ $link['icon'] }}" style="color:{{ $link['ic'] }};"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;color:var(--txt);">{{ $link['title'] }}</div>
                <div style="font-size:.7rem;color:var(--mut);margin-top:.08rem;">{{ $link['sub'] }}</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);font-size:.85rem;"></i>
        </a>
        @endforeach
    </div>

    @if(auth()->check())
    <div class="card" style="overflow:hidden;margin-bottom:.75rem;">
        <a href="{{ route('admin.dashboard') }}"
           style="display:flex;align-items:center;gap:.875rem;padding:.875rem 1rem;text-decoration:none;color:var(--txt);">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(0,230,118,.12);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="bi bi-shield-lock-fill" style="color:var(--p);"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;color:var(--txt);">Admin Panel</div>
                <div style="font-size:.7rem;color:var(--mut);margin-top:.08rem;">Manage matches, teams & players</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);font-size:.85rem;"></i>
        </a>
    </div>
    @endif

    <div class="card" style="padding:1.5rem 1rem;text-align:center;">
        <img src="/logo.jfif" style="width:54px;height:54px;border-radius:50%;object-fit:cover;margin-bottom:.875rem;border:2px solid rgba(0,230,118,.4);box-shadow:0 0 24px rgba(0,230,118,.2);">
        <div style="font-weight:900;font-size:.95rem;margin-bottom:.25rem;" class="grad">Royal Champions League</div>
        <div style="font-size:.7rem;color:var(--mut);">Village Cricket Council · Pakistan</div>
        <div style="font-size:.6rem;color:var(--mut);margin-top:.875rem;padding-top:.875rem;border-top:1px solid rgba(255,255,255,.06);">
            Developed by <span style="color:var(--p);font-weight:700;">Sarfraz Jutt</span>
        </div>
    </div>
</div>

<div style="height:.5rem;"></div>
@endsection
