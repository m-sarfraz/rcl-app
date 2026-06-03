@extends('layouts.admin')
@section('title','Add Cabinet Member')
@section('page-title','Add VCC Cabinet Member')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.vcc.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="{{ old('name') }}"></div>
                <div class="col-md-6"><label class="form-label">Role / Title *</label><input type="text" name="role_title" class="form-control" required placeholder="e.g. Chairman" value="{{ old('role_title') }}"></div>
                <div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
                <div class="col-md-6"><label class="form-label">Village / Location</label><input type="text" name="village" class="form-control" value="{{ old('village') }}" placeholder="e.g. from Dubai"></div>
                <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3">{{ old('bio') }}</textarea></div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Save Member</button>
                    <a href="{{ route('admin.vcc.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
