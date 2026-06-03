@extends('layouts.admin')
@section('title','Edit Cabinet Member')
@section('page-title','Edit VCC Member')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.vcc.update', $vcc) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="{{ $vcc->name }}" required></div>
                <div class="col-md-6"><label class="form-label">Role / Title *</label><input type="text" name="role_title" class="form-control" value="{{ $vcc->role_title }}" required></div>
                <div class="col-md-6">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    @if($vcc->photo)
                    <div style="margin-top:.5rem;font-size:.75rem;color:var(--rcl-muted);">Current: <img src="{{ asset('storage/'.$vcc->photo) }}" style="height:36px;border-radius:50%;margin-left:.25rem;"></div>
                    @endif
                </div>
                <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ $vcc->display_order }}" min="0"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $vcc->phone }}"></div>
                <div class="col-md-6"><label class="form-label">Village / Location</label><input type="text" name="village" class="form-control" value="{{ $vcc->village }}"></div>
                <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3">{{ $vcc->bio }}</textarea></div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Update</button>
                    <a href="{{ route('admin.vcc.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
