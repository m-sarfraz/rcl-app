@extends('layouts.app')
@section('title','Teams')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(0,230,118,.12);color:var(--p);">
                <i class="bi bi-shield-fill"></i>
            </div>
            Teams
        </div>
        @if($currentEdition)
            <span class="pill pill-green">{{ $currentEdition->edition_number }}th Ed.</span>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        @forelse($teams as $team)
        <a href="{{ route('team.show', $team) }}" style="text-decoration:none;">
            <div style="background:linear-gradient(150deg,#0D2418,#091B0F);border:1px solid rgba(27,138,78,.25);border-radius:14px;padding:1rem;text-align:center;
                        box-shadow:0 3px 14px rgba(0,0,0,.25);
                        transition:border-color .2s,transform .2s cubic-bezier(.34,1.56,.64,1),box-shadow .2s;"
                 onmouseover="this.style.borderColor='rgba(27,138,78,.6)';this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,.35)'"
                 onmouseout="this.style.borderColor='rgba(27,138,78,.25)';this.style.transform='none';this.style.boxShadow='0 3px 14px rgba(0,0,0,.25)'">
                @if($team->logo)
                    <img src="{{ asset('storage/'.$team->logo) }}"
                         style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(27,138,78,.4);margin-bottom:.625rem;display:block;margin-left:auto;margin-right:auto;box-shadow:0 4px 14px rgba(0,0,0,.3);">
                @else
                    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,{{ $team->primary_color ?? '#1B8A4E' }},{{ $team->secondary_color ?? '#D4900A' }});display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.1rem;margin:0 auto .625rem;box-shadow:0 4px 16px rgba(0,0,0,.35);">
                        {{ strtoupper(substr($team->short_code ?? $team->name, 0, 2)) }}
                    </div>
                @endif
                <div style="font-weight:700;font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:.2rem;color:#fff;">{{ $team->name }}</div>
                @if($team->village_name)
                    <div style="font-size:.7rem;color:rgba(255,255,255,.5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $team->village_name }}</div>
                @endif
                <div style="font-size:.65rem;color:#4DEBA0;margin-top:.25rem;font-weight:700;letter-spacing:.04em;">{{ $team->short_code }}</div>
            </div>
        </a>
        @empty
        <div style="grid-column:span 2;padding:3rem 1rem;text-align:center;color:var(--mut);">
            <i class="bi bi-shield" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
            No teams yet.
        </div>
        @endforelse
    </div>
</div>
@endsection
