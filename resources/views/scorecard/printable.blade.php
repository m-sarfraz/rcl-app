<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scorecard — {{ $match->homeTeam?->short_code }} vs {{ $match->awayTeam?->short_code }}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; background:#fff; color:#111; font-size:13px; padding:1.5rem; }
        .header { text-align:center; border-bottom:3px solid #00c853; padding-bottom:1rem; margin-bottom:1rem; }
        .header h1 { font-size:1.3rem; font-weight:900; color:#0d1117; }
        .header h2 { font-size:1rem; font-weight:700; color:#00c853; margin:.25rem 0; }
        .header p  { font-size:.8rem; color:#555; }
        .match-result { background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:.625rem 1rem; text-align:center; font-weight:700; color:#166534; margin-bottom:1rem; }
        .section-title { font-weight:800; font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:#00c853; margin:.875rem 0 .375rem; }
        table { width:100%; border-collapse:collapse; font-size:.82rem; }
        thead th { background:#0d1117; color:#fff; padding:.4rem .6rem; font-weight:700; font-size:.75rem; text-align:left; }
        tbody td { padding:.4rem .6rem; border-bottom:1px solid #e5e7eb; }
        tbody tr:last-child td { border-bottom:none; }
        .innings-header { display:flex; justify-content:space-between; align-items:center; background:#f3f4f6; padding:.5rem .75rem; border-radius:6px; margin:.5rem 0; }
        .innings-title  { font-weight:800; font-size:.9rem; }
        .innings-score  { font-weight:900; font-size:1.1rem; color:#00c853; }
        .not-out { color:#00c853; font-weight:700; }
        .footer { text-align:center; font-size:.7rem; color:#9ca3af; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:.75rem; }
        @media print { body { padding:.5rem; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Royal Champions League (RCL)</h1>
        <h2>{{ $match->edition?->name }}</h2>
        <p>Match #{{ $match->match_number }} • {{ $match->venue }} • {{ $match->scheduled_at?->format('d M Y') }}</p>
        <p style="font-weight:700;margin-top:.25rem;">{{ $match->homeTeam?->name }} vs {{ $match->awayTeam?->name }}</p>
    </div>

    @if($match->winner || $match->result_type)
    <div class="match-result">
        @if($match->winner) {{ $match->winner->name }} won by {{ $match->result_margin }} {{ $match->result_type }}
        @elseif($match->result_type==='tie') Match Tied
        @else No Result @endif
    </div>
    @endif

    @foreach([$inn1, $inn2] as $inn)
    @if($inn)
    <div class="innings-header">
        <span class="innings-title">{{ $inn->battingTeam?->name }} — Innings {{ $inn->innings_number }}</span>
        <span class="innings-score">{{ $inn->total_runs }}/{{ $inn->total_wickets }} ({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }})</span>
    </div>

    <div class="section-title">Batting</div>
    <table>
        <thead><tr><th>Batter</th><th>Dismissal</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th></tr></thead>
        <tbody>
            @foreach($inn->battingScorecards ?? [] as $sc)
            <tr>
                <td style="font-weight:700;">{{ $sc->player?->name }}</td>
                <td>
                    @if($sc->is_out) {{ $sc->dismissal_type }} b {{ $sc->bowledBy?->name }}
                    @else <span class="not-out">not out</span>
                    @endif
                </td>
                <td style="font-weight:800;">{{ $sc->runs }}</td>
                <td>{{ $sc->balls_faced }}</td>
                <td>{{ $sc->fours }}</td>
                <td>{{ $sc->sixes }}</td>
                <td>{{ $sc->balls_faced > 0 ? number_format($sc->runs/$sc->balls_faced*100,1) : '-' }}</td>
            </tr>
            @endforeach
            <tr style="background:#f9fafb;font-weight:700;"><td colspan="2">Extras</td><td colspan="5">{{ $inn->total_extras ?? 0 }}</td></tr>
            <tr style="background:#0d1117;color:#fff;font-weight:900;"><td colspan="2">Total</td><td>{{ $inn->total_runs }}/{{ $inn->total_wickets }}</td><td colspan="4">({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov)</td></tr>
        </tbody>
    </table>

    <div class="section-title">Bowling</div>
    <table>
        <thead><tr><th>Bowler</th><th>O</th><th>M</th><th>R</th><th>W</th><th>Econ</th></tr></thead>
        <tbody>
            @foreach($inn->bowlingScorecards ?? [] as $sc)
            <tr>
                <td style="font-weight:700;">{{ $sc->player?->name }}</td>
                <td>{{ $sc->overs }}</td>
                <td>{{ $sc->maidens ?? 0 }}</td>
                <td>{{ $sc->runs_conceded }}</td>
                <td style="font-weight:800;color:#00c853;">{{ $sc->wickets }}</td>
                <td>{{ $sc->economy ? number_format($sc->economy,2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach

    <div class="footer">
        Generated by Royal Champions League (RCL) — Village Cricket Council · {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
