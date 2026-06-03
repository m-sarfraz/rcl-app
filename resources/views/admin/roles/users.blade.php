@extends('layouts.admin')
@section('title','Users')
@section('page-title','User Management')
@section('content')
<div class="rcl-card">
    <div class="rcl-card-body p-0">
        <table class="rcl-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight:700;">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.roles.assign', $user) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <select name="role_id" onchange="this.form.submit()" style="background:var(--rcl-surface2);border:1px solid var(--rcl-border);color:var(--rcl-text);border-radius:5px;padding:.2rem .4rem;font-size:.75rem;">
                                @foreach($roles as $r)<option value="{{ $r->id }}" {{ $user->role_id===$r->id?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$r->name)) }}</option>@endforeach
                            </select>
                        </form>
                    </td>
                    <td><span class="badge-rcl {{ $user->is_active?'badge-paid':'badge-unpaid' }}">{{ $user->is_active?'Active':'Inactive' }}</span></td>
                    <td style="font-size:.8rem;color:var(--rcl-muted);">{{ $user->created_at?->format('d M Y') }}</td>
                    <td>
                        @if(!$user->isSuperAdmin())
                        <form method="POST" action="{{ route('admin.roles.toggle-user', $user) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-rcl-secondary" style="font-size:.72rem;padding:.25rem .6rem;">{{ $user->is_active?'Deactivate':'Activate' }}</button>
                        </form>
                        @else
                        <span style="font-size:.72rem;color:var(--rcl-muted);">Protected</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--rcl-muted);">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $users->links() }}
@endsection
