@extends('layouts.admin')
@section('title', 'Demerit Points')
@section('page-title', 'Demerit Points Disciplinary System')

@section('topbar-actions')
<a href="{{ route('admin.demerit-points.create', ['tab' => $currentTab]) }}" class="topbar-btn">
    <i class="bi bi-plus-lg"></i> Record Demerit Point
</a>
@endsection

@section('content')

<!-- Rule Note Banner -->
<div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: .75rem;">
        <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(239, 68, 68, 0.2); display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 1.3rem;">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        <div>
            <div style="font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 800; color: #ef4444;">Official Disciplinary Code</div>
            <div style="font-size: .95rem; font-weight: 700; color: var(--rcl-text); margin-top: 2px;">
                {{ $ruleNote }}
            </div>
        </div>
    </div>
    <div style="display: flex; gap: .5rem; align-items: center;">
        <span style="display: inline-flex; align-items: center; gap: .3rem; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 6px; padding: .25rem .6rem; font-size: .75rem; font-weight: 700;">
            <i class="bi bi-circle-fill" style="font-size: .45rem;"></i> 0 Pts: Clear
        </span>
        <span style="display: inline-flex; align-items: center; gap: .3rem; background: rgba(234, 179, 8, 0.15); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 6px; padding: .25rem .6rem; font-size: .75rem; font-weight: 700;">
            <i class="bi bi-circle-fill" style="font-size: .45rem;"></i> 1 Pt: Warning
        </span>
        <span style="display: inline-flex; align-items: center; gap: .3rem; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; padding: .25rem .6rem; font-size: .75rem; font-weight: 700;">
            <i class="bi bi-circle-fill" style="font-size: .45rem;"></i> 2 Pts: Final Alert
        </span>
        <span style="display: inline-flex; align-items: center; gap: .3rem; background: #ef4444; color: #fff; border-radius: 6px; padding: .25rem .6rem; font-size: .75rem; font-weight: 800;">
            <i class="bi bi-slash-circle-fill"></i> 3+ Pts: BANNED
        </span>
    </div>
</div>

<!-- Stat Summary Tiles -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--rcl-text);">{{ $totalActivePoints }}</div>
                <div style="font-size: .75rem; color: var(--rcl-muted); text-transform: uppercase;">Active Demerit Points</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.25); color: #ef4444;">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444;">{{ $bannedPlayersCount }}</div>
                <div style="font-size: .75rem; color: var(--rcl-muted); text-transform: uppercase;">Banned Players (>= 3 pts)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.25); color: #ef4444;">
                <i class="bi bi-shield-x"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444;">{{ $bannedTeamsCount }}</div>
                <div style="font-size: .75rem; color: var(--rcl-muted); text-transform: uppercase;">Banned Teams (>= 3 pts)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(0, 230, 118, 0.15); color: var(--rcl-primary);">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--rcl-text);">{{ $incidents->total() }}</div>
                <div style="font-size: .75rem; color: var(--rcl-muted); text-transform: uppercase;">Recorded Incidents</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs and Filter Toolbar -->
