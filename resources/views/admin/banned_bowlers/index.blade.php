@extends('layouts.admin')
@section('title','Banned Bowlers')
@section('page-title','Banned Bowlers')

@section('topbar-actions')
<a href="{{ route('admin.banned-bowlers.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Add Ban</a>
@endsection

@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Reason</th>
                    <th>Banned From</th>
                    <th>Banned Until</th>
                    <th>Status</th>
                    <th>Issued By</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bans as $ban)
                <tr>
                    <td style="font-weight:700;">{{ $ban->player?->name }}</td>
                    <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ban->reason }}</td>
                    <td style="font-size:.8rem;">{{ $ban->banned_from->format('d M Y') }}</td>
                    <td style="font-size:.8rem;">{{ $ban->banned_until?->format('d M Y') ?? 'Indefinite' }}</td>
                    <td>
                        <span class="badge-rcl {{ $ban->is_active ? 'badge-unpaid' : 'badge-completed' }}">
                            {{ $ban->is_active ? 'Active' : 'Lifted' }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $ban->issuedBy?->name }}</td>
                    <td style="font-size:.75rem;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--rcl-muted);">
                        {{ $ban->notes ?? '—' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.banned-bowlers.toggle', $ban) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-rcl-secondary ms-1" style="padding:.2rem .6rem;font-size:.75rem;">
                                {{ $ban->is_active ? 'Lift' : 'Reinstate' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.banned-bowlers.destroy', $ban) }}" class="d-inline" onsubmit="return confirm('Delete this ban record?')">
                            @csrf @method('DELETE')
                            <button class="btn-rcl-danger btn ms-1" style="padding:.2rem .5rem;font-size:.75rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No banned bowlers on record.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $bans->links() }}
@endsection
