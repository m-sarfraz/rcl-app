@extends('layouts.app')
@section('title', $team->name)

@push('styles')
<style>
/* ── Team Cover ─────────────────────────────────────── */
.team-cover-wrap {
    position: relative;
    height: 168px;
    flex-shrink: 0;
    overflow: hidden;
    background: linear-gradient(135deg,
        {{ $team->primary_color ?? '#1B8A4E' }} 0%,
        {{ $team->secondary_color ?? '#D4900A' }} 100%);
}
.team-cover-wrap::before {
    content:'';
    position:absolute;
    inset:0;
    background:
        radial-gradient(ellipse 80% 60% at 20% 50%, rgba(255,255,255,.12) 0%, transparent 60%),
        radial-gradient(ellipse 50% 80% at 80% 20%, rgba(0,0,0,.18) 0%, transparent 60%);
}
.team-cover-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
}
.team-cover-fade {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom,
        rgba(0,0,0,.08) 0%,
        rgba(0,0,0,.55) 100%);
}
/* Cricket pattern overlay */
.team-cover-wrap::after {
    content: '🏏';
    position: absolute;
    right: -10px; bottom: -8px;
    font-size: 5rem;
    opacity: .07;
    line-height: 1;
    pointer-events: none;
}
/* Team logo badge overlapping cover bottom */
.team-logo-overlap {
    position: relative;
    display: flex;
    justify-content: center;
    margin-top: -38px;
    z-index: 5;
    pointer-events: none;
}
.team-logo-circle {
    width: 76px; height: 76px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,.22), 0 0 0 2px rgba(27,138,78,.25);
    background: linear-gradient(135deg,
        {{ $team->primary_color ?? '#1B8A4E' }},
        {{ $team->secondary_color ?? '#D4900A' }});
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; color: #fff; font-size: 1.6rem;
    overflow: hidden;
    flex-shrink: 0;
}
.team-logo-circle img {
    width: 100%; height: 100%;
    object-fit: cover;
}
/* Short code tag on cover */
.team-cover-code {
    position: absolute;
    top: .75rem; left: .75rem;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff;
    font-size: .62rem; font-weight: 800;
    padding: .22em .65em;
    border-radius: 20px;
    letter-spacing: .06em;
}
/* Edition tag on cover */
.team-cover-edition {
    position: absolute;
    top: .75rem; right: .75rem;
    background: linear-gradient(135deg,var(--g),var(--g2));
    color: #fff;
    font-size: .58rem; font-weight: 700;
    padding: .28em .75em;
    border-radius: 20px;
    letter-spacing: .08em;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
/* Info section below cover */
.team-info-section {
    text-align: center;
    padding: .5rem 1rem 1rem;
    background: #fff;
    border-bottom: 1px solid var(--bd);
}
.team-info-name {
    font-size: 1.2rem; font-weight: 900;
    color: var(--txt); letter-spacing: .01em;
    margin-top: .25rem;
}
.team-info-village {
    font-size: .75rem; color: var(--mut);
    margin-top: .2rem;
    display: flex; align-items: center; justify-content: center; gap: .25rem;
}
.team-info-pills {
    display: flex; justify-content: center;
    gap: .4rem; flex-wrap: wrap;
    margin-top: .75rem;
}
</style>
@endpush

@section('content')

{{-- ── Team Cover Photo ──────────────────────────────── --}}
<div class="team-cover-wrap">
    @if($team->cover_photo)
        <img src="{{ asset('storage/'.$team->cover_photo) }}" class="team-cover-img" alt="{{ $team->name }} cover">
    @endif
    <div class="team-cover-fade"></div>

    @if($team->short_code)
        <div class="team-cover-code">{{ $team->short_code }}</div>
    @endif
    @if($currentEdition)
        <div class="team-cover-edition">{{ $currentEdition->edition_number }}th Ed.</div>
    @endif
</div>

{{-- ── Team Logo (overlaps cover) + Info ────────────── --}}
<div style="background:#fff;">
    <div class="team-logo-overlap">
        <div class="team-logo-circle">
            @if($team->logo)
                <img src="{{ asset('storage/'.$team->logo) }}" alt="{{ $team->name }}">
            @else
                {{ strtoupper(substr($team->short_code ?? $team->name, 0, 2)) }}
            @endif
        </div>
    </div>

    <div class="team-info-section">
        <div class="team-info-name">{{ $team->name }}</div>
        @if($team->village_name)
            <div class="team-info-village">
                <i class="bi bi-geo-alt-fill" style="color:var(--p);font-size:.7rem;"></i>
                {{ $team->village_name }}
            </div>
        @endif
        <div class="team-info-pills">
            <span class="pill pill-green">
                <i class="bi bi-people-fill" style="font-size:.6rem;"></i>
                {{ $players->count() }} Players
            </span>
            @if($currentEdition)
                <span class="pill pill-gold">Season {{ $currentEdition->edition_number }}</span>
            @endif
            @php
                $captain = $players->firstWhere('is_captain', 1) ?? $players->firstWhere('is_captain', true);
            @endphp
            @if($captain)
                <span class="pill pill-muted">
                    <i class="bi bi-star-fill" style="color:var(--g);font-size:.6rem;"></i>
                    C: {{ explode(' ', $captain->name)[0] }}
                </span>
            @endif
        </div>
    </div>
</div>

{{-- Players Grid --}}
<div class="sec">
    @if($players->isEmpty())
        <div style="padding:3rem 1rem;text-align:center;color:var(--mut);">
            <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
            No players assigned to this squad yet.
        </div>
    @else
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        @foreach($players as $player)
        @php $stat = $stats->get($player->id); @endphp
        <a href="{{ route('player', ['player' => $player->id]) }}" style="text-decoration:none;">
            <div style="background:var(--s1);border:1px solid {{ $player->is_captain ? 'rgba(212,144,10,.45)' : ($player->is_vice_captain ? 'rgba(27,138,78,.35)' : 'var(--bd)') }};border-radius:14px;padding:.875rem;text-align:center;transition:border-color .2s,transform .15s;cursor:pointer;position:relative;"
                 onmouseover="this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.transform='none'">

                {{-- jersey number --}}
                @if($player->jersey_number)
                <div style="position:absolute;top:.5rem;right:.5rem;font-size:.58rem;color:var(--mut);font-weight:700;">#{{ $player->jersey_number }}</div>
                @endif

                {{-- captain / vc badge --}}
                @if($player->is_captain)
                <div style="position:absolute;top:.5rem;left:.5rem;background:var(--g);color:#fff;font-size:.52rem;font-weight:800;padding:.15em .45em;border-radius:4px;">C</div>
                @elseif($player->is_vice_captain)
                <div style="position:absolute;top:.5rem;left:.5rem;background:var(--p);color:#fff;font-size:.52rem;font-weight:800;padding:.15em .45em;border-radius:4px;">VC</div>
                @endif

                {{-- avatar --}}
                @if($player->photo)
                    <img src="{{ asset('storage/'.$player->photo) }}"
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--bd);margin-bottom:.5rem;display:block;margin-left:auto;margin-right:auto;">
                @else
                    <img src="/images/player-avatar.svg"
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;margin:0 auto .5rem;display:block;">
                @endif

                <div style="font-weight:700;font-size:.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $player->name }}</div>
                <div style="font-size:.68rem;color:var(--mut);margin-top:.1rem;">{{ ucwords(str_replace('_',' ',$player->role ?? 'Player')) }}</div>

                @if($stat && ($stat->total_runs > 0 || $stat->total_wickets > 0))
                <div style="display:flex;justify-content:center;gap:.75rem;margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--bd);">
                    <div>
                        <div style="font-size:.78rem;font-weight:800;color:var(--p);">{{ $stat->total_runs }}</div>
                        <div style="font-size:.58rem;color:var(--mut);">Runs</div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:800;color:var(--g);">{{ $stat->total_wickets }}</div>
                        <div style="font-size:.58rem;color:var(--mut);">Wkts</div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:800;color:var(--mut);">{{ $stat->matches_played }}</div>
                        <div style="font-size:.58rem;color:var(--mut);">M</div>
                    </div>
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

@if($team->description)
<div class="sec" style="padding-top:0;">
    <div class="card" style="padding:1rem;">
        <div style="font-size:.75rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">About</div>
        <div style="font-size:.85rem;color:var(--txt);line-height:1.6;">{{ $team->description }}</div>
    </div>
</div>
@endif

@endsection
