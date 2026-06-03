@extends('layouts.admin')
@section('title','Edit Banner')
@section('page-title','Edit Banner')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <div style="margin-bottom:1rem;">
            <img src="{{ asset('storage/'.$banner->image_path) }}" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;border:1px solid var(--rcl-border);">
        </div>
        <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Replace Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}"></div>
                <div class="col-md-6"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $banner->subtitle) }}"></div>
                <div class="col-md-8"><label class="form-label">Link URL</label><input type="url" name="link_url" class="form-control" value="{{ old('link_url', $banner->link_url) }}"></div>
                <div class="col-md-4"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ old('display_order', $banner->display_order) }}" min="0"></div>
                <div class="col-12">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem;background:var(--rcl-surface2);border-radius:6px;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }} style="width:14px;height:14px;">
                        <label for="is_active" style="font-size:.85rem;cursor:pointer;">Active</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Update Banner</button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
