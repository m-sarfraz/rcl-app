@extends('layouts.admin')
@section('title','Schedule Match')
@section('page-title','Schedule New Match')
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.matches.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Edition *</label><select name="edition_id" class="form-select" required><option value="">Select</option>@foreach($editions as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Match Number *</label><input type="text" name="match_number" class="form-control" placeholder="e.g. M1, QF1, SF1" required></div>
                <div class="col-md-6"><label class="form-label">Home Team *</label><select name="home_team_id" class="form-select" required><option value="">Select</option>@foreach($teams as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Away Team *</label><select name="away_team_id" class="form-select" required><option value="">Select</option>@foreach($teams as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Match Type *</label><select name="match_type" class="form-select"><option value="group">Group</option><option value="quarter_final">Quarter Final</option><option value="semi_final">Semi Final</option><option value="final">Final</option></select></div>
                <div class="col-md-6"><label class="form-label">Venue *</label><input type="text" name="venue" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Date & Time *</label><input type="datetime-local" name="scheduled_at" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Overs Per Side *</label><input type="number" name="overs_per_side" class="form-control" value="20" min="1" max="50" required></div>
                <div class="col-md-6"><label class="form-label">Umpire 1</label><select name="umpire1_id" class="form-select"><option value="">—</option>@foreach($officials as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Umpire 2</label><select name="umpire2_id" class="form-select"><option value="">—</option>@foreach($officials as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Scorer</label><select name="scorer_id" class="form-select"><option value="">—</option>@foreach($officials as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Schedule Match</button><a href="{{ route('admin.matches.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
