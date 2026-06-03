@extends('layouts.app')
@section('title','Team Fines')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-ico" style="background:rgba(212,144,10,.1);color:var(--g);">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <div class="sec-title">Team Fines</div>
            <div style="font-size:.72rem;color:var(--mut);">Disciplinary fines by team</div>
        </div>
        @if($currentEdition)
        <div style="margin-left:auto;">
            <span class="pill pill-green">{{ $currentEdition->name }}</span>
        </div>
        @endif
    </div>

    @if($teamFines->isEmpty())
    <div class="card" style="text-align:center;padding:2.5rem 1rem;">
        <div style="font-size:2rem;margin-bottom:.5rem;">🏏</div>
        <div style="font-weight:700;font-size:.9rem;">No Fines Recorded</div>
        <div style="font-size:.75rem;color:var(--mut);margin-top:.25rem;">All teams are in good standing.</div>
    </div>
    @else

    {{-- Summary strip --}}
    @php
        $totalUnpaid   = $teamFines->flatten(1)->where('status', 'unpaid')->sum('amount');
        $totalFines    = $teamFines->flatten(1)->count();
    @endphp
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem;">
        <div class="card" style="padding:.75rem;text-align:center;">
            <div style="font-size:1.1rem;font-weight:700;color:var(--red);">{{ $totalFines }}</div>
            <div style="font-size:.7rem;color:var(--mut);">Total Fines</div>
        </div>
        <div class="card" style="padding:.75rem;text-align:center;">
            <div style="font-size:1.1rem;font-weight:700;color:var(--red);">PKR {{ number_format($totalUnpaid) }}</div>
            <div style="font-size:.7rem;color:var(--mut);">Total Unpaid</div>
        </div>
    </div>

    @foreach($teamFines as $teamName => $fines)
    @php
        $teamUnpaid = $fines->where('status','unpaid')->sum('amount');
        $teamTotal  = $fines->sum('amount');
    @endphp
    <div class="card" style="margin-bottom:.875rem;">
        {{-- Team header --}}
        <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem;border-bottom:1px solid var(--bd);background:var(--s2);border-radius:12px 12px 0 0;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--p);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;font-weight:700;flex-shrink:0;">
                {{ mb_substr($teamName, 0, 2) }}
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.9rem;">{{ $teamName }}</div>
                <div style="font-size:.7rem;color:var(--mut);">{{ $fines->count() }} fine{{ $fines->count() !== 1 ? 's' : '' }}</div>
            </div>
            <div style="text-align:right;">
                @if($teamUnpaid > 0)
                <div style="font-size:.8rem;font-weight:700;color:var(--red);">PKR {{ number_format($teamUnpaid) }}</div>
                <div style="font-size:.65rem;color:var(--mut);">unpaid</div>
                @else
                <span class="pill pill-green" style="font-size:.65rem;">Cleared</span>
                @endif
            </div>
        </div>

        {{-- Fine rows --}}
        @foreach($fines as $fine)
        <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.75rem .875rem;border-bottom:1px solid var(--bd);">
            <div style="flex-shrink:0;margin-top:.1rem;">
                @if($fine->status === 'unpaid')
                    <i class="bi bi-exclamation-circle-fill" style="color:var(--red);font-size:.85rem;"></i>
                @elseif($fine->status === 'paid')
                    <i class="bi bi-check-circle-fill" style="color:var(--p);font-size:.85rem;"></i>
                @else
                    <i class="bi bi-dash-circle-fill" style="color:var(--mut);font-size:.85rem;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.82rem;">{{ $fine->player?->name }}</div>
                <div style="font-size:.72rem;color:var(--mut);margin-top:.1rem;">{{ $fine->description }}</div>
                <div style="font-size:.68rem;color:var(--mut);margin-top:.25rem;">
                    {{ ucfirst(str_replace('_',' ',$fine->violation_type)) }}
                    @if($fine->due_date) · Due {{ $fine->due_date->format('d M Y') }} @endif
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-weight:700;font-size:.82rem;color:{{ $fine->status==='unpaid' ? 'var(--red)' : 'var(--mut)' }};">
                    PKR {{ number_format($fine->amount) }}
                </div>
                <span class="pill {{ $fine->status==='paid' ? 'pill-green' : ($fine->status==='waived' ? 'pill-muted' : 'pill-red') }}" style="font-size:.62rem;margin-top:.2rem;display:inline-block;">
                    {{ ucfirst($fine->status) }}
                </span>
            </div>
        </div>
        @endforeach

        {{-- Team total footer --}}
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem .875rem;background:var(--s2);border-radius:0 0 12px 12px;">
            <span style="font-size:.72rem;color:var(--mut);">Total issued</span>
            <span style="font-size:.8rem;font-weight:700;color:var(--txt);">PKR {{ number_format($teamTotal) }}</span>
        </div>
    </div>
    @endforeach

    @endif
</div>
@endsection
