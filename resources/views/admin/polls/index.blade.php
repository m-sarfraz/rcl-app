@extends('layouts.admin')
@section('title','Polls')
@section('page-title','Fan Polls')
@section('topbar-actions')
<a href="{{ route('admin.polls.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> New Poll</a>
@endsection
@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>Question</th><th>Edition</th><th>Votes</th><th>Status</th><th>Expires</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($polls as $poll)
                <tr>
                    <td style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;">{{ $poll->question }}</td>
                    <td>{{ $poll->edition?->name }}</td>
                    <td>{{ $poll->options?->sum('votes') ?? 0 }}</td>
                    <td><span class="badge-rcl {{ $poll->is_active?'badge-paid':'badge-unpaid' }}">{{ $poll->is_active?'Active':'Inactive' }}</span></td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $poll->ends_at?->format('d M Y') ?? '∞' }}</td>
                    <td class="d-flex gap-1">
                        <form method="POST" action="{{ route('admin.polls.toggle',$poll) }}">@csrf @method('PATCH')<button class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">{{ $poll->is_active?'Stop':'Start' }}</button></form>
                        <form method="POST" action="{{ route('admin.polls.destroy',$poll) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No polls created.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $polls->links() }}
@endsection
