@extends('layouts.app')
@section('title','RCL Cabinet')

@section('content')
<div class="sec">
    <div style="text-align:center;margin-bottom:1.25rem;">
        <div class="grad" style="font-weight:800;font-size:1.25rem;letter-spacing:-.01em;">RCL Cabinet</div>
        <div style="font-size:.78rem;color:var(--mut);margin-top:.25rem;">Royal Champions League — Governing Body & Leadership</div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem;">
        @forelse($members as $member)
        <div style="background:linear-gradient(150deg,#0D2418,#091B0F);border:1px solid rgba(179,136,255,.2);border-radius:14px;padding:1rem .75rem;text-align:center;display:flex;flex-direction:column;align-items:center;gap:.45rem;box-shadow:0 3px 14px rgba(0,0,0,.25);transition:transform .2s cubic-bezier(.34,1.56,.64,1),box-shadow .2s;"
             onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,.35)'"
             onmouseout="this.style.transform='none';this.style.boxShadow='0 3px 14px rgba(0,0,0,.25)'">
            @if($member->photo)
                <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}"
                     style="width:78px;height:98px;border-radius:12px;object-fit:cover;border:2px solid rgba(179,136,255,.45);box-shadow:0 4px 12px rgba(0,0,0,.35);">
            @else
                <div style="width:78px;height:98px;border-radius:12px;background:linear-gradient(135deg,#7C3AED,var(--g));display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#fff;box-shadow:0 4px 14px rgba(124,58,237,.35);">
                    {{ strtoupper(substr($member->name,0,2)) }}
                </div>
            @endif
            <div style="font-weight:700;font-size:.88rem;line-height:1.2;color:#fff;">{{ $member->name }}</div>
            <div style="font-size:.68rem;color:rgba(206,147,216,.9);font-weight:700;text-transform:uppercase;letter-spacing:.04em;">{{ $member->role_title }}</div>
            @if($member->village)
            <div style="font-size:.68rem;color:rgba(77,235,160,.9);font-weight:600;"><i class="bi bi-geo-alt"></i> {{ $member->village }}</div>
            @endif
            @if($member->bio)
            <div style="font-size:.7rem;color:rgba(255,255,255,.5);line-height:1.4;text-align:center;">{{ Str::limit($member->bio, 80) }}</div>
            @endif
            @if($member->phone)
            <a href="tel:{{ $member->phone }}" style="font-size:.72rem;color:#4DEBA0;text-decoration:none;margin-top:.15rem;">
                <i class="bi bi-telephone-fill"></i> {{ $member->phone }}
            </a>
            @endif
        </div>
        @empty
        <div style="grid-column:1/-1;padding:3rem;text-align:center;color:var(--mut);">
            <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
            Cabinet information coming soon
        </div>
        @endforelse
    </div>
</div>
@endsection
