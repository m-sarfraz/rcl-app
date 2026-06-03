@extends('layouts.admin')
@section('title','Editions')
@section('page-title','Tournament Editions')
@section('topbar-actions')
<a href="{{ route('admin.editions.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> New Edition</a>
@endsection
@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>#</th><th>Name</th><th>Host Village</th><th>Teams</th><th>Matches</th><th>Status</th><th>Current</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($editions as $e)
                <tr>
                    <td style="font-weight:900;color:var(--rcl-gold);">{{ $e->edition_number }}</td>
                    <td style="font-weight:700;">{{ $e->name }}</td>
                    <td>{{ $e->host_village }}</td>
                    <td>{{ $e->teams_count }}</td>
                    <td>{{ $e->matches_count }}</td>
                    <td><span class="badge-rcl badge-{{ $e->status==='active'?'live':($e->status==='completed'?'completed':'upcoming') }}">{{ ucfirst($e->status) }}</span></td>
                    <td>{{ $e->is_current ? '✅' : '—' }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.editions.edit',$e) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                        <form method="POST" action="{{ route('admin.editions.destroy',$e) }}" onsubmit="return confirm('Delete edition?')">
                            @csrf @method('DELETE')
                            <button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No editions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
