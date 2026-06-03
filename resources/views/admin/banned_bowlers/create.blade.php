@extends('layouts.admin')
@section('title','Ban Bowler')
@section('page-title','Ban a Bowler')

@section('content')
<div class="rcl-card" style="max-width:640px;">
    <div class="rcl-card-header">New Bowling Action Ban</div>
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.banned-bowlers.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Player *</label>
                    <select name="player_id" class="form-select" required>
                        <option value="">Select player</option>
                        @foreach($players as $p)
                        <option value="{{ $p->id }}" {{ old('player_id')==$p->id?'selected':'' }}>
                            {{ $p->name }}
                            @if($p->bowling_action_status === 'banned') (Already Banned) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('player_id')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Banned From *</label>
                    <input type="date" name="banned_from" class="form-control" value="{{ old('banned_from', date('Y-m-d')) }}" required>
                    @error('banned_from')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Banned Until <span style="color:var(--rcl-muted);font-size:.75rem;">(leave blank = indefinite)</span></label>
                    <input type="date" name="banned_until" class="form-control" value="{{ old('banned_until') }}">
                    @error('banned_until')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Reason *</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for bowling action ban...">{{ old('reason') }}</textarea>
                    @error('reason')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Admin Notes <span style="color:var(--rcl-muted);font-size:.75rem;">(internal only, not shown on frontend)</span></label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary">Issue Ban</button>
                    <a href="{{ route('admin.banned-bowlers.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
