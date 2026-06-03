@extends('layouts.app')
@section('title','Editions')

@section('content')
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,214,0,.12);color:var(--g);">
                <i class="bi bi-trophy-fill"></i>
            </div>
            All Editions
        </div>
        <span class="pill pill-muted">{{ $editions->total() }} total</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        @foreach($editions as $edition)
        <a href="{{ route('tournaments.show', $edition) }}" style="text-decoration:none;">
            <div style="background:linear-gradient(150deg,#0D2418,#091B0F);
                        border:1px solid {{ $edition->is_current ? 'rgba(27,138,78,.5)' : 'rgba(27,138,78,.22)' }};
                        border-radius:14px;overflow:hidden;
                        box-shadow:{{ $edition->is_current ? '0 4px 20px rgba(27,138,78,.25)' : '0 3px 14px rgba(0,0,0,.25)' }};
                        transition:transform .2s cubic-bezier(.34,1.56,.64,1),box-shadow .2s;"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,.35)'"
                 onmouseout="this.style.transform='none';this.style.boxShadow='{{ $edition->is_current ? '0 4px 20px rgba(27,138,78,.25)' : '0 3px 14px rgba(0,0,0,.25)' }}'">

                @if($edition->thumbnail)
                    <img src="{{ asset('storage/'.$edition->thumbnail) }}" style="width:100%;height:80px;object-fit:cover;display:block;">
                @else
                    <div style="width:100%;height:80px;background:{{ $edition->is_current ? 'linear-gradient(135deg,#134D2A,#0A2E18)' : 'linear-gradient(135deg,#1A3525,#0E2118)' }};display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.1rem;">
                        <div style="font-weight:700;font-size:1.8rem;line-height:1;color:{{ $edition->is_current ? '#4DEBA0' : 'rgba(255,255,255,.4)' }};{{ $edition->is_current ? 'text-shadow:0 0 16px rgba(77,235,160,.4);' : '' }}">{{ $edition->edition_number }}</div>
                        <div style="font-size:.55rem;color:{{ $edition->is_current ? 'rgba(255,255,255,.6)' : 'rgba(255,255,255,.3)' }};text-transform:uppercase;letter-spacing:.07em;">Edition</div>
                    </div>
                @endif

                <div style="padding:.5rem .625rem .7rem;">
                    <div style="font-size:.8rem;font-weight:700;display:flex;align-items:center;gap:.25rem;line-height:1.2;color:#fff;">
                        {{ \Str::limit($edition->name, 15) }}
                        @if($edition->is_current)
                            <span class="pill pill-red" style="font-size:.5rem;padding:.1em .4em;">Live</span>
                        @elseif($edition->status === 'completed')
                            <span class="pill pill-muted" style="font-size:.5rem;padding:.1em .4em;">Done</span>
                        @endif
                    </div>
                    <div style="font-size:.62rem;color:rgba(255,255,255,.45);margin-top:.25rem;">
                        {{ $edition->teams_count }} teams
                        @if($edition->matches_count) · {{ $edition->matches_count }} matches @endif
                    </div>
                    @if($edition->host_village)
                    <div style="font-size:.6rem;color:rgba(255,255,255,.4);margin-top:.1rem;">
                        <i class="bi bi-geo-alt"></i> {{ $edition->host_village }}
                    </div>
                    @endif
                    @if($edition->start_date)
                    <div style="font-size:.6rem;color:rgba(255,255,255,.4);margin-top:.1rem;">
                        {{ $edition->start_date->format('M Y') }}{{ $edition->end_date ? ' – '.$edition->end_date->format('M Y') : '' }}
                    </div>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($editions->hasPages())
    <div style="margin-top:1.25rem;">{{ $editions->links() }}</div>
    @endif
</div>
@endsection
