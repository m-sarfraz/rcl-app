@extends('layouts.admin')
@section('title','Manage Captain — '.$team->name)
@section('page-title','Manage Captain — '.$team->name)

@section('topbar-actions')
<a href="{{ route('admin.captains.index') }}" class="topbar-btn">
    <i class="bi bi-arrow-left"></i> All Teams
</a>
@endsection

@push('styles')
<style>
.player-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.65rem 1rem; border-bottom:1px solid var(--rcl-border);
    transition:background .15s;
}
.player-row:last-child { border-bottom:none; }
.player-row:hover { background:var(--rcl-surface2); }
.player-avatar {
    width:34px; height:34px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,{{ $team->primary_color ?? '#00e676' }},{{ $team->secondary_color ?? '#ffd600' }});
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.68rem; color:#fff;
}
.role-badge { font-size:.65rem; padding:.18em .55em; border-radius:4px; font-weight:600; }
.role-bat   { background:rgba(0,230,118,.12); color:var(--rcl-primary); }
.role-bowl  { background:rgba(239,68,68,.12);  color:#ef4444; }
.role-ar    { background:rgba(255,214,0,.12);  color:var(--rcl-gold); }
.role-wk    { background:rgba(96,165,250,.12); color:#60a5fa; }
.radio-cap, .radio-vc { accent-color:var(--rcl-gold); width:16px; height:16px; cursor:pointer; }
.radio-vc { accent-color:var(--rcl-primary); }
</style>
@endpush

@section('content')
<div style="max-width:760px;">

    {{-- Team header card --}}
    <div class="rcl-card" style="margin-bottom:1.25rem;">
        <div class="rcl-card-body" style="display:flex;align-items:center;gap:1rem;">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,{{ $team->primary_color ?? '#00e676' }},{{ $team->secondary_color ?? '#ffd600' }});display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($team->short_code,0,2)) }}
            </div>
            <div>
                <div style="font-size:1.05rem;font-weight:700;">{{ $team->name }}</div>
                <div style="font-size:.78rem;color:var(--rcl-muted);">{{ $team->village_name }} · {{ $edition?->name }}</div>
            </div>
            <div style="margin-left:auto;text-align:right;">
                <div style="font-size:1.4rem;font-weight:700;color:var(--rcl-primary);">{{ $players->count() }}</div>
                <div style="font-size:.65rem;color:var(--rcl-muted);text-transform:uppercase;">Players</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.captains.update', $team) }}">
        @csrf @method('PUT')

        <div class="rcl-card" style="margin-bottom:1.25rem;">
            <div class="rcl-card-header">
                <span><i class="bi bi-star-fill" style="color:var(--rcl-gold);"></i> Select Captain &amp; Vice-Captain</span>
            </div>

            {{-- Column headers --}}
            <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem 1rem;background:var(--rcl-surface2);border-bottom:1px solid var(--rcl-border);font-size:.68rem;font-weight:700;color:var(--rcl-muted);text-transform:uppercase;letter-spacing:.06em;">
                <div style="width:34px;flex-shrink:0;"></div>
                <div style="flex:1;">Player</div>
                <div style="width:55px;text-align:center;color:var(--rcl-gold);">C</div>
                <div style="width:55px;text-align:center;color:var(--rcl-primary);">VC</div>
            </div>

            @if($players->isEmpty())
                <div style="padding:2rem;text-align:center;color:var(--rcl-muted);">
                    No players found for this team in the current edition.
                </div>
            @else
                @foreach($players as $player)
                <div class="player-row">
                    <div class="player-avatar">{{ strtoupper(substr($player->name,0,1)) }}</div>
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:.875rem;">{{ $player->name }}</div>
                        <span class="role-badge
                            @if($player->role==='batsman') role-bat
                            @elseif($player->role==='bowler') role-bowl
                            @elseif($player->role==='all_rounder') role-ar
                            @else role-wk @endif">
                            {{ str_replace('_',' ',ucfirst($player->role ?? 'player')) }}
                        </span>
                    </div>
                    <div style="width:55px;text-align:center;">
                        <input type="radio" name="captain_id" value="{{ $player->id }}" class="radio-cap"
                            {{ $player->is_captain ? 'checked' : '' }}>
                    </div>
                    <div style="width:55px;text-align:center;">
                        <input type="radio" name="vc_id" value="{{ $player->id }}" class="radio-vc"
                            {{ $player->is_vice_captain ? 'checked' : '' }}>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        @if($errors->any())
            <div class="alert-rcl-error mb-3">{{ $errors->first() }}</div>
        @endif

        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-rcl-primary">
                <i class="bi bi-save"></i> Save Captain & VC
            </button>
            <a href="{{ route('admin.captains.index') }}" class="btn btn-rcl-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Clicking a VC radio on a player who is already captain auto-deselects it
document.querySelectorAll('.radio-vc').forEach(function(vc){
    vc.addEventListener('change', function(){
        var capSelected = document.querySelector('.radio-cap:checked');
        if(capSelected && capSelected.value === vc.value){
            vc.checked = false;
            alert('Captain and Vice-Captain must be different players.');
        }
    });
});
</script>
@endpush
