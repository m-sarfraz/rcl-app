@extends('layouts.admin')
@section('title', $edition->name)
@section('page-title', $edition->name)

@section('topbar-actions')
    <a href="{{ route('admin.editions.edit', $edition) }}" class="topbar-btn"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.editions.index') }}" class="topbar-btn"><i class="bi bi-arrow-left"></i> All editions</a>
@endsection

@section('content')
<div class="row g-3 mb-3">
    @foreach([
        ['Edition',  '#'.$edition->edition_number,           'bi-hash',           'var(--rcl-primary)'],
        ['Host',     $edition->host_village,                  'bi-geo-alt-fill',   'var(--rcl-gold)'],
        ['Teams',    $edition->teams_count,                   'bi-shield-fill',    '#40c4ff'],
        ['Matches',  $edition->matches_count,                 'bi-calendar2-event','#ce93d8'],
    ] as [$label, $value, $icon, $color])
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:{{ $color }}22;color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
                <div>
                    <div style="font-size:1.25rem;font-weight:800;">{{ $value }}</div>
                    <div style="font-size:.72rem;color:var(--rcl-muted);text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="rcl-card mb-3">
            <div class="rcl-card-header">
                <span><i class="bi bi-table"></i> Points Table</span>
                <span style="font-size:.72rem;color:var(--rcl-muted);">NRR uses the VCC 0.17-overs rule</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="rcl-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Team</th><th>P</th><th>W</th><th>L</th><th>T</th><th>NR</th><th>Pts</th><th>NRR</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($table as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $row['team']->name }}</td>
                            <td>{{ $row['played'] }}</td>
                            <td>{{ $row['won'] }}</td>
                            <td>{{ $row['lost'] }}</td>
                            <td>{{ $row['tied'] }}</td>
                            <td>{{ $row['no_result'] }}</td>
                            <td style="font-weight:800;color:var(--rcl-primary);">{{ $row['points'] }}</td>
                            <td style="color:{{ $row['nrr'] >= 0 ? 'var(--rcl-primary)' : '#ef4444' }};">
                                {{ $row['nrr'] > 0 ? '+' : '' }}{{ number_format($row['nrr'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No completed matches yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rcl-card">
            <div class="rcl-card-header"><span><i class="bi bi-calendar2-event"></i> Fixtures</span></div>
            <div style="overflow-x:auto;">
                <table class="rcl-table">
                    <thead><tr><th>#</th><th>Match</th><th>Venue</th><th>Date</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($matches as $m)
                        <tr>
                            <td>{{ $m->match_number }}</td>
                            <td style="font-weight:600;">{{ $m->homeTeam?->short_code }} vs {{ $m->awayTeam?->short_code }}</td>
                            <td style="font-size:.8rem;">{{ $m->venue }}</td>
                            <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $m->scheduled_at?->format('d M Y') }}</td>
                            <td><span class="badge-rcl badge-{{ $m->status==='live'?'live':($m->status==='completed'?'completed':'upcoming') }}">{{ ucfirst($m->status) }}</span></td>
                            <td><a href="{{ route('admin.scorecard.show', $m) }}" class="btn-rcl-secondary btn" style="font-size:.7rem;padding:.2rem .5rem;">Card</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No fixtures scheduled.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="rcl-card mb-3">
            <div class="rcl-card-header"><span><i class="bi bi-shield-fill"></i> Participating Teams</span></div>
            <div class="rcl-card-body" style="display:flex;flex-wrap:wrap;gap:.5rem;">
                @forelse($edition->teams as $team)
                    <a href="{{ route('admin.teams.show', $team) }}"
                       style="text-decoration:none;padding:.4rem .75rem;border-radius:6px;font-size:.8rem;font-weight:600;
                              background:var(--rcl-surface2);border:1px solid var(--rcl-border);color:var(--rcl-text);">
                        {{ $team->short_code }} · {{ $team->name }}
                    </a>
                @empty
                    <span style="color:var(--rcl-muted);font-size:.85rem;">No teams assigned to this edition.</span>
                @endforelse
            </div>
        </div>

        @foreach([
            'orange_cap' => ['Orange Cap — Most Runs', 'total_runs',    'Runs'],
            'purple_cap' => ['Purple Cap — Most Wickets', 'total_wickets','Wkts'],
            'mvp'        => ['MVP Rankings', 'mvp_points', 'Pts'],
        ] as $key => [$title, $field, $col])
            <div class="rcl-card mb-3">
                <div class="rcl-card-header"><span>{{ $title }}</span></div>
                <table class="rcl-table">
                    <thead><tr><th>#</th><th>Player</th><th>Team</th><th style="text-align:right;">{{ $col }}</th></tr></thead>
                    <tbody>
                    @forelse($leaderboards[$key] as $i => $stat)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $stat->player?->name ?? '—' }}</td>
                            <td style="font-size:.78rem;color:var(--rcl-muted);">{{ $stat->team?->short_code ?? '' }}</td>
                            <td style="text-align:right;font-weight:800;color:var(--rcl-primary);">{{ $stat->{$field} }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;padding:1.25rem;color:var(--rcl-muted);font-size:.82rem;">No data yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection
