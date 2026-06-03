@extends('layouts.app')
@section('title','Team Captains')

@push('styles')
<style>
.cap-card {
    background:#fff;
    border:1px solid var(--bd); border-radius:16px; overflow:hidden;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    margin-bottom:.875rem;
}
.cap-card-header {
    padding:.65rem 1rem;
    display:flex; align-items:center; gap:.625rem;
}
.cap-badge {
    font-size:.58rem; font-weight:800; padding:.18em .55em;
    border-radius:5px; flex-shrink:0; letter-spacing:.04em;
}
.cap-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.65rem 1rem; border-top:1px solid var(--bd);
}
.cap-avatar {
    width:44px; height:44px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.9rem; color:#fff;
}
</style>
@endpush

@section('content')
<div class="sec" style="padding-bottom:.5rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(212,144,10,.14);color:var(--g);">
                <i class="bi bi-star-fill" style="font-size:.8rem;"></i>
            </div>
            Team Captains
        </div>
        <span style="font-size:.72rem;color:var(--mut);">{{ $currentEdition?->name }}</span>
    </div>

    @forelse($teams as $team)
    @php $teamCaps = $captains->get($team->id, collect()); @endphp
    <div class="cap-card">
        {{-- Team header --}}
        <div class="cap-card-header" style="background:linear-gradient(135deg,{{ $team->primary_color ?? '#1B8A4E' }}22,{{ $team->secondary_color ?? '#D4900A' }}11);">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,{{ $team->primary_color ?? 'var(--p)' }},{{ $team->secondary_color ?? 'var(--g)' }});display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.7rem;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($team->short_code,0,2)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:.88rem;color:var(--txt);">{{ $team->name }}</div>
                <div style="font-size:.63rem;color:var(--mut);">{{ $team->village_name }}</div>
            </div>
            <a href="{{ route('team.show', $team->id) }}" style="font-size:.65rem;color:var(--p);font-weight:700;text-decoration:none;flex-shrink:0;">
                Squad <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if($teamCaps->isEmpty())
            <div class="cap-row" style="color:var(--mut);font-size:.8rem;font-style:italic;">
                <i class="bi bi-dash-circle" style="color:var(--mut);"></i> Captain not assigned
            </div>
        @else
            @foreach($teamCaps as $cap)
            <div class="cap-row">
                <div class="cap-avatar" style="background:transparent;padding:0;">
                    @if($cap->photo)
                        <img src="{{ asset('storage/'.$cap->photo) }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;display:block;">
                    @else
                        <img src="/images/player-avatar.svg" style="width:44px;height:44px;border-radius:50%;display:block;">
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:.88rem;color:var(--txt);">{{ $cap->player_name }}</div>
                    <div style="font-size:.65rem;color:var(--mut);">{{ ucwords(str_replace('_',' ',$cap->role ?? '')) }}</div>
                </div>
                @if($cap->is_captain)
                    <span class="cap-badge" style="background:var(--g);color:#fff;">CAPTAIN</span>
                @else
                    <span class="cap-badge" style="background:var(--p);color:#fff;">VICE-C</span>
                @endif
            </div>
            @endforeach
        @endif
    </div>
    @empty
        <div style="padding:3rem 1rem;text-align:center;color:var(--mut);">No teams found.</div>
    @endforelse
</div>
@endsection
