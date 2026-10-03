@extends('layouts.admin')
@section('title', 'Manage Match #' . $match->match_number)
@section('page-title', 'Manage Match #' . $match->match_number . ' — ' . ($match->homeTeam?->short_code ?? 'TBD') . ' vs ' . ($match->awayTeam?->short_code ?? 'TBD'))

@section('topbar-actions')
<div class="d-flex gap-2 flex-wrap">
    <form method="POST" action="{{ route('admin.matches.manage.rebuild', $match) }}" class="d-inline" onsubmit="return confirm('Rebuild and synchronize entire match statistics, scorecards, and targets?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning" title="Recalculates all overs, running totals, scorecards and targets from ball logs">
            <i class="bi bi-arrow-repeat"></i> Rebuild Entire Match
        </button>
    </form>
    <a href="{{ route('admin.scorecard.show', $match) }}" target="_blank" class="topbar-btn">
        <i class="bi bi-card-text"></i> View Card
    </a>
    <a href="{{ route('embed.widget', ['match' => $match->id, 'layout' => 'broadcast']) }}" target="_blank" class="topbar-btn">
        <i class="bi bi-broadcast"></i> Broadcast Bar
    </a>
    <a href="{{ route('admin.matches.index') }}" class="topbar-btn">
        <i class="bi bi-arrow-left"></i> All Matches
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="alert alert-rcl-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-rcl-error alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-rcl-error alert-dismissible fade show mb-3" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Match Overview Card --}}
    <div class="rcl-card mb-4">
        <div class="rcl-card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-sliders text-success fs-5"></i>
                <span class="fs-6">Match Controls & Information</span>
                <span class="badge-rcl badge-{{ $match->status === 'live' ? 'live' : ($match->status === 'completed' ? 'completed' : 'upcoming') }} ms-2">
                    {{ strtoupper($match->status) }}
                </span>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#matchControlsCollapse">
                <i class="bi bi-chevron-down"></i> Toggle Form
            </button>
        </div>
        <div class="collapse show" id="matchControlsCollapse">
            <div class="rcl-card-body">
                <form method="POST" action="{{ route('admin.matches.manage.match', $match) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Match Status</label>
                            <select name="status" class="form-select form-select-sm">
                                @foreach(['upcoming' => 'Upcoming', 'live' => 'Live in Play', 'completed' => 'Completed', 'abandoned' => 'Abandoned', 'postponed' => 'Postponed'] as $sVal => $sLabel)
                                <option value="{{ $sVal }}" {{ $match->status === $sVal ? 'selected' : '' }}>{{ $sLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Overs Per Side</label>
                            <input type="number" name="overs_per_side" class="form-control form-control-sm" value="{{ $match->overs_per_side }}" min="1" max="50" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Venue</label>
                            <input type="text" name="venue" class="form-control form-control-sm" value="{{ $match->venue }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Toss Winner</label>
                            <select name="toss_winner_id" class="form-select form-select-sm">
                                <option value="">-- Select Winner --</option>
                                <option value="{{ $match->home_team_id }}" {{ $match->toss_winner_id === $match->home_team_id ? 'selected' : '' }}>{{ $match->homeTeam?->name }}</option>
                                <option value="{{ $match->away_team_id }}" {{ $match->toss_winner_id === $match->away_team_id ? 'selected' : '' }}>{{ $match->awayTeam?->name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Toss Decision</label>
                            <select name="toss_decision" class="form-select form-select-sm">
                                <option value="">-- Decision --</option>
                                <option value="bat" {{ $match->toss_decision === 'bat' ? 'selected' : '' }}>Bat First</option>
                                <option value="field" {{ $match->toss_decision === 'field' ? 'selected' : '' }}>Field First</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Match Winner</label>
                            <select name="winner_id" class="form-select form-select-sm">
                                <option value="">-- No Winner Yet --</option>
                                <option value="{{ $match->home_team_id }}" {{ $match->winner_id === $match->home_team_id ? 'selected' : '' }}>{{ $match->homeTeam?->name }}</option>
                                <option value="{{ $match->away_team_id }}" {{ $match->winner_id === $match->away_team_id ? 'selected' : '' }}>{{ $match->awayTeam?->name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Result Type</label>
                            <select name="result_type" class="form-select form-select-sm">
                                <option value="">-- Result Type --</option>
                                <option value="runs" {{ $match->result_type === 'runs' ? 'selected' : '' }}>Runs</option>
                                <option value="wickets" {{ $match->result_type === 'wickets' ? 'selected' : '' }}>Wickets</option>
                                <option value="tie" {{ $match->result_type === 'tie' ? 'selected' : '' }}>Tie</option>
                                <option value="super_over" {{ $match->result_type === 'super_over' ? 'selected' : '' }}>Super Over</option>
                                <option value="no_result" {{ $match->result_type === 'no_result' ? 'selected' : '' }}>No Result</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Result Margin</label>
                            <input type="number" name="result_margin" class="form-control form-control-sm" value="{{ $match->result_margin }}" placeholder="e.g. 15 or 4">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-bold">Result Description</label>
                            <input type="text" name="result_description" class="form-control form-control-sm" value="{{ $match->result_description }}" placeholder="e.g. Azaad CC won by 15 runs">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-rcl-primary w-100 btn-sm py-2">
                                <i class="bi bi-save me-1"></i> Save Match Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Innings Tabs --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <ul class="nav nav-pills gap-2" id="inningsTab">
            @forelse($inningsList as $inn)
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fw-bold {{ $activeInnings && $activeInnings->id === $inn->id ? 'active' : '' }}"
                   href="{{ route('admin.matches.manage', ['match' => $match->id, 'innings_id' => $inn->id]) }}"
                   style="{{ $activeInnings && $activeInnings->id === $inn->id ? 'background: var(--rcl-primary); color: #000;' : 'background: var(--rcl-surface2); color: var(--rcl-text); border: 1px solid var(--rcl-border);' }}">
                    <span>Innings {{ $inn->innings_number }}: {{ $inn->battingTeam?->short_code ?? 'TBD' }}</span>
                    <span class="badge {{ $inn->is_completed ? 'bg-secondary' : 'bg-danger' }} ms-1" style="font-size:.68rem;">
                        {{ $inn->total_runs }}/{{ $inn->total_wickets }} ({{ $inn->overs_faced }} ov)
                    </span>
                    @if($inn->is_completed)
                    <i class="bi bi-check2 text-dark ms-1" title="Innings Completed"></i>
                    @else
                    <span class="spinner-grow spinner-grow-sm text-warning ms-1" style="width: 8px; height: 8px;" role="status"></span>
                    @endif
                </a>
            </li>
            @empty
            <li class="nav-item text-muted p-2">No innings recorded yet.</li>
            @endforelse
        </ul>

        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#createInningsModal">
            <i class="bi bi-plus-circle me-1"></i> Create New Innings
        </button>
    </div>

    @if($activeInnings)
    {{-- Active Innings Details Card --}}
    <div class="rcl-card mb-4 border-success">
        <div class="rcl-card-header d-flex justify-content-between align-items-center bg-dark py-2">
            <div>
                <span class="fw-bold text-success">Innings {{ $activeInnings->innings_number }} — {{ $activeInnings->battingTeam?->name }} (Batting) vs {{ $activeInnings->bowlingTeam?->name }} (Bowling)</span>
                @if($activeInnings->is_completed)
                <span class="badge bg-secondary ms-2">Completed</span>
                @else
                <span class="badge bg-danger ms-2">In Progress</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.matches.manage.innings.rebuild', ['match' => $match->id, 'innings' => $activeInnings->id]) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info" title="Recomputes totals, extras, scorecards from ball-by-ball">
                        <i class="bi bi-calculator"></i> Re-Calculate This Innings
                    </button>
                </form>
            </div>
        </div>
        <div class="rcl-card-body">
            {{-- Innings Stats Bar --}}
            <div class="row g-2 text-center mb-3">
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Score</small>
                        <span class="fs-5 fw-bold text-warning">{{ $activeInnings->total_runs }} / {{ $activeInnings->total_wickets }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Overs (Balls)</small>
                        <span class="fs-5 fw-bold text-light">{{ $activeInnings->overs_faced }} ov ({{ $activeInnings->total_balls }}b)</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Run Rate</small>
                        <span class="fs-5 fw-bold text-light">{{ $activeInnings->run_rate }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Target</small>
                        <span class="fs-5 fw-bold text-info">{{ $activeInnings->target ? $activeInnings->target : 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Extras Total</small>
                        <span class="fs-5 fw-bold text-light">{{ $activeInnings->extras_wides + $activeInnings->extras_no_balls + $activeInnings->extras_byes + $activeInnings->extras_leg_byes + $activeInnings->extras_penalty }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="p-2 rounded bg-dark border border-secondary">
                        <small class="text-muted d-block">Wd / Nb / B / Lb</small>
                        <span class="fs-6 fw-bold text-muted">{{ $activeInnings->extras_wides }}w, {{ $activeInnings->extras_no_balls }}nb, {{ $activeInnings->extras_byes }}b, {{ $activeInnings->extras_leg_byes }}lb</span>
                    </div>
                </div>
            </div>

            {{-- Innings Settings Form --}}
            <form method="POST" action="{{ route('admin.matches.manage.innings.update', ['match' => $match->id, 'innings' => $activeInnings->id]) }}">
                @csrf
                @method('PUT')
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Innings Completed?</label>
                        <select name="is_completed" class="form-select form-select-sm">
                            <option value="1" {{ $activeInnings->is_completed ? 'selected' : '' }}>Yes - Innings is Completed</option>
                            <option value="0" {{ ! $activeInnings->is_completed ? 'selected' : '' }}>No - Innings is In Progress</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Target Runs</label>
                        <input type="number" name="target" class="form-control form-control-sm" value="{{ $activeInnings->target }}" placeholder="e.g. 111">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Batting Team</label>
                        <select name="batting_team_id" class="form-select form-select-sm" required>
                            <option value="{{ $match->home_team_id }}" {{ $activeInnings->batting_team_id === $match->home_team_id ? 'selected' : '' }}>{{ $match->homeTeam?->name }}</option>
                            <option value="{{ $match->away_team_id }}" {{ $activeInnings->batting_team_id === $match->away_team_id ? 'selected' : '' }}>{{ $match->awayTeam?->name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Bowling Team</label>
                        <select name="bowling_team_id" class="form-select form-select-sm" required>
                            <option value="{{ $match->home_team_id }}" {{ $activeInnings->bowling_team_id === $match->home_team_id ? 'selected' : '' }}>{{ $match->homeTeam?->name }}</option>
                            <option value="{{ $match->away_team_id }}" {{ $activeInnings->bowling_team_id === $match->away_team_id ? 'selected' : '' }}>{{ $match->awayTeam?->name }}</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-rcl-primary btn-sm w-100 py-1" title="Save and Recalculate">
                            <i class="bi bi-check-lg"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Deliveries & Ball-by-Ball Management --}}
    <div class="rcl-card">
        <div class="rcl-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-warning"></i>
                <span class="fs-6 fw-bold">Deliveries ({{ $allDeliveries->count() }} balls logged)</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-rcl-primary" data-bs-toggle="modal" data-bs-target="#addBallModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Delivery
                </button>
            </div>
        </div>

        {{-- Over Quick-Filter Pills --}}
        <div class="px-3 py-2 bg-dark border-bottom border-secondary d-flex align-items-center gap-1 flex-wrap">
            <span class="text-muted small fw-bold me-2">Overs:</span>
            <a href="{{ route('admin.matches.manage', ['match' => $match->id, 'innings_id' => $activeInnings->id]) }}"
               class="btn btn-sm py-0 px-2 {{ empty($selectedOver) ? 'btn-success' : 'btn-outline-secondary text-light' }}" style="font-size:.78rem;">
               All Overs
            </a>
            @foreach($ballsByOver as $overNum => $balls)
            @php $legalCount = $balls->where('is_wide', false)->where('is_no_ball', false)->count(); @endphp
            <a href="{{ route('admin.matches.manage', ['match' => $match->id, 'innings_id' => $activeInnings->id, 'over' => $overNum]) }}"
               class="btn btn-sm py-0 px-2 {{ $selectedOver == $overNum ? 'btn-success' : 'btn-outline-secondary text-light' }}" style="font-size:.78rem;">
               Over {{ $overNum }} ({{ $legalCount }}/6)
            </a>
            @endforeach
        </div>

        <div class="rcl-card-body p-0 table-responsive">
            <table class="rcl-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Ball #</th>
                        <th>Bowler</th>
                        <th>Batsman (Striker)</th>
                        <th>Non-Striker</th>
                        <th style="width: 120px;">Outcome</th>
                        <th>Score After</th>
                        <th>Wicket Detail</th>
                        <th>Notes</th>
                        <th style="width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $displayDeliveries = !empty($selectedOver) ? $allDeliveries->where('over_number', (int) $selectedOver) : $allDeliveries;
                    @endphp
                    @forelse($displayDeliveries as $b)
                    <tr>
                        <td class="fw-bold text-success font-monospace">
                            {{ $b->over_number }}.{{ $b->ball_number }}
                        </td>
                        <td>
                            <span class="fw-semibold text-light">{{ $b->bowler?->name ?? 'ID: '.$b->bowler_id }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-warning">{{ $b->batsman?->name ?? 'ID: '.$b->batsman_id }}</span>
                        </td>
                        <td class="text-muted small">
                            {{ $b->nonStriker?->name ?? '—' }}
                        </td>
                        <td>
                            @if($b->is_wicket)
                                <span class="badge bg-danger">W</span>
                            @endif

                            @if($b->runs_scored == 6)
                                <span class="badge bg-warning text-dark fw-bold">6</span>
                            @elseif($b->runs_scored == 4)
                                <span class="badge bg-primary fw-bold">4</span>
                            @elseif($b->runs_scored > 0 && !$b->is_wicket)
                                <span class="badge bg-dark border text-light">{{ $b->runs_scored }}</span>
                            @elseif($b->runs_scored == 0 && !$b->is_wicket && !$b->is_wide && !$b->is_no_ball && !$b->is_bye && !$b->is_leg_bye)
                                <span class="badge bg-secondary">0 (dot)</span>
                            @endif

                            @if($b->is_wide)
                                <span class="badge bg-purple text-light" style="background:#8b5cf6;">{{ $b->extra_runs }}wd</span>
                            @elseif($b->is_no_ball)
                                <span class="badge bg-warning text-dark">{{ $b->extra_runs }}nb</span>
                            @elseif($b->is_bye)
                                <span class="badge bg-info text-dark">{{ $b->extra_runs }}b</span>
                            @elseif($b->is_leg_bye)
                                <span class="badge bg-info text-dark">{{ $b->extra_runs }}lb</span>
                            @endif
                        </td>
                        <td class="font-monospace fw-bold text-light">
                            {{ $b->batting_team_score_after }}/{{ $b->batting_team_wickets_after }}
                        </td>
                        <td>
                            @if($b->is_wicket)
                            <div class="small text-danger">
                                <strong>{{ ucwords(str_replace('_',' ', (string) $b->wicket_type)) }}</strong>:
                                {{ $b->outPlayer?->name ?? 'Batsman' }}
                                @if($b->fielder)
                                <span class="text-muted">(c: {{ $b->fielder->name }})</span>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $b->commentary ?: '—' }}
                        </td>
                        <td style="text-align: right;">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 btn-edit-ball"
                                    data-ball="{{ json_encode([
                                        'id' => $b->id,
                                        'over_number' => $b->over_number,
                                        'ball_number' => $b->ball_number,
                                        'bowler_id' => $b->bowler_id,
                                        'batsman_id' => $b->batsman_id,
                                        'non_striker_id' => $b->non_striker_id,
                                        'runs_scored' => $b->runs_scored,
                                        'extra_type' => $b->is_wide ? 'wide' : ($b->is_no_ball ? 'no_ball' : ($b->is_bye ? 'bye' : ($b->is_leg_bye ? 'leg_bye' : ($b->is_penalty ? 'penalty' : 'none')))),
                                        'extra_runs' => $b->extra_runs,
                                        'is_wicket' => $b->is_wicket ? 1 : 0,
                                        'wicket_type' => $b->wicket_type,
                                        'out_player_id' => $b->out_player_id,
                                        'fielder_id' => $b->fielder_id,
                                        'commentary' => $b->commentary,
                                        'update_url' => route('admin.matches.manage.balls.update', ['match' => $match->id, 'innings' => $activeInnings->id, 'ball' => $b->id])
                                    ]) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.matches.manage.balls.destroy', ['match' => $match->id, 'innings' => $activeInnings->id, 'ball' => $b->id]) }}" class="d-inline" onsubmit="return confirm('Delete delivery {{ $b->over_number }}.{{ $b->ball_number }}? Statistics will be automatically recalculated.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            No deliveries found for this selection. Click <strong>Add Delivery</strong> to insert balls.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- Modal: Add Delivery --}}
@if($activeInnings)
@php
    $lastBall = $allDeliveries->last();
    $nextOver = $lastBall ? (int) $lastBall->over_number : 1;
    $nextBall = $lastBall ? ((int) $lastBall->ball_number + 1) : 1;
    if ($nextBall > 6) {
        $nextOver++;
        $nextBall = 1;
    }
@endphp
<div class="modal fade" id="addBallModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-secondary">
            <form method="POST" action="{{ route('admin.matches.manage.balls.store', ['match' => $match->id, 'innings' => $activeInnings->id]) }}">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-plus-circle me-2"></i>Add Delivery to Innings {{ $activeInnings->innings_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-bold">Over Number *</label>
                            <input type="number" name="over_number" class="form-control form-control-sm" value="{{ $selectedOver ?: $nextOver }}" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-bold">Ball Number *</label>
                            <input type="number" name="ball_number" class="form-control form-control-sm" value="{{ $nextBall }}" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Bowler *</label>
                            <select name="bowler_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Bowler --</option>
                                @foreach($bowlingPlayers as $p)
                                <option value="{{ $p->id }}" {{ $lastBall && $lastBall->bowler_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Batsman (Striker) *</label>
                            <select name="batsman_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Striker --</option>
                                @foreach($battingPlayers as $p)
                                <option value="{{ $p->id }}" {{ $lastBall && $lastBall->batsman_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Non-Striker</label>
                            <select name="non_striker_id" class="form-select form-select-sm">
                                <option value="">-- Choose Non-Striker --</option>
                                @foreach($battingPlayers as $p)
                                <option value="{{ $p->id }}" {{ $lastBall && $lastBall->non_striker_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Runs Scored *</label>
                            <select name="runs_scored" class="form-select form-select-sm" required>
                                <option value="0">0 (Dot ball)</option>
                                <option value="1">1 Run</option>
                                <option value="2">2 Runs</option>
                                <option value="3">3 Runs</option>
                                <option value="4">4 Runs (Boundary)</option>
                                <option value="5">5 Runs</option>
                                <option value="6">6 Runs (Six)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Extra Type</label>
                            <select name="extra_type" class="form-select form-select-sm" id="addBallExtraType">
                                <option value="none">None (Legal ball)</option>
                                <option value="wide">Wide</option>
                                <option value="no_ball">No Ball</option>
                                <option value="bye">Bye</option>
                                <option value="leg_bye">Leg Bye</option>
                                <option value="penalty">Penalty</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Extra Runs</label>
                            <input type="number" name="extra_runs" id="addBallExtraRuns" class="form-control form-control-sm" value="0" min="0" max="10">
                        </div>

                        {{-- Wicket Section --}}
                        <div class="col-12 border-top border-secondary pt-2 mt-2">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_wicket" value="1" id="addBallIsWicket">
                                <label class="form-check-label fw-bold text-danger" for="addBallIsWicket">A Wicket Fell on this Delivery</label>
                            </div>
                            <div class="row g-2 d-none" id="addBallWicketFields">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Dismissal Type</label>
                                    <select name="wicket_type" class="form-select form-select-sm">
                                        <option value="bowled">Bowled</option>
                                        <option value="caught">Caught</option>
                                        <option value="run_out">Run Out</option>
                                        <option value="lbw">LBW</option>
                                        <option value="stumped">Stumped</option>
                                        <option value="hit_wicket">Hit Wicket</option>
                                        <option value="retired_hurt">Retired Hurt</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Out Player</label>
                                    <select name="out_player_id" class="form-select form-select-sm">
                                        <option value="">-- Dismissed Player --</option>
                                        @foreach($battingPlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Fielder (Catch/Runout)</label>
                                    <select name="fielder_id" class="form-select form-select-sm">
                                        <option value="">-- Fielder --</option>
                                        @foreach($fieldingPlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted">Commentary / Notes</label>
                            <input type="text" name="commentary" class="form-control form-control-sm" placeholder="e.g. Smashed through covers for four">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rcl-primary btn-sm px-4">Record Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Delivery --}}
<div class="modal fade" id="editBallModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-secondary">
            <form method="POST" id="editBallForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Delivery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-bold">Over Number *</label>
                            <input type="number" name="over_number" id="editBallOver" class="form-control form-control-sm" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-bold">Ball Number *</label>
                            <input type="number" name="ball_number" id="editBallNumber" class="form-control form-control-sm" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Bowler *</label>
                            <select name="bowler_id" id="editBallBowler" class="form-select form-select-sm" required>
                                @foreach($bowlingPlayers as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Batsman (Striker) *</label>
                            <select name="batsman_id" id="editBallBatsman" class="form-select form-select-sm" required>
                                @foreach($battingPlayers as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-bold">Non-Striker</label>
                            <select name="non_striker_id" id="editBallNonStriker" class="form-select form-select-sm">
                                <option value="">-- None --</option>
                                @foreach($battingPlayers as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Runs Scored *</label>
                            <select name="runs_scored" id="editBallRuns" class="form-select form-select-sm" required>
                                <option value="0">0 (Dot ball)</option>
                                <option value="1">1 Run</option>
                                <option value="2">2 Runs</option>
                                <option value="3">3 Runs</option>
                                <option value="4">4 Runs</option>
                                <option value="5">5 Runs</option>
                                <option value="6">6 Runs</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Extra Type</label>
                            <select name="extra_type" id="editBallExtraType" class="form-select form-select-sm">
                                <option value="none">None</option>
                                <option value="wide">Wide</option>
                                <option value="no_ball">No Ball</option>
                                <option value="bye">Bye</option>
                                <option value="leg_bye">Leg Bye</option>
                                <option value="penalty">Penalty</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Extra Runs</label>
                            <input type="number" name="extra_runs" id="editBallExtraRuns" class="form-control form-control-sm" min="0" max="10">
                        </div>

                        {{-- Wicket Section --}}
                        <div class="col-12 border-top border-secondary pt-2 mt-2">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_wicket" value="1" id="editBallIsWicket">
                                <label class="form-check-label fw-bold text-danger" for="editBallIsWicket">A Wicket Fell on this Delivery</label>
                            </div>
                            <div class="row g-2 d-none" id="editBallWicketFields">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Dismissal Type</label>
                                    <select name="wicket_type" id="editBallWicketType" class="form-select form-select-sm">
                                        <option value="bowled">Bowled</option>
                                        <option value="caught">Caught</option>
                                        <option value="run_out">Run Out</option>
                                        <option value="lbw">LBW</option>
                                        <option value="stumped">Stumped</option>
                                        <option value="hit_wicket">Hit Wicket</option>
                                        <option value="retired_hurt">Retired Hurt</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Out Player</label>
                                    <select name="out_player_id" id="editBallOutPlayer" class="form-select form-select-sm">
                                        <option value="">-- Dismissed Player --</option>
                                        @foreach($battingPlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Fielder</label>
                                    <select name="fielder_id" id="editBallFielder" class="form-select form-select-sm">
                                        <option value="">-- Fielder --</option>
                                        @foreach($fieldingPlayers as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small text-muted">Commentary / Notes</label>
                            <input type="text" name="commentary" id="editBallCommentary" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 text-dark fw-bold">Update Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal: Create Innings --}}
<div class="modal fade" id="createInningsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-secondary">
            <form method="POST" action="{{ route('admin.matches.manage.innings.store', $match) }}">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-plus-circle me-2"></i>Create New Innings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php $nextInningsNumber = $inningsList->count() + 1; @endphp
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Innings Number *</label>
                        <input type="number" name="innings_number" class="form-control" value="{{ $nextInningsNumber }}" min="1" max="4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Batting Team *</label>
                        <select name="batting_team_id" class="form-select" required>
                            <option value="{{ $match->home_team_id }}">{{ $match->homeTeam?->name }}</option>
                            <option value="{{ $match->away_team_id }}" selected>{{ $match->awayTeam?->name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Bowling Team *</label>
                        <select name="bowling_team_id" class="form-select" required>
                            <option value="{{ $match->home_team_id }}" selected>{{ $match->homeTeam?->name }}</option>
                            <option value="{{ $match->away_team_id }}">{{ $match->awayTeam?->name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Target Runs (Optional)</label>
                        <input type="number" name="target" class="form-control" placeholder="Leave empty if 1st innings">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rcl-primary btn-sm px-4">Create Innings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add delivery extra type handler
    var addExtra = document.getElementById('addBallExtraType');
    var addExtraRuns = document.getElementById('addBallExtraRuns');
    if (addExtra && addExtraRuns) {
        addExtra.addEventListener('change', function () {
            if (this.value === 'wide' || this.value === 'no_ball') {
                if (parseInt(addExtraRuns.value, 10) === 0) addExtraRuns.value = 1;
            } else if (this.value === 'none') {
                addExtraRuns.value = 0;
            }
        });
    }

    // Add delivery wicket checkbox toggle
    var addWicketCheck = document.getElementById('addBallIsWicket');
    var addWicketFields = document.getElementById('addBallWicketFields');
    if (addWicketCheck && addWicketFields) {
        addWicketCheck.addEventListener('change', function () {
            addWicketFields.classList.toggle('d-none', !this.checked);
        });
    }

    // Edit delivery extra type handler
    var editExtra = document.getElementById('editBallExtraType');
    var editExtraRuns = document.getElementById('editBallExtraRuns');
    if (editExtra && editExtraRuns) {
        editExtra.addEventListener('change', function () {
            if (this.value === 'wide' || this.value === 'no_ball') {
                if (parseInt(editExtraRuns.value, 10) === 0) editExtraRuns.value = 1;
            } else if (this.value === 'none') {
                editExtraRuns.value = 0;
            }
        });
    }

    // Edit delivery wicket checkbox toggle
    var editWicketCheck = document.getElementById('editBallIsWicket');
    var editWicketFields = document.getElementById('editBallWicketFields');
    if (editWicketCheck && editWicketFields) {
        editWicketCheck.addEventListener('change', function () {
            editWicketFields.classList.toggle('d-none', !this.checked);
        });
    }

    // Edit ball modal populator
    var editButtons = document.querySelectorAll('.btn-edit-ball');
    var editModalEl = document.getElementById('editBallModal');
    var editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var data = JSON.parse(this.getAttribute('data-ball'));
            document.getElementById('editBallForm').action = data.update_url;
            document.getElementById('editBallOver').value = data.over_number;
            document.getElementById('editBallNumber').value = data.ball_number;
            document.getElementById('editBallBowler').value = data.bowler_id;
            document.getElementById('editBallBatsman').value = data.batsman_id;
            document.getElementById('editBallNonStriker').value = data.non_striker_id || '';
            document.getElementById('editBallRuns').value = data.runs_scored;
            document.getElementById('editBallExtraType').value = data.extra_type || 'none';
            document.getElementById('editBallExtraRuns').value = data.extra_runs || 0;
            document.getElementById('editBallCommentary').value = data.commentary || '';

            var isWkt = data.is_wicket == 1;
            editWicketCheck.checked = isWkt;
            editWicketFields.classList.toggle('d-none', !isWkt);
            if (isWkt) {
                document.getElementById('editBallWicketType').value = data.wicket_type || 'bowled';
                document.getElementById('editBallOutPlayer').value = data.out_player_id || '';
                document.getElementById('editBallFielder').value = data.fielder_id || '';
            }

            if (editModal) {
                editModal.show();
            }
        });
    });
});
</script>
@endsection
