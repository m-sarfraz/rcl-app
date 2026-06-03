@extends('layouts.app')
@section('title','Home')

@push('styles')
<style>
/* ── Banner ──────────────────────────────────────────── */
#banner-wrap { position:relative; overflow:hidden; flex-shrink:0; height:210px; background:#0A2415; }
#banner-track { display:flex; height:100%; transition:transform .55s cubic-bezier(.4,0,.2,1); }
.banner-slide { min-width:100%; height:100%; flex-shrink:0; position:relative; }
.banner-slide img { width:100%; height:100%; object-fit:cover; display:block; }
.banner-slide-inner {
    width:100%; height:100%;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:.5rem; position:relative; overflow:hidden;
}
.banner-overlay {
    position:absolute; bottom:0; left:0; right:0;
    background:linear-gradient(0deg,rgba(6,14,8,.95) 0%,rgba(6,14,8,.5) 55%,transparent 100%);
    padding:.875rem 1rem .75rem;
}
/* Arrow buttons */
.banner-arrow {
    position:absolute; top:50%; transform:translateY(-50%);
    width:34px; height:34px; border-radius:50%;
    background:rgba(0,0,0,.45); border:1px solid rgba(255,255,255,.25);
    color:#fff; font-size:1rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background .2s, transform .2s;
    z-index:10; backdrop-filter:blur(4px);
}
.banner-arrow:hover { background:rgba(0,0,0,.7); transform:translateY(-50%) scale(1.08); }
#banner-prev { left:.625rem; }
#banner-next { right:.625rem; }
/* Dots */
.banner-dots { position:absolute; bottom:.5rem; left:0; right:0; display:flex; justify-content:center; gap:.4rem; z-index:10; }
.banner-dot { height:4px; border-radius:2px; border:none; cursor:pointer; padding:0; transition:all .35s; background:rgba(255,255,255,.35); }
.banner-dot.on { background:#fff; box-shadow:0 0 6px rgba(255,255,255,.7); }

/* ── Edition hero strip ─────────────────────────────── */
.hero-strip {
    display:flex; align-items:stretch; gap:0;
    background:linear-gradient(90deg, rgba(0,230,118,.12) 0%, rgba(255,214,0,.06) 100%);
    border-bottom:1px solid var(--bd);
}
.hero-brand {
    display:flex; align-items:center; gap:.5rem;
    padding:.75rem 1rem; border-right:1px solid var(--bd);
    flex-shrink:0;
}
.hero-stats { display:flex; flex:1; overflow-x:auto; }
.hero-stats::-webkit-scrollbar { display:none; }
.hero-stat { flex:1; min-width:60px; text-align:center; padding:.75rem .5rem; border-right:1px solid rgba(0,230,118,.1); }
.hero-stat:last-child { border-right:none; }
.hero-val { font-size:1.35rem; font-weight:700; line-height:1; }
.hero-lbl { font-size:.55rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut); margin-top:.2rem; }

