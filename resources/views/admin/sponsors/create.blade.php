@extends('layouts.admin')
@section('title','Add Sponsor')
@section('page-title','Add Sponsor')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.sponsors.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Sponsor Name *</label><input type="text" name="name" class="form-control" required value="{{ old('name') }}"></div>
                <div class="col-md-6">
                    <label class="form-label">Tier *</label>
                    <select name="tier" class="form-select" required>
                        <option value="title" {{ old('tier')==='title'?'selected':'' }}>Title Sponsor</option>
                        <option value="gold" {{ old('tier')==='gold'?'selected':'' }}>Gold Sponsor</option>
                        <option value="silver" {{ old('tier')==='silver'?'selected':'' }}>Silver Sponsor</option>
                        <option value="general" {{ old('tier','general')==='general'?'selected':'' }}>General Sponsor</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Logo / Image</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Website URL</label><input type="url" name="website" class="form-control" placeholder="https://" value="{{ old('website') }}"></div>
                <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0"></div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active / Visible</label>
                    </div>
                </div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Brief note about this sponsor...">{{ old('description') }}</textarea></div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Save Sponsor</button>
                    <a href="{{ route('admin.sponsors.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
