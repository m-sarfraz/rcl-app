@extends('layouts.app')
@section('title','Home')

@push('styles')
<style>
/* ── Banner ─────────────────────────────────────────── */
#banner-wrap { position:relative; overflow:hidden; flex-shrink:0; height:210px; background:#030907; }
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
    background:linear-gradient(0deg,rgba(3,9,7,.97) 0%,rgba(3,9,7,.55) 55%,transparent 100%);
    padding:.875rem 1rem .75rem;
}
.banner-arrow {
    position:absolute; top:50%; transform:translateY(-50%);
    width:34px; height:34px; border-radius:50%;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.2);
    color:#fff; font-size:1rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background .2s; z-index:10; backdrop-filter:blur(8px);
}
.banner-arrow:hover { background:rgba(0,230,118,.2); border-color:rgba(0,230,118,.4); }
#banner-prev { left:.625rem; }
#banner-next { right:.625rem; }
.banner-dots { position:absolute; bottom:.625rem; left:0; right:0; display:flex; justify-content:center; gap:.4rem; z-index:10; }
.banner-dot { height:4px; border-radius:2px; border:none; cursor:pointer; padding:0; transition:all .35s; background:rgba(255,255,255,.25); }
.banner-dot.on { background:var(--p); box-shadow:0 0 8px rgba(0,230,118,.6); }

/* ── Hero stats strip ───────────────────────────────── */
.hero-strip {
    display:flex; align-items:stretch; gap:0;
    background:rgba(0,230,118,.05);
    border-bottom:1px solid rgba(255,255,255,.06);
}
.hero-brand {
    display:flex; align-items:center; gap:.5rem;
    padding:.75rem 1rem; border-right:1px solid rgba(255,255,255,.06); flex-shrink:0;
}
.hero-stats { display:flex; flex:1; overflow-x:auto; }
.hero-stats::-webkit-scrollbar { display:none; }
.hero-stat { flex:1; min-width:58px; text-align:center; padding:.75rem .5rem; border-right:1px solid rgba(255,255,255,.05); }
.hero-stat:last-child { border-right:none; }
.hero-val { font-size:1.3rem; font-weight:900; line-height:1; }
.hero-lbl { font-size:.52rem; text-transform:uppercase; letter-spacing:.08em; color:var(--mut); margin-top:.22rem; }