/* ── Quick nav ──────────────────────────────────────── */
.qnav { display:grid; grid-template-columns:repeat(3,1fr); gap:.6rem; }
.qnav-btn {
    display:flex; flex-direction:column; align-items:center; gap:.38rem;
    background:#fff;
    border:1px solid rgba(27,138,78,.15); border-radius:14px;
    padding:.85rem .4rem; text-decoration:none; color:var(--txt);
    transition:border-color .25s, transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.qnav-btn:hover, .qnav-btn:active {
    border-color:rgba(27,138,78,.45);
    transform:translateY(-4px);
    box-shadow:0 10px 24px rgba(27,138,78,.14), 0 2px 6px rgba(0,0,0,.05);
    background:linear-gradient(145deg,#fff,#F4FAF7);
}
.qnav-ico {
    width:38px; height:38px; border-radius:11px;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
    box-shadow:0 2px 8px rgba(0,0,0,.07);
}
.qnav-lbl { font-size:.67rem; font-weight:700; letter-spacing:.04em; color:var(--txt); }

/* ── Team chip ──────────────────────────────────────── */
.team-chip {
    flex-shrink:0; width:76px; text-align:center;
    background:#fff;
    border:1px solid rgba(27,138,78,.15); border-radius:14px; padding:.65rem .35rem;
    text-decoration:none; color:var(--txt);
    transition:border-color .2s, transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
    display:block;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.team-chip:hover { border-color:rgba(27,138,78,.45); transform:translateY(-3px); box-shadow:0 8px 20px rgba(27,138,78,.13); }
.team-badge-circle {
    width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.68rem; color:#fff;
    margin:0 auto .3rem; box-shadow:0 3px 12px rgba(0,0,0,.2);
}

/* ── Edition card ───────────────────────────────────── */
.ed-card {
    background:#fff;
    border:1px solid rgba(27,138,78,.15); border-radius:14px; overflow:hidden;
    text-decoration:none; color:var(--txt); display:block;
    transition:border-color .25s, transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.ed-card:hover { border-color:rgba(27,138,78,.45); transform:translateY(-4px); box-shadow:0 12px 28px rgba(27,138,78,.14); }

/* ── VCC chip ───────────────────────────────────────── */
.vcc-chip {
    flex-shrink:0; width:100px;
    background:#fff;
    border:1px solid rgba(206,147,216,.25); border-radius:14px;
    padding:.75rem .5rem; text-align:center;
    transition:border-color .2s, transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
}
.vcc-chip:hover { border-color:rgba(206,147,216,.55); transform:translateY(-3px); box-shadow:0 8px 20px rgba(124,58,237,.1); }
.vcc-avatar {
    width:48px; height:48px; border-radius:50%;
    margin:0 auto .4rem; border:2px solid rgba(206,147,216,.4);
    display:flex; align-items:center; justify-content:center;
    font-size:.88rem; font-weight:700; color:#fff;
    background:linear-gradient(135deg,#7C3AED,var(--g));
    box-shadow:0 3px 12px rgba(124,58,237,.25);
}
.vcc-avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
</style>
@endpush

@section('content')

{{-- ══ BANNER SLIDER ════════════════════════════════════ --}}
@if($banners->count())
<div id="banner-wrap">
    <div id="banner-track">
        @foreach($banners as $banner)
        @php
            $grads = ['linear-gradient(135deg,#0A2415 0%,#134D2A 40%,#1B8A4E 70%,#0F3320 100%)','linear-gradient(135deg,#1A0A00 0%,#4D2800 40%,#D4900A 100%)','linear-gradient(135deg,#060D26 0%,#0F2060 50%,#1B4FD4 100%)'];
        @endphp
        <div class="banner-slide">
            @if($banner->link_url)<a href="{{ $banner->link_url }}" target="_blank" style="display:block;height:100%;">@endif

            @if($banner->image_path)
                <img src="{{ asset('storage/'.$banner->image_path) }}" alt="{{ $banner->title }}">
                @if($banner->title || $banner->subtitle)
                <div class="banner-overlay">
                    @if($banner->title)<div style="font-weight:700;font-size:1rem;text-shadow:0 2px 8px rgba(0,0,0,.9);color:#fff;">{{ $banner->title }}</div>@endif
                    @if($banner->subtitle)<div style="font-size:.75rem;color:rgba(255,255,255,.75);margin-top:.2rem;">{{ $banner->subtitle }}</div>@endif
                </div>
                @endif
            @else
                <div class="banner-slide-inner" style="background:{{ $grads[$loop->index % 3] }};">
                    <div style="position:absolute;width:180px;height:180px;border-radius:50%;border:1px solid rgba(255,255,255,.06);top:-40px;right:-30px;"></div>
                    <div style="position:absolute;font-size:4rem;opacity:.08;right:1rem;bottom:.5rem;line-height:1;">🏏</div>
                    <img src="/logo.jfif" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.5);box-shadow:0 0 18px rgba(255,255,255,.25);position:relative;z-index:1;">
                    <div style="text-align:center;padding:0 1.25rem;position:relative;z-index:1;">
                        <div style="font-weight:700;font-size:1rem;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,.6);">{{ $banner->title }}</div>
                        @if($banner->subtitle)<div style="font-size:.72rem;color:rgba(255,255,255,.7);margin-top:.25rem;">{{ $banner->subtitle }}</div>@endif
                    </div>
                </div>
            @endif

            @if($banner->link_url)</a>@endif
        </div>
        @endforeach
    </div>

    {{-- Arrows --}}
    @if($banners->count() > 1)
    <button class="banner-arrow" id="banner-prev"><i class="bi bi-chevron-left"></i></button>
    <button class="banner-arrow" id="banner-next"><i class="bi bi-chevron-right"></i></button>
    {{-- Dots --}}
    <div class="banner-dots" id="banner-dots">
        @foreach($banners as $banner)
        <button class="banner-dot {{ $loop->first ? 'on' : '' }}" data-idx="{{ $loop->index }}"
                style="width:{{ $loop->first ? '22px' : '6px' }};"></button>
        @endforeach
    </div>
    @endif
</div>
@else
{{-- ── Fallback hero banner when no banners uploaded ── --}}
<div style="position:relative;overflow:hidden;height:186px;flex-shrink:0;
            background:linear-gradient(135deg,#0A2415 0%,#134D2A 40%,#1B8A4E 70%,#0F3320 100%);">
    {{-- decorative circles --}}
    <div style="position:absolute;width:220px;height:220px;border-radius:50%;border:1px solid rgba(255,255,255,.06);top:-60px;right:-40px;"></div>
    <div style="position:absolute;width:140px;height:140px;border-radius:50%;border:1px solid rgba(255,255,255,.07);top:30px;right:30px;"></div>
    <div style="position:absolute;width:80px;height:80px;border-radius:50%;background:rgba(212,144,10,.08);bottom:-20px;left:20px;"></div>
    <div style="position:absolute;width:50px;height:50px;border-radius:50%;background:rgba(27,138,78,.15);top:20px;left:60px;"></div>
    {{-- cricket ball icon --}}
    <div style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:4.5rem;opacity:.08;line-height:1;">🏏</div>
    {{-- content --}}
    <div style="position:absolute;inset:0;padding:1.25rem 1rem;display:flex;flex-direction:column;justify-content:center;">
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.75rem;">
            <img src="/logo.jfif" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.5);box-shadow:0 0 14px rgba(255,255,255,.2);">
            <div>
                <div style="font-size:.55rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.12em;font-weight:700;">Village Cricket Council</div>
                <div style="font-size:.85rem;font-weight:700;color:#fff;line-height:1.2;">Royal Champions League</div>
            </div>
        </div>
        @if($currentEdition)
        <div style="font-size:1.55rem;font-weight:700;color:#fff;line-height:1.1;margin-bottom:.5rem;">
            {{ $currentEdition->name }}
        </div>
        <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap;">
            <span style="background:rgba(212,144,10,.25);border:1px solid rgba(212,144,10,.4);color:#F5C842;font-size:.65rem;font-weight:700;padding:.22em .75em;border-radius:20px;">
                🏆 Season {{ $currentEdition->edition_number }}
            </span>
            @if($currentEdition->host_village)
            <span style="color:rgba(255,255,255,.6);font-size:.68rem;">
                <i class="bi bi-geo-alt-fill" style="color:#4DEBA0;margin-right:.2rem;"></i>{{ $currentEdition->host_village }}
            </span>
            @endif
        </div>
        @else
        <div style="font-size:1.4rem;font-weight:700;color:#fff;line-height:1.2;margin-bottom:.4rem;">Pakistan's Premier<br>Village Cricket</div>
        <span style="background:rgba(77,235,160,.15);border:1px solid rgba(77,235,160,.3);color:#4DEBA0;font-size:.65rem;font-weight:700;padding:.22em .75em;border-radius:20px;">🏏 Season Starting Soon</span>
        @endif
    </div>
</div>
@endif

{{-- ══ EDITION HERO STRIP ══════════════════════════════ --}}
@if($currentEdition)
<div class="hero-strip">
    <div class="hero-brand">
        <img src="/logo.jfif" style="width:26px;height:26px;border-radius:50%;object-fit:cover;border:1.5px solid var(--p);box-shadow:0 0 10px rgba(0,230,118,.4);">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--p);">{{ $currentEdition->edition_number }}th Edition</div>
            <div style="font-size:.55rem;color:var(--mut);">Active Season</div>
        </div>
    </div>
    <div class="hero-stats">
        <div class="hero-stat">
            <div class="hero-val" style="color:var(--p);">{{ $currentEdition->teams_count }}</div>
            <div class="hero-lbl">Teams</div>
        </div>
        <div class="hero-stat">
            <div class="hero-val" style="color:var(--g);">{{ $currentEdition->matches_count }}</div>
            <div class="hero-lbl">Matches</div>
        </div>
        <div class="hero-stat">
            <div class="hero-val" style="color:var(--blue);">{{ $recentMatches->count() }}</div>
            <div class="hero-lbl">Results</div>
        </div>
        @if($currentEdition->host_village)
        <div class="hero-stat">
            <div class="hero-val" style="font-size:.8rem;color:var(--txt);">{{ $currentEdition->host_village }}</div>
            <div class="hero-lbl">Host</div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ══ LIVE MATCH ═══════════════════════════════════════ --}}
@if($liveMatches->count())
<div class="sec" style="padding-bottom:.25rem;">
    @foreach($liveMatches as $m)
    @php $inn = $m->innings->last(); @endphp
    <div class="match-card match-card-live card-hover" onclick="window.location='{{ route('scorecard',$m) }}'">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.55rem;">
            <span class="live-badge">Live Now</span>
            <span style="font-size:.65rem;color:var(--mut);">{{ $m->edition?->name }} · M{{ $m->match_number }}</span>
        </div>
        <div class="teams-row">
            <div style="flex:1;">
                <div class="team-name">{{ $m->homeTeam?->name }}</div>
                <div style="font-size:.65rem;color:var(--mut);">{{ $m->homeTeam?->short_code }}</div>
            </div>
            <div style="text-align:center;padding:0 .5rem;">
                @if($inn)
                <div class="team-score">{{ $inn->total_runs }}/{{ $inn->total_wickets }}</div>
                <div style="font-size:.63rem;color:var(--mut);">{{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov</div>
                @else<div class="vs-pill">VS</div>@endif
            </div>
            <div style="flex:1;text-align:right;">
                <div class="team-name" style="text-align:right;">{{ $m->awayTeam?->name }}</div>
                <div style="font-size:.65rem;color:var(--mut);text-align:right;">{{ $m->awayTeam?->short_code }}</div>
            </div>
        </div>
        @if($inn && $inn->innings_number===2 && $inn->target)
        <div style="font-size:.72rem;color:var(--g);text-align:center;margin-top:.35rem;font-weight:700;background:rgba(255,214,0,.06);border-radius:8px;padding:.3rem;">
            Target {{ $inn->target }} · Need {{ max(0,$inn->target-$inn->total_runs) }} off {{ ($m->overs_per_side*6)-$inn->total_balls }} balls
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ══ QUICK NAV GRID ══════════════════════════════════ --}}
<div class="sec">
    <div class="qnav">
        <a href="{{ route('teams') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(0,230,118,.14);">
                <i class="bi bi-shield-fill" style="color:var(--p);"></i>
            </div>
            <div class="qnav-lbl">Teams</div>
        </a>
        <a href="{{ route('schedule') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(255,214,0,.14);">
                <i class="bi bi-calendar3" style="color:var(--g);"></i>
            </div>
            <div class="qnav-lbl">Schedule</div>
        </a>
        <a href="{{ route('tournaments.index') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(255,171,0,.14);">
                <i class="bi bi-trophy-fill" style="color:var(--g2);"></i>
            </div>
            <div class="qnav-lbl">Editions</div>
        </a>
        <a href="{{ route('stats') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(64,196,255,.14);">
                <i class="bi bi-bar-chart-fill" style="color:var(--blue);"></i>
            </div>
            <div class="qnav-lbl">Stats</div>
        </a>
        <a href="{{ route('vcc') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(206,147,216,.14);">
                <i class="bi bi-person-badge-fill" style="color:var(--pur);"></i>
            </div>
            <div class="qnav-lbl">Cabinet</div>
        </a>
        <a href="{{ route('more') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(255,255,255,.06);">
                <i class="bi bi-grid-fill" style="color:var(--mut);"></i>
            </div>
            <div class="qnav-lbl">More</div>
        </a>
    </div>
</div>

<div class="divider"></div>

{{-- ══ TEAMS SCROLL ════════════════════════════════════ --}}
@if($teams->count())
<div class="sec" style="padding-bottom:.75rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(0,230,118,.14);color:var(--p);">
                <i class="bi bi-shield-fill" style="font-size:.8rem;"></i>
            </div>
            Teams
        </div>
        <a href="{{ route('teams') }}" class="sec-link">All {{ $teams->count() }} →</a>
    </div>
    <div style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.25rem;" class="hide-scroll">
        @foreach($teams as $team)
        <a href="{{ route('team.show',$team) }}" class="team-chip">
            @if($team->logo)
                <img src="{{ asset('storage/'.$team->logo) }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;margin:0 auto .3rem;display:block;border:2px solid var(--bd);">
            @else
                <div class="team-badge-circle" style="background:linear-gradient(135deg,{{ $team->primary_color ?? '#00e676' }},{{ $team->secondary_color ?? '#ffd600' }});">
                    {{ strtoupper(substr($team->short_code ?? $team->name,0,2)) }}
                </div>
            @endif
            <div style="font-size:.6rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--txt);">{{ $team->short_code ?? substr($team->name,0,4) }}</div>
            <div style="font-size:.53rem;color:var(--mut);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:.08rem;">{{ \Str::limit($team->village_name ?? '',7) }}</div>
        </a>
        @endforeach
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ UPCOMING FIXTURES ═══════════════════════════════ --}}
@if($upcomingMatches->count())
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,214,0,.14);color:var(--g);">
                <i class="bi bi-calendar3" style="font-size:.8rem;"></i>
            </div>
            Upcoming
        </div>
        <a href="{{ route('schedule') }}" class="sec-link">All →</a>
    </div>
    @foreach($upcomingMatches->take(3) as $m)
    <div class="match-card match-card-upcoming" style="margin-bottom:.6rem;">
        <div class="teams-row">
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.86rem;">{{ $m->homeTeam?->name }}</div>
                <div style="font-size:.62rem;color:var(--mut);">{{ $m->homeTeam?->short_code }}</div>
            </div>
            <div style="text-align:center;padding:0 .4rem;">
                <div class="vs-pill">{{ $m->overs_per_side }}ov</div>
                <div style="font-size:.58rem;color:var(--g);margin-top:.25rem;font-weight:700;">{{ $m->scheduled_at?->format('d M') }}</div>
            </div>
            <div style="flex:1;text-align:right;">
                <div style="font-weight:700;font-size:.86rem;">{{ $m->awayTeam?->name }}</div>
                <div style="font-size:.62rem;color:var(--mut);text-align:right;">{{ $m->awayTeam?->short_code }}</div>
            </div>
        </div>
        <div class="match-meta" style="margin-top:.35rem;">
            <span><i class="bi bi-calendar3" style="color:var(--g);"></i> {{ $m->scheduled_at?->format('D, h:i A') }}</span>
            @if($m->venue)<span><i class="bi bi-geo-alt"></i> {{ $m->venue }}</span>@endif
        </div>
    </div>
    @endforeach
