@extends('layouts.app')
@section('title','Sponsors')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(212,144,10,.14);color:var(--g);">
                <i class="bi bi-award-fill" style="font-size:.8rem;"></i>
            </div>
            Our Sponsors
        </div>
        <span style="font-size:.72rem;color:var(--mut);">Proud partners of RCL</span>
    </div>

    @php
    $tierConfig = [
        'title'   => ['label' => 'Title Sponsor',   'color' => 'var(--g)',    'bg' => 'rgba(212,144,10,.12)', 'size' => '80px'],
        'gold'    => ['label' => 'Gold Sponsors',    'color' => 'var(--g)',    'bg' => 'rgba(212,144,10,.08)', 'size' => '64px'],
        'silver'  => ['label' => 'Silver Sponsors',  'color' => '#9CA3AF',    'bg' => 'rgba(156,163,175,.1)', 'size' => '56px'],
        'general' => ['label' => 'Our Supporters',   'color' => 'var(--p)',   'bg' => 'rgba(27,138,78,.08)',  'size' => '48px'],
    ];
    @endphp

    @foreach(['title','gold','silver','general'] as $tier)
    @if($sponsors->has($tier))
    @php $cfg = $tierConfig[$tier]; @endphp
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:{{ $cfg['color'] }};margin-bottom:.75rem;padding:0 .25rem;">
            {{ $cfg['label'] }}
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
            @foreach($sponsors[$tier] as $s)
            @php $wrapped = $s->website ? 'a' : 'div'; @endphp
            <{{ $wrapped }}
                @if($s->website) href="{{ $s->website }}" target="_blank" rel="noopener" @endif
                style="background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:1rem;display:flex;flex-direction:column;align-items:center;gap:.5rem;text-decoration:none;flex:1;min-width:120px;max-width:160px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
                @if($s->logo)
                    <img src="{{ asset('storage/'.$s->logo) }}" alt="{{ $s->name }}"
                         style="height:{{ $cfg['size'] }};max-width:120px;object-fit:contain;">
                @else
                    <div style="width:{{ $cfg['size'] }};height:{{ $cfg['size'] }};border-radius:12px;background:{{ $cfg['bg'] }};display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.4rem;color:{{ $cfg['color'] }};">
                        {{ strtoupper(substr($s->name,0,1)) }}
                    </div>
                @endif
                <div style="font-weight:700;font-size:.8rem;color:var(--txt);text-align:center;line-height:1.3;">{{ $s->name }}</div>
                @if($s->description)
                <div style="font-size:.65rem;color:var(--mut);text-align:center;">{{ Str::limit($s->description, 60) }}</div>
                @endif
            </{{ $wrapped }}>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    @if($sponsors->isEmpty())
    <div style="padding:3rem 1rem;text-align:center;color:var(--mut);">
        <i class="bi bi-award" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
        Sponsor information coming soon.
    </div>
    @endif
</div>
@endsection