/* ── Quick nav tiles ────────────────────────────────── */
.qnav { display:grid; grid-template-columns:repeat(3,1fr); gap:.55rem; }
.qnav-btn {
    display:flex; flex-direction:column; align-items:center; gap:.375rem;
    background:#fff;
    border:1px solid var(--bd); border-radius:var(--r);
    padding:.9rem .4rem; text-decoration:none; color:var(--txt);
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}
.qnav-btn:hover {
    border-color:rgba(14,169,84,.35);
    background:linear-gradient(145deg,#fff,#f4faf6);
    transform:translateY(-4px);
    box-shadow:0 12px 28px rgba(14,169,84,.12);
}
.qnav-ico {
    width:40px; height:40px; border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.qnav-lbl { font-size:.66rem; font-weight:700; letter-spacing:.04em; color:var(--txt); }

/* ── Team chips ─────────────────────────────────────── */
.team-chip {
    flex-shrink:0; width:76px; text-align:center;
    background:#fff; border:1px solid var(--bd);
    border-radius:var(--r); padding:.625rem .35rem; text-decoration:none;
    color:var(--txt); transition:all .22s cubic-bezier(.34,1.56,.64,1); display:block;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
}
.team-chip:hover {
    border-color:rgba(14,169,84,.35); background:var(--s2);
    transform:translateY(-3px); box-shadow:0 8px 20px rgba(14,169,84,.12);
}
.team-badge-circle {
    width:40px; height:40px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.65rem; color:#000;
    margin:0 auto .3rem; box-shadow:0 4px 14px rgba(0,0,0,.4);
}

/* ── Edition cards ──────────────────────────────────── */
.ed-card {
    background:#fff; border:1px solid var(--bd);
    border-radius:var(--r); overflow:hidden; text-decoration:none; color:var(--txt);
    display:block; transition:all .22s cubic-bezier(.34,1.56,.64,1);
    box-shadow:0 2px 10px rgba(0,0,0,.06);
}
.ed-card:hover {
    border-color:rgba(14,169,84,.35);
    transform:translateY(-4px);
    box-shadow:0 14px 32px rgba(14,169,84,.14);
}

/* ── VCC chips ──────────────────────────────────────── */
.vcc-chip {
    flex-shrink:0; width:96px;
    background:#fff; border:1px solid rgba(124,58,237,.15);
    border-radius:var(--r); padding:.75rem .5rem; text-align:center;
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
}
.vcc-chip:hover { border-color:rgba(206,147,216,.3); transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.4); }
.vcc-avatar {
    width:48px; height:48px; border-radius:50%;
    margin:0 auto .4rem; border:2px solid rgba(206,147,216,.3);
    display:flex; align-items:center; justify-content:center;
    font-size:.88rem; font-weight:700; color:#fff;
    background:linear-gradient(135deg,#7C3AED,rgba(255,202,40,.8));
    box-shadow:0 4px 16px rgba(124,58,237,.3);
    overflow:hidden;
}
.vcc-avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
</style>
@endpush

@section('content')

{{-- ══ BANNER SLIDER ══════════════════════════════════ --}}
@if($banners->count())
<div id="banner-wrap">
    <div id="banner-track">
        @foreach($banners as $banner)
        <div class="banner-slide">
            @if($banner->link_url)<a href="{{ $banner->link_url }}" target="_blank" style="display:block;height:100%;">@endif
            @if($banner->image_path)
                <img src="{{ asset('storage/'.$banner->image_path) }}" alt="{{ $banner->title }}">
                @if($banner->title || $banner->subtitle)
                <div class="banner-overlay">
                    @if($banner->title)<div style="font-weight:800;font-size:1rem;color:#fff;text-shadow:0 2px 10px rgba(0,0,0,.9);">{{ $banner->title }}</div>@endif
                    @if($banner->subtitle)<div style="font-size:.75rem;color:rgba(255,255,255,.7);margin-top:.2rem;">{{ $banner->subtitle }}</div>@endif
                </div>
                @endif
            @else
                @php $grads=['linear-gradient(135deg,#030c06 0%,#0a2414 50%,#030c06 100%)','linear-gradient(135deg,#0a0800 0%,#2a1800 50%,#0a0800 100%)','linear-gradient(135deg,#030a1a 0%,#081840 50%,#030a1a 100%)']; @endphp
                <div class="banner-slide-inner" style="background:{{ $grads[$loop->index%3] }};">
                    <div style="position:absolute;width:200px;height:200px;border-radius:50%;border:1px solid rgba(0,230,118,.08);top:-50px;right:-30px;"></div>
                    <div style="position:absolute;font-size:5rem;opacity:.06;right:1rem;bottom:.5rem;line-height:1;">🏏</div>
                    <img src="/logo.jfif" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,230,118,.5);box-shadow:0 0 24px rgba(0,230,118,.3);position:relative;z-index:1;">
                    <div style="text-align:center;padding:0 1.25rem;position:relative;z-index:1;">
                        <div style="font-weight:800;font-size:1rem;color:#fff;text-shadow:0 2px 10px rgba(0,0,0,.7);">{{ $banner->title }}</div>
                        @if($banner->subtitle)<div style="font-size:.72rem;color:rgba(255,255,255,.6);margin-top:.25rem;">{{ $banner->subtitle }}</div>@endif
                    </div>
                </div>
            @endif
            @if($banner->link_url)</a>@endif
        </div>
        @endforeach
    </div>
    @if($banners->count() > 1)
    <button class="banner-arrow" id="banner-prev"><i class="bi bi-chevron-left"></i></button>
    <button class="banner-arrow" id="banner-next"><i class="bi bi-chevron-right"></i></button>
    <div class="banner-dots" id="banner-dots">
        @foreach($banners as $b)
        <button class="banner-dot {{ $loop->first ? 'on' : '' }}" data-idx="{{ $loop->index }}"
                style="width:{{ $loop->first ? '22px' : '6px' }};"></button>
        @endforeach
    </div>
    @endif
</div>

@else
{{-- Fallback hero --}}
<div style="position:relative;overflow:hidden;height:200px;flex-shrink:0;
            background:linear-gradient(135deg,#030c06 0%,#071408 35%,#0d2818 65%,#030c06 100%);">
    <div style="position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(0,230,118,.1) 0%,transparent 65%);top:-80px;right:-60px;filter:blur(30px);"></div>
    <div style="position:absolute;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(0,178,255,.07) 0%,transparent 65%);bottom:-40px;left:-20px;filter:blur(25px);"></div>
    <div style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:5rem;opacity:.06;line-height:1;">🏏</div>
    <div style="position:absolute;inset:0;padding:1.25rem 1rem;display:flex;flex-direction:column;justify-content:center;">
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.875rem;">
            <img src="/logo.jfif" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,230,118,.5);box-shadow:0 0 16px rgba(0,230,118,.25);">
            <div>
                <div style="font-size:.5rem;color:rgba(223,240,229,.45);text-transform:uppercase;letter-spacing:.12em;font-weight:700;">Village Cricket Council</div>
                <div style="font-size:.85rem;font-weight:800;color:#fff;line-height:1.2;">Royal Champions League</div>
            </div>
        </div>
        @if($currentEdition)
        <div style="font-size:1.65rem;font-weight:900;line-height:1.1;margin-bottom:.5rem;
                    background:linear-gradient(135deg,#fff,rgba(255,255,255,.7));
                    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            {{ $currentEdition->name }}
        </div>
        <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap;">
            <span style="background:rgba(255,202,40,.12);border:1px solid rgba(255,202,40,.3);color:var(--g);font-size:.65rem;font-weight:700;padding:.22em .75em;border-radius:99px;">
                🏆 Season {{ $currentEdition->edition_number }}
            </span>
            @if($currentEdition->host_village)
            <span style="color:rgba(223,240,229,.55);font-size:.68rem;">
                <i class="bi bi-geo-alt-fill" style="color:var(--p);margin-right:.2rem;"></i>{{ $currentEdition->host_village }}
            </span>
            @endif
        </div>
        @else
        <div style="font-size:1.5rem;font-weight:900;color:#fff;line-height:1.2;margin-bottom:.5rem;">Pakistan's Premier<br>Village Cricket</div>
        <span style="background:rgba(0,230,118,.1);border:1px solid rgba(0,230,118,.25);color:var(--p);font-size:.65rem;font-weight:700;padding:.22em .75em;border-radius:99px;">🏏 Season Starting Soon</span>
        @endif
    </div>
</div>
@endif

{{-- ══ EDITION HERO STRIP ═════════════════════════════ --}}
@if($currentEdition)
<div class="hero-strip">
    <div class="hero-brand">
        <img src="/logo.jfif" style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1.5px solid rgba(0,230,118,.5);box-shadow:0 0 10px rgba(0,230,118,.3);">
        <div>
            <div style="font-size:.7rem;font-weight:800;color:var(--p);letter-spacing:.01em;">{{ $currentEdition->edition_number }}th Ed.</div>
            <div style="font-size:.5rem;color:var(--mut);">Active</div>
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
            <div class="hero-val" style="font-size:.75rem;color:var(--txt);">{{ $currentEdition->host_village }}</div>
            <div class="hero-lbl">Host</div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ══ LIVE MATCH ══════════════════════════════════════ --}}
