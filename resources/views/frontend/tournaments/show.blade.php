@extends('layouts.app')
@section('title', $edition->name)

@section('content')

{{-- Edition Hero --}}
<div style="background:linear-gradient(180deg,rgba(0,230,118,.1) 0%,var(--dark) 100%);padding:1.25rem 1rem .875rem;text-align:center;border-bottom:1px solid var(--bd);">
    @if($edition->thumbnail)
        <img src="{{ asset('storage/'.$edition->thumbnail) }}" style="width:72px;height:72px;border-radius:14px;object-fit:cover;margin-bottom:.75rem;display:block;margin-left:auto;margin-right:auto;">
    @else
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--g));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.5rem;margin:0 auto .75rem;box-shadow:0 4px 16px rgba(27,138,78,.35);">{{ $edition->edition_number }}</div>
    @endif
    <div style="font-weight:900;font-size:1.2rem;" class="grad">{{ $edition->name }}</div>
    @if($edition->host_village)<div style="font-size:.78rem;color:var(--mut);margin-top:.25rem;"><i class="bi bi-geo-alt"></i> {{ $edition->host_village }}</div>@endif
    @if($edition->start_date)
    <div style="font-size:.72rem;color:var(--mut);margin-top:.15rem;">{{ $edition->start_date->format('d M Y') }}{{ $edition->end_date ? ' – '.$edition->end_date->format('d M Y') : '' }}</div>
    @endif
    <div style="display:flex;justify-content:center;gap:.5rem;margin-top:.625rem;flex-wrap:wrap;">
        @if($edition->is_current)<span class="pill pill-green">Live Season</span>@elseif($edition->status==='completed')<span class="pill pill-muted">Completed</span>@endif
    </div>
</div>

{{-- Tab Navigation --}}
<div class="ed-tabs">
    @foreach(['points'=>'Points','live'=>'Live','upcoming'=>'Schedule','completed'=>'Results','stats'=>'Stats'] as $tab => $label)
    <button class="ed-tab {{ $tab==='points'?'active':'' }}" data-tab="{{ $tab }}">
        {{ $label }}
        @if($tab==='live' && $liveMatches->count())<span style="background:var(--red);color:#fff;border-radius:20px;padding:.1em .4em;font-size:.6rem;margin-left:.2rem;">{{ $liveMatches->count() }}</span>@endif
    </button>
    @endforeach
</div>

{{-- Points Table --}}
<div class="ed-panel active" id="tab-points">
    <div class="sec" style="padding-top:.75rem;">
        <div class="card" style="overflow:hidden;">
            <table class="pts-table">
                <thead>
                    <tr><th>#</th><th style="text-align:left;">Team</th><th>P</th><th>W</th><th>L</th><th>Pts</th><th>NRR</th></tr>
                </thead>
                <tbody>
                    @forelse($pointsTable as $i => $row)
                    <tr>
                        <td class="rank">{{ $i+1 }}</td>
                        <td>
                            <div class="team-cell">
                                <div class="team-badge-sm" style="background:linear-gradient(135deg,{{ $row['team']->primary_color ?? 'var(--p)' }},{{ $row['team']->secondary_color ?? 'var(--g)' }});">{{ $row['team']->short_code }}</div>
                                <span style="font-weight:600;font-size:.82rem;">{{ $row['team']->name }}</span>
                            </div>
                        </td>
                        <td>{{ $row['played'] }}</td>
                        <td style="color:var(--p);font-weight:700;">{{ $row['won'] }}</td>
                        <td style="color:var(--red);">{{ $row['lost'] }}</td>
                        <td style="font-weight:900;font-size:.95rem;">{{ $row['points'] }}</td>
                        <td class="{{ $row['nrr'] >= 0 ? 'nrr-pos' : 'nrr-neg' }}">{{ $row['nrr'] > 0 ? '+' : '' }}{{ $row['nrr'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:2rem;text-align:center;color:var(--mut);">No matches played yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Live --}}
<div class="ed-panel" id="tab-live">
    <div class="sec">
        @forelse($liveMatches as $m)
        @php $inn = $m->innings->last(); @endphp
        <div class="match-card match-card-live card-hover" onclick="window.location='{{ route('scorecard',$m) }}'">
            <div style="margin-bottom:.35rem;"><span class="live-badge">Live</span></div>
            <div class="teams-row">
                <div style="flex:1;"><div class="team-name">{{ $m->homeTeam?->name }}</div><div style="font-size:.67rem;color:var(--mut);">{{ $m->homeTeam?->short_code }}</div></div>
                <div style="text-align:center;">
                    @if($inn)<div class="team-score">{{ $inn->total_runs }}/{{ $inn->total_wickets }}</div><div style="font-size:.67rem;color:var(--mut);">{{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov</div>
                    @else<div class="vs-pill">VS</div>@endif
                </div>
                <div style="flex:1;text-align:right;"><div class="team-name" style="text-align:right;">{{ $m->awayTeam?->name }}</div></div>
            </div>
            <div class="match-meta" style="margin-top:.4rem;"><span><i class="bi bi-geo-alt"></i> {{ $m->venue }}</span></div>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--mut);">No live matches right now</div>
        @endforelse
    </div>
</div>

{{-- Schedule --}}
<div class="ed-panel" id="tab-upcoming">
    <div class="sec">
        @forelse($upcomingMatches as $m)
        <div class="match-card match-card-upcoming">
            <div style="font-size:.7rem;color:var(--g);font-weight:700;margin-bottom:.375rem;">{{ $m->scheduled_at?->format('D, d M · h:i A') }}</div>
            <div class="teams-row">
                <div style="flex:1;"><div class="team-name">{{ $m->homeTeam?->name }}</div></div>
                <div class="vs-pill">VS</div>
                <div style="flex:1;text-align:right;"><div class="team-name" style="text-align:right;">{{ $m->awayTeam?->name }}</div></div>
            </div>
            <div class="match-meta" style="margin-top:.4rem;"><span><i class="bi bi-geo-alt"></i> {{ $m->venue }}</span><span>{{ $m->overs_per_side }} overs</span></div>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--mut);">No upcoming matches</div>
        @endforelse
    </div>
</div>

{{-- Results --}}
<div class="ed-panel" id="tab-completed">
    <div class="sec">
        @forelse($completedMatches as $m)
        <div class="match-card card-hover" onclick="window.location='{{ route('scorecard',$m) }}'">
            <div class="teams-row">
                <div style="flex:1;">
                    <div style="font-weight:{{ $m->winner_id===$m->home_team_id?'800':'500' }};font-size:.9rem;color:{{ $m->winner_id===$m->home_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->homeTeam?->name }}</div>
                </div>
                <div class="vs-pill">vs</div>
                <div style="flex:1;text-align:right;">
                    <div style="font-weight:{{ $m->winner_id===$m->away_team_id?'800':'500' }};font-size:.9rem;color:{{ $m->winner_id===$m->away_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->awayTeam?->name }}</div>
                </div>
            </div>
            <div class="match-meta" style="margin-top:.4rem;justify-content:center;">
                @if($m->winner)<span style="color:var(--p);font-weight:600;">{{ $m->winner->name }} won by {{ $m->result_margin }} {{ $m->result_type }}</span>
                @elseif($m->result_type==='tie')<span style="color:var(--g);">Tied</span>
                @else<span style="color:var(--mut);">No result</span>@endif
            </div>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--mut);">No completed matches</div>
        @endforelse
    </div>
</div>

{{-- Stats --}}
<div class="ed-panel" id="tab-stats">
    <div class="sec">
        <div style="font-weight:700;font-size:.82rem;margin-bottom:.5rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Top Batsmen</div>
        <div class="card" style="margin-bottom:.875rem;">
            @foreach($topBatsmen->take(5) as $i => $stat)
            <div class="leader-row">
                <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
                <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
                <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
                <div class="leader-val">{{ $stat->total_runs }} <span style="font-size:.7rem;font-weight:400;color:var(--mut);">runs</span></div>
            </div>
            @endforeach
        </div>
        <div style="font-weight:700;font-size:.82rem;margin-bottom:.5rem;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;">Top Bowlers</div>
        <div class="card">
            @foreach($topBowlers->take(5) as $i => $stat)
            <div class="leader-row">
                <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
                <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
                <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
                <div class="leader-val">{{ $stat->total_wickets }} <span style="font-size:.7rem;font-weight:400;color:var(--mut);">wkts</span></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
$('.ed-tab').on('click', function() {
    var tab = $(this).data('tab');
    $('.ed-tab').removeClass('active');
    $(this).addClass('active');
    $('.ed-panel').removeClass('active').hide();
    $('#tab-' + tab).addClass('active').show();
});
// show correct panel on load
$('.ed-panel').hide();
$('.ed-panel.active').show();
</script>
@endpush

@endsection

