@extends('layouts.admin')
@section('title','Players')
@section('page-title','Players')
@section('topbar-actions')
<a href="{{ route('admin.players.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Player</a>
@endsection

@push('styles')
<style>
/* ── Filter bar ─────────────────────────────────────────────── */
.player-filters {
    background: var(--rcl-surface);
    border: 1px solid var(--rcl-border);
    border-radius: 10px;
    padding: .875rem 1.125rem;
    display: flex;
    align-items: center;
    gap: .625rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.player-filters .form-control,
.player-filters .form-select { font-size: .8rem; padding: .4rem .65rem; }
.player-filters .form-control { max-width: 210px; }
.player-filters .form-select { max-width: 175px; }
.filter-count {
    margin-left: auto;
    font-size: .75rem;
    color: var(--rcl-muted);
    white-space: nowrap;
}
.filter-count strong { color: var(--rcl-primary); }

/* ── Card grid ──────────────────────────────────────────────── */
.player-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(272px, 1fr));
    gap: 1rem;
}

/* ── Individual card ────────────────────────────────────────── */
.player-card {
    background: var(--rcl-surface);
    border: 1px solid var(--rcl-border);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: border-color .2s ease, transform .18s ease, box-shadow .18s ease;
}
.player-card:hover {
    border-color: var(--rcl-primary);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.35);
}

/* color accent strip at top */
.pc-accent { height: 4px; width: 100%; }