@if($liveMatches->count())
<div class="sec" style="padding-bottom:.25rem;">
    @foreach($liveMatches as $m)
    @php $inn = $m->innings->last(); @endphp
    <div class="match-card match-card-live card-hover" onclick="window.location='{{ route('scorecard',$m) }}'">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem;">
            <span class="live-badge">Live Now</span>
            <span style="font-size:.65rem;color:var(--mut);">{{ $m->edition?->name }} · M{{ $m->match_number }}</span>
        </div>
        <div class="teams-row">
            <div style="flex:1;">
                <div class="team-name">{{ $m->homeTeam?->name }}</div>
                <div style="font-size:.63rem;color:var(--mut);margin-top:.1rem;">{{ $m->homeTeam?->short_code }}</div>
            </div>
            <div style="text-align:center;padding:0 .5rem;">
                @if($inn)
                <div class="team-score">{{ $inn->total_runs }}/{{ $inn->total_wickets }}</div>
                <div style="font-size:.6rem;color:var(--mut);">{{ floor($inn->total_balls/6) }}.{{ $inn->total_balls%6 }} ov</div>
                @else<div class="vs-pill">VS</div>@endif
            </div>
            <div style="flex:1;text-align:right;">
                <div class="team-name" style="text-align:right;">{{ $m->awayTeam?->name }}</div>
                <div style="font-size:.63rem;color:var(--mut);text-align:right;margin-top:.1rem;">{{ $m->awayTeam?->short_code }}</div>
            </div>
        </div>
        @if($inn && $inn->innings_number===2 && $inn->target)
        <div style="font-size:.72rem;color:var(--g);text-align:center;margin-top:.5rem;font-weight:700;
                    background:rgba(255,202,40,.07);border:1px solid rgba(255,202,40,.15);border-radius:10px;padding:.35rem;">
            Target {{ $inn->target }} · Need {{ max(0,$inn->target-$inn->total_runs) }} off {{ ($m->overs_per_side*6)-$inn->total_balls }} balls
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ══ QUICK NAV ═══════════════════════════════════════ --}}
<div class="sec">
    <div class="qnav">
        <a href="{{ route('teams') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(0,230,118,.1);">
                <i class="bi bi-shield-fill" style="color:var(--p);"></i>
            </div>
            <div class="qnav-lbl">Teams</div>
        </a>
        <a href="{{ route('schedule') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(255,202,40,.1);">
                <i class="bi bi-calendar3" style="color:var(--g);"></i>
            </div>
            <div class="qnav-lbl">Schedule</div>
        </a>
        <a href="{{ route('tournaments.index') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(255,152,0,.1);">
                <i class="bi bi-trophy-fill" style="color:var(--g2);"></i>
            </div>
            <div class="qnav-lbl">Editions</div>
        </a>
        <a href="{{ route('stats') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(64,196,255,.1);">
                <i class="bi bi-bar-chart-fill" style="color:var(--blue);"></i>
            </div>
            <div class="qnav-lbl">Stats</div>
        </a>
        <a href="{{ route('vcc') }}" class="qnav-btn">
            <div class="qnav-ico" style="background:rgba(206,147,216,.1);">
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

