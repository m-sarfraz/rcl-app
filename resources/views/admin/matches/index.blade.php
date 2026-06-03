@extends('layouts.admin')
@section('title','Matches')
@section('page-title','Matches')
@section('topbar-actions')
<a href="{{ route('admin.matches.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Schedule Match</a>
@endsection
@section('content')
<div class="rcl-card mb-3" style="padding:.75rem 1rem;">
    <form method="GET" class="d-flex gap-2">
        <select name="edition_id" class="form-select" style="max-width:180px;">
            <option value="">All Editions</option>
            @foreach($editions as $e)<option value="{{ $e->id }}" {{ request('edition_id')==$e->id?'selected':'' }}>{{ $e->name }}</option>@endforeach
        </select>
        <select name="status" class="form-select" style="max-width:140px;">
            <option value="">All Status</option>
            @foreach(['upcoming','live','completed','abandoned'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-rcl-primary">Filter</button>
    </form>
</div>
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>#</th><th>Edition</th><th>Teams</th><th>Venue</th><th>Date</th><th>Overs</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($matches as $m)
                <tr>
                    <td style="font-weight:700;">{{ $m->match_number }}</td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $m->edition?->name }}</td>
                    <td style="font-weight:600;">{{ $m->homeTeam?->short_code }} vs {{ $m->awayTeam?->short_code }}</td>
                    <td style="font-size:.8rem;">{{ $m->venue }}</td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $m->scheduled_at?->format('d M Y H:i') }}</td>
                    <td>{{ $m->overs_per_side }}</td>
                    <td><span class="badge-rcl badge-{{ $m->status==='live'?'live':($m->status==='completed'?'completed':'upcoming') }}">{{ ucfirst($m->status) }}</span></td>
                    <td class="d-flex gap-1">
                        @if(in_array($m->status,['upcoming','live']))
                        <a href="{{ route('admin.scoring.console',$m) }}" class="btn btn-rcl-primary" style="font-size:.72rem;padding:.25rem .6rem;"><i class="bi bi-broadcast"></i> Score</a>
                        @endif
                        <a href="{{ route('admin.matches.edit',$m) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                        @if($m->status==='completed')
                        <a href="{{ route('admin.scorecard.show',$m) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Card</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No matches found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $matches->links() }}
@endsection
