@extends('layouts.admin')
@section('title','Create Edition')
@section('page-title','Create New Edition')
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.editions.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Edition Name *</label><input type="text" name="name" class="form-control" placeholder="e.g. 35th Edition" required value="{{ old('name') }}"></div>
                <div class="col-md-6"><label class="form-label">Edition Number *</label><input type="number" name="edition_number" class="form-control" required value="{{ old('edition_number') }}"></div>
                <div class="col-md-6"><label class="form-label">Host Village *</label><input type="text" name="host_village" class="form-control" required value="{{ old('host_village') }}"></div>
                <div class="col-md-6"><label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="upcoming">Upcoming</option><option value="active">Active</option><option value="completed">Completed</option><option value="archived">Archived</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}"></div>
                <div class="col-md-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}"></div>
                <div class="col-md-6"><label class="form-label">Thumbnail</label><input type="file" name="thumbnail" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Banner</label><input type="file" name="banner" class="form-control" accept="image/*"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea></div>
                <div class="col-12">
                    <label class="form-label">Assign Teams</label>
                    <div class="row g-2">
                        @foreach($teams as $t)
                        <div class="col-6 col-md-3">
                            <div style="display:flex;align-items:center;gap:.4rem;padding:.35rem .5rem;background:var(--rcl-surface2);border:1px solid var(--rcl-border);border-radius:6px;">
                                <input type="checkbox" name="team_ids[]" value="{{ $t->id }}" id="team_{{ $t->id }}" style="width:14px;height:14px;">
                                <label for="team_{{ $t->id }}" style="font-size:.78rem;cursor:pointer;">{{ $t->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem;background:var(--rcl-surface2);border-radius:6px;">
                        <input type="checkbox" name="set_as_current" id="set_as_current" value="1" style="width:14px;height:14px;">
                        <label for="set_as_current" style="font-size:.85rem;cursor:pointer;">Set as current active edition</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Create Edition</button>
                    <a href="{{ route('admin.editions.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