{{-- ══ TEAMS ═══════════════════════════════════════════ --}}
@if($teams->count())
<div class="sec" style="padding-bottom:.75rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(0,230,118,.1);color:var(--p);"><i class="bi bi-shield-fill" style="font-size:.82rem;"></i></div>
            Teams
        </div>
        <a href="{{ route('teams') }}" class="sec-link">All {{ $teams->count() }} →</a>
    </div>
    <div style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.25rem;" class="hide-scroll">
        @foreach($teams as $team)
        <a href="{{ route('team.show',$team) }}" class="team-chip">
            @if($team->logo)
                <img src="{{ asset('storage/'.$team->logo) }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;margin:0 auto .3rem;display:block;border:1.5px solid rgba(0,230,118,.2);">
            @else
                <div class="team-badge-circle" style="background:linear-gradient(135deg,{{ $team->primary_color ?? '#00e676' }},{{ $team->secondary_color ?? '#ffca28' }});">
                    {{ strtoupper(substr($team->short_code ?? $team->name,0,2)) }}
                </div>
            @endif
            <div style="font-size:.6rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--txt);">{{ $team->short_code ?? substr($team->name,0,4) }}</div>
            <div style="font-size:.52rem;color:var(--mut);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:.06rem;">{{ \Str::limit($team->village_name ?? '',7) }}</div>
        </a>
        @endforeach
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ UPCOMING FIXTURES ══════════════════════════════ --}}
@if($upcomingMatches->count())
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,202,40,.1);color:var(--g);"><i class="bi bi-calendar3" style="font-size:.82rem;"></i></div>
            Upcoming
        </div>
        <a href="{{ route('schedule') }}" class="sec-link">All →</a>
    </div>
    @foreach($upcomingMatches->take(3) as $m)
    <div class="match-card match-card-upcoming" style="margin-bottom:.55rem;">
        <div class="teams-row">
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.86rem;color:var(--txt);">{{ $m->homeTeam?->name }}</div>
                <div style="font-size:.62rem;color:var(--mut);margin-top:.1rem;">{{ $m->homeTeam?->short_code }}</div>
            </div>
            <div style="text-align:center;padding:0 .5rem;">
                <div class="vs-pill">{{ $m->overs_per_side }}ov</div>
                <div style="font-size:.58rem;color:var(--g);margin-top:.25rem;font-weight:700;">{{ $m->scheduled_at?->format('d M') }}</div>
            </div>
            <div style="flex:1;text-align:right;">
                <div style="font-weight:700;font-size:.86rem;color:var(--txt);">{{ $m->awayTeam?->name }}</div>
                <div style="font-size:.62rem;color:var(--mut);text-align:right;margin-top:.1rem;">{{ $m->awayTeam?->short_code }}</div>
            </div>
        </div>
        <div class="match-meta" style="margin-top:.4rem;">
            <span><i class="bi bi-calendar3" style="color:var(--g);"></i> {{ $m->scheduled_at?->format('D, h:i A') }}</span>
            @if($m->venue)<span><i class="bi bi-geo-alt"></i> {{ $m->venue }}</span>@endif
        </div>
    </div>
    @endforeach
