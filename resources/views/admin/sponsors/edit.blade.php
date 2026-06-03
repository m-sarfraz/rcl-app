@extends('layouts.admin')
@section('title','Edit Sponsor')
@section('page-title','Edit Sponsor')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Sponsor Name *</label><input type="text" name="name" class="form-control" required value="{{ $sponsor->name }}"></div>
                <div class="col-md-6">
                    <label class="form-label">Tier *</label>
                    <select name="tier" class="form-select" required>
                        <option value="title" {{ $sponsor->tier==='title'?'selected':'' }}>Title Sponsor</option>
                        <option value="gold" {{ $sponsor->tier==='gold'?'selected':'' }}>Gold Sponsor</option>
                        <option value="silver" {{ $sponsor->tier==='silver'?'selected':'' }}>Silver Sponsor</option>
                        <option value="general" {{ $sponsor->tier==='general'?'selected':'' }}>General Sponsor</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo / Image</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if($sponsor->logo)
                    <div style="margin-top:.5rem;">
                        <img src="{{ asset('storage/'.$sponsor->logo) }}" style="height:40px;max-width:120px;object-fit:contain;border-radius:4px;border:1px solid var(--rcl-border);">
                        <span style="font-size:.72rem;color:var(--rcl-muted);margin-left:.5rem;">Current logo</span>
                    </div>
                    @endif
                </div>
                <div class="col-md-6"><label class="form-label">Website URL</label><input type="url" name="website" class="form-control" placeholder="https://" value="{{ $sponsor->website }}"></div>
                <div class="col-md-6"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="{{ $sponsor->display_order }}" min="0"></div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $sponsor->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active / Visible</label>
                    </div>
                </div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ $sponsor->description }}</textarea></div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Update Sponsor</button>
                    <a href="{{ route('admin.sponsors.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
