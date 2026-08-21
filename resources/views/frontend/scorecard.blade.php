@extends('layouts.app')
@section('title', ($match->homeTeam?->short_code ?? '?') . ' vs ' . ($match->awayTeam?->short_code ?? '?'))

@php
    /*
     | Social preview. The image is rendered live from the current score, and the
     | ?v= fingerprint changes whenever anything visible changes — that is what
     | lets a re-shared link show a newer score instead of the cached first one.
     */
    $ogTitle = trim(sprintf('%s v %s', $match->homeTeam?->name ?? 'TBD', $match->awayTeam?->name ?? 'TBD'));
    $ogDesc  = $scoreboard['headline'] ?? '';
    if ($match->venue) {
        $ogDesc .= '  ·  ' . $match->venue;
    }
    $ogImage = route('embed.image', $match) . '?v=' . ($scoreboard['revision'] ?? '1');
@endphp

@section('social')
    <meta property="og:site_name" content="Royal Champions League">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDesc }}">
    <meta property="og:url" content="{{ route('scorecard', $match) }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $ogTitle }} — live scoreboard">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="description" content="{{ $ogDesc }}">
    <link rel="alternate" type="application/json+oembed"
          href="{{ route('embed.oembed', ['url' => route('scorecard', $match)]) }}"
          title="{{ $ogTitle }}">
    @if($match->status === 'live')
        {{-- A live page should not sit in a proxy cache. --}}
        <meta http-equiv="refresh" content="30">
    @endif
@endsection

@section('content')

{{-- Match header --}}
<div style="background:linear-gradient(180deg,rgba(0,230,118,.07) 0%,transparent 100%);padding:1.25rem 1rem 1rem;">
    <div style="text-align:center;margin-bottom:.875rem;">
        <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.3rem;">
            {{ $match->edition?->name }} · Match #{{ $match->match_number }}
        </div>
        <div style="font-weight:900;font-size:1.15rem;color:var(--txt);letter-spacing:-.01em;">
            {{ $match->homeTeam?->name }} <span style="color:var(--mut);font-weight:400;font-size:.9rem;">vs</span> {{ $match->awayTeam?->name }}
        </div>
        @if($match->venue)
        <div style="font-size:.72rem;color:var(--mut);margin-top:.3rem;">
            <i class="bi bi-geo-alt-fill" style="color:var(--p);"></i> {{ $match->venue }}
        </div>
        @endif
    </div>

    @if($match->status === 'completed')
    <div style="text-align:center;padding:.75rem 1rem;background:rgba(0,230,118,.06);border:1px solid rgba(0,230,118,.15);border-radius:var(--r);">
        @if($match->winner)
        <div style="font-weight:800;font-size:.95rem;color:var(--p);">
            <i class="bi bi-award-fill"></i> {{ $match->winner->name }} won by {{ $match->result_margin }} {{ $match->result_type }}
        </div>
        @elseif($match->result_type==='tie')
        <div style="font-weight:800;color:var(--g);">Match Tied</div>
        @else
        <div style="color:var(--mut);">No Result</div>
        @endif
    </div>
    @elseif($match->status === 'live')
    <div style="text-align:center;"><span class="live-badge">Live Now</span></div>
    @endif

    {{-- Share & embed. Live matches lead with it, since that is when a broadcast
         overlay or a shared link is actually wanted. --}}
    <div style="display:flex;gap:.5rem;justify-content:center;margin-top:.875rem;">
        <a href="{{ route('share', $match) }}"
           style="display:inline-flex;align-items:center;gap:.4rem;background:var(--p);color:#fff;
                  text-decoration:none;font-weight:700;font-size:.78rem;padding:.5rem .95rem;border-radius:999px;">
            <i class="bi bi-share-fill"></i> Share &amp; Embed
        </a>
        <a href="{{ route('scorecard.print', $match) }}"
           style="display:inline-flex;align-items:center;gap:.4rem;background:var(--s2);color:var(--txt);
                  border:1px solid var(--bd);text-decoration:none;font-weight:700;font-size:.78rem;
                  padding:.5rem .95rem;border-radius:999px;">
            <i class="bi bi-printer"></i> Print card
        </a>
    </div>
