@extends('layouts.app')
@section('title','Schedule')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,214,0,.12);color:var(--g);">
                <i class="bi bi-calendar3"></i>
            </div>
            Schedule
        </div>
    </div>

    <div style="display:flex;gap:.375rem;margin-bottom:.875rem;overflow-x:auto;" class="hide-scroll">
        @foreach(['all'=>'All','upcoming'=>'Upcoming','live'=>'Live','completed'=>'Results'] as $s => $label)
        <a href="{{ route('schedule', ['status'=>$s]) }}"
           style="flex-shrink:0;padding:.35rem .875rem;border-radius:20px;font-size:.75rem;font-weight:600;text-decoration:none;white-space:nowrap;
                  border:1px solid {{ (request('status')==$s||($s==='all'&&!request('status'))) ? 'var(--p)' : 'var(--bd)' }};
                  background:{{ (request('status')==$s||($s==='all'&&!request('status'))) ? 'rgba(0,230,118,.15)' : 'var(--s2)' }};
                  color:{{ (request('status')==$s||($s==='all'&&!request('status'))) ? 'var(--p)' : 'var(--mut)' }};">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @forelse($matches as $m)
    <div class="match-card {{ $m->status==='live' ? 'match-card-live' : ($m->status==='upcoming' ? 'match-card-upcoming' : '') }} card-hover"
         onclick="window.location='{{ route('scorecard',$m) }}'">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
            @if($m->status==='live')
                <span class="live-badge">Live</span>
            @elseif($m->status==='upcoming')
                <span style="font-size:.7rem;color:var(--g);font-weight:700;">{{ $m->scheduled_at?->format('D, d M · h:i A') }}</span>
            @else
                <span style="font-size:.68rem;color:var(--mut);">{{ $m->scheduled_at?->format('d M Y') }}</span>
            @endif
            <span style="font-size:.68rem;color:var(--mut);">{{ $m->match_type ? ucfirst(str_replace('_',' ',$m->match_type)) : '' }}</span>
        </div>
        <div class="teams-row">
            <div style="flex:1;"><div class="team-name">{{ $m->homeTeam?->name }}</div></div>
            <div class="vs-pill">VS</div>
            <div style="flex:1;text-align:right;"><div class="team-name" style="text-align:right;">{{ $m->awayTeam?->name }}</div></div>
        </div>
        <div class="match-meta" style="margin-top:.4rem;">
            <span><i class="bi bi-geo-alt"></i> {{ $m->venue }}</span>
            <span>{{ $m->overs_per_side }} ov</span>
            @if($m->status==='completed' && $m->winner)
                <span style="color:var(--p);">{{ $m->winner->name }} won</span>
            @endif
        </div>
    </div>
    @empty
    <div style="padding:3rem;text-align:center;color:var(--mut);">
        <i class="bi bi-calendar-x" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
        No matches found
    </div>
    @endforelse

    <div style="margin-top:.75rem;">{{ $matches->withQueryString()->links() }}</div>
</div>
@endsection
