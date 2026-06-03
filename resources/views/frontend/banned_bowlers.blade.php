@extends('layouts.app')
@section('title','Banned Bowlers')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-ico" style="background:rgba(220,38,38,.1);color:var(--red);">
            <i class="bi bi-slash-circle-fill"></i>
        </div>
        <div>
            <div class="sec-title">Banned Bowlers</div>
            <div style="font-size:.72rem;color:var(--mut);">Bowling action bans &amp; restrictions</div>
        </div>
    </div>

    @if($bans->isEmpty())
    <div class="card" style="text-align:center;padding:2.5rem 1rem;">
        <div style="font-size:2rem;margin-bottom:.5rem;">✅</div>
        <div style="font-weight:700;font-size:.9rem;">No Active Bans</div>
        <div style="font-size:.75rem;color:var(--mut);margin-top:.25rem;">All bowlers are currently cleared to bowl.</div>
    </div>
    @else

    {{-- Active Bans --}}
    @php $active = $bans->where('is_active', true); @endphp
    @if($active->isNotEmpty())
    <div style="font-size:.7rem;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;padding:0 .25rem;">
        Active Bans ({{ $active->count() }})
    </div>
    @foreach($active as $ban)
    <div class="card" style="margin-bottom:.625rem;border-left:3px solid var(--red);">
        <div style="display:flex;align-items:center;gap:.875rem;padding:.875rem;">
            <div style="width:42px;height:42px;border-radius:50%;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--red);flex-shrink:0;">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:.9rem;">{{ $ban->player?->name }}</div>
                <div style="font-size:.75rem;color:var(--mut);margin-top:.125rem;">{{ $ban->reason }}</div>
                <div style="display:flex;gap:.625rem;margin-top:.375rem;flex-wrap:wrap;">
                    <span class="pill pill-red">
                        <i class="bi bi-calendar-x" style="margin-right:.2rem;"></i>
                        Since {{ $ban->banned_from->format('d M Y') }}
                    </span>
                    <span class="pill pill-muted">
                        Until: {{ $ban->banned_until?->format('d M Y') ?? 'Indefinite' }}
                    </span>
                </div>
            </div>
            <div style="flex-shrink:0;">
                <span style="font-size:.65rem;font-weight:700;background:rgba(220,38,38,.1);color:var(--red);border:1px solid rgba(220,38,38,.2);border-radius:20px;padding:.2rem .6rem;">BANNED</span>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Lifted Bans --}}
    @php $lifted = $bans->where('is_active', false); @endphp
    @if($lifted->isNotEmpty())
    <div style="font-size:.7rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin:.875rem 0 .5rem;padding:0 .25rem;">
        Lifted Bans ({{ $lifted->count() }})
    </div>
    @foreach($lifted as $ban)
    <div class="card" style="margin-bottom:.5rem;opacity:.7;">
        <div style="display:flex;align-items:center;gap:.875rem;padding:.75rem;">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--s2);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--mut);flex-shrink:0;">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:.85rem;text-decoration:line-through;color:var(--mut);">{{ $ban->player?->name }}</div>
                <div style="font-size:.72rem;color:var(--mut);">{{ $ban->reason }}</div>
                <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">
                    {{ $ban->banned_from->format('d M Y') }} — {{ $ban->banned_until?->format('d M Y') ?? 'Lifted' }}
                </div>
            </div>
            <span style="font-size:.65rem;font-weight:700;background:rgba(27,138,78,.1);color:var(--p);border:1px solid rgba(27,138,78,.2);border-radius:20px;padding:.2rem .6rem;">LIFTED</span>
        </div>
    </div>
    @endforeach
    @endif

    @endif
</div>
@endsection
