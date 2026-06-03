@extends('layouts.admin')
@section('title','Create Poll')
@section('page-title','Create Fan Poll')
@section('content')
<div class="rcl-card" style="max-width:600px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.polls.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Question *</label><textarea name="question" class="form-control" rows="2" required placeholder="e.g. Who will win the 35th Edition Opener?"></textarea></div>
                <div class="col-md-6"><label class="form-label">Edition *</label><select name="edition_id" class="form-select" required>@foreach($editions as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Ends At</label><input type="datetime-local" name="ends_at" class="form-control"></div>
                <div class="col-12">
                    <label class="form-label">Options (2–6) *</label>
                    <div id="options-container">
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 1" required>
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 2" required>
                    </div>
                    <button type="button" onclick="addOption()" class="btn btn-rcl-secondary" style="font-size:.8rem;padding:.375rem .875rem;"><i class="bi bi-plus"></i> Add Option</button>
                </div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Create Poll</button><a href="{{ route('admin.polls.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
var optCount = 2;
function addOption() {
    if (optCount >= 6) { alert('Max 6 options'); return; }
    optCount++;
    $('#options-container').append('<input type="text" name="options[]" class="form-control mb-2" placeholder="Option ' + optCount + '">');
}
</script>
@endpush
