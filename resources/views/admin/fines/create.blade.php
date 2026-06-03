@extends('layouts.admin')
@section('title','Issue Fine')
@section('page-title','Issue Fine')

@section('content')
<div class="rcl-card" style="max-width:660px;">
    <div class="rcl-card-header">New Fine / Disciplinary Action</div>
    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.fines.store') }}" id="fineForm">
            @csrf
            <div class="row g-3">

                {{-- Step 1: Edition --}}
                <div class="col-md-6">
                    <label class="form-label">Edition *</label>
                    <select name="edition_id" id="edition_id" class="form-select" required>
                        @foreach($editions as $e)
                        <option value="{{ $e->id }}" {{ $currentEdition?->id == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Step 2: Team --}}
                <div class="col-md-6">
                    <label class="form-label">Team * <span style="font-size:.72rem;color:var(--rcl-muted);">(fine is against the team)</span></label>
                    <select name="team_id" id="team_id" class="form-select" required>
                        <option value="">— Select team first —</option>
                        @foreach($teams as $t)
                        <option value="{{ $t->id }}" data-color="{{ $t->primary_color }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Step 3: Player (optional, loaded after team) --}}
                <div class="col-12">
                    <label class="form-label">
                        Highlighted Player
                        <span style="font-size:.72rem;color:var(--rcl-muted);">(optional — player involved in the incident)</span>
                    </label>
                    <select name="player_id" id="player_id" class="form-select" disabled>
                        <option value="">— Select team first —</option>
                    </select>
                    <div id="playerNote" style="display:none;margin-top:.4rem;font-size:.72rem;color:var(--rcl-muted);">
                        <i class="bi bi-info-circle"></i> Leave blank if fine applies to the whole team with no individual highlight.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Violation Type *</label>
                    <select name="violation_type" class="form-select" required>
                        <option value="code_of_conduct">Code of Conduct</option>
                        <option value="chucking">Chucking / Illegal Action</option>
                        <option value="disciplinary_card">Disciplinary Card</option>
                        <option value="misconduct">Misconduct</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Amount (PKR) *</label>
                    <input type="number" name="amount" class="form-control" min="1" required placeholder="e.g. 500">
                </div>

                <div class="col-12">
                    <label class="form-label">Description / Reason *</label>
                    <textarea name="description" class="form-control" rows="3" required
                        placeholder="Describe the violation clearly. If a player is highlighted, mention them here too."></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Match (optional)</label>
                    <input type="number" name="match_id" class="form-control" placeholder="Match ID if applicable">
                </div>

                <div class="col-12">
                    <label class="form-label">Admin Notes <span style="font-size:.72rem;color:var(--rcl-muted);">(internal only)</span></label>
                    <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-rcl-primary"><i class="bi bi-exclamation-triangle-fill"></i> Issue Fine</button>
                    <a href="{{ route('admin.fines.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const teamSel    = document.getElementById('team_id');
const editionSel = document.getElementById('edition_id');
const playerSel  = document.getElementById('player_id');
const playerNote = document.getElementById('playerNote');
const apiBase    = '{{ route('admin.fines.players-by-team', ['team' => '__TEAM__']) }}';

function loadPlayers() {
    const teamId    = teamSel.value;
    const editionId = editionSel.value;

    if (!teamId || !editionId) {
        playerSel.innerHTML = '<option value="">— Select team first —</option>';
        playerSel.disabled = true;
        playerNote.style.display = 'none';
        return;
    }

    const url = apiBase.replace('__TEAM__', teamId) + '?edition_id=' + editionId;

    fetch(url)
        .then(r => r.json())
        .then(players => {
            playerSel.innerHTML = '<option value="">— None (team-wide fine) —</option>';
            players.forEach(p => {
                const label = [
                    p.jersey_number ? '#' + p.jersey_number : '',
                    p.name,
                    p.father_name ? 'S/o ' + p.father_name : '',
                    p.is_captain ? '(C)' : (p.is_vice_captain ? '(VC)' : '')
                ].filter(Boolean).join(' ');
                playerSel.innerHTML += `<option value="${p.id}">${label}</option>`;
            });
            playerSel.disabled = false;
            playerNote.style.display = 'block';
        });
}

teamSel.addEventListener('change', loadPlayers);
editionSel.addEventListener('change', loadPlayers);
</script>
@endpush
@endsection
