@extends('layouts.admin')
@section('title','Edit Edition')
@section('page-title','Edit Edition — '.$edition->name)
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.editions.update',$edition) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Edition Name *</label><input type="text" name="name" class="form-control" value="{{ $edition->name }}" required></div>
                <div class="col-md-6"><label class="form-label">Edition Number *</label><input type="number" name="edition_number" class="form-control" value="{{ $edition->edition_number }}" required></div>
                <div class="col-md-6"><label class="form-label">Host Village *</label><input type="text" name="host_village" class="form-control" value="{{ $edition->host_village }}" required></div>
                <div class="col-md-6"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['upcoming','active','completed','archived'] as $s)
                        <option value="{{ $s }}" {{ $edition->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ $edition->start_date?->format('Y-m-d') }}"></div>
                <div class="col-md-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ $edition->end_date?->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ $edition->description }}</textarea></div>
                <div class="col-12">
                    <label class="form-label">Teams</label>
                    <div class="row g-2">
                        @foreach($teams as $t)
                        <div class="col-6 col-md-3">
                            <div style="display:flex;align-items:center;gap:.4rem;padding:.35rem .5rem;background:var(--rcl-surface2);border:1px solid var(--rcl-border);border-radius:6px;">
                                <input type="checkbox" name="team_ids[]" value="{{ $t->id }}" id="et_{{ $t->id }}" {{ in_array($t->id,$assignedIds)?'checked':'' }}>
                                <label for="et_{{ $t->id }}" style="font-size:.78rem;cursor:pointer;">{{ $t->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem;background:var(--rcl-surface2);border-radius:6px;">
                        <input type="checkbox" name="set_as_current" id="set_as_current" value="1" {{ $edition->is_current?'checked':'' }}>
                        <label for="set_as_current" style="font-size:.85rem;cursor:pointer;">Set as current edition</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Update Edition</button>
                    <a href="{{ route('admin.editions.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
