@extends('layouts.admin')
@section('title', 'Record Demerit Point')
@section('page-title', 'Record Demerit Point')

@section('content')
<div class="rcl-card" style="max-width: 720px; margin: 0 auto;">
    <div class="rcl-card-header">
        <span style="font-weight: 800; font-size: .95rem;">
            <i class="bi bi-shield-slash-fill me-1 text-danger"></i> Record Disciplinary Demerit Point
        </span>
        <a href="{{ route('admin.demerit-points.index') }}" class="btn btn-sm btn-rcl-secondary">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
    </div>

    <div class="rcl-card-body">
        <!-- Rule Reminder -->
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1.25rem; font-size: .82rem; color: var(--rcl-text); display: flex; align-items: center; gap: .6rem;">
            <i class="bi bi-info-circle-fill" style="color: #ef4444; font-size: 1.1rem;"></i>
            <div>
                <strong>Rule Note:</strong> Once an entity (team or player) reaches <strong>3 demerit points</strong>, they will be automatically marked as <strong>BANNED</strong> from playing until further decision.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.demerit-points.store') }}" id="demeritForm">
            @csrf

            <div class="row g-3">

                <!-- Step 1: Select Tab / Target Category -->
                <div class="col-12">
                    <label class="form-label" style="font-weight: 700;">Disciplinary Category / Tab *</label>
                    <div class="d-flex flex-wrap gap-2" id="targetTypeChips">
                        @php
                            $defaultType = request('new_tab') ? 'custom' : (request('tab') ?: 'player');
                            if (!in_array($defaultType, array_merge($existingCategories, ['custom']))) {
                                $defaultType = 'player';
                            }
                        @endphp

                        @foreach($existingCategories as $cat)
                        <label class="btn btn-sm btn-rcl-secondary category-chip {{ $defaultType === $cat ? 'active' : '' }}" style="cursor: pointer; font-weight: 600;">
                            <input type="radio" name="target_type" value="{{ $cat }}" {{ $defaultType === $cat ? 'checked' : '' }} class="d-none">
                            <i class="bi {{ $cat === 'player' ? 'bi-person-fill' : ($cat === 'team' ? 'bi-shield-fill' : 'bi-patch-exclamation-fill') }}"></i>
                            {{ $cat === 'player' ? 'Player' : ($cat === 'team' ? 'Team' : ($cat === 'umpire' ? 'Umpire' : ucfirst($cat))) }}
                        </label>
                        @endforeach

                        <label class="btn btn-sm btn-rcl-secondary category-chip {{ $defaultType === 'custom' ? 'active' : '' }}" style="cursor: pointer; font-weight: 600; border-style: dashed; border-color: var(--rcl-primary); color: var(--rcl-primary);">
                            <input type="radio" name="target_type" value="custom" {{ $defaultType === 'custom' ? 'checked' : '' }} class="d-none">
                            <i class="bi bi-plus-circle"></i> + Add New Tab / Category
                        </label>
                    </div>
                </div>

                <!-- Custom Category Name input (shown only if custom selected) -->
                <div class="col-md-6" id="customCategoryGroup" style="{{ $defaultType === 'custom' ? '' : 'display: none;' }}">
                    <label class="form-label" style="font-weight: 700;">New Tab Name *</label>
                    <input type="text" name="custom_target_type" id="custom_target_type" class="form-control" placeholder="e.g. Manager, Official, Scorer" value="{{ old('custom_target_type') }}">
                    <div style="font-size: .72rem; color: var(--rcl-muted); margin-top: 3px;">
                        This will create a new tab in both the admin ledger and mobile app.
                    </div>
                </div>

                <!-- Edition Selection -->
                <div class="col-md-6">
                    <label class="form-label">Edition</label>
                    <select name="edition_id" id="edition_id" class="form-select">
                        <option value="">— All / General —</option>
                        @foreach($editions as $e)
                        <option value="{{ $e->id }}" {{ ($currentEdition?->id == $e->id) ? 'selected' : '' }}>
                            {{ $e->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Team Dropdown (Shown for Player & Team) -->
                <div class="col-md-6" id="teamSelectGroup">
                    <label class="form-label" id="teamLabel">Team *</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">— Select team —</option>
                        @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->short_name }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Player Dropdown (Shown only for Player) -->
                <div class="col-md-6" id="playerSelectGroup">
                    <label class="form-label">Player *</label>
                    <select name="player_id" id="player_id" class="form-select" disabled>
                        <option value="">— Select team first —</option>
                    </select>
                    <div id="playerLoading" style="display: none; font-size: .72rem; color: var(--rcl-primary); margin-top: 3px;">
                        <i class="bi bi-arrow-clockwise"></i> Loading squad players...
                    </div>
                </div>

                <!-- Entity / Target Name (Shown for Umpire or Custom) -->
                <div class="col-12" id="targetNameGroup" style="display: none;">
                    <label class="form-label" id="targetNameLabel">Target Name *</label>
                    <input type="text" name="target_name" id="target_name" class="form-control" placeholder="Enter recipient or umpire name" value="{{ old('target_name') }}">
                </div>

                <!-- Demerit Points Value -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 700;">Demerit Points *</label>
                    <div style="display: flex; gap: .5rem; align-items: center;">
                        <input type="number" name="points" id="pointsInput" class="form-control" min="1" max="10" value="{{ old('points', 1) }}" required style="font-weight: 800; font-size: 1.1rem; width: 100px;">
                        <button type="button" class="btn btn-sm btn-rcl-secondary point-quick-btn" data-pts="1" style="border-color: #eab308; color: #eab308;">+1 Pt</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary point-quick-btn" data-pts="2" style="border-color: #ef4444; color: #ef4444;">+2 Pts</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary point-quick-btn" data-pts="3" style="background: rgba(239,68,68,0.2); border-color: #ef4444; color: #ef4444; font-weight: 800;">+3 Pts (Ban)</button>
                    </div>
                    <div style="font-size: .72rem; color: var(--rcl-muted); margin-top: 4px;">
                        Points will accumulate towards the 3-point ban threshold.
                    </div>
                </div>

                <!-- Incident Date -->
                <div class="col-md-6">
                    <label class="form-label">Incident Date *</label>
                    <input type="date" name="incident_date" class="form-control" value="{{ old('incident_date', date('Y-m-d')) }}" required>
                </div>

                <!-- Match (Optional) -->
                <div class="col-12">
                    <label class="form-label">Associated Match <span style="font-size: .72rem; color: var(--rcl-muted);">(optional)</span></label>
                    <select name="match_id" class="form-select">
                        <option value="">— No specific match / Out of match —</option>
                        @foreach($matches as $m)
                        <option value="{{ $m->id }}">
                            Match #{{ $m->match_number }}: {{ $m->homeTeam?->short_name }} vs {{ $m->awayTeam?->short_name }} ({{ $m->scheduled_at?->format('d M Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reason / Violation -->
                <div class="col-12">
                    <label class="form-label" style="font-weight: 700;">Reason / Infraction Description *</label>
                    <textarea name="reason" id="reasonInput" class="form-control" rows="3" placeholder="Describe the reason for the demerit point..." required>{{ old('reason') }}</textarea>
                    
                    <!-- Quick Suggestions -->
                    <div style="display: flex; gap: .35rem; flex-wrap: wrap; margin-top: .4rem;">
                        <span style="font-size: .72rem; color: var(--rcl-muted); align-self: center;">Quick fill:</span>
                        <button type="button" class="btn btn-sm btn-rcl-secondary reason-preset" style="font-size: .72rem; padding: .1rem .45rem;">Dissent against umpire</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary reason-preset" style="font-size: .72rem; padding: .1rem .45rem;">Abusive language</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary reason-preset" style="font-size: .72rem; padding: .1rem .45rem;">Slow over rate</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary reason-preset" style="font-size: .72rem; padding: .1rem .45rem;">Equipment abuse</button>
                        <button type="button" class="btn btn-sm btn-rcl-secondary reason-preset" style="font-size: .72rem; padding: .1rem .45rem;">Delaying the match</button>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div class="col-12">
                    <label class="form-label">Admin Notes <span style="font-size: .72rem; color: var(--rcl-muted);">(internal only)</span></label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Internal investigation or council decision notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-rcl-primary">
                        <i class="bi bi-shield-slash-fill"></i> Save Demerit Point
                    </button>
                    <a href="{{ route('admin.demerit-points.index') }}" class="btn btn-rcl-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chipInputs         = document.querySelectorAll('input[name="target_type"]');
    const teamGroup          = document.getElementById('teamSelectGroup');
    const teamSel            = document.getElementById('team_id');
    const teamLabel          = document.getElementById('teamLabel');
    const playerGroup        = document.getElementById('playerSelectGroup');
    const playerSel          = document.getElementById('player_id');
    const playerLoading      = document.getElementById('playerLoading');
    const targetNameGroup    = document.getElementById('targetNameGroup');
    const targetNameLabel    = document.getElementById('targetNameLabel');
    const customCatGroup     = document.getElementById('customCategoryGroup');
    const editionSel         = document.getElementById('edition_id');
    const pointsInput        = document.getElementById('pointsInput');
    const reasonInput        = document.getElementById('reasonInput');

    const apiBase = '{{ route('admin.demerit-points.players-by-team', ['team' => '__TEAM__']) }}';

    function getSelectedCategory() {
        const checked = document.querySelector('input[name="target_type"]:checked');
        return checked ? checked.value : 'player';
    }

    function updateFormVisibility() {
        const cat = getSelectedCategory();

        // Highlight active chip
        document.querySelectorAll('.category-chip').forEach(btn => {
            const radio = btn.querySelector('input');
            if (radio && radio.checked) {
                btn.classList.add('active');
                btn.style.background = 'var(--rcl-primary)';
                btn.style.color = '#000';
            } else {
                btn.classList.remove('active');
                btn.style.background = 'var(--rcl-surface2)';
                btn.style.color = '';
            }
        });

        // Toggle custom tab name input
        if (cat === 'custom') {
            customCatGroup.style.display = 'block';
        } else {
            customCatGroup.style.display = 'none';
        }

        if (cat === 'player') {
            teamGroup.style.display = 'block';
            teamLabel.textContent = 'Team *';
            teamSel.required = true;
            playerGroup.style.display = 'block';
            playerSel.required = true;
            targetNameGroup.style.display = 'none';
            document.getElementById('target_name').required = false;
        } else if (cat === 'team') {
            teamGroup.style.display = 'block';
            teamLabel.textContent = 'Team to Penalize *';
            teamSel.required = true;
            playerGroup.style.display = 'none';
            playerSel.required = false;
            targetNameGroup.style.display = 'none';
            document.getElementById('target_name').required = false;
        } else if (cat === 'umpire') {
            teamGroup.style.display = 'none';
            teamSel.required = false;
            playerGroup.style.display = 'none';
            playerSel.required = false;
            targetNameGroup.style.display = 'block';
            targetNameLabel.textContent = 'Umpire Name *';
            document.getElementById('target_name').required = true;
        } else {
            // Custom category
            teamGroup.style.display = 'none';
            teamSel.required = false;
            playerGroup.style.display = 'none';
            playerSel.required = false;
            targetNameGroup.style.display = 'block';
            targetNameLabel.textContent = 'Recipient / Official Name *';
            document.getElementById('target_name').required = true;
        }
    }

    function loadPlayers() {
        const teamId = teamSel.value;
        const editionId = editionSel.value;

        if (!teamId) {
            playerSel.innerHTML = '<option value="">— Select team first —</option>';
            playerSel.disabled = true;
            return;
        }

        playerLoading.style.display = 'block';
        const url = apiBase.replace('__TEAM__', teamId) + (editionId ? '?edition_id=' + editionId : '');

        fetch(url)
            .then(r => r.json())
            .then(players => {
                playerLoading.style.display = 'none';
                if (!players || players.length === 0) {
                    playerSel.innerHTML = '<option value="">— No players registered for this team —</option>';
                    playerSel.disabled = false;
                    return;
                }

                playerSel.innerHTML = '<option value="">— Select player —</option>';
                players.forEach(p => {
                    const label = [
                        p.jersey_number ? '#' + p.jersey_number : '',
                        p.name,
                        p.father_name ? 's/o ' + p.father_name : ''
                    ].filter(Boolean).join(' ');
                    playerSel.innerHTML += `<option value="${p.id}">${label}</option>`;
                });
                playerSel.disabled = false;
            })
            .catch(() => {
                playerLoading.style.display = 'none';
            });
    }

    chipInputs.forEach(input => {
        input.addEventListener('change', updateFormVisibility);
    });

    teamSel.addEventListener('change', function () {
        if (getSelectedCategory() === 'player') {
            loadPlayers();
        }
    });

    editionSel.addEventListener('change', function () {
        if (getSelectedCategory() === 'player' && teamSel.value) {
            loadPlayers();
        }
    });

    // Quick point buttons
    document.querySelectorAll('.point-quick-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            pointsInput.value = this.dataset.pts;
        });
    });

    // Quick presets for reason
    document.querySelectorAll('.reason-preset').forEach(btn => {
        btn.addEventListener('click', function () {
            reasonInput.value = this.textContent.trim();
        });
    });

    updateFormVisibility();
});
</script>
@endpush
@endsection
