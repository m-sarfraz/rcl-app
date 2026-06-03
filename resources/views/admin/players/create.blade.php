@extends('layouts.admin')
@section('title','Add Player')
@section('page-title','Add Player')
@section('content')
<div class="rcl-card" style="max-width:700px;">
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.players.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Father Name <span style="font-size:.72rem;color:var(--rcl-muted);">(S/o)</span></label><input type="text" name="father_name" class="form-control" placeholder="Son of..."></div>
                <div class="col-md-6"><label class="form-label">Jersey #</label><input type="text" name="jersey_number" class="form-control"></div>
                <div class="col-md-6">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="batsman">Batsman</option><option value="bowler">Bowler</option><option value="all_rounder">All Rounder</option><option value="wicket_keeper">Wicket Keeper</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batting Style *</label>
                    <select name="batting_style" class="form-select" required>
                        <option value="right_hand">Right Hand</option><option value="left_hand">Left Hand</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bowling Style *</label>
                    <select name="bowling_style" class="form-select" required>
                        <option value="none">None (Batsman)</option>
                        <option value="right_arm_fast">Right Arm Fast</option><option value="right_arm_medium">Right Arm Medium</option>
                        <option value="right_arm_spin">Right Arm Spin</option><option value="left_arm_fast">Left Arm Fast</option>
                        <option value="left_arm_medium">Left Arm Medium</option><option value="left_arm_spin">Left Arm Spin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bowling Action Status *</label>
                    <select name="bowling_action_status" class="form-select" required>
                        <option value="legal">Legal</option><option value="flagged">Flagged</option><option value="banned">Banned</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="2"></textarea></div>
                <div style="padding:.75rem;background:var(--rcl-surface2);border-radius:8px;" class="col-12">
                    <div style="font-weight:700;font-size:.85rem;margin-bottom:.5rem;color:var(--rcl-muted);">Assign to Edition & Team (optional)</div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Edition</label><select name="edition_id" class="form-select"><option value="">—</option>@foreach($editions as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div>
                        <div class="col-6"><label class="form-label">Team</label><select name="team_id" class="form-select"><option value="">—</option>@foreach($teams as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-rcl-primary">Create Player</button><a href="{{ route('admin.players.index') }}" class="btn btn-rcl-secondary">Cancel</a></div>
            </div>
        </form>
    </div>
</div>
@endsection
