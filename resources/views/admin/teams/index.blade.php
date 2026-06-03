@extends('layouts.admin')
@section('title','Teams')
@section('page-title','Teams')
@section('topbar-actions')
<a href="{{ route('admin.teams.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Team</a>
@endsection
@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>Logo</th><th>Name</th><th>Village</th><th>Code</th><th>Players</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($teams as $team)
                <tr>
                    <td>
                        @if($team->logo)
                        <img src="{{ asset('storage/'.$team->logo) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        @else
                        <div style="width:32px;height:32px;border-radius:50%;background:{{ $team->primary_color ?? 'var(--rcl-primary)' }};display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:.7rem;">{{ $team->short_code }}</div>
                        @endif
                    </td>
                    <td style="font-weight:700;">{{ $team->name }}</td>
                    <td>{{ $team->village_name }}</td>
                    <td><span class="badge-rcl badge-upcoming">{{ $team->short_code }}</span></td>
                    <td><a href="{{ route('admin.players.index', ['team_id' => $team->id]) }}" style="color:var(--rcl-gold);font-weight:700;text-decoration:none;">{{ $team->players_count }}</a></td>
                    <td><span class="badge-rcl {{ $team->is_active?'badge-paid':'badge-unpaid' }}">{{ $team->is_active?'Active':'Inactive' }}</span></td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.teams.edit',$team) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                        <form method="POST" action="{{ route('admin.teams.destroy',$team) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No teams yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $teams->links() }}
@endsection
