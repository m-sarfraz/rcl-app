@extends('layouts.admin')
@section('title','Roles')
@section('page-title','Roles & Permissions')
@section('topbar-actions')
<a href="{{ route('admin.roles.create') }}" class="topbar-btn"><i class="bi bi-plus-lg"></i> New Role</a>
<a href="{{ route('admin.roles.users') }}" class="topbar-btn"><i class="bi bi-people"></i> Manage Users</a>
@endsection
@section('content')
<div class="row g-3">
    @foreach($roles as $role)
    <div class="col-md-4">
        <div class="rcl-card">
            <div class="rcl-card-header">
                <span style="font-weight:700;">{{ ucfirst(str_replace('_',' ',$role->name)) }}</span>
                @if($role->is_system)<span class="badge-rcl badge-live" style="font-size:.65rem;">System</span>@endif
            </div>
            <div class="rcl-card-body">
                <div style="font-size:.8rem;color:var(--rcl-muted);margin-bottom:.75rem;">{{ $role->description ?? 'No description.' }}</div>
                <div style="font-size:.75rem;color:var(--rcl-muted);">{{ $role->users_count }} user(s)</div>
                <div class="d-flex gap-1 mt-2">
                    @if(!$role->is_system || auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.roles.edit',$role) }}" class="btn-rcl-secondary btn" style="font-size:.72rem;padding:.25rem .6rem;">Edit</a>
                    @if(!$role->is_system)
                    <form method="POST" action="{{ route('admin.roles.destroy',$role) }}" onsubmit="return confirm('Delete role?')">@csrf @method('DELETE')<button class="btn-rcl-danger btn" style="font-size:.72rem;padding:.25rem .6rem;">Del</button></form>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
