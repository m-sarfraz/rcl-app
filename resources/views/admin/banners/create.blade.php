@extends('layouts.admin')
@section('title','Add Banner')
@section('page-title','Add Banner')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Banner Image * <span style="color:var(--rcl-muted);font-size:.72rem;">(Recommended: 1080×400px)</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="col-md-6"><label class="form-label">Title (optional)</label><input type="text" name="title" class="form-control" placeholder="e.g. 35th Edition Live!" value="{{ old('title') }}"></div>
                <div class="col-md-6"><label class="form-label">Subtitle (optional)</label><input type="text" name="subtitle" class="form-control" placeholder="Short tagline" value="{{ old('subtitle') }}"></div>
                <div class="col-md-8"><label class="form-label">Link URL (optional)</label><input type="url" name="link_url" class="form-control" placeholder="https://..." value="{{ old('link_url') }}"></div>
                <div class="col-md-4"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" min="0"></div>
                <div class="col-12">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem;background:var(--rcl-surface2);border-radius:6px;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked style="width:14px;height:14px;">
                        <label for="is_active" style="font-size:.85rem;cursor:pointer;">Active (show on home page)</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Upload Banner</button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
