@extends('layouts.admin')
@section('title','Edit Match')
@section('page-title','Edit Match #'.$match->match_number)
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.matches.update',$match) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Match Number *</label><input type="text" name="match_number" class="form-control" value="{{ $match->match_number }}" required></div>
                <div class="col-md-6"><label class="form-label">Home Team *</label><select name="home_team_id" class="form-select" required>@foreach($teams as $t)<option value="{{ $t->id }}" {{ $match->home_team_id===$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Away Team *</label><select name="away_team_id" class="form-select" required>@foreach($teams as $t)<option value="{{ $t->id }}" {{ $match->away_team_id===$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Match Type *</label><select name="match_type" class="form-select">@foreach(['group','quarter_final','semi_final','final'] as $mt)<option value="{{ $mt }}" {{ $match->match_type===$mt?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$mt)) }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Venue *</label><input type="text" name="venue" class="form-control" value="{{ $match->venue }}" required></div>
                <div class="col-md-6"><label class="form-label">Date & Time *</label><input type="datetime-local" name="scheduled_at" class="form-control" value="{{ $match->scheduled_at?->format('Y-m-d\TH:i') }}" required></div>
                <div class="col-md-6"><label class="form-label">Overs Per Side *</label><input type="number" name="overs_per_side" class="form-control" value="{{ $match->overs_per_side }}" required></div>
                <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['upcoming','live','completed','abandoned','postponed'] as $s)<option value="{{ $s }}" {{ $match->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Update Match</button><a href="{{ route('admin.matches.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
