@extends('layouts.app')
@section('title', $player->name)

@section('content')

{{-- Player Header --}}
<div class="player-header">
    @if($player->photo)
        <img src="{{ Storage::url($player->photo) }}" alt="{{ $player->name }}" class="player-avatar">
    @else
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--g));display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;color:#000;margin:0 auto;">
            {{ strtoupper(substr($player->name, 0, 2)) }}
        </div>
    @endif
    <div style="font-size:1.2rem;font-weight:900;margin-top:.75rem;">{{ $player->name }}</div>
    @if($player->jersey_number)
        <div style="font-size:.8rem;color:var(--g);font-weight:700;margin-top:.2rem;">#{{ $player->jersey_number }}</div>
    @endif
    <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:.625rem;flex-wrap:wrap;">
        <span class="pill pill-green">{{ ucwords(str_replace('_',' ',$player->role)) }}</span>
        @if($player->bowling_action_status === 'banned')
            <span class="pill pill-red">Action Banned</span>
        @elseif($player->bowling_action_status === 'flagged')
            <span class="pill pill-gold">Action Flagged</span>
        @endif
        @if(!$player->is_active)
            <span class="pill pill-muted">Inactive</span>
        @endif
    </div>
    @if($player->teams->isNotEmpty())
    <div style="margin-top:.75rem;font-size:.8rem;color:var(--mut);">
        Playing for <span style="color:var(--txt);font-weight:600;">{{ $player->teams->last()->name }}</span>
    </div>
    @endif
</div>

{{-- Quick Stats --}}
@if($player->editionStats->isNotEmpty())
@php
    $careerRuns    = $player->editionStats->sum('total_runs');
    $careerWkts    = $player->editionStats->sum('total_wickets');
    $careerMatches = $player->editionStats->max('matches_played');
@endphp
<div class="stat-row" style="padding:1rem 1rem .25rem;">
    <div class="stat-pill">
        <div class="val" style="color:var(--p);">{{ $careerRuns }}</div>
        <div class="lbl">Career Runs</div>
    </div>
    <div class="stat-pill">
        <div class="val" style="color:var(--g);">{{ $careerWkts }}</div>
        <div class="lbl">Career Wkts</div>
    </div>
    <div class="stat-pill">
        <div class="val">{{ $player->editionStats->sum('total_fours') }}</div>
        <div class="lbl">4s Hit</div>
    </div>
    <div class="stat-pill">
        <div class="val">{{ $player->editionStats->sum('total_sixes') }}</div>
        <div class="lbl">6s Hit</div>
    </div>
    <div class="stat-pill">
        <div class="val" style="color:var(--g);">{{ $player->editionStats->sum('mvp_count') }}</div>
        <div class="lbl">MVP Awards</div>
    </div>
</div>
@endif

{{-- Bio --}}
@if($player->bio)
<div class="sec" style="padding-bottom:0;">
    <div class="card" style="padding:.875rem;">
        <div style="font-size:.72rem;color:var(--mut);margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">About</div>
        <div style="font-size:.875rem;line-height:1.6;color:var(--txt);">{{ $player->bio }}</div>
    </div>
</div>
@endif

{{-- Player Details --}}
<div class="sec" style="padding-bottom:0;">
    <div class="card">
        @php
            $details = [
                ['icon'=>'bi-person-lines-fill','label'=>'Batting Style','val'=>ucwords(str_replace('_',' ',$player->batting_style))],
                ['icon'=>'bi-arrow-repeat','label'=>'Bowling Style','val'=>ucwords(str_replace('_',' ',$player->bowling_style))],
            ];
            if($player->date_of_birth) $details[] = ['icon'=>'bi-calendar-heart','label'=>'Date of Birth','val'=>$player->date_of_birth->format('d M Y')];
        @endphp
        @foreach($details as $d)
        <div style="display:flex;align-items:center;gap:.875rem;padding:.75rem 1rem;border-bottom:1px solid var(--bd);">
            <i class="bi {{ $d['icon'] }}" style="color:var(--mut);font-size:1rem;width:20px;text-align:center;"></i>
            <div style="flex:1;">
                <div style="font-size:.7rem;color:var(--mut);">{{ $d['label'] }}</div>
                <div style="font-size:.875rem;font-weight:600;">{{ $d['val'] }}</div>
            </div>
        </div>
        @endforeach
        <div style="display:flex;align-items:center;gap:.875rem;padding:.75rem 1rem;">
            <i class="bi bi-shield-check" style="color:var(--mut);font-size:1rem;width:20px;text-align:center;"></i>
            <div>
                <div style="font-size:.7rem;color:var(--mut);">Eligibility</div>
                @if($player->isEligible())
                    <div style="font-size:.875rem;font-weight:600;color:var(--p);">Eligible to play</div>
                @else
                    <div style="font-size:.875rem;font-weight:600;color:var(--red);">Not eligible</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Edition Stats --}}
@if($player->editionStats->isNotEmpty())
<div class="sec" style="padding-bottom:0;">
    <div class="sec-hd" style="margin-bottom:.5rem;">
        <div class="sec-title">Edition Stats</div>
    </div>
    <div class="card" style="overflow-x:auto;">
        <table class="sc-table" style="min-width:380px;">
            <thead>
                <tr>
                    <th style="text-align:left;">Edition</th>
                    <th>M</th><th>Runs</th><th>Wkts</th><th>4s</th><th>6s</th><th>MVP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($player->editionStats->sortByDesc(fn($s)=>$s->edition?->edition_number) as $stat)
                <tr>
                    <td style="font-weight:600;white-space:nowrap;">{{ $stat->edition?->name ?? 'Edition '.$stat->edition_id }}</td>
                    <td style="text-align:center;">{{ $stat->matches_played }}</td>
                    <td style="text-align:center;font-weight:700;color:var(--p);">{{ $stat->total_runs }}</td>
                    <td style="text-align:center;font-weight:700;color:var(--g);">{{ $stat->total_wickets }}</td>
                    <td style="text-align:center;">{{ $stat->total_fours }}</td>
                    <td style="text-align:center;">{{ $stat->total_sixes }}</td>
                    <td style="text-align:center;color:var(--g);font-weight:700;">{{ $stat->mvp_count ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Unpaid Fines --}}
@if($player->fines->isNotEmpty())
<div class="sec" style="padding-bottom:0;">
    <div style="background:rgba(255,77,77,.05);border:1px solid rgba(255,77,77,.3);border-radius:14px;padding:.875rem;">
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.5rem;">
            <i class="bi bi-exclamation-triangle-fill" style="color:var(--red);"></i>
            <div style="font-weight:700;font-size:.875rem;color:var(--red);">Unpaid Fines ({{ $player->fines->count() }})</div>
        </div>
        @foreach($player->fines as $fine)
        <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:.35rem 0;border-bottom:1px solid rgba(255,77,77,.1);">
            <span style="color:var(--txt);">{{ $fine->reason }}</span>
            <span style="font-weight:700;color:var(--red);">PKR {{ number_format($fine->amount) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<div style="height:1rem;"></div>
@endsection