</div>

{{-- Innings --}}
@foreach([$inn1, $inn2] as $inn)
@if($inn)
<div class="sec" style="padding-top:.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
        <div style="font-weight:800;font-size:.88rem;color:var(--g);">{{ $inn->battingTeam?->name }} Innings</div>
        <div style="font-weight:900;font-size:1.05rem;color:var(--txt);">
            {{ $inn->total_runs }}/<span style="color:var(--red);">{{ $inn->total_wickets }}</span>
            <span style="font-size:.72rem;font-weight:500;color:var(--mut);margin-left:.3rem;">({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }})</span>
        </div>
    </div>

    {{-- Batting --}}
    <div class="card" style="margin-bottom:.625rem;overflow:hidden;">
        <div style="padding:.5rem .875rem;font-size:.62rem;font-weight:800;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;border-bottom:1px solid var(--bd);">Batting</div>
        <table class="sc-table">
            <thead>
                <tr><th style="text-align:left;">Batter</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th></tr>
            </thead>
            <tbody>
                @php
                    /* Anyone who never came to the crease belongs in one line at
                       the bottom, not as eight identical 0-not-out rows. */
                    $cards    = ($inn->battingScorecards ?? collect())->sortBy('batting_position');
                    $batted   = $cards->filter(fn($c) => $c->balls_faced > 0 || $c->is_out);
                    $yetToBat = $cards->reject(fn($c) => $c->balls_faced > 0 || $c->is_out);
                @endphp

                @forelse($batted as $sc)
                <tr>
                    <td style="max-width:120px;">
                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;{{ !$sc->is_out ? 'color:var(--p);' : '' }}">{{ $sc->player?->name }}</div>
                        @if($sc->is_out)
                        <div style="font-size:.6rem;color:var(--mut);">
                            {{ str_replace('_',' ',$sc->dismissal_type) }}@if($sc->bowledBy) b {{ $sc->bowledBy->name }}@endif
                        </div>
                        @else
                        <div style="font-size:.6rem;color:var(--p);">not out ✦</div>
                        @endif
                    </td>
                    <td style="font-weight:900;color:var(--txt);">{{ $sc->runs }}</td>
                    <td style="color:var(--mut);">{{ $sc->balls_faced }}</td>
                    <td style="color:var(--blue);">{{ $sc->fours }}</td>
                    <td style="color:var(--g);">{{ $sc->sixes }}</td>
                    <td style="color:var(--mut);font-size:.72rem;">{{ $sc->balls_faced > 0 ? number_format($sc->runs/$sc->balls_faced*100,1) : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:1.25rem;color:var(--mut);font-size:.78rem;">No deliveries recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding:.5rem .875rem;font-size:.7rem;color:var(--mut);border-top:1px solid var(--bd);display:flex;justify-content:space-between;gap:1rem;">
            <span>Extras</span>
            <span style="color:var(--txt);font-weight:600;">{{ $inn->total_extras ?? 0 }}</span>
        </div>
        <div style="padding:.5rem .875rem;font-size:.72rem;border-top:1px solid var(--bd);display:flex;justify-content:space-between;gap:1rem;background:var(--s2);">
            <span style="font-weight:700;">Total</span>
            <span style="font-weight:800;color:var(--txt);">
                {{ $inn->total_runs }}/{{ $inn->total_wickets }}
                <span style="color:var(--mut);font-weight:500;">({{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov, RR {{ number_format($inn->run_rate,2) }})</span>
            </span>
        </div>
        @if($yetToBat->isNotEmpty())
        <div style="padding:.5rem .875rem;font-size:.68rem;color:var(--mut);border-top:1px solid var(--bd);line-height:1.5;">
            <span style="font-weight:700;text-transform:uppercase;letter-spacing:.06em;font-size:.6rem;">Yet to bat</span><br>
            {{ $yetToBat->map(fn($c) => $c->player?->name)->filter()->implode(', ') }}
        </div>
        @endif
    </div>

    {{-- Bowling --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:.5rem .875rem;font-size:.62rem;font-weight:800;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;border-bottom:1px solid var(--bd);">Bowling</div>
        <table class="sc-table">
            <thead>
                <tr><th style="text-align:left;">Bowler</th><th>O</th><th>R</th><th>W</th><th>Econ</th></tr>
            </thead>
            <tbody>
                @foreach($inn->bowlingScorecards ?? [] as $sc)
                <tr>
                    <td style="max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">{{ $sc->player?->name }}</td>
                    <td>{{ $sc->overs }}</td>
                    <td style="color:var(--mut);">{{ $sc->runs_conceded }}</td>
                    <td style="font-weight:900;color:var(--p);">{{ $sc->wickets }}</td>
                    <td style="color:var(--mut);font-size:.72rem;">{{ $sc->economy ? number_format($sc->economy,2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

{{-- Actions --}}
<div class="sec" style="display:flex;align-items:center;justify-content:center;gap:.625rem;flex-wrap:wrap;padding-top:.25rem;">
    <a href="{{ route('scorecard.print', $match) }}" target="_blank"
       style="display:inline-flex;align-items:center;gap:.375rem;padding:.5rem 1.25rem;border-radius:var(--r-f);
              background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:var(--txt);
              font-size:.8rem;font-weight:700;text-decoration:none;transition:all .2s;"
       onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
        <i class="bi bi-download"></i> Download
    </a>
    <button class="btn-share" onclick="shareScorecard()">
        <i class="bi bi-share-fill"></i> Share Card
    </button>
</div>

@push('scripts')
<script>
function shareScorecard() {
    @php
        $inn1Summary = $inn1 ? ($inn1->battingTeam?->short_code ?? $inn1->battingTeam?->name) . '  ' . $inn1->total_runs . '/' . $inn1->total_wickets . ' (' . floor($inn1->total_balls/6) . '.' . ($inn1->total_balls%6) . ')' : null;
        $inn2Summary = $inn2 ? ($inn2->battingTeam?->short_code ?? $inn2->battingTeam?->name) . '  ' . $inn2->total_runs . '/' . $inn2->total_wickets . ' (' . floor($inn2->total_balls/6) . '.' . ($inn2->total_balls%6) . ')' : null;
        $resultText = '';
        if($match->status === 'completed') {
            if($match->winner) $resultText = $match->winner->name . ' won by ' . $match->result_margin . ' ' . $match->result_type;
            elseif($match->result_type === 'tie') $resultText = 'Match Tied';
        } elseif($match->status === 'live') { $resultText = 'LIVE'; }
    @endphp
    var matchTitle = @json(($match->homeTeam?->short_code ?? '?') . ' vs ' . ($match->awayTeam?->short_code ?? '?'));
    var inn1 = @json($inn1Summary); var inn2 = @json($inn2Summary);
    var result = @json($resultText); var editionName = @json($match->edition?->name ?? 'RCL');
    var p = function(c,v){ return '<div style="display:flex;justify-content:space-between;align-items:center;padding:7px 18px;border-bottom:1px solid rgba(0,230,118,.08);font-size:12px;"><span style="color:#6B9973;font-weight:600;">'+c+'</span><span style="font-weight:800;color:#1A3020;">'+v+'</span></div>'; };
    var body = '<div style="padding:14px 0 4px;"><div style="text-align:center;padding:4px 18px 12px;"><div style="font-size:20px;font-weight:900;color:#1A3020;">'+matchTitle+'</div><div style="font-size:10px;color:#6B9973;letter-spacing:.06em;margin-top:3px;">'+editionName+' · Match #{{ $match->match_number }}</div></div>';
    if(inn1) body += p('1st Innings', inn1);
    if(inn2) body += p('2nd Innings', inn2);
    if(result) body += '<div style="margin:10px 18px;padding:10px;background:rgba(0,230,118,.08);border-radius:10px;text-align:center;font-weight:800;font-size:13px;color:#1B8A4E;">'+result+'</div>';
    body += '</div>';
    shareRCL(matchTitle + ' Scorecard', body, editionName);
}
</script>
@endpush

@endsection
