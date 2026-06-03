@extends('layouts.app')
@section('title','Scoring Console — Select Match')

@section('content')

<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(212,144,10,.12);color:var(--g);">
                <i class="bi bi-broadcast"></i>
            </div>
            Live Scoring
        </div>
        <a href="{{ route('frontend.scoring.lock') }}"
           style="font-size:.72rem;color:var(--mut);text-decoration:none;display:flex;align-items:center;gap:.3rem;"
           onclick="return confirm('Lock scoring console?')">
            <i class="bi bi-lock-fill"></i> Lock
        </a>
    </div>
</div>

@if(session('success'))
<div style="margin:0 1rem .75rem;padding:.65rem .875rem;background:rgba(27,138,78,.08);border:1px solid rgba(27,138,78,.2);border-radius:10px;font-size:.8rem;color:var(--p);">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

@if($matches->isEmpty())
<div style="padding:3rem 1.5rem;text-align:center;color:var(--mut);">
    <i class="bi bi-calendar-x" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
    <div style="font-weight:700;">No scoreable matches</div>
    <div style="font-size:.8rem;margin-top:.3rem;">All upcoming and live matches will appear here.</div>
</div>
@else

@foreach($matches as $m)
<div style="margin:0 1rem .75rem;">
    <a href="{{ route('frontend.scoring.console', $m) }}" style="text-decoration:none;">
        <div class="card" style="padding:.875rem 1rem;display:flex;align-items:center;gap:.875rem;transition:box-shadow .2s;"
             onmouseenter="this.style.boxShadow='var(--gp)'" onmouseleave="this.style.boxShadow=''">
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;">
                    @if($m->status === 'live')
                    <span class="pill pill-red" style="font-size:.58rem;padding:.15em .55em;">LIVE</span>
                    @else
                    <span class="pill pill-gold" style="font-size:.58rem;padding:.15em .55em;">Upcoming</span>
                    @endif
                    <span style="font-size:.68rem;color:var(--mut);">{{ $m->edition?->name }} · #{{ $m->match_number }}</span>
                </div>
                <div style="font-weight:800;font-size:.92rem;color:var(--txt);">
                    {{ $m->homeTeam?->short_code ?? $m->homeTeam?->name }} vs {{ $m->awayTeam?->short_code ?? $m->awayTeam?->name }}
                </div>
                @if($m->venue)
                <div style="font-size:.72rem;color:var(--mut);margin-top:.2rem;">
                    <i class="bi bi-geo-alt-fill"></i> {{ $m->venue }}
                </div>
                @endif
            </div>
            <i class="bi bi-chevron-right" style="color:var(--mut);font-size:.9rem;flex-shrink:0;"></i>
        </div>
    </a>
</div>
@endforeach

@endif

<div style="height:1rem;"></div>
@endsection
