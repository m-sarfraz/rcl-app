@extends('layouts.admin')
@section('title','Notifications')
@section('page-title','Notification & Ticker Management')

@section('content')
<div class="row g-3">
    <div class="col-md-5">
        <div class="rcl-card">
            <div class="rcl-card-header">Publish Notification</div>
            <div class="rcl-card-body">
                <form method="POST" action="{{ route('admin.notifications.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="e.g. LIVE: Village A needs 14 runs in 6 balls"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="info">Info</option>
                                <option value="live_update">Live Update</option>
                                <option value="suspension">Suspension</option>
                                <option value="fine">Fine</option>
                                <option value="announcement">Announcement</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mb-3" style="display:flex;align-items:center;gap:.5rem;">
                        <input class="form-check-input" type="checkbox" name="is_ticker" id="is_ticker" value="1" checked style="width:16px;height:16px;">
                        <label class="form-check-label" for="is_ticker" style="font-size:.85rem;">Show in ticker bar</label>
                    </div>
                    <button type="submit" class="btn btn-rcl-primary w-100">Publish</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="rcl-card">
            <div class="rcl-card-body p-0">
                <table class="rcl-table">
                    <thead><tr><th>Message</th><th>Type</th><th>Ticker</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($notifications as $n)
                        <tr>
                            <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.82rem;">{{ $n->message }}</td>
                            <td><span class="badge-rcl badge-upcoming" style="font-size:.68rem;">{{ ucfirst(str_replace('_',' ',$n->type)) }}</span></td>
                            <td>{{ $n->is_ticker ? '✅' : '—' }}</td>
                            <td><span class="badge-rcl {{ $n->is_active?'badge-paid':'badge-unpaid' }}">{{ $n->is_active?'Active':'Inactive' }}</span></td>
                            <td class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.notifications.toggle', $n) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-rcl-secondary btn" style="padding:.2rem .5rem;font-size:.72rem;">{{ $n->is_active?'Off':'On' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-rcl-danger btn" style="padding:.2rem .5rem;font-size:.72rem;">Del</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No notifications.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $notifications->links() }}
    </div>
</div>
@endsection
