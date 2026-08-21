@extends('layouts.admin')
@section('title', $team->name)
@section('page-title', $team->name)

@section('topbar-actions')
    <a href="{{ route('admin.teams.edit', $team) }}" class="topbar-btn"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('admin.teams.index') }}" class="topbar-btn"><i class="bi bi-arrow-left"></i> All teams</a>
@endsection

@section('content')
@php
    $completed = $matches->where('status', 'completed');
    $won  = $completed->where('winner_id', $team->id)->count();
    $lost = $completed->whereNotNull('winner_id')->where('winner_id', '!=', $team->id)->count();
@endphp

<div class="rcl-card mb-3">
    <div class="rcl-card-body" style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
        <div style="width:72px;height:72px;border-radius:16px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
                    background:linear-gradient(135deg,{{ $team->primary_color }},{{ $team->secondary_color }});
                    font-weight:900;color:#fff;font-size:1.35rem;">
            @if($team->logo)
                <img src="{{ asset('storage/'.$team->logo) }}" alt="{{ $team->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">
            @else
                {{ $team->short_code }}
            @endif
        </div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:1.15rem;font-weight:800;">{{ $team->name }}</div>
            <div style="color:var(--rcl-muted);font-size:.85rem;"><i class="bi bi-geo-alt"></i> {{ $team->village_name }}</div>
            @if($team->description)
                <div style="color:var(--rcl-muted);font-size:.8rem;margin-top:.4rem;">{{ $team->description }}</div>
            @endif
        </div>
        <div style="display:flex;gap:.75rem;">
            @foreach([['Squad', $team->players_count], ['Played', $completed->count()], ['Won', $won], ['Lost', $lost]] as [$l, $v])
                <div style="text-align:center;padding:.6rem 1rem;background:var(--rcl-surface2);border-radius:8px;min-width:66px;">
                    <div style="font-size:1.15rem;font-weight:800;">{{ $v }}</div>
                    <div style="font-size:.68rem;color:var(--rcl-muted);text-transform:uppercase;">{{ $l }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="rcl-card">
            <div class="rcl-card-header">
                <span><i class="bi bi-people-fill"></i> Squad</span>
                <span style="font-size:.72rem;color:var(--rcl-muted);">
                    {{ $editionId ? 'Current edition' : 'All editions' }} · {{ $squad->count() }} players
                </span>
            </div>
            <table class="rcl-table">
                <thead><tr><th>Player</th><th>Role</th><th>Action</th><th></th></tr></thead>
                <tbody>
                @forelse($squad as $p)
                    <tr>
                        <td style="font-weight:600;">{{ $p->name }}</td>
                        <td style="font-size:.8rem;color:var(--rcl-muted);">{{ ucwords(str_replace('_',' ',$p->role)) }}</td>
                        <td>
                            <span class="badge-rcl {{ $p->bowling_action_status === 'banned' ? 'badge-unpaid' : ($p->bowling_action_status === 'flagged' ? 'badge-upcoming' : 'badge-paid') }}">
                                {{ ucfirst($p->bowling_action_status) }}
                            </span>
                        </td>
                        <td><a href="{{ route('admin.players.show', $p) }}" class="btn-rcl-secondary btn" style="font-size:.7rem;padding:.2rem .5rem;">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No players on this roster.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="rcl-card">
            <div class="rcl-card-header"><span><i class="bi bi-calendar2-event"></i> Recent Matches</span></div>
            <table class="rcl-table">
                <thead><tr><th>Match</th><th>Edition</th><th>Date</th><th>Result</th></tr></thead>
                <tbody>
                @forelse($matches as $m)
                    <tr>
                        <td style="font-weight:600;">{{ $m->homeTeam?->short_code }} vs {{ $m->awayTeam?->short_code }}</td>
                        <td style="font-size:.78rem;color:var(--rcl-muted);">{{ $m->edition?->name }}</td>
                        <td style="font-size:.78rem;color:var(--rcl-muted);">{{ $m->scheduled_at?->format('d M Y') }}</td>
                        <td style="font-size:.78rem;">
                            @if($m->status === 'completed')
                                @if($m->winner_id === $team->id)
                                    <span style="color:var(--rcl-primary);font-weight:700;">Won</span>
                                @elseif($m->winner_id)
                                    <span style="color:#ef4444;font-weight:700;">Lost</span>
                                @else
                                    <span style="color:var(--rcl-muted);">{{ ucfirst(str_replace('_',' ', $m->result_type ?? 'no result')) }}</span>
                                @endif
                            @else
                                <span class="badge-rcl badge-{{ $m->status==='live'?'live':'upcoming' }}">{{ ucfirst($m->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No matches yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
