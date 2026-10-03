@extends('layouts.admin')
@section('title','Scorecard')
@section('page-title','Match Scorecard')
@section('topbar-actions')
<a href="{{ route('admin.matches.manage', $match) }}" class="topbar-btn"><i class="bi bi-sliders"></i> Edit Deliveries & Match</a>
<a href="{{ route('scorecard.print', $match) }}" target="_blank" class="topbar-btn"><i class="bi bi-printer"></i> Print / Download</a>
@endsection
@section('content')
<div style="max-width:800px;">
    <div class="rcl-card mb-3">
        <div class="rcl-card-body" style="text-align:center;">
            <div style="font-weight:900;font-size:1.2rem;">{{ $match->homeTeam?->name }} vs {{ $match->awayTeam?->name }}</div>
            <div style="color:var(--rcl-muted);font-size:.85rem;margin-top:.25rem;">{{ $match->edition?->name }} • {{ $match->venue }} • Match #{{ $match->match_number }}</div>
            @if($match->winner)
            <div style="margin-top:.75rem;padding:.5rem;background:rgba(0,230,118,.08);border-radius:8px;font-weight:800;color:var(--rcl-primary);">
                {{ $match->winner->name }} won by {{ $match->result_margin }} {{ $match->result_type }}
            </div>
            @endif
        </div>
    </div>

    @foreach([$inn1, $inn2] as $inn)
    @if($inn)
    <div class="rcl-card mb-3">
        <div class="rcl-card-header" style="background:rgba(0,230,118,.08);">
            <span style="font-weight:800;color:var(--rcl-gold);">{{ $inn->battingTeam?->name }} — Innings {{ $inn->innings_number }}</span>
            <span style="font-weight:900;font-size:1rem;">{{ $inn->total_runs }}/{{ $inn->total_wickets }} ({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }})</span>
        </div>
        <div class="rcl-card-body p-0">
            <div style="padding:.5rem 1rem;font-size:.72rem;font-weight:700;color:var(--rcl-muted);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--rcl-border);">Batting</div>
            <table class="rcl-table">
                <thead><tr><th>Batter</th><th>Dismissal</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th></tr></thead>
                <tbody>
                    @foreach($inn->battingScorecards ?? [] as $sc)
                    <tr>
                        <td style="font-weight:700;">{{ $sc->player?->name }}</td>
                        <td style="font-size:.8rem;color:var(--rcl-muted);">
                            @if($sc->is_out) {{ $sc->dismissal_type }} b {{ $sc->bowledBy?->name }}
                            @else <span style="color:var(--rcl-primary);">not out</span>
                            @endif
                        </td>
                        <td style="font-weight:800;">{{ $sc->runs }}</td>
                        <td>{{ $sc->balls_faced }}</td>
                        <td>{{ $sc->fours }}</td>
                        <td>{{ $sc->sixes }}</td>
                        <td>{{ $sc->balls_faced > 0 ? number_format($sc->runs/$sc->balls_faced*100,1) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:.5rem 1rem;font-size:.72rem;font-weight:700;color:var(--rcl-muted);text-transform:uppercase;letter-spacing:.06em;border-top:1px solid var(--rcl-border);border-bottom:1px solid var(--rcl-border);">Bowling</div>
            <table class="rcl-table">
                <thead><tr><th>Bowler</th><th>O</th><th>M</th><th>R</th><th>W</th><th>Econ</th></tr></thead>
                <tbody>
                    @foreach($inn->bowlingScorecards ?? [] as $sc)
                    <tr>
                        <td style="font-weight:700;">{{ $sc->player?->name }}</td>
                        <td>{{ $sc->overs }}</td>
                        <td>{{ $sc->maidens ?? 0 }}</td>
                        <td>{{ $sc->runs_conceded }}</td>
                        <td style="font-weight:800;color:var(--rcl-primary);">{{ $sc->wickets }}</td>
                        <td>{{ $sc->economy ? number_format($sc->economy,2) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection
