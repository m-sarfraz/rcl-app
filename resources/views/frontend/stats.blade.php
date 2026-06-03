@extends('layouts.app')
@section('title','Statistics')

@section('content')
<div class="sec" style="padding-bottom:.5rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(64,196,255,.12);color:var(--blue);">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            Leaderboards
        </div>
        <form method="GET">
            <select name="edition_id" onchange="this.form.submit()"
                    style="background:var(--s2);border:1px solid var(--bd);color:var(--txt);border-radius:8px;padding:.25rem .5rem;font-size:.72rem;">
                @foreach($editions as $ed)
                    <option value="{{ $ed->id }}" {{ $ed->id==$editionId ? 'selected' : '' }}>{{ $ed->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Tab nav --}}
<div style="display:flex;gap:.375rem;overflow-x:auto;padding:.1rem 1rem .75rem;" class="hide-scroll">
    @foreach(['batting'=>'Batting','bowling'=>'Bowling','boundaries'=>'Boundaries','sixes'=>'Sixes','mvp'=>'MVP'] as $tab => $label)
    <button class="stat-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $tab }}"
            style="flex-shrink:0;padding:.4rem .9rem;border-radius:20px;font-size:.75rem;font-weight:600;cursor:pointer;white-space:nowrap;transition:all .2s;
                   border:1px solid {{ $loop->first ? 'var(--p)' : 'var(--bd)' }};
                   background:{{ $loop->first ? 'rgba(0,230,118,.15)' : 'var(--s2)' }};
                   color:{{ $loop->first ? 'var(--p)' : 'var(--mut)' }};">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- Batting --}}
<div class="stat-panel" id="tab-batting">
    <div class="card" style="margin:0 1rem 1rem;">
        <div style="padding:.5rem .875rem;font-size:.63rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);display:flex;">
            <span style="width:28px;">#</span><span style="flex:1;">Player / Team</span>
            <span style="width:46px;text-align:right;">Runs</span><span style="width:40px;text-align:right;">SR</span>
        </div>
        @forelse($topBatsmen as $i => $stat)
        <a href="{{ route('player', $stat->player) }}" class="leader-row" style="text-decoration:none;color:var(--txt);">
            <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
            <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
            <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
            <div class="leader-val" style="width:46px;text-align:right;">{{ $stat->total_runs }}</div>
            <div style="width:40px;text-align:right;font-size:.75rem;color:var(--mut);">{{ $stat->batting_strike_rate ? number_format($stat->batting_strike_rate,1) : '-' }}</div>
        </a>
        @empty<div style="padding:2rem;text-align:center;color:var(--mut);">No data yet</div>
        @endforelse
    </div>
</div>

{{-- Bowling --}}
<div class="stat-panel" id="tab-bowling" style="display:none;">
    <div class="card" style="margin:0 1rem 1rem;">
        <div style="padding:.5rem .875rem;font-size:.63rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);display:flex;">
            <span style="width:28px;">#</span><span style="flex:1;">Player / Team</span>
            <span style="width:40px;text-align:right;">Wkts</span><span style="width:44px;text-align:right;">Econ</span>
        </div>
        @forelse($topBowlers as $i => $stat)
        <a href="{{ route('player', $stat->player) }}" class="leader-row" style="text-decoration:none;color:var(--txt);">
            <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
            <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
            <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
            <div class="leader-val" style="width:40px;text-align:right;">{{ $stat->total_wickets }}</div>
            <div style="width:44px;text-align:right;font-size:.75rem;color:var(--mut);">{{ $stat->bowling_economy ? number_format($stat->bowling_economy,2) : '-' }}</div>
        </a>
        @empty<div style="padding:2rem;text-align:center;color:var(--mut);">No data yet</div>
        @endforelse
    </div>
</div>

{{-- Boundaries --}}
<div class="stat-panel" id="tab-boundaries" style="display:none;">
    <div class="card" style="margin:0 1rem 1rem;">
        <div style="padding:.5rem .875rem;font-size:.63rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);display:flex;">
            <span style="width:28px;">#</span><span style="flex:1;">Player</span><span style="width:36px;text-align:right;">4s</span>
        </div>
        @forelse($topBoundaries as $i => $stat)
        <a href="{{ route('player', $stat->player) }}" class="leader-row" style="text-decoration:none;color:var(--txt);">
            <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
            <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
            <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
            <div class="leader-val" style="width:36px;text-align:right;">{{ $stat->total_fours }}</div>
        </a>
        @empty<div style="padding:2rem;text-align:center;color:var(--mut);">No data yet</div>
        @endforelse
    </div>
</div>

{{-- Sixes --}}
<div class="stat-panel" id="tab-sixes" style="display:none;">
    <div class="card" style="margin:0 1rem 1rem;">
        <div style="padding:.5rem .875rem;font-size:.63rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);display:flex;">
            <span style="width:28px;">#</span><span style="flex:1;">Player</span><span style="width:36px;text-align:right;">6s</span>
        </div>
        @forelse($topSixes as $i => $stat)
        <a href="{{ route('player', $stat->player) }}" class="leader-row" style="text-decoration:none;color:var(--txt);">
            <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
            <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
            <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
            <div class="leader-val" style="width:36px;text-align:right;">{{ $stat->total_sixes }}</div>
        </a>
        @empty<div style="padding:2rem;text-align:center;color:var(--mut);">No data yet</div>
        @endforelse
    </div>
</div>

{{-- MVP --}}
<div class="stat-panel" id="tab-mvp" style="display:none;">
    <div class="card" style="margin:0 1rem 1rem;">
        <div style="padding:.5rem .875rem;font-size:.63rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--bd);display:flex;">
            <span style="width:28px;">#</span><span style="flex:1;">Player</span><span style="width:44px;text-align:right;">MVP</span>
        </div>
        @forelse($mvps as $i => $stat)
        <a href="{{ route('player', $stat->player) }}" class="leader-row" style="text-decoration:none;color:var(--txt);">
            <div class="leader-rank {{ $i<3?'gold':'' }}">{{ $i+1 }}</div>
            <div class="leader-avatar"><img src="/images/player-avatar.svg" alt=""></div>
            <div style="flex:1;"><div class="leader-name">{{ $stat->player?->name }}</div><div class="leader-sub">{{ $stat->team?->name }}</div></div>
            <div class="leader-val" style="width:44px;text-align:right;color:var(--g);">{{ $stat->mvp_count }}</div>
        </a>
        @empty<div style="padding:2rem;text-align:center;color:var(--mut);">No data yet</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
$('.stat-tab').on('click', function() {
    var tab = $(this).data('tab');
    $('.stat-tab').css({ background:'var(--s2)', color:'var(--mut)', borderColor:'var(--bd)' });
    $(this).css({ background:'rgba(0,230,118,.15)', color:'var(--p)', borderColor:'var(--p)' });
    $('.stat-panel').hide();
    $('#tab-' + tab).show();
});
</script>
@endpush
@endsection