<div class="rcl-card mb-4">
    <div style="padding: .85rem 1.25rem; border-bottom: 1px solid var(--rcl-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        
        <!-- Category Tabs -->
        <div style="display: flex; gap: .4rem; align-items: center; flex-wrap: wrap;">
            @foreach($allCategories as $cat)
            @php
                $isActive = $currentTab === $cat;
                $label = $cat === 'player' ? 'Players' : ($cat === 'team' ? 'Teams' : ($cat === 'umpire' ? 'Umpires' : ucfirst($cat)));
                $icon = $cat === 'player' ? 'bi-person-fill' : ($cat === 'team' ? 'bi-shield-fill' : 'bi-patch-exclamation-fill');
            @endphp
            <a href="{{ route('admin.demerit-points.index', array_merge(request()->query(), ['tab' => $cat])) }}" 
               style="text-decoration: none; font-size: .82rem; font-weight: 700; padding: .45rem .9rem; border-radius: 6px; display: inline-flex; align-items: center; gap: .4rem; transition: all .15s; {{ $isActive ? 'background: var(--rcl-primary); color: #000;' : 'background: var(--rcl-surface2); color: var(--rcl-muted); border: 1px solid var(--rcl-border);' }}">
                <i class="bi {{ $icon }}"></i> {{ $label }}
            </a>
            @endforeach

            <a href="{{ route('admin.demerit-points.index', array_merge(request()->query(), ['tab' => 'all'])) }}"
               style="text-decoration: none; font-size: .82rem; font-weight: 700; padding: .45rem .9rem; border-radius: 6px; display: inline-flex; align-items: center; gap: .4rem; transition: all .15s; {{ $currentTab === 'all' ? 'background: var(--rcl-primary); color: #000;' : 'background: var(--rcl-surface2); color: var(--rcl-muted); border: 1px solid var(--rcl-border);' }}">
                <i class="bi bi-list-ul"></i> All Records
            </a>

            <!-- Add New Tab Button -->
            <a href="{{ route('admin.demerit-points.create', ['new_tab' => 1]) }}"
               style="text-decoration: none; font-size: .82rem; font-weight: 700; padding: .45rem .85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: .35rem; background: rgba(0, 230, 118, 0.1); color: var(--rcl-primary); border: 1px dashed rgba(0, 230, 118, 0.4);">
                <i class="bi bi-plus-circle"></i> Add New Tab
            </a>
        </div>

        <!-- Search & Filter Form -->
        <form method="GET" style="display: flex; gap: .5rem; align-items: center;">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <select name="edition_id" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                <option value="">All Editions</option>
                @foreach($editions as $ed)
                <option value="{{ $ed->id }}" {{ request('edition_id') == $ed->id ? 'selected' : '' }}>{{ $ed->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search player/team..." value="{{ request('search') }}" style="width: 180px;">
            <button type="submit" class="btn btn-sm btn-rcl-primary">Search</button>
            @if(request()->hasAny(['search', 'edition_id']))
            <a href="{{ route('admin.demerit-points.index', ['tab' => $currentTab]) }}" class="btn btn-sm btn-rcl-secondary">Clear</a>
            @endif
        </form>
    </div>

    <!-- Active Tab Standings / Leaderboard -->
    @if($currentTab !== 'all' && count($standings) > 0)
    <div style="padding: 1.25rem; border-bottom: 1px solid var(--rcl-border);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h6 style="margin: 0; font-weight: 800; font-size: .95rem; text-transform: uppercase; letter-spacing: .05em; color: var(--rcl-text);">
                {{ ucfirst($currentTab) }} Demerit Points Standing
            </h6>
            <span style="font-size: .75rem; color: var(--rcl-muted);">
                Showing {{ count($standings) }} {{ $currentTab }}{{ count($standings) === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="row g-3">
            @foreach($standings as $item)
            @php
                $pts = $item['total_points'];
                $isBanned = $pts >= 3;
                $color = $item['color'];
                $badgeBg = $isBanned ? '#ef4444' : ($pts === 2 ? 'rgba(239,68,68,0.15)' : ($pts === 1 ? 'rgba(234,179,8,0.15)' : 'rgba(34,197,94,0.15)'));
                $badgeBorder = $isBanned ? '#ef4444' : ($pts === 2 ? 'rgba(239,68,68,0.4)' : ($pts === 1 ? 'rgba(234,179,8,0.4)' : 'rgba(34,197,94,0.4)'));
                $badgeColor = $isBanned ? '#ffffff' : $color;
            @endphp
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div style="background: var(--rcl-surface2); border: 1px solid {{ $isBanned ? '#ef4444' : 'var(--rcl-border)' }}; border-radius: 8px; padding: 1rem; position: relative; overflow: hidden;">
                    @if($isBanned)
                    <div style="position: absolute; top: 0; right: 0; background: #ef4444; color: #fff; font-size: .62rem; font-weight: 800; padding: .15rem .5rem; border-bottom-left-radius: 6px; letter-spacing: .05em;">
                        BANNED
                    </div>
                    @endif

                    <div style="display: flex; align-items: center; gap: .75rem;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: {{ $badgeBg }}; border: 1px solid {{ $badgeBorder }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; color: {{ $badgeColor }}; flex-shrink: 0;">
                            {{ $pts }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 800; font-size: .88rem; color: var(--rcl-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item['name'] }}
                            </div>
                            @if(!empty($item['father_name']))
                            <div style="font-size: .72rem; color: var(--rcl-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                s/o {{ $item['father_name'] }}
                            </div>
                            @elseif(!empty($item['team_name']))
                            <div style="font-size: .72rem; color: var(--rcl-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item['team_name'] }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: .75rem; padding-top: .6rem; border-top: 1px solid rgba(255,255,255,0.06); font-size: .72rem;">
                        <span style="color: var(--rcl-muted);">{{ $item['incident_count'] }} Incident{{ $item['incident_count'] === 1 ? '' : 's' }}</span>
                        <span style="font-weight: 700; color: {{ $badgeColor }};">
                            @if($isBanned)
                            🚫 Banned to Play
                            @elseif($pts === 2)
                            ⚠️ 1 pt to Ban
                            @elseif($pts === 1)
                            ⚡ 1 Demerit Pt
                            @else
                            ✅ 0 Points (Clear)
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @elseif($currentTab !== 'all' && count($standings) === 0)
    <div style="padding: 2rem; text-align: center; color: var(--rcl-muted);">
        <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #22c55e;"></i>
        <div style="font-weight: 700; margin-top: .5rem; color: var(--rcl-text);">No Demerit Points Recorded</div>
        <div style="font-size: .8rem;">Every {{ $currentTab }} currently has 0 demerit points and is in good standing.</div>
    </div>
    @endif

    <!-- Incident Log Table -->
    <div class="rcl-card-header" style="background: rgba(0,0,0,0.15);">
        <span style="font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;">
            <i class="bi bi-journal-text me-1"></i> Recorded Demerit Points History
        </span>
        <span style="font-size: .75rem; color: var(--rcl-muted);">
            Showing {{ $incidents->firstItem() ?? 0 }}-{{ $incidents->lastItem() ?? 0 }} of {{ $incidents->total() }} records
        </span>
    </div>

    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Target / Recipient</th>
                    <th>Team</th>
                    <th>Points</th>
                    <th>Reason / Infraction</th>
                    <th>Match</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $inc)
                @php
                    $pts = $inc->points;
                    $badgeBg = $pts >= 3 ? '#ef4444' : ($pts === 2 ? 'rgba(239,68,68,0.15)' : ($pts === 1 ? 'rgba(234,179,8,0.15)' : 'rgba(34,197,94,0.15)'));
                    $badgeBorder = $pts >= 3 ? '#ef4444' : ($pts === 2 ? 'rgba(239,68,68,0.4)' : ($pts === 1 ? 'rgba(234,179,8,0.4)' : 'rgba(34,197,94,0.4)'));
                    $badgeColor = $pts >= 3 ? '#ffffff' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e'));
                @endphp
                <tr>
                    <td style="font-size: .8rem; color: var(--rcl-muted); white-space: nowrap;">
                        {{ $inc->incident_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td>
                        <span style="display: inline-block; padding: .15rem .5rem; border-radius: 4px; font-size: .72rem; font-weight: 700; text-transform: uppercase; background: var(--rcl-surface2); border: 1px solid var(--rcl-border); color: var(--rcl-muted);">
                            {{ $inc->target_type }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; font-size: .88rem; color: var(--rcl-text);">
                            {{ $inc->entity_name }}
                        </div>
                        @if($inc->player && $inc->player->father_name)
                        <div style="font-size: .72rem; color: var(--rcl-muted);">s/o {{ $inc->player->father_name }}</div>
                        @endif
                    </td>
                    <td>
                        @if($inc->team)
                        <div style="font-weight: 600; font-size: .82rem;">{{ $inc->team->name }}</div>
                        @elseif($inc->player && $inc->player->currentTeam)
                        <div style="font-weight: 600; font-size: .82rem;">{{ $inc->player->currentTeam->name }}</div>
                        @else
                        <span style="color: var(--rcl-muted); font-size: .75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: .3rem; font-weight: 800; font-size: .82rem; padding: .2rem .6rem; border-radius: 6px; background: {{ $badgeBg }}; border: 1px solid {{ $badgeBorder }}; color: {{ $badgeColor }};">
                            +{{ $pts }} pt{{ $pts === 1 ? '' : 's' }}
                        </span>
                    </td>
                    <td style="max-width: 280px;">
                        <div style="font-size: .82rem; color: var(--rcl-text);">{{ $inc->reason }}</div>
                        @if($inc->notes)
                        <div style="font-size: .72rem; color: var(--rcl-muted); margin-top: 2px;">Note: {{ $inc->notes }}</div>
                        @endif
                    </td>
                    <td style="font-size: .78rem; color: var(--rcl-muted); white-space: nowrap;">
                        @if($inc->match)
                        Match #{{ $inc->match->match_number }}
                        @else
                        —
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.demerit-points.toggle', $inc) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;">
                                @if($inc->is_active)
                                <span style="display: inline-block; padding: .15rem .5rem; border-radius: 4px; font-size: .7rem; font-weight: 700; background: rgba(0, 230, 118, 0.15); color: var(--rcl-primary); border: 1px solid rgba(0, 230, 118, 0.3);">Active</span>
                                @else
                                <span style="display: inline-block; padding: .15rem .5rem; border-radius: 4px; font-size: .7rem; font-weight: 700; background: rgba(255, 255, 255, 0.05); color: var(--rcl-muted); border: 1px solid var(--rcl-border);">Inactive</span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="{{ route('admin.demerit-points.edit', $inc) }}" class="btn btn-sm btn-rcl-secondary" style="padding: .2rem .5rem; font-size: .75rem;" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.demerit-points.destroy', $inc) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this demerit point record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-rcl-danger" style="padding: .2rem .5rem; font-size: .75rem;" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: var(--rcl-muted);">
                        No demerit point incidents recorded for this criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($incidents->hasPages())
    <div style="padding: .75rem 1.25rem; border-top: 1px solid var(--rcl-border);">
        {{ $incidents->links() }}
    </div>
    @endif
</div>

@endsection