</div>
<div class="divider"></div>
@endif

{{-- ══ RECENT RESULTS ═══════════════════════════════════ --}}
@if($recentMatches->count())
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,171,0,.14);color:var(--g2);">
                <i class="bi bi-trophy" style="font-size:.8rem;"></i>
            </div>
            Recent Results
        </div>
        <a href="{{ route('tournaments.index') }}" class="sec-link">All →</a>
    </div>
    @foreach($recentMatches->take(3) as $m)
    <div class="match-card card-hover" style="margin-bottom:.6rem;" onclick="window.location='{{ route('scorecard',$m) }}'">
        <div class="teams-row">
            <div style="flex:1;">
                <div style="font-weight:{{ $m->winner_id===$m->home_team_id?'700':'500' }};font-size:.86rem;color:{{ $m->winner_id===$m->home_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->homeTeam?->name }}</div>
            </div>
            <div style="font-size:.6rem;color:var(--mut);padding:0 .35rem;">vs</div>
            <div style="flex:1;text-align:right;">
                <div style="font-weight:{{ $m->winner_id===$m->away_team_id?'700':'500' }};font-size:.86rem;color:{{ $m->winner_id===$m->away_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->awayTeam?->name }}</div>
            </div>
        </div>
        <div style="text-align:center;margin-top:.35rem;font-size:.72rem;">
            @if($m->winner)
                <span style="color:var(--p);font-weight:700;"><i class="bi bi-award-fill"></i> {{ $m->winner->name }} won @if($m->result_margin) by {{ $m->result_margin }} {{ $m->result_type==='wickets'?'wkts':'runs' }} @endif</span>
            @elseif($m->result_type==='tie')
                <span style="color:var(--g);">Match Tied</span>
            @else<span style="color:var(--mut);">No Result</span>@endif
        </div>
    </div>
    @endforeach
