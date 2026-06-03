@extends('layouts.admin')
@section('title','Edit Player')
@section('page-title','Edit Player — '.$player->name)
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.players.update',$player) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="{{ $player->name }}" required></div>
                <div class="col-md-6"><label class="form-label">Father Name <span style="font-size:.72rem;color:var(--rcl-muted);">(S/o)</span></label><input type="text" name="father_name" class="form-control" value="{{ $player->father_name }}" placeholder="Son of..."></div>
                <div class="col-md-6"><label class="form-label">Jersey #</label><input type="text" name="jersey_number" class="form-control" value="{{ $player->jersey_number }}"></div>
                <div class="col-md-6"><label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach(['batsman','bowler','all_rounder','wicket_keeper'] as $r)
                        <option value="{{ $r }}" {{ $player->role===$r?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Batting Style *</label>
                    <select name="batting_style" class="form-select" required>
                        <option value="right_hand" {{ $player->batting_style==='right_hand'?'selected':'' }}>Right Hand</option>
                        <option value="left_hand"  {{ $player->batting_style==='left_hand'?'selected':'' }}>Left Hand</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Bowling Style *</label>
                    <select name="bowling_style" class="form-select" required>
                        @foreach(['none','right_arm_fast','right_arm_medium','right_arm_spin','left_arm_fast','left_arm_medium','left_arm_spin'] as $bs)
                        <option value="{{ $bs }}" {{ $player->bowling_style===$bs?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$bs)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Bowling Action Status *</label>
                    <select name="bowling_action_status" class="form-select" required>
                        <option value="legal"   {{ $player->bowling_action_status==='legal'?'selected':'' }}>Legal</option>
                        <option value="flagged" {{ $player->bowling_action_status==='flagged'?'selected':'' }}>Flagged</option>
                        <option value="banned"  {{ $player->bowling_action_status==='banned'?'selected':'' }}>Banned</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $player->phone }}"></div>
                <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="2">{{ $player->bio }}</textarea></div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Update Player</button><a href="{{ route('admin.players.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
