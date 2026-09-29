@extends('layouts.admin')
@section('title', 'Edit Demerit Point')
@section('page-title', 'Edit Demerit Point')

@section('content')
<div class="rcl-card" style="max-width: 720px; margin: 0 auto;">
    <div class="rcl-card-header">
        <span style="font-weight: 800; font-size: .95rem;">
            <i class="bi bi-pencil-square me-1"></i> Edit Demerit Point Incident #{{ $demeritPoint->id }}
        </span>
        <a href="{{ route('admin.demerit-points.index', ['tab' => $demeritPoint->target_type]) }}" class="btn btn-sm btn-rcl-secondary">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
    </div>

    <div class="rcl-card-body">
        <form method="POST" action="{{ route('admin.demerit-points.update', $demeritPoint) }}" id="editDemeritForm">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <!-- Target Category -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 700;">Category / Tab</label>
                    <select name="target_type" id="target_type" class="form-select">
                        @foreach($existingCategories as $cat)
                        <option value="{{ $cat }}" {{ $demeritPoint->target_type === $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Edition Selection -->
                <div class="col-md-6">
                    <label class="form-label">Edition</label>
                    <select name="edition_id" id="edition_id" class="form-select">
                        <option value="">— General / All —</option>
                        @foreach($editions as $e)
                        <option value="{{ $e->id }}" {{ $demeritPoint->edition_id == $e->id ? 'selected' : '' }}>
                            {{ $e->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Team Dropdown -->
                <div class="col-md-6" id="teamSelectGroup">
                    <label class="form-label" id="teamLabel">Team</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">— Select team —</option>
                        @foreach($teams as $t)
                        <option value="{{ $t->id }}" {{ $demeritPoint->team_id == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->short_name }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Player Dropdown -->
                <div class="col-md-6" id="playerSelectGroup">
                    <label class="form-label">Player</label>
                    <select name="player_id" id="player_id" class="form-select">
                        <option value="">— Select player —</option>
                        @foreach($teamPlayers as $p)
                        <option value="{{ $p->id }}" {{ $demeritPoint->player_id == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->father_name ? '(s/o ' . $p->father_name . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Name (for umpire or custom) -->
                <div class="col-12" id="targetNameGroup" style="display: none;">
                    <label class="form-label">Target Name / Recipient</label>
                    <input type="text" name="target_name" id="target_name" class="form-control" value="{{ old('target_name', $demeritPoint->target_name) }}">
                </div>

                <!-- Points Value -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 700;">Demerit Points *</label>
                    <input type="number" name="points" id="pointsInput" class="form-control" min="1" max="10" value="{{ old('points', $demeritPoint->points) }}" required style="font-weight: 800; font-size: 1.1rem; width: 120px;">
                </div>

                <!-- Incident Date -->
                <div class="col-md-6">
                    <label class="form-label">Incident Date *</label>
                    <input type="date" name="incident_date" class="form-control" value="{{ old('incident_date', $demeritPoint->incident_date?->format('Y-m-d')) }}" required>
                </div>

                <!-- Match -->
                <div class="col-12">
                    <label class="form-label">Associated Match</label>
                    <select name="match_id" class="form-select">
                        <option value="">— No specific match —</option>
                        @foreach($matches as $m)
                        <option value="{{ $m->id }}" {{ $demeritPoint->match_id == $m->id ? 'selected' : '' }}>
                            Match #{{ $m->match_number }}: {{ $m->homeTeam?->short_name }} vs {{ $m->awayTeam?->short_name }} ({{ $m->scheduled_at?->format('d M Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reason / Violation -->
                <div class="col-12">
                    <label class="form-label" style="font-weight: 700;">Reason / Infraction Description *</label>
                    <textarea name="reason" class="form-control" rows="3" required>{{ old('reason', $demeritPoint->reason) }}</textarea>
                </div>

                <!-- Status Checkbox -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $demeritPoint->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <strong>Active Disciplinary Point</strong> (counts towards ban threshold)
                        </label>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div class="col-12">
                    <label class="form-label">Admin Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $demeritPoint->notes) }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-rcl-primary">
                        <i class="bi bi-check-circle-fill"></i> Update Demerit Point
                    </button>
                    <a href="{{ route('admin.demerit-points.index', ['tab' => $demeritPoint->target_type]) }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetTypeSel = document.getElementById('target_type');
    const teamGroup     = document.getElementById('teamSelectGroup');
    const teamSel       = document.getElementById('team_id');
    const playerGroup   = document.getElementById('playerSelectGroup');
    const playerSel     = document.getElementById('player_id');
    const targetGroup   = document.getElementById('targetNameGroup');
    const editionSel    = document.getElementById('edition_id');

    const apiBase = '{{ route('admin.demerit-points.players-by-team', ['team' => '__TEAM__']) }}';

    function updateVisibility() {
        const cat = targetTypeSel.value;
        if (cat === 'player') {
            teamGroup.style.display = 'block';
            playerGroup.style.display = 'block';
            targetGroup.style.display = 'none';
        } else if (cat === 'team') {
            teamGroup.style.display = 'block';
            playerGroup.style.display = 'none';
            targetGroup.style.display = 'none';
        } else {
            teamGroup.style.display = 'none';
            playerGroup.style.display = 'none';
            targetGroup.style.display = 'block';
        }
    }

    teamSel.addEventListener('change', function () {
        if (targetTypeSel.value !== 'player' || !teamSel.value) return;

        const url = apiBase.replace('__TEAM__', teamSel.value) + (editionSel.value ? '?edition_id=' + editionSel.value : '');
        fetch(url)
            .then(r => r.json())
            .then(players => {
                playerSel.innerHTML = '<option value="">— Select player —</option>';
                players.forEach(p => {
                    const label = [
                        p.jersey_number ? '#' + p.jersey_number : '',
                        p.name,
                        p.father_name ? 's/o ' + p.father_name : ''
                    ].filter(Boolean).join(' ');
                    playerSel.innerHTML += `<option value="${p.id}">${label}</option>`;
                });
            });
    });

    targetTypeSel.addEventListener('change', updateVisibility);
    updateVisibility();
});
</script>
@endpush
@endsection
