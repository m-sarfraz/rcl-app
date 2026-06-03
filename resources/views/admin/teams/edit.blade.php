@extends('layouts.admin')
@section('title','Edit Team')
@section('page-title','Edit Team — '.$team->name)
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.teams.update',$team) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Team Name *</label><input type="text" name="name" class="form-control" value="{{ $team->name }}" required></div>
                <div class="col-md-6"><label class="form-label">Village Name *</label><input type="text" name="village_name" class="form-control" value="{{ $team->village_name }}" required></div>
                <div class="col-md-4"><label class="form-label">Short Code *</label><input type="text" name="short_code" class="form-control" value="{{ $team->short_code }}" maxlength="5" required></div>
                <div class="col-md-4"><label class="form-label">Primary Color</label><input type="color" name="primary_color" class="form-control" value="{{ $team->primary_color ?? '#00e676' }}"></div>
                <div class="col-md-4"><label class="form-label">Secondary Color</label><input type="color" name="secondary_color" class="form-control" value="{{ $team->secondary_color ?? '#004d40' }}"></div>
                <div class="col-md-6">
                    <label class="form-label">New Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if($team->logo)<div style="margin-top:.4rem;"><img src="{{ asset('storage/'.$team->logo) }}" style="height:36px;border-radius:50%;"></div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cover Photo <small style="color:var(--rcl-muted);">(header banner, max 2MB)</small></label>
                    <input type="file" name="cover_photo" class="form-control" accept="image/*">
                    @if($team->cover_photo)<div style="margin-top:.4rem;"><img src="{{ asset('storage/'.$team->cover_photo) }}" style="height:36px;border-radius:6px;object-fit:cover;width:120px;"></div>@endif
                </div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $team->description }}</textarea></div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Update Team</button><a href="{{ route('admin.teams.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