/* header: avatar + info */
.pc-header {
    display: flex;
    align-items: flex-start;
    gap: .875rem;
    padding: .9rem 1rem .7rem;
}
.pc-avatar-wrap { position: relative; flex-shrink: 0; }
.pc-avatar {
    width: 56px; height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--rcl-border);
}
.pc-avatar-initials {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--rcl-primary), var(--rcl-gold));
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; color: #000; font-size: .95rem;
    border: 2px solid rgba(255,255,255,.08);
}
.pc-jersey {
    position: absolute;
    bottom: -5px; right: -5px;
    background: var(--rcl-dark);
    border: 1px solid var(--rcl-border);
    color: var(--rcl-gold);
    font-size: .58rem; font-weight: 800;
    padding: .1rem .28rem;
    border-radius: 4px;
    line-height: 1.2;
}
.pc-name {
    font-weight: 700; font-size: .875rem;
    color: var(--rcl-text); text-decoration: none;
    line-height: 1.25; display: block;
}
.pc-name:hover { color: var(--rcl-primary); }
.pc-son {
    font-size: .66rem; color: var(--rcl-muted);
    margin-top: .1rem; line-height: 1.3;
}
.pc-team-pill {
    display: inline-flex; align-items: center; gap: .28rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--rcl-border);
    border-radius: 20px;
    padding: .18rem .55rem;
    font-size: .68rem; font-weight: 600;
    margin-top: .45rem; color: var(--rcl-text);
}
.pc-team-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}
.pc-cap-badge {
    font-size: .58rem; font-weight: 800;
    padding: .08rem .28rem; border-radius: 3px;
    letter-spacing: .04em; line-height: 1.3;
}
.pc-cap-badge.captain  { background: var(--rcl-gold); color: #000; }
.pc-cap-badge.vc       { background: #6ec6f5; color: #000; }

/* chips row */
.pc-chips {
    display: flex; gap: .4rem;
    padding: 0 1rem .75rem;
    flex-wrap: wrap;
}
.pc-chip {
    background: var(--rcl-surface2);
    border: 1px solid var(--rcl-border);
    border-radius: 6px;
    padding: .22rem .55rem;
    display: flex; flex-direction: column; align-items: center;
    min-width: 50px;
}
.pc-chip-label { font-size: .56rem; color: var(--rcl-muted); text-transform: uppercase; letter-spacing: .06em; line-height: 1; }
.pc-chip-val   { font-size: .72rem; font-weight: 700; color: var(--rcl-text); line-height: 1.4; }

/* role chip special colours */
.chip-batsman    .pc-chip-val { color: #4ade80; }
.chip-bowler     .pc-chip-val { color: #f87171; }
.chip-allrounder .pc-chip-val { color: var(--rcl-gold); }
.chip-keeper     .pc-chip-val { color: #a78bfa; }

/* footer */
.pc-footer {
    margin-top: auto;
    display: flex; align-items: center; justify-content: space-between;
    padding: .55rem 1rem;
    border-top: 1px solid var(--rcl-border);
    background: rgba(0,0,0,.18);
}
.pc-action-btn {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 7px; font-size: .75rem;
    cursor: pointer; border: none;
    transition: all .15s; text-decoration: none;
}
.pc-action-btn.edit {
    background: var(--rcl-surface2);
    border: 1px solid var(--rcl-border);
    color: var(--rcl-text);
}
.pc-action-btn.edit:hover  { border-color: var(--rcl-primary); color: var(--rcl-primary); }
.pc-action-btn.del {
    background: rgba(239,68,68,.1);
    border: 1px solid rgba(239,68,68,.3);
    color: #ef4444;
}
.pc-action-btn.del:hover { background: #ef4444; color: #fff; }

/* empty state */
.player-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3.5rem 1rem;
    color: var(--rcl-muted);
}
.player-empty i { font-size: 2.5rem; display: block; margin-bottom: .75rem; opacity: .4; }
</style>
@endpush

@section('content')

{{-- ── Filters ────────────────────────────────────────────── --}}
<form method="GET" class="player-filters">
    <input type="text" name="search" class="form-control"
           placeholder="Search name…" value="{{ request('search') }}">

    <select name="team_id" class="form-select">
        <option value="">All teams</option>
        @foreach($teams as $t)
        <option value="{{ $t->id }}" {{ request('team_id') == $t->id ? 'selected' : '' }}>
            {{ $t->short_code }} – {{ $t->name }}
        </option>
        @endforeach
    </select>

    <select name="role" class="form-select">
        <option value="">All roles</option>
        @foreach($roles as $r)
        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
            {{ ucfirst(str_replace('_', ' ', $r)) }}
        </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-rcl-primary" style="font-size:.8rem;padding:.4rem .9rem;">
        <i class="bi bi-funnel-fill"></i> Filter
    </button>

    @if(request()->hasAny(['search','team_id','role']))
    <a href="{{ route('admin.players.index') }}" class="btn btn-rcl-secondary" style="font-size:.8rem;padding:.4rem .9rem;">
        <i class="bi bi-x-lg"></i> Clear
    </a>
    @endif

    <span class="filter-count">
        <strong>{{ $players->total() }}</strong> player{{ $players->total() !== 1 ? 's' : '' }}
    </span>
</form>

{{-- ── Card grid ──────────────────────────────────────────── --}}
<div class="player-grid">
    @forelse($players as $p)
    @php
        $team     = $p->teams->first();
        $teamColor = $team->primary_color ?? 'var(--rcl-primary)';
        $isCap    = $team && $team->pivot->is_captain;
        $isVC     = $team && $team->pivot->is_vice_captain;

        $bowlAbbr = ['right_arm_fast'=>'RAF','right_arm_medium'=>'RAM','right_arm_spin'=>'RAS',
                     'left_arm_fast'=>'LAF','left_arm_medium'=>'LAM','left_arm_spin'=>'LAS','none'=>'—'];
        $batAbbr  = ['right_hand'=>'RHB','left_hand'=>'LHB'];
        $roleClass = ['batsman'=>'chip-batsman','bowler'=>'chip-bowler',
                      'all_rounder'=>'chip-allrounder','wicket_keeper'=>'chip-keeper'];
        $roleLabel = ['batsman'=>'Batsman','bowler'=>'Bowler','all_rounder'=>'All-rounder','wicket_keeper'=>'W.Keeper'];
    @endphp

    <div class="player-card">
        {{-- colour accent --}}
        <div class="pc-accent" style="background:{{ $teamColor }};"></div>

        {{-- header --}}
        <div class="pc-header">
            <div class="pc-avatar-wrap">
                @if($p->photo)
                    <img src="{{ asset('storage/'.$p->photo) }}" class="pc-avatar" alt="{{ $p->name }}">
                @else
                    <img src="/images/player-avatar.svg" class="pc-avatar" alt="{{ $p->name }}">
                @endif
                @if($p->jersey_number)
                    <span class="pc-jersey">#{{ $p->jersey_number }}</span>
                @endif
            </div>

            <div style="flex:1;min-width:0;">
                <a href="{{ route('admin.players.show', $p) }}" class="pc-name">{{ $p->name }}</a>
                @if($p->father_name)
                    <div class="pc-son">S/o {{ $p->father_name }}</div>
                @endif
                @if($team)
                    <div class="pc-team-pill">
                        <span class="pc-team-dot" style="background:{{ $teamColor }};"></span>
                        {{ $team->short_code ?? $team->name }}
                        @if($isCap)
                            <span class="pc-cap-badge captain">C</span>
                        @elseif($isVC)
                            <span class="pc-cap-badge vc">VC</span>
                        @endif
                    </div>
                @else
                    <div class="pc-team-pill" style="color:var(--rcl-muted);border-style:dashed;">
                        <i class="bi bi-dash" style="font-size:.65rem;"></i> Unassigned
                    </div>
                @endif
            </div>
        </div>

        {{-- chips --}}
        <div class="pc-chips">
            <div class="pc-chip {{ $roleClass[$p->role] ?? '' }}">
                <span class="pc-chip-label">Role</span>
                <span class="pc-chip-val">{{ $roleLabel[$p->role] ?? ucfirst($p->role) }}</span>
            </div>
            <div class="pc-chip">
                <span class="pc-chip-label">Bat</span>
                <span class="pc-chip-val">{{ $batAbbr[$p->batting_style] ?? '—' }}</span>
            </div>
            <div class="pc-chip">
                <span class="pc-chip-label">Bowl</span>
                <span class="pc-chip-val">{{ $bowlAbbr[$p->bowling_style] ?? '—' }}</span>
            </div>
        </div>

        {{-- footer --}}
        <div class="pc-footer">
            <span class="badge-rcl {{ $p->bowling_action_status==='legal' ? 'badge-paid' : ($p->bowling_action_status==='flagged' ? 'badge-upcoming' : 'badge-unpaid') }}"
                  style="font-size:.67rem;">
                <i class="bi bi-{{ $p->bowling_action_status==='legal' ? 'check-circle-fill' : 'exclamation-triangle-fill' }}" style="font-size:.65rem;"></i>
                {{ ucfirst($p->bowling_action_status) }}
            </span>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.players.edit', $p) }}" class="pc-action-btn edit" title="Edit">
                    <i class="bi bi-pencil-fill"></i>
                </a>
                <form method="POST" action="{{ route('admin.players.destroy', $p) }}"
                      onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="pc-action-btn del" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @empty
    <div class="player-empty">
        <i class="bi bi-people"></i>
        No players found. <a href="{{ route('admin.players.create') }}" style="color:var(--rcl-primary);">Add one</a>
    </div>
    @endforelse
</div>

{{-- pagination --}}
<div class="mt-3">{{ $players->withQueryString()->links() }}</div>

@endsection