</div>
<div class="divider"></div>
@endif

{{-- ══ RECENT RESULTS ══════════════════════════════════ --}}
@if($recentMatches->count())
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,152,0,.1);color:var(--g2);"><i class="bi bi-trophy" style="font-size:.82rem;"></i></div>
            Recent Results
        </div>
        <a href="{{ route('tournaments.index') }}" class="sec-link">All →</a>
    </div>
    @foreach($recentMatches->take(3) as $m)
    <div class="match-card card-hover" style="margin-bottom:.55rem;" onclick="window.location='{{ route('scorecard',$m) }}'">
        <div class="teams-row">
            <div style="flex:1;">
                <div style="font-weight:{{ $m->winner_id===$m->home_team_id?'800':'500' }};font-size:.86rem;color:{{ $m->winner_id===$m->home_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->homeTeam?->name }}</div>
            </div>
            <div style="font-size:.6rem;color:var(--mut);padding:0 .4rem;">vs</div>
            <div style="flex:1;text-align:right;">
                <div style="font-weight:{{ $m->winner_id===$m->away_team_id?'800':'500' }};font-size:.86rem;color:{{ $m->winner_id===$m->away_team_id?'var(--txt)':'var(--mut)' }};">{{ $m->awayTeam?->name }}</div>
            </div>
        </div>
        <div style="text-align:center;margin-top:.4rem;font-size:.72rem;">
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
            <div class="sec-ico" style="background:rgba(255,202,40,.1);color:var(--g);"><i class="bi bi-trophy-fill" style="font-size:.82rem;"></i></div>
            Editions
        </div>
        <a href="{{ route('tournaments.index') }}" class="sec-link">All →</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        @foreach($editions->take(4) as $edition)
        <a href="{{ route('tournaments.show',$edition) }}" class="ed-card">
            @if($edition->thumbnail)
                <img src="{{ asset('storage/'.$edition->thumbnail) }}" style="width:100%;height:72px;object-fit:cover;display:block;">
            @else
                <div style="width:100%;height:76px;
                            background:{{ $edition->is_current?'linear-gradient(135deg,#071408,#0d2416)':'linear-gradient(135deg,#060e09,#0a1810)' }};
                            display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.1rem;position:relative;overflow:hidden;">
                    <div style="position:absolute;width:70px;height:70px;border-radius:50%;border:1px solid rgba(255,255,255,.06);top:-15px;right:-10px;"></div>
                    <div style="font-size:2rem;font-weight:900;line-height:1;color:{{ $edition->is_current?'var(--p)':'rgba(255,255,255,.5)' }};{{ $edition->is_current?'text-shadow:0 0 20px rgba(0,230,118,.5);':'' }}">{{ $edition->edition_number }}</div>
                    <div style="font-size:.48rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;">Edition</div>
                </div>
            @endif
            <div style="padding:.5rem .65rem .625rem;">
                <div style="font-size:.78rem;font-weight:700;display:flex;align-items:center;gap:.3rem;line-height:1.25;color:var(--txt);">
                    {{ \Str::limit($edition->name,15) }}
                    @if($edition->is_current)<span class="pill pill-red" style="font-size:.48rem;padding:.1em .45em;">Live</span>@endif
                </div>
                <div style="font-size:.6rem;color:var(--mut);margin-top:.2rem;">
                    {{ $edition->teams_count }} teams · {{ $edition->matches_count }} matches
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ FAN POLL ════════════════════════════════════════ --}}
@if($activePoll)
<div class="sec">
    <div style="background:linear-gradient(135deg,rgba(255,202,40,.08),rgba(255,152,0,.04));border:1px solid rgba(255,202,40,.15);border-radius:var(--r);padding:.875rem 1rem;margin-bottom:.875rem;display:flex;align-items:center;gap:.75rem;">
        <div style="width:38px;height:38px;border-radius:12px;background:rgba(255,202,40,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 18px rgba(255,202,40,.2);">
            <i class="bi bi-bar-chart-fill" style="color:var(--g);font-size:1rem;"></i>
        </div>
        <div>
            <div style="font-weight:800;font-size:.88rem;color:var(--g);">Fan Poll</div>
            <div style="font-size:.63rem;color:var(--mut);margin-top:.05rem;">Cast your vote · results live</div>
        </div>
    </div>
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r);padding:.95rem 1rem;">
        <div style="font-weight:700;font-size:.9rem;margin-bottom:.875rem;line-height:1.45;color:var(--txt);">{{ $activePoll->question }}</div>
        @php $tv = $activePoll->options->sum('votes_count'); @endphp
        @foreach($activePoll->options as $opt)
        @php $pct = $tv>0 ? round($opt->votes_count/$tv*100) : 0; @endphp
        <div class="poll-opt" data-poll="{{ $activePoll->id }}" data-opt="{{ $opt->id }}">
            <div class="poll-bar" style="width:{{ $pct }}%"></div>
            <div class="poll-label">
                <span>{{ $opt->option_text }}</span>
                <span style="font-weight:800;color:var(--p);min-width:34px;text-align:right;">{{ $pct }}%</span>
            </div>
        </div>
        @endforeach
        <div style="font-size:.63rem;color:var(--mut);margin-top:.5rem;display:flex;align-items:center;gap:.35rem;">
            <i class="bi bi-people"></i> {{ number_format($tv) }} votes cast
        </div>
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ VCC CABINET ═════════════════════════════════════ --}}
@if($vccMembers->count())
<div class="sec" style="padding-bottom:.75rem;">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(206,147,216,.1);color:var(--pur);"><i class="bi bi-person-badge-fill" style="font-size:.82rem;"></i></div>
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
                    <i class="bi bi-person-fill" style="font-size:1.2rem;color:rgba(255,255,255,.8);"></i>
                @endif
            </div>
            <div style="font-size:.66rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--txt);">{{ \Str::limit($member->name,11) }}</div>
            <div style="font-size:.56rem;color:var(--pur);margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->role_title }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ MEETING NOTICE ══════════════════════════════════ --}}