</div>
<div class="divider"></div>
@endif

{{-- ══ EDITIONS GRID ════════════════════════════════════ --}}
@if($editions->count())
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,214,0,.14);color:var(--g);">
                <i class="bi bi-trophy-fill" style="font-size:.8rem;"></i>
            </div>
            Editions
        </div>
        <a href="{{ route('tournaments.index') }}" class="sec-link">All →</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;">
        @foreach($editions->take(4) as $edition)
        <a href="{{ route('tournaments.show',$edition) }}" class="ed-card">
            @if($edition->thumbnail)
                <img src="{{ asset('storage/'.$edition->thumbnail) }}" style="width:100%;height:72px;object-fit:cover;display:block;">
            @else
                <div style="width:100%;height:76px;background:{{ $edition->is_current?'linear-gradient(135deg,#0F3D20,#1B8A4E)':'linear-gradient(135deg,#1A3525,#2D5E40)' }};display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.1rem;position:relative;overflow:hidden;">
                    <div style="position:absolute;width:80px;height:80px;border-radius:50%;border:1px solid rgba(255,255,255,.08);top:-20px;right:-10px;"></div>
                    <div style="font-size:1.85rem;font-weight:700;line-height:1;color:{{ $edition->is_current?'#4DEBA0':'rgba(255,255,255,.75)' }};{{ $edition->is_current?'text-shadow:0 0 18px rgba(77,235,160,.5);':'' }}">{{ $edition->edition_number }}</div>
                    <div style="font-size:.52rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;">Edition</div>
                </div>
            @endif
            <div style="padding:.5rem .65rem .65rem;">
                <div style="font-size:.78rem;font-weight:700;display:flex;align-items:center;gap:.25rem;line-height:1.2;color:var(--txt);">
                    {{ \Str::limit($edition->name,15) }}
                    @if($edition->is_current)<span class="pill pill-red" style="font-size:.5rem;padding:.1em .4em;">Live</span>@endif
                </div>
                <div style="font-size:.62rem;color:var(--mut);margin-top:.2rem;">
                    {{ $edition->teams_count }} teams · {{ $edition->matches_count }} matches
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ FAN POLL ══════════════════════════════════════════ --}}
@if($activePoll)
<div class="sec">
    {{-- Poll header card --}}
    <div style="background:linear-gradient(135deg,rgba(255,214,0,.12),rgba(255,171,0,.06));border:1px solid var(--bd2);border-radius:14px;padding:.875rem 1rem;margin-bottom:.875rem;display:flex;align-items:center;gap:.75rem;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,214,0,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 16px rgba(255,214,0,.25);">
            <i class="bi bi-bar-chart-fill" style="color:var(--g);font-size:1rem;"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:.88rem;color:var(--g);">Fan Poll</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.05rem;">Cast your vote · results live</div>
        </div>
    </div>
    <div style="background:linear-gradient(150deg,#0C2E18,#071B0E);border:1px solid rgba(27,138,78,.35);border-radius:14px;padding:.95rem;box-shadow:0 4px 20px rgba(0,0,0,.15);">
        <div style="font-weight:700;font-size:.9rem;margin-bottom:.875rem;line-height:1.4;color:#fff;">{{ $activePoll->question }}</div>
        @php $tv = $activePoll->options->sum('votes_count'); @endphp
        @foreach($activePoll->options as $opt)
        @php $pct = $tv>0 ? round($opt->votes_count/$tv*100) : 0; @endphp
        <div class="poll-opt" data-poll="{{ $activePoll->id }}" data-opt="{{ $opt->id }}">
            <div class="poll-bar" style="width:{{ $pct }}%"></div>
            <div class="poll-label">
                <span>{{ $opt->option_text }}</span>
                <span style="font-weight:700;color:#4DEBA0;min-width:34px;text-align:right;">{{ $pct }}%</span>
            </div>
        </div>
        @endforeach
        <div style="font-size:.65rem;color:rgba(255,255,255,.55);margin-top:.5rem;display:flex;align-items:center;gap:.3rem;">
            <i class="bi bi-people"></i> {{ number_format($tv) }} votes cast
        </div>
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ VCC CABINET ══════════════════════════════════════ --}}
@if($vccMembers->count())
<div class="sec" style="padding-bottom:.75rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(206,147,216,.14);color:var(--pur);">
                <i class="bi bi-person-badge-fill" style="font-size:.8rem;"></i>
            </div>
            VCC Cabinet
        </div>
        <a href="{{ route('vcc') }}" class="sec-link">All →</a>
    </div>
    <div style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.25rem;" class="hide-scroll">
        @foreach($vccMembers as $member)
        <div class="vcc-chip">
            <div class="vcc-avatar">
                @if($member->photo)
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}">
                @else
                    <i class="bi bi-person-fill" style="font-size:1.1rem;color:rgba(255,255,255,.9);"></i>
                @endif
            </div>
            <div style="font-size:.68rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ \Str::limit($member->name,11) }}</div>
            <div style="font-size:.57rem;color:var(--pur);margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->role_title }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ MEETING / ANNOUNCEMENT NOTICE ═══════════════════ --}}
