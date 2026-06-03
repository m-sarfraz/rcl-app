@extends('layouts.admin')
@section('title','Edit Role')
@section('page-title','Edit Role — '.ucfirst(str_replace('_',' ',$role->name)))
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.roles.update',$role) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Role Name *</label><input type="text" name="name" class="form-control" value="{{ $role->name }}" required {{ $role->is_system?'readonly':'' }}></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea></div>
                <div class="col-12">
                    <label class="form-label">Permissions</label>
                    @foreach($permissions as $module => $perms)
                    <div style="margin-bottom:.75rem;">
                        <div style="font-size:.75rem;font-weight:700;color:var(--rcl-gold);text-transform:uppercase;margin-bottom:.375rem;">{{ ucfirst($module) }}</div>
                        <div class="row g-1">
                            @foreach($perms as $perm)
                            <div class="col-6 col-md-4">
                                <div style="display:flex;align-items:center;gap:.4rem;padding:.3rem .5rem;background:var(--rcl-surface2);border:1px solid var(--rcl-border);border-radius:6px;">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="ep_{{ $perm->id }}" {{ in_array($perm->id,$assignedPermissions)?'checked':'' }} style="width:13px;height:13px;">
                                    <label for="ep_{{ $perm->id }}" style="font-size:.75rem;cursor:pointer;">{{ $perm->label }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Update Role</button><a href="{{ route('admin.roles.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