@if($meetingContent)
<div class="divider"></div>
<div class="sec">
    <div class="sec-hd">
        <div class="sec-title">
            <div class="sec-ico" style="background:rgba(255,202,40,.1);color:var(--g);"><i class="bi bi-megaphone-fill" style="font-size:.82rem;"></i></div>
            Notice Board
        </div>
    </div>
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r);padding:1rem 1.1rem;line-height:1.65;font-size:.88rem;color:var(--txt);">
        {!! $meetingContent !!}
    </div>
</div>
<div class="divider"></div>
@endif

{{-- ══ EMPTY STATE ════════════════════════════════════ --}}
@if(!$liveMatches->count()&&!$upcomingMatches->count()&&!$recentMatches->count()&&!$banners->count()&&!$teams->count())
<div style="padding:4rem 1rem;text-align:center;">
    <img src="/logo.jfif" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:1rem;border:2px solid rgba(0,230,118,.4);box-shadow:0 0 30px rgba(0,230,118,.2);">
    <div style="font-weight:800;font-size:1.1rem;margin-bottom:.5rem;color:var(--txt);">Season Starting Soon</div>
    <div style="color:var(--mut);font-size:.85rem;">Fixtures will be announced shortly.</div>
</div>
@endif

<div style="height:.75rem;"></div>
@endsection

@push('scripts')
<script>
$(function(){
    var count = {{ $banners->count() }};
    var cur = 0, timer;
    function goBanner(idx) {
        cur = (idx + count) % count;
        $('#banner-track').css('transform','translateX(-'+(cur*100)+'%)');
        $('#banner-dots .banner-dot').each(function(i){
            $(this).toggleClass('on',i===cur).css('width',i===cur?'22px':'6px');
        });
    }
    function startAuto(){ clearInterval(timer); timer=setInterval(function(){ goBanner(cur+1); },4800); }
    if (count > 1) {
        startAuto();
        $('#banner-prev').on('click',function(){ goBanner(cur-1); startAuto(); });
        $('#banner-next').on('click',function(){ goBanner(cur+1); startAuto(); });
        $('#banner-dots').on('click','.banner-dot',function(){ goBanner(parseInt($(this).data('idx'))); startAuto(); });
        var tx=0;
        document.getElementById('banner-wrap').addEventListener('touchstart',function(e){ tx=e.touches[0].clientX; },{passive:true});
        document.getElementById('banner-wrap').addEventListener('touchend',function(e){ var dx=e.changedTouches[0].clientX-tx; if(Math.abs(dx)>40){ goBanner(dx<0?cur+1:cur-1); startAuto(); } },{passive:true});
    }

    $('.poll-opt').on('click', function(){
        var pollId=$(this).data('poll'), optId=$(this).data('opt');
        var $opts=$(this).closest('.card, div[style*="border-radius"]').find('.poll-opt');
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