@if($meetingContent)
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(212,144,10,.14);color:var(--g);">
                <i class="bi bi-megaphone-fill" style="font-size:.8rem;"></i>
            </div>
            Notice Board
        </div>
    </div>
    <div style="background:#fff;border:1px solid var(--bd);border-radius:14px;padding:1rem 1.1rem;box-shadow:0 2px 12px rgba(0,0,0,.06);line-height:1.65;font-size:.88rem;color:var(--txt);">
        {!! $meetingContent !!}
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ EMPTY STATE ══════════════════════════════════════ --}}
@if(!$liveMatches->count()&&!$upcomingMatches->count()&&!$recentMatches->count()&&!$banners->count()&&!$teams->count())
<div style="padding:4rem 1rem;text-align:center;">
    <img src="/logo.jfif" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:1rem;box-shadow:var(--gp);">
    <div style="font-weight:700;font-size:1.1rem;margin-bottom:.5rem;">Season Starting Soon</div>
    <div style="color:var(--mut);font-size:.85rem;">The 35th Edition fixtures will be announced shortly.</div>
</div>
@endif

<div style="height:.75rem;"></div>
@endsection

@push('scripts')
<script>
$(function(){
    // ── Banner carousel
    var count = {{ $banners->count() }};
    var cur = 0, timer;
    function goBanner(idx) {
        cur = (idx + count) % count;
        $('#banner-track').css('transform', 'translateX(-'+(cur*100)+'%)');
        $('#banner-dots .banner-dot').each(function(i){
            $(this).toggleClass('on', i===cur).css('width', i===cur ? '22px' : '6px');
        });
    }
    function startAuto() { clearInterval(timer); timer = setInterval(function(){ goBanner(cur+1); }, 4800); }
    if (count > 1) {
        startAuto();
        $('#banner-prev').on('click', function(){ goBanner(cur-1); startAuto(); });
        $('#banner-next').on('click', function(){ goBanner(cur+1); startAuto(); });
        $('#banner-dots').on('click','.banner-dot',function(){ goBanner(parseInt($(this).data('idx'))); startAuto(); });
        var tx=0;
        document.getElementById('banner-wrap').addEventListener('touchstart',function(e){ tx=e.touches[0].clientX; },{passive:true});
        document.getElementById('banner-wrap').addEventListener('touchend',function(e){ var dx=e.changedTouches[0].clientX-tx; if(Math.abs(dx)>40){ goBanner(dx<0?cur+1:cur-1); startAuto(); } },{passive:true});
    }

    // ── Poll voting
    $('.poll-opt').on('click', function(){
        var pollId=$(this).data('poll'), optId=$(this).data('opt');
        var $opts=$(this).closest('div[style*="border-radius:14px"]').find('.poll-opt');
        $.post('/api/poll/vote',{poll_id:pollId,option_id:optId},function(r){
            if(r.success){
                var total=0;
                $.each(r.counts,function(k,v){ total+=v; });
                $opts.each(function(){
                    var id=$(this).data('opt'), pct=total>0?Math.round((r.counts[id]||0)/total*100):0;
                    $(this).find('.poll-bar').css('width',pct+'%');
                    $(this).find('.poll-label span:last').text(pct+'%');
                });
                showToast('Vote recorded!','ok');
            }
        }).fail(function(){ showToast('Already voted','err'); });
    });
});
</script>
@endpush
