@extends('layouts.admin')
@section('title','Fines')
@section('page-title','Disciplinary Fines')

@section('topbar-actions')
<a href="{{ route('admin.fines.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> Issue Fine</a>
@endsection

@section('content')
<div class="rcl-card mb-3" style="padding:.75rem 1rem;">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <select name="status" class="form-select" style="max-width:140px;">
            <option value="">All Status</option>
            @foreach(['unpaid','paid','waived'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="edition_id" class="form-select" style="max-width:200px;">
            <option value="">All Editions</option>
            @foreach($editions as $e)
            <option value="{{ $e->id }}" {{ request('edition_id')==$e->id?'selected':'' }}>{{ $e->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-rcl-primary">Filter</button>
    </form>
</div>

<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Highlighted Player</th>
                    <th>Edition</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fines as $fine)
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:.88rem;">{{ $fine->team?->name ?? '—' }}</div>
                        @if($fine->team?->short_code)
                        <div style="font-size:.68rem;color:var(--rcl-muted);">{{ $fine->team->short_code }}</div>
                        @endif
                    </td>
                    <td>
                        @if($fine->player)
                        <div style="display:inline-flex;align-items:center;gap:.35rem;background:rgba(212,144,10,.1);border:1px solid rgba(212,144,10,.3);border-radius:6px;padding:.15rem .45rem;">
                            <i class="bi bi-person-fill" style="color:var(--rcl-gold);font-size:.72rem;"></i>
                            <span style="font-size:.78rem;font-weight:600;">{{ $fine->player->name }}</span>
                        </div>
                        @else
                        <span style="font-size:.75rem;color:var(--rcl-muted);">Team-wide</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;">{{ $fine->edition?->name }}</td>
                    <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.82rem;">{{ $fine->description }}</td>
                    <td style="font-weight:700;">PKR {{ number_format($fine->amount) }}</td>
                    <td><span class="badge-rcl badge-upcoming" style="font-size:.68rem;">{{ ucfirst(str_replace('_',' ',$fine->violation_type)) }}</span></td>
                    <td>
                        <span class="badge-rcl {{ $fine->status==='paid' ? 'badge-paid' : ($fine->status==='waived'?'badge-completed':'badge-unpaid') }}">
                            {{ ucfirst($fine->status) }}
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:var(--rcl-muted);">{{ $fine->due_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.fines.status', $fine) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                style="background:var(--rcl-surface2);border:1px solid var(--rcl-border);color:var(--rcl-text);border-radius:5px;padding:.2rem .4rem;font-size:.72rem;">
                                <option value="unpaid" {{ $fine->status==='unpaid'?'selected':'' }}>Unpaid</option>
                                <option value="paid"   {{ $fine->status==='paid'?'selected':'' }}>Paid</option>
                                <option value="waived" {{ $fine->status==='waived'?'selected':'' }}>Waived</option>
                            </select>
                        </form>
                        <form method="POST" action="{{ route('admin.fines.destroy', $fine) }}" class="d-inline"
                              onsubmit="return confirm('Delete this fine?')">
                            @csrf @method('DELETE')
                            <button class="btn-rcl-danger btn ms-1" style="padding:.2rem .5rem;font-size:.72rem;">Del</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No fines recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $fines->links() }}
@endsection
