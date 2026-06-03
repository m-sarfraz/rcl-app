@extends('layouts.app')
@section('title','Scoring — ' . ($match->homeTeam?->short_code ?? '?') . ' vs ' . ($match->awayTeam?->short_code ?? '?'))

@push('styles')
<style>
/* ── Scoring console ──────────────────────────────────── */
#scoring-wrap { display:flex; flex-direction:column; height:100%; }

/* Scoreboard strip */
.sc-board {
    background:linear-gradient(135deg,#0F3A1E,#1B8A4E,#127040);
    color:#fff; padding:.875rem 1rem;
    display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
}
.sc-board-score { font-size:2rem; font-weight:900; line-height:1; letter-spacing:.02em; }
.sc-board-meta  { font-size:.72rem; color:rgba(255,255,255,.65); margin-top:.25rem; }
.sc-board-rhs   { text-align:right; }
.sc-board-rrr   { font-size:.85rem; font-weight:700; color:#D4900A; }

/* Over display */
.over-track {
    display:flex; align-items:center; gap:.4rem;
    padding:.55rem 1rem; background:rgba(27,138,78,.07);
    border-bottom:1px solid var(--bd); flex-shrink:0; overflow-x:auto;
}
.over-ball {
    width:26px; height:26px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.65rem; font-weight:800;
    border:1.5px solid currentColor;
}
.over-ball.dot  { color:var(--mut);  background:rgba(107,143,116,.1); border-color:var(--bd); }
.over-ball.run  { color:var(--txt);  background:var(--s2); }
.over-ball.four { color:#0284C7;     background:rgba(2,132,199,.12); border-color:rgba(2,132,199,.4); }
.over-ball.six  { color:var(--g);    background:rgba(212,144,10,.12);border-color:rgba(212,144,10,.4);}
.over-ball.wicket{ color:#fff;       background:var(--red); border-color:var(--red); }
.over-ball.wide { color:var(--pur);  background:rgba(124,58,237,.1); border-color:rgba(124,58,237,.3);}
.over-ball.noball{ color:#EA580C;    background:rgba(234,88,12,.1);  border-color:rgba(234,88,12,.3); }
.over-ball.bye, .over-ball.legbye { color:var(--mut); background:rgba(107,143,116,.12); }

/* Player strip */
.player-strip {
    display:flex; gap:.5rem; padding:.55rem 1rem;
    border-bottom:1px solid var(--bd); flex-shrink:0;
    overflow-x:auto;
}
.player-chip {
    flex-shrink:0; display:flex; flex-direction:column;
    align-items:center; gap:.2rem;
    background:var(--s2); border:1.5px solid var(--bd);
    border-radius:10px; padding:.4rem .65rem;
    font-size:.7rem; min-width:80px; cursor:pointer;
    transition:border-color .2s;
}
.player-chip.striker { border-color:var(--p); background:rgba(27,138,78,.08); }
.player-chip.bowler-chip { border-color:var(--g); background:rgba(212,144,10,.07); }
.player-chip .role-lbl { font-size:.58rem; color:var(--mut); text-transform:uppercase; letter-spacing:.05em; }
.player-chip .name-lbl { font-weight:700; color:var(--txt); text-align:center; max-width:90px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Main action area - scrollable */
.scoring-body { flex:1; overflow-y:auto; padding:.75rem 1rem; }

/* Run buttons */
.run-grid {
    display:grid; grid-template-columns:repeat(6,1fr); gap:.45rem;
    margin-bottom:.75rem;
}
.run-btn {
    padding:.75rem .25rem; border-radius:12px; border:2px solid;
    font-size:1.05rem; font-weight:900; cursor:pointer;
    transition:transform .1s, box-shadow .15s;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:.2rem; font-family:inherit;
}
.run-btn:active { transform:scale(.93); }
.run-btn .sub-lbl { font-size:.52rem; font-weight:600; letter-spacing:.04em; }
.btn-0   { background:var(--s2);                border-color:var(--bd);              color:var(--txt); }
.btn-1   { background:rgba(2,132,199,.1);        border-color:rgba(2,132,199,.35);    color:var(--blue); }
.btn-2   { background:rgba(2,132,199,.15);       border-color:rgba(2,132,199,.45);    color:var(--blue); }
.btn-3   { background:rgba(2,132,199,.2);        border-color:rgba(2,132,199,.55);    color:var(--blue); }
.btn-4   { background:rgba(27,138,78,.15);       border-color:rgba(27,138,78,.5);     color:var(--p); }
.btn-6   { background:rgba(212,144,10,.15);      border-color:rgba(212,144,10,.5);    color:var(--g); }

/* Extras row */
.extras-row {
    display:grid; grid-template-columns:repeat(4,1fr); gap:.4rem;
    margin-bottom:.75rem;
}
.extra-btn {
    padding:.55rem .25rem; border-radius:10px; border:1.5px solid;
    font-size:.8rem; font-weight:800; cursor:pointer;
    display:flex; flex-direction:column; align-items:center; gap:.15rem;
    transition:all .15s; font-family:inherit;
}
.extra-btn.active { transform:scale(.96); }
.extra-btn .sub   { font-size:.52rem; color:inherit; opacity:.7; }
.btn-wd  { background:rgba(124,58,237,.08); border-color:rgba(124,58,237,.3);  color:var(--pur); }
.btn-wd.active   { background:rgba(124,58,237,.2); border-color:var(--pur); }
.btn-nb  { background:rgba(234,88,12,.08);  border-color:rgba(234,88,12,.3);   color:#EA580C; }
.btn-nb.active   { background:rgba(234,88,12,.2);  border-color:#EA580C; }
.btn-bye { background:rgba(107,143,116,.1); border-color:var(--bd);            color:var(--mut); }
.btn-bye.active  { background:rgba(107,143,116,.2); border-color:var(--mut); }
.btn-lb  { background:rgba(107,143,116,.1); border-color:var(--bd);            color:var(--mut); }
.btn-lb.active   { background:rgba(107,143,116,.2); border-color:var(--mut); }

/* Wicket + Undo row */
.action-row { display:grid; grid-template-columns:1fr 1fr; gap:.4rem; margin-bottom:.75rem; }
.btn-wicket {
    padding:.65rem; border-radius:10px; border:2px solid rgba(220,38,38,.5);
    background:rgba(220,38,38,.08); color:var(--red);
    font-size:.95rem; font-weight:900; cursor:pointer; font-family:inherit;
    transition:background .15s; display:flex; align-items:center; justify-content:center; gap:.375rem;
}
.btn-wicket.active { background:rgba(220,38,38,.2); border-color:var(--red); }
.btn-undo {
    padding:.65rem; border-radius:10px; border:2px solid rgba(234,88,12,.4);
    background:rgba(234,88,12,.07); color:#EA580C;
    font-size:.95rem; font-weight:900; cursor:pointer; font-family:inherit;
    transition:background .15s; display:flex; align-items:center; justify-content:center; gap:.375rem;
}

/* Wicket panel */
#wicket-panel {
    background:rgba(220,38,38,.05); border:1px solid rgba(220,38,38,.2);
    border-radius:12px; padding:.75rem; margin-bottom:.75rem;
}
.wicket-type-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.375rem; margin-bottom:.625rem; }
.wt-btn {
    padding:.5rem .25rem; border-radius:8px; border:1.5px solid rgba(220,38,38,.3);
    background:transparent; color:var(--red); font-size:.72rem; font-weight:700;
    cursor:pointer; text-align:center; transition:all .15s; font-family:inherit;
}
.wt-btn.active { background:rgba(220,38,38,.15); border-color:var(--red); }

/* Player selection modal */
#player-modal {
    position:fixed; inset:0; z-index:9990;
    background:rgba(0,0,0,.5); display:none; align-items:flex-end;
}
#player-modal.open { display:flex; }
.player-modal-sheet {
    width:100%; max-width:var(--vw); margin:0 auto;
    background:var(--s1); border-radius:20px 20px 0 0;
    max-height:60vh; display:flex; flex-direction:column;
    overflow:hidden;
}
.player-modal-header {
    padding:.875rem 1rem; font-weight:800; font-size:.92rem;
    border-bottom:1px solid var(--bd); display:flex; align-items:center; justify-content:space-between;
    flex-shrink:0;
}
.player-modal-list { overflow-y:auto; flex:1; }
.player-modal-item {
    padding:.75rem 1rem; border-bottom:1px solid var(--bd);
    display:flex; align-items:center; gap:.75rem; cursor:pointer;
    transition:background .15s;
}
.player-modal-item:hover { background:var(--s2); }
.player-modal-item .avatar {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,var(--p),var(--g));
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:.8rem; color:#000; flex-shrink:0;
}

/* Innings start panel */
#innings-start-panel { padding:1rem; }
.innings-card { background:var(--s1); border:1.5px solid var(--bd); border-radius:16px; padding:1.25rem; }
.innings-label { font-size:.68rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.375rem; font-weight:700; }
.innings-select {
    width:100%; padding:.65rem .875rem; border-radius:10px;
    border:1.5px solid var(--bd); background:var(--s2); color:var(--txt);
    font-size:.875rem; font-family:inherit; outline:none;
}
.innings-select:focus { border-color:var(--p); }

/* Complete match btn */
.btn-complete {
    width:100%; padding:.75rem; border-radius:12px; border:none;
    background:linear-gradient(135deg,#1B8A4E,#127040); color:#fff;
    font-size:.9rem; font-weight:800; cursor:pointer; font-family:inherit;
    margin-top:.625rem; display:flex; align-items:center; justify-content:center; gap:.5rem;
}
.btn-complete-secondary {
    width:100%; padding:.65rem; border-radius:12px;
    border:1.5px solid rgba(220,38,38,.4); background:rgba(220,38,38,.06);
    color:var(--red); font-size:.85rem; font-weight:700; cursor:pointer;
    font-family:inherit; margin-top:.5rem;
    display:flex; align-items:center; justify-content:center; gap:.5rem;
}

/* Spinner overlay */
#sc-spinner {
    position:fixed; inset:0; z-index:9995;
    background:rgba(0,0,0,.3); display:none; align-items:center; justify-content:center;
}
#sc-spinner.on { display:flex; }
</style>
@endpush

@section('content')
<div id="scoring-wrap">

{{-- ══ CASE: No active innings → show start panel ══ --}}
@if(!$currentInnings)
<div class="sc-board">
    <div>
        <div style="font-size:.7rem;color:rgba(255,255,255,.6);">{{ $match->edition?->name }} · Match #{{ $match->match_number }}</div>
        <div style="font-size:1.05rem;font-weight:800;margin-top:.15rem;">
            {{ $match->homeTeam?->short_code ?? $match->homeTeam?->name }} vs {{ $match->awayTeam?->short_code ?? $match->awayTeam?->name }}
        </div>
    </div>
    <a href="{{ route('frontend.scoring') }}" style="color:rgba(255,255,255,.7);font-size:.75rem;text-decoration:none;">
        <i class="bi bi-list"></i> Matches
    </a>
</div>

<div id="innings-start-panel">
    <div class="innings-card">
        @php
            $inningsNumber = $match->innings()->count() + 1;
            $completedInnings = $match->innings()->where('is_completed', true)->get();
            $allTeams = collect([$match->homeTeam, $match->awayTeam])->filter();
            $battingFirst = $completedInnings->count() > 0 ? $completedInnings->first()?->bowlingTeam : null;
        @endphp
        <div style="font-size:1rem;font-weight:800;margin-bottom:1rem;">
            Start {{ $inningsNumber === 1 ? '1st' : '2nd' }} Innings
        </div>

        @if($inningsNumber === 2 && $completedInnings->count() > 0)
        @php $inn1 = $completedInnings->first(); @endphp
        <div style="background:rgba(27,138,78,.08);border:1px solid var(--bd);border-radius:10px;padding:.75rem;margin-bottom:1rem;font-size:.82rem;">
            <strong>{{ $inn1->battingTeam?->name }}</strong> scored
            <strong style="color:var(--p);">{{ $inn1->total_runs }}/{{ $inn1->total_wickets }}</strong>
            — Target: <strong style="color:var(--g);">{{ $inn1->total_runs + 1 }}</strong>
        </div>
        @endif

        <form id="start-innings-form">
            @csrf
            <input type="hidden" name="innings_number" value="{{ $inningsNumber }}">

            <div style="margin-bottom:.875rem;">
                <div class="innings-label">Batting Team</div>
                <select name="batting_team_id" id="batting-team" class="innings-select" required>
                    <option value="">Select batting team…</option>
                    @foreach($allTeams as $t)
                    <option value="{{ $t->id }}"
                        {{ $battingFirst && $t->id === $battingFirst->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div class="innings-label">Bowling Team</div>
                <select name="bowling_team_id" id="bowling-team" class="innings-select" required>
                    <option value="">Select bowling team…</option>
                    @foreach($allTeams as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-complete">
                <i class="bi bi-play-circle-fill"></i> Start Innings
            </button>
        </form>
    </div>

    @if($match->innings()->where('is_completed', true)->count() >= 1)
    <button class="btn-complete-secondary" onclick="completeMatch()">
        <i class="bi bi-flag-fill"></i> End Match
    </button>
    @endif
</div>

{{-- ══ CASE: Active innings → show full scoring console ══ --}}
@else
@php
    $battingTeam = $currentInnings->battingTeam;
    $bowlingTeam = $currentInnings->bowlingTeam;
    $players = $battingTeam && $battingTeam->id === $match->home_team_id ? $homePlayers : $awayPlayers;
    $bowlers  = $battingTeam && $battingTeam->id === $match->home_team_id ? $awayPlayers  : $homePlayers;
    $legalBalls = $currentInnings->total_balls;
    $currentOver = floor($legalBalls / 6);
    $ballInOver  = $legalBalls % 6;
    $currentOverBalls = $currentInnings->ballByBall()
        ->where('over_number', $currentOver + 1)
        ->orderBy('id')->get();
@endphp

{{-- Scoreboard --}}
<div class="sc-board">
    <div>
        <div style="font-size:.68rem;color:rgba(255,255,255,.6);">
            {{ $battingTeam?->short_code ?? $battingTeam?->name }} batting
            · Inn {{ $currentInnings->innings_number }}
        </div>
        <div class="sc-board-score" id="live-score">
            {{ $currentInnings->total_runs }}/{{ $currentInnings->total_wickets }}
        </div>
        <div class="sc-board-meta" id="live-overs">
            ({{ $currentOver }}.{{ $ballInOver }} ov)
        </div>
    </div>
    <div class="sc-board-rhs">
        @if($currentInnings->target)
        <div class="sc-board-rrr" id="live-rrr">Need {{ $currentInnings->target - $currentInnings->total_runs }}</div>
        <div style="font-size:.65rem;color:rgba(255,255,255,.55);">to win</div>
        @endif
        <a href="{{ route('frontend.scoring') }}" style="display:inline-block;color:rgba(255,255,255,.65);font-size:.72rem;text-decoration:none;margin-top:.4rem;">
            <i class="bi bi-list"></i> Matches
        </a>
    </div>
</div>

{{-- Current over balls --}}
<div class="over-track">
    <span style="font-size:.65rem;font-weight:700;color:var(--mut);flex-shrink:0;">Ov {{ $currentOver + 1 }}</span>
    @foreach($currentOverBalls as $b)
        @php
            $cls = 'dot';
            $lbl = '·';
            if ($b->is_wicket)            { $cls = 'wicket'; $lbl = 'W'; }
            elseif ($b->is_six)           { $cls = 'six';    $lbl = '6'; }
            elseif ($b->is_four)          { $cls = 'four';   $lbl = '4'; }
            elseif ($b->is_wide)          { $cls = 'wide';   $lbl = 'Wd'; }
            elseif ($b->is_no_ball)       { $cls = 'noball'; $lbl = 'NB'; }
            elseif ($b->is_bye)           { $cls = 'bye';    $lbl = 'B'; }
            elseif ($b->is_leg_bye)       { $cls = 'legbye'; $lbl = 'LB'; }
            elseif (($b->runs_scored + $b->extra_runs) > 0) { $cls = 'run'; $lbl = $b->runs_scored + $b->extra_runs; }
        @endphp
        <div class="over-ball {{ $cls }}">{{ $lbl }}</div>
    @endforeach
    <div class="over-ball dot" style="border-style:dashed;opacity:.4;"></div>
</div>

{{-- Player chips (striker / non-striker / bowler) --}}
<div class="player-strip" id="player-strip">
    <div class="player-chip striker" id="chip-striker" onclick="openPlayerModal('striker')">
        <span class="role-lbl">Striker ✦</span>
        <span class="name-lbl" id="striker-name">Tap to set</span>
    </div>
    <div class="player-chip" id="chip-non-striker" onclick="openPlayerModal('non_striker')">
        <span class="role-lbl">Non-striker</span>
        <span class="name-lbl" id="non-striker-name">Tap to set</span>
    </div>
    <div class="player-chip bowler-chip" id="chip-bowler" onclick="openPlayerModal('bowler')">
        <span class="role-lbl">Bowler</span>
        <span class="name-lbl" id="bowler-name">Tap to set</span>
    </div>
</div>

{{-- Main scoring body --}}
<div class="scoring-body" id="scoring-body">

    {{-- Run buttons --}}
    <div class="run-grid">
        <button class="run-btn btn-0" onclick="selectRuns(0)"><span>0</span><span class="sub-lbl">Undi</span></button>
        <button class="run-btn btn-1" onclick="selectRuns(1)"><span>1</span></button>
        <button class="run-btn btn-2" onclick="selectRuns(2)"><span>2</span></button>
        <button class="run-btn btn-3" onclick="selectRuns(3)"><span>3</span></button>
        <button class="run-btn btn-4" onclick="selectRuns(4)"><span>4</span><span class="sub-lbl">Boundary</span></button>
        <button class="run-btn btn-6" onclick="selectRuns(6)"><span>6</span><span class="sub-lbl">Six!</span></button>
    </div>

    {{-- Extras --}}
    <div class="extras-row">
        <button class="extra-btn btn-wd" id="btn-wd" onclick="toggleExtra('wide')">
            Wd<span class="sub">Wide</span>
        </button>
        <button class="extra-btn btn-nb" id="btn-nb" onclick="toggleExtra('no_ball')">
            NB<span class="sub">No Ball</span>
        </button>
        <button class="extra-btn btn-bye" id="btn-bye" onclick="toggleExtra('bye')">
            Bye<span class="sub">Bye</span>
        </button>
        <button class="extra-btn btn-lb" id="btn-lb" onclick="toggleExtra('leg_bye')">
            LB<span class="sub">Leg Bye</span>
        </button>
    </div>

    {{-- Wicket + Undo --}}
    <div class="action-row">
        <button class="btn-wicket" id="btn-wicket" onclick="toggleWicket()">
            <i class="bi bi-exclamation-octagon-fill"></i> WICKET
        </button>
        <button class="btn-undo" onclick="undoBall()">
            <i class="bi bi-arrow-counterclockwise"></i> UNDO
        </button>
    </div>

    {{-- Wicket panel --}}
    <div id="wicket-panel" style="display:none;">
        <div style="font-size:.75rem;font-weight:700;color:var(--red);margin-bottom:.625rem;">
            <i class="bi bi-exclamation-octagon-fill"></i> Wicket Type
        </div>
        <div class="wicket-type-grid">
            @foreach(['bowled'=>'Bowled','caught'=>'Caught','lbw'=>'LBW','run_out'=>'Run Out','stumped'=>'Stumped','hit_wicket'=>'Hit Wkt'] as $val => $lbl)
            <button class="wt-btn" data-type="{{ $val }}" onclick="selectWicketType('{{ $val }}')">{{ $lbl }}</button>
            @endforeach
        </div>
        <div style="display:flex;gap:.5rem;">
            <div style="flex:1;">
                <div class="innings-label">Fielder (optional)</div>
                <select id="fielder-select" class="innings-select" style="padding:.5rem .75rem;font-size:.8rem;">
                    <option value="">None</option>
                    @foreach($bowlers as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;">
                <div class="innings-label">New batsman</div>
                <select id="new-batsman-select" class="innings-select" style="padding:.5rem .75rem;font-size:.8rem;">
                    <option value="">— set after —</option>
                    @foreach($players as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Extra runs input (shown when wide/no-ball/bye/leg-bye active) --}}
    <div id="extra-runs-wrap" style="display:none;margin-bottom:.75rem;">
        <div class="innings-label">Extra runs</div>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <button onclick="changeExtraRuns(-1)" style="width:36px;height:36px;border-radius:50%;border:1.5px solid var(--bd);background:var(--s2);font-size:1.1rem;font-weight:700;cursor:pointer;">−</button>
            <span id="extra-runs-val" style="font-size:1.3rem;font-weight:900;min-width:40px;text-align:center;">1</span>
            <button onclick="changeExtraRuns(1)"  style="width:36px;height:36px;border-radius:50%;border:1.5px solid var(--bd);background:var(--s2);font-size:1.1rem;font-weight:700;cursor:pointer;">+</button>
        </div>
    </div>

    {{-- Complete match --}}
    <div style="margin-top:.25rem;padding-top:.75rem;border-top:1px solid var(--bd);">
        <button class="btn-complete-secondary" onclick="completeMatch()" style="width:100%;padding:.65rem;">
            <i class="bi bi-flag-fill"></i> End Match
        </button>
    </div>

    <div style="height:1rem;"></div>
</div>
@endif

</div>{{-- #scoring-wrap --}}

{{-- Player selection modal --}}
<div id="player-modal" onclick="if(event.target===this) closePlayerModal()">
    <div class="player-modal-sheet">
        <div class="player-modal-header">
            <span id="modal-title">Select Player</span>
            <button onclick="closePlayerModal()" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:var(--mut);padding:0;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="player-modal-list" id="modal-player-list"></div>
    </div>
</div>

{{-- Spinner --}}
<div id="sc-spinner"><div style="background:#fff;border-radius:12px;padding:1rem 1.5rem;font-size:.85rem;font-weight:700;color:var(--txt);">Recording…</div></div>

@endsection

@push('scripts')
<script>
var MATCH_ID    = {{ $match->id }};
var INNINGS_ID  = {{ $currentInnings?->id ?? 'null' }};
var HOME_TEAM_ID = {{ $match->home_team_id }};
var AWAY_TEAM_ID = {{ $match->away_team_id }};

var HOME_PLAYERS = @json($homePlayers->map(fn($p)=>['id'=>$p->id,'name'=>$p->name])->values());
var AWAY_PLAYERS = @json($awayPlayers->map(fn($p)=>['id'=>$p->id,'name'=>$p->name])->values());
var BATTING_TEAM_ID = {{ $currentInnings?->batting_team_id ?? 'null' }};

/* ── State ──────────────────────────────────────────── */
var state = {
    batsmanId:     null,
    nonStrikerId:  null,
    bowlerId:      null,
    runsSelected:  null,
    isWide:   false,
    isNoBall: false,
    isBye:    false,
    isLegBye: false,
    isWicket: false,
    wicketType: null,
    fielderId: null,
    extraRuns: 1,
    modalFor: null,
};

/* ── Player lists ───────────────────────────────────── */
function getPlayerList(role) {
    if (INNINGS_ID === null) return [];
    if (role === 'bowler') {
        return BATTING_TEAM_ID === HOME_TEAM_ID ? AWAY_PLAYERS : HOME_PLAYERS;
    }
    return BATTING_TEAM_ID === HOME_TEAM_ID ? HOME_PLAYERS : AWAY_PLAYERS;
}

/* ── Run selection ──────────────────────────────────── */
function selectRuns(r) {
    state.runsSelected = r;
    /* If wide/no-ball, ask for extra runs separately, else auto-submit */
    if (state.isWide || state.isNoBall || state.isBye || state.isLegBye) {
        /* Extra runs set manually — commit */
        commitBall(r);
    } else {
        commitBall(r);
    }
}

/* ── Extra toggles ──────────────────────────────────── */
function toggleExtra(type) {
    /* Mutual exclusivity: wide vs no-ball */
    if (type === 'wide' && state.isNoBall) { state.isNoBall = false; $('#btn-nb').removeClass('active'); }
    if (type === 'no_ball' && state.isWide) { state.isWide = false; $('#btn-wd').removeClass('active'); }

    var map = { wide:'isWide', no_ball:'isNoBall', bye:'isBye', leg_bye:'isLegBye' };
    var btnMap = { wide:'btn-wd', no_ball:'btn-nb', bye:'btn-bye', leg_bye:'btn-lb' };
    state[map[type]] = !state[map[type]];
    $('#' + btnMap[type]).toggleClass('active', state[map[type]]);

    var anyExtra = state.isWide || state.isNoBall || state.isBye || state.isLegBye;
    $('#extra-runs-wrap').toggle(anyExtra);
}

function changeExtraRuns(delta) {
    state.extraRuns = Math.max(0, state.extraRuns + delta);
    $('#extra-runs-val').text(state.extraRuns);
}

/* ── Wicket ─────────────────────────────────────────── */
function toggleWicket() {
    state.isWicket = !state.isWicket;
    $('#btn-wicket').toggleClass('active', state.isWicket);
    $('#wicket-panel').toggle(state.isWicket);
    if (!state.isWicket) { state.wicketType = null; $('.wt-btn').removeClass('active'); }
}
function selectWicketType(t) {
    state.wicketType = t;
    $('.wt-btn').removeClass('active');
    $('.wt-btn[data-type="' + t + '"]').addClass('active');
}

/* ── Undo ───────────────────────────────────────────── */
function undoBall() {
    if (!INNINGS_ID) return;
    if (!confirm('Undo the last delivery?')) return;
    showSpinner();
    $.ajax({
        url: '/score/' + MATCH_ID + '/innings/' + INNINGS_ID + '/undo',
        type: 'DELETE',
        success: function(r) {
            if (r.success) location.reload();
            else { showToast('Nothing to undo', 'err'); }
        },
        error: function() { showToast('Undo failed', 'err'); },
        complete: hideSpinner,
    });
}

/* ── Commit ball ────────────────────────────────────── */
function commitBall(runsScored) {
    if (!INNINGS_ID) { showToast('No active innings', 'err'); return; }
    if (!state.batsmanId) { showToast('Set the striker first', 'err'); openPlayerModal('striker'); return; }
    if (!state.bowlerId)  { showToast('Set the bowler first', 'err');  openPlayerModal('bowler');  return; }
    if (state.isWicket && !state.wicketType) { showToast('Select wicket type', 'err'); return; }

    var isFour = runsScored === 4 && !state.isWide && !state.isNoBall && !state.isBye && !state.isLegBye;
    var isSix  = runsScored === 6 && !state.isWide && !state.isNoBall && !state.isBye && !state.isLegBye;

    var extraRuns = (state.isWide || state.isNoBall || state.isBye || state.isLegBye) ? state.extraRuns : 0;

    var payload = {
        batsman_id:     state.batsmanId,
        bowler_id:      state.bowlerId,
        non_striker_id: state.nonStrikerId,
        runs_scored:    runsScored,
        extra_runs:     extraRuns,
        is_wide:        state.isWide   ? 1 : 0,
        is_no_ball:     state.isNoBall ? 1 : 0,
        is_bye:         state.isBye    ? 1 : 0,
        is_leg_bye:     state.isLegBye ? 1 : 0,
        is_wicket:      state.isWicket ? 1 : 0,
        wicket_type:    state.wicketType,
        fielder_id:     state.isWicket ? ($('#fielder-select').val() || null) : null,
        is_four:        isFour ? 1 : 0,
        is_six:         isSix  ? 1 : 0,
    };

    showSpinner();
    $.post('/score/' + MATCH_ID + '/innings/' + INNINGS_ID + '/ball', payload, function(r) {
        if (r.is_over) {
            showToast('Innings complete!', 'ok');
            setTimeout(function(){ location.reload(); }, 1200);
        } else {
            location.reload();
        }
    }).fail(function(xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error recording ball';
        showToast(msg, 'err');
        hideSpinner();
    });
}

/* ── Complete match ──────────────────────────────────── */
function completeMatch() {
    if (!confirm('Mark this match as completed?')) return;
    showSpinner();
    $.post('/score/' + MATCH_ID + '/complete', {}, function() {
        showToast('Match completed!', 'ok');
        setTimeout(function(){ window.location = '/score'; }, 1500);
    }).fail(function() { showToast('Failed to complete match', 'err'); hideSpinner(); });
}

/* ── Player modal ───────────────────────────────────── */
function openPlayerModal(role) {
    state.modalFor = role;
    var titles = { striker:'Select Striker', non_striker:'Select Non-striker', bowler:'Select Bowler' };
    $('#modal-title').text(titles[role] || 'Select Player');
    var players = getPlayerList(role);
    var html = '';
    players.forEach(function(p) {
        var initials = p.name.substring(0,2).toUpperCase();
        html += '<div class="player-modal-item" onclick="selectPlayer(' + p.id + ',\'' + p.name.replace(/'/g,"\\'") + '\')">'
            + '<div class="avatar">' + initials + '</div>'
            + '<div style="font-weight:700;font-size:.88rem;color:var(--txt);">' + p.name + '</div>'
            + '</div>';
    });
    if (!html) html = '<div style="padding:2rem;text-align:center;color:var(--mut);">No players found</div>';
    $('#modal-player-list').html(html);
    $('#player-modal').addClass('open');
}

function closePlayerModal() {
    $('#player-modal').removeClass('open');
}

function selectPlayer(id, name) {
    var role = state.modalFor;
    if (role === 'striker')      { state.batsmanId = id;    $('#striker-name').text(name);     $('#chip-striker').addClass('striker'); }
    if (role === 'non_striker')  { state.nonStrikerId = id; $('#non-striker-name').text(name); }
    if (role === 'bowler')       { state.bowlerId = id;     $('#bowler-name').text(name);      }
    closePlayerModal();
}

/* ── Start Innings (form) ───────────────────────────── */
@if(!$currentInnings)
$('#start-innings-form').on('submit', function(e) {
    e.preventDefault();
    var bat = $('#batting-team').val();
    var bowl = $('#bowling-team').val();
    if (!bat || !bowl || bat === bowl) { showToast('Select different teams', 'err'); return; }
    showSpinner();
    $.post('/score/' + MATCH_ID + '/start-innings', $(this).serialize(), function() {
        location.reload();
    }).fail(function() { showToast('Failed to start innings', 'err'); hideSpinner(); });
});
/* Auto-fill bowling team when batting team changes */
$('#batting-team').on('change', function() {
    var bat = $(this).val();
    $('#bowling-team option').each(function() { $(this).prop('disabled', $(this).val() === bat); });
});
@endif

/* ── Spinner helpers ────────────────────────────────── */
function showSpinner() { $('#sc-spinner').addClass('on'); }
function hideSpinner() { $('#sc-spinner').removeClass('on'); }
</script>
@endpush
