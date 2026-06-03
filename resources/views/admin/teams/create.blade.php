@extends('layouts.admin')
@section('title','Add Team')
@section('page-title','Add Team')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.teams.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Team Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Village Name *</label><input type="text" name="village_name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Short Code (max 5) *</label><input type="text" name="short_code" class="form-control" maxlength="5" required style="text-transform:uppercase;"></div>
                <div class="col-md-4"><label class="form-label">Primary Color</label><input type="color" name="primary_color" class="form-control" value="#00e676"></div>
                <div class="col-md-4"><label class="form-label">Secondary Color</label><input type="color" name="secondary_color" class="form-control" value="#004d40"></div>
                <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Cover Photo <small style="color:var(--rcl-muted);">(header banner, max 2MB)</small></label><input type="file" name="cover_photo" class="form-control" accept="image/*"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Create Team</button><a href="{{ route('admin.teams.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
