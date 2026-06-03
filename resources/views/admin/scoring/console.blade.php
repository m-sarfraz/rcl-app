@extends('layouts.admin')
@section('title','Scoring Console')
@section('page-title','Live Scoring Console — ' . $match->homeTeam?->short_code . ' vs ' . $match->awayTeam?->short_code)

@push('styles')
<style>
.score-display { font-size:2.5rem;font-weight:900;letter-spacing:.02em; }
.ball-btn { width:52px;height:52px;border-radius:50%;border:2px solid var(--rcl-border);background:var(--rcl-surface2);color:var(--rcl-text);font-weight:800;font-size:1rem;cursor:pointer;transition:all .15s; }
.ball-btn:hover,.ball-btn.active { border-color:var(--rcl-primary);background:rgba(0,230,118,.15);color:var(--rcl-primary); }
.ball-btn.six { border-color:var(--rcl-gold);color:var(--rcl-gold); }
.ball-btn.wicket { border-color:#ef4444;color:#ef4444; }
.over-ball { display:inline-flex;width:30px;height:30px;border-radius:50%;border:2px solid var(--rcl-border);align-items:center;justify-content:center;font-size:.75rem;font-weight:700;margin:.1rem; }
.over-ball.dot { color:var(--rcl-muted); }
.over-ball.run { background:rgba(0,230,118,.15);border-color:var(--rcl-primary);color:var(--rcl-primary); }
.over-ball.four { background:rgba(0,230,118,.25);border-color:var(--rcl-primary);color:var(--rcl-primary); }
.over-ball.six { background:rgba(255,214,0,.2);border-color:var(--rcl-gold);color:var(--rcl-gold); }
.over-ball.wicket { background:rgba(239,68,68,.2);border-color:#ef4444;color:#ef4444; }
.over-ball.wide,.over-ball.no-ball { background:rgba(124,77,255,.15);border-color:#7c4dff;color:#7c4dff; }
</style>
@endpush

@section('content')
<div class="row g-3">
    {{-- Scoreboard --}}
    <div class="col-md-8">
        <div class="rcl-card mb-3">
            <div class="rcl-card-body">
                <div class="row g-3 text-center">
                    <div class="col-6" style="border-right:1px solid var(--rcl-border);">
                        <div style="font-size:.75rem;color:var(--rcl-muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em;">{{ $match->homeTeam?->name }}</div>
                        <div id="score-home" class="score-display" style="color:var(--rcl-primary);">—</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.75rem;color:var(--rcl-muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em;">{{ $match->awayTeam?->name }}</div>
                        <div id="score-away" class="score-display">—</div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <span id="current-over-display" style="font-size:.875rem;color:var(--rcl-muted);">{{ $match->overs_per_side }} overs</span>
                    <span id="rrr-display" style="font-size:.875rem;color:var(--rcl-gold);margin-left:1rem;"></span>
                </div>
                <div class="text-center mt-2" id="over-balls" style="min-height:40px;"></div>
            </div>
        </div>

        {{-- Delivery Input --}}
        @if(!$currentInnings || !$currentInnings->is_completed)
        <div class="rcl-card mb-3" id="delivery-panel">
            <div class="rcl-card-header">Record Delivery</div>
            <div class="rcl-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Batsman on Strike</label>
                        <select id="batsman_id" class="form-select">
                            <option value="">Select batsman</option>
                            @foreach(($currentInnings?->innings_number == 1 ? $homePlayers : $awayPlayers) ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Bowler</label>
                        <select id="bowler_id" class="form-select">
                            <option value="">Select bowler</option>
                            @foreach(($currentInnings?->innings_number == 1 ? $awayPlayers : $homePlayers) ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Runs Scored</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach([0,1,2,3,4,6] as $r)
                        <button class="ball-btn {{ $r==6?'six':'' }}" data-run="{{ $r }}" onclick="selectRun(this)">{{ $r }}</button>
                        @endforeach
                        <button class="ball-btn wicket" onclick="selectWicket()">W</button>
                    </div>
                </div>

                <div id="wicket-panel" style="display:none;" class="mb-3 p-3 rounded" style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.2);">
                    <label class="form-label">Wicket Type</label>
                    <select id="wicket_type" class="form-select mb-2">
                        <option value="">— Select —</option>
                        <option value="bowled">Bowled</option>
                        <option value="caught">Caught</option>
                        <option value="run_out">Run Out</option>
                        <option value="lbw">LBW</option>
                        <option value="stumped">Stumped</option>
                        <option value="hit_wicket">Hit Wicket</option>
                    </select>
                    <label class="form-label">Fielder (optional)</label>
                    <select id="fielder_id" class="form-select">
                        <option value="">—</option>
                        @foreach(($currentInnings?->innings_number == 1 ? $awayPlayers : $homePlayers) ?? [] as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Extras</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(['Wide','No Ball','Bye','Leg Bye'] as $ex)
                            <button class="btn btn-sm" style="border:1px solid var(--rcl-border);background:var(--rcl-surface2);color:var(--rcl-muted);border-radius:6px;font-size:.75rem;" data-extra="{{ Str::snake($ex) }}" onclick="toggleExtra(this)">{{ $ex }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Extra Runs</label>
                        <input type="number" id="extra_runs" class="form-control" value="0" min="0" max="10">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-rcl-primary flex-1" id="record-btn" onclick="recordDelivery()">
                        <i class="bi bi-check-circle-fill"></i> Record Ball
                    </button>
                    <button class="btn btn-rcl-secondary" onclick="undoLast()">
                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Complete Match --}}
        @if($match->status === 'live')
        <div class="rcl-card">
            <div class="rcl-card-body text-center">
                <button class="btn btn-rcl-danger" onclick="if(confirm('Complete this match?')) completeMatch()">
                    <i class="bi bi-flag-fill"></i> Complete Match
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- Innings Setup / Info --}}
    <div class="col-md-4">
        @if(!$currentInnings)
        <div class="rcl-card mb-3">
            <div class="rcl-card-header">Start Innings</div>
            <div class="rcl-card-body">
                <div class="mb-2">
                    <label class="form-label">Batting Team</label>
                    <select id="batting_team_id" class="form-select">
                        <option value="{{ $match->home_team_id }}">{{ $match->homeTeam?->name }}</option>
                        <option value="{{ $match->away_team_id }}">{{ $match->awayTeam?->name }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Innings #</label>
                    <select id="innings_number" class="form-select">
                        <option value="1">1st Innings</option>
                        <option value="2">2nd Innings</option>
                    </select>
                </div>
                <button class="btn btn-rcl-primary w-100" onclick="startInnings()">Start Innings</button>
            </div>
        </div>
        @else
        <div class="rcl-card mb-3">
            <div class="rcl-card-header">Current Innings</div>
            <div class="rcl-card-body" style="font-size:.85rem;">
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:var(--rcl-muted);">Batting</span>
                    <span style="font-weight:700;">{{ $currentInnings->battingTeam?->name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:var(--rcl-muted);">Bowling</span>
                    <span style="font-weight:700;">{{ $currentInnings->bowlingTeam?->name }}</span>
                </div>
                @if($currentInnings->target)
                <div class="d-flex justify-content-between">
                    <span style="color:var(--rcl-muted);">Target</span>
                    <span style="font-weight:900;color:var(--rcl-gold);">{{ $currentInnings->target }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Recent balls --}}
        <div class="rcl-card">
            <div class="rcl-card-header">Recent Deliveries</div>
            <div class="rcl-card-body" id="recent-balls" style="font-size:.8rem;min-height:80px;">
                <div style="color:var(--rcl-muted);text-align:center;padding:1rem;">No deliveries yet</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var matchId   = {{ $match->id }};
var inningsId = {{ $currentInnings?->id ?? 'null' }};
var selectedRun = 0;
var selectedWicket = false;
var selectedExtras = {};

function selectRun(btn) {
    $('.ball-btn').removeClass('active');
    $(btn).addClass('active');
    selectedRun = parseInt($(btn).data('run'));
    selectedWicket = false;
    $('#wicket-panel').hide();
}

function selectWicket() {
    $('.ball-btn').removeClass('active');
    $('.ball-btn.wicket').addClass('active');
    selectedWicket = true;
    $('#wicket-panel').slideDown(200);
}

function toggleExtra(btn) {
    var ex = $(btn).data('extra');
    if (selectedExtras[ex]) {
        delete selectedExtras[ex];
        $(btn).css({ borderColor:'var(--rcl-border)', color:'var(--rcl-muted)' });
    } else {
        selectedExtras[ex] = true;
        $(btn).css({ borderColor:'#7c4dff', color:'#7c4dff' });
    }
}

function recordDelivery() {
    if (!inningsId) { showToast('Start innings first!', 'error'); return; }
    var payload = {
        batsman_id:  $('#batsman_id').val(),
        bowler_id:   $('#bowler_id').val(),
        runs_scored: selectedRun,
        extra_runs:  parseInt($('#extra_runs').val()) || 0,
        is_wide:     selectedExtras['wide']    ? 1 : 0,
        is_no_ball:  selectedExtras['no_ball'] ? 1 : 0,
        is_bye:      selectedExtras['bye']     ? 1 : 0,
        is_leg_bye:  selectedExtras['leg_bye'] ? 1 : 0,
        is_wicket:   selectedWicket ? 1 : 0,
        wicket_type: selectedWicket ? $('#wicket_type').val() : null,
        fielder_id:  $('#fielder_id').val() || null,
        is_four:     selectedRun === 4 ? 1 : 0,
        is_six:      selectedRun === 6 ? 1 : 0,
    };

    if (!payload.batsman_id || !payload.bowler_id) {
        showToast('Select batsman and bowler first.', 'error');
        return;
    }

    $('#record-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.post('/admin/matches/' + matchId + '/innings/' + inningsId + '/ball', payload, function(res) {
        refreshState();
        resetInputs();
        if (res.is_over) showToast('Innings completed!', 'success');
    }).fail(function(xhr) {
        showToast(xhr.responseJSON?.message || 'Error recording ball.', 'error');
    }).always(function() {
        $('#record-btn').prop('disabled', false).html('<i class="bi bi-check-circle-fill"></i> Record Ball');
    });
}

function startInnings() {
    $.post('/admin/matches/' + matchId + '/start-innings', {
        batting_team_id: $('#batting_team_id').val(),
        bowling_team_id: $('#batting_team_id').val() == {{ $match->home_team_id }} ? {{ $match->away_team_id }} : {{ $match->home_team_id }},
        innings_number:  $('#innings_number').val(),
    }, function(res) {
        inningsId = res.innings.id;
        location.reload();
    }).fail(function(xhr) {
        showToast(xhr.responseJSON?.message || 'Error starting innings.', 'error');
    });
}

function completeMatch() {
    $.post('/admin/matches/' + matchId + '/complete', {}, function() {
        showToast('Match completed!', 'success');
        setTimeout(function() { window.location = '/admin/matches'; }, 1500);
    });
}

function undoLast() {
    showToast('Undo feature — delete last ball from DB.', 'info');
}

function refreshState() {
    $.get('/admin/matches/' + matchId + '/live-state', function(data) {
        if (data.innings) {
            var inn = data.innings;
            var teamId = inn.batting_team_id;
            var score  = inn.total_runs + '/' + inn.total_wickets;
            var overs  = Math.floor(inn.total_balls/6) + '.' + (inn.total_balls%6);
            if (teamId == {{ $match->home_team_id }}) {
                $('#score-home').text(score);
            } else {
                $('#score-away').text(score);
            }
            $('#current-over-display').text(overs + ' / {{ $match->overs_per_side }} overs');
        }
        if (data.rrr) $('#rrr-display').text('RRR: ' + data.rrr);
    });
}

function resetInputs() {
    selectedRun = 0; selectedWicket = false; selectedExtras = {};
    $('.ball-btn').removeClass('active');
    $('[data-extra]').css({ borderColor:'var(--rcl-border)', color:'var(--rcl-muted)' });
    $('#extra_runs').val(0);
    $('#wicket-panel').hide();
}

// Show admin toast
function showToast(msg, type) {
    var classes = type === 'success' ? 'alert-rcl-success' : 'alert-rcl-error';
    var $t = $('<div class="' + classes + ' mb-2 d-flex align-items-center gap-2">' + msg + '</div>');
    $('#main-content .content-area').prepend($t);
    setTimeout(function() { $t.fadeOut(300, function() { $t.remove(); }); }, 3000);
}

// Auto-refresh every 10 seconds
setInterval(refreshState, 10000);
refreshState();
</script>
@endpush
