@extends('layouts.app')
@section('title', $match->homeTeam?->short_code . ' vs ' . $match->awayTeam?->short_code)

@section('content')

{{-- Match Header --}}
<div style="background:linear-gradient(180deg,rgba(0,230,118,.08) 0%,var(--dark) 100%);padding:1rem;">
    <div style="text-align:center;margin-bottom:.75rem;">
        <div style="font-size:.7rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;">{{ $match->edition?->name }} · Match #{{ $match->match_number }}</div>
        <div style="font-weight:900;font-size:1.1rem;margin:.25rem 0;">{{ $match->homeTeam?->name }} vs {{ $match->awayTeam?->name }}</div>
        <div style="font-size:.75rem;color:var(--mut);">{{ $match->venue }}</div>
    </div>

    @if($match->status === 'completed')
    <div style="text-align:center;padding:.625rem;background:rgba(0,230,118,.06);border-radius:10px;border:1px solid rgba(0,230,118,.2);">
        @if($match->winner)
        <div style="font-weight:800;font-size:.95rem;color:var(--p);">{{ $match->winner->name }} won by {{ $match->result_margin }} {{ $match->result_type }}</div>
        @elseif($match->result_type==='tie')
        <div style="font-weight:800;color:var(--g);">Match Tied</div>
        @else
        <div style="color:var(--mut);">No Result</div>
        @endif
    </div>
    @elseif($match->status === 'live')
    <div style="text-align:center;"><span class="live-badge">Live</span></div>
    @endif
</div>

@foreach([$inn1, $inn2] as $inn)
@if($inn)
<div class="sec" style="padding-top:.5rem;">
    <div style="font-weight:800;font-size:.85rem;margin-bottom:.625rem;color:var(--g);">
        {{ $inn->battingTeam?->name }} Innings
        <span style="font-weight:900;font-size:1rem;color:var(--txt);float:right;">{{ $inn->total_runs }}/{{ $inn->total_wickets }} ({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }})</span>
    </div>

    {{-- Batting --}}
    <div class="card" style="margin-bottom:.625rem;overflow:hidden;">
        <div style="padding:.5rem .75rem;font-size:.65rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);">Batting</div>
        <table class="sc-table">
            <thead>
                <tr><th style="text-align:left;">Batter</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th></tr>
            </thead>
            <tbody>
                @foreach($inn->battingScorecards ?? [] as $sc)
                <tr>
                    <td style="max-width:120px;">
                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;{{ !$sc->is_out ? 'color:var(--p);' : '' }}">{{ $sc->player?->name }}</div>
                        @if($sc->is_out)
                        <div style="font-size:.62rem;color:var(--mut);">{{ $sc->dismissal_type }} b {{ $sc->bowledBy?->name }}</div>
                        @else
                        <div style="font-size:.62rem;color:var(--p);">not out</div>
                        @endif
                    </td>
                    <td style="font-weight:800;">{{ $sc->runs }}</td>
                    <td style="color:var(--mut);">{{ $sc->balls_faced }}</td>
                    <td>{{ $sc->fours }}</td>
                    <td>{{ $sc->sixes }}</td>
                    <td style="color:var(--mut);font-size:.75rem;">{{ $sc->balls_faced > 0 ? number_format($sc->runs/$sc->balls_faced*100,1) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:.5rem .75rem;font-size:.72rem;color:var(--mut);border-top:1px solid var(--bd);">
            Extras: {{ $inn->total_extras ?? 0 }}
        </div>
    </div>

    {{-- Bowling --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:.5rem .75rem;font-size:.65rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);">Bowling</div>
        <table class="sc-table">
            <thead>
                <tr><th style="text-align:left;">Bowler</th><th>O</th><th>R</th><th>W</th><th>Econ</th></tr>
            </thead>
            <tbody>
                @foreach($inn->bowlingScorecards ?? [] as $sc)
                <tr>
                    <td style="max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $sc->player?->name }}</td>
                    <td>{{ $sc->overs }}</td>
                    <td>{{ $sc->runs_conceded }}</td>
                    <td style="font-weight:800;color:var(--p);">{{ $sc->wickets }}</td>
                    <td style="color:var(--mut);font-size:.75rem;">{{ $sc->economy ? number_format($sc->economy,2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

{{-- Download --}}
<div class="sec" style="text-align:center;">
    <a href="{{ route('scorecard.print', $match) }}" target="_blank"
       class="pill pill-green" style="padding:.5rem 1.25rem;font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:.375rem;">
        <i class="bi bi-download"></i> Download Scorecard
    </a>
</div>

@endsection
