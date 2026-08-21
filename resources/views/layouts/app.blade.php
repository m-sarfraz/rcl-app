<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0ea954">
    <title>@yield('title','Royal Champions League') — RCL</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Social metadata. Pages that have something better to say push their own. --}}
    @hasSection('social')
        @yield('social')
    @else
        <meta property="og:site_name" content="Royal Champions League">
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('title','Royal Champions League')">
        <meta property="og:description" content="Fixtures, live scores and statistics for the Village Cricket Council.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary">
    @endif

    <style>

/* ════════════════════════════════════════════════════════
   ROYAL CRICKET — Design System v2  (Light Mode)
   All CSS variables here → every page auto-adapts
════════════════════════════════════════════════════════ */
:root {
    /* ── Brand palette ──────────────────────────────── */
    --p:   #0ea954;          /* vivid cricket green     */
    --p2:  #077a3a;          /* deep green              */
    --g:   #d97706;          /* rich gold               */
    --g2:  #b45309;          /* dark amber              */
    --red: #e53935;          /* bold red                */
    --blue:#0288d1;          /* bold blue               */
    --pur: #7c3aed;          /* violet                  */

    /* ── Surfaces (light) ──────────────────────────── */
    --dark: #f0faf4;         /* page / canvas bg        */
    --s1:   #ffffff;         /* card surface            */
    --s2:   #f4faf6;         /* input / chip            */
    --s3:   #e8f5ee;         /* hover / strip           */

    /* ── Borders ────────────────────────────────────── */
    --bd:   rgba(14,169,84,.14);
    --bd2:  rgba(217,119,6,.18);

    /* ── Text ───────────────────────────────────────── */
    --txt:  #0d1f14;
    --mut:  #6b8f74;

    /* ── Glows ──────────────────────────────────────── */
    --gp:  0 4px 20px rgba(14,169,84,.18);
    --gg:  0 4px 20px rgba(217,119,6,.18);

    /* ── Gradients ──────────────────────────────────── */
    --grad-p:    linear-gradient(135deg,#0ea954,#06b6d4);
    --grad-g:    linear-gradient(135deg,#d97706,#ea580c);
    --grad-red:  linear-gradient(135deg,#e53935,#b71c1c);

    /* ── Layout ─────────────────────────────────────── */
    --vw:   550px;
    --bnav: 74px;
    --head: 58px;

    /* ── Radii ──────────────────────────────────────── */
    --r:    18px;
    --r-sm: 12px;
    --r-lg: 24px;
    --r-xl: 32px;
    --r-f:  999px;
}

/* ════════════════════════════════════════════════════════
   BASE
════════════════════════════════════════════════════════ */
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html,body {
    height:100%; overflow:hidden;
    background:#d1ead9;
    font-family:'Plus Jakarta Sans',system-ui,sans-serif;
    color:var(--txt); font-size:14px; line-height:1.55; letter-spacing:.01em;
}

/* ════════════════════════════════════════════════════════
   APP SHELL
════════════════════════════════════════════════════════ */
#app-shell {
    width:100%; max-width:var(--vw); height:100dvh;
    margin:0 auto;
    background:
        radial-gradient(ellipse 80% 50% at 15% 0%,   rgba(14,169,84,.1)   0%, transparent 55%),
        radial-gradient(ellipse 60% 40% at 85% 100%,  rgba(217,119,6,.06)  0%, transparent 55%),
        radial-gradient(ellipse 50% 35% at 50% 50%,   rgba(6,182,212,.04)  0%, transparent 50%),
        linear-gradient(160deg, #edfaf3 0%, #f5fff9 50%, #edfaf3 100%);
    display:flex; flex-direction:column;
    position:relative; overflow:hidden;
    box-shadow:0 0 60px rgba(0,0,0,.15), 0 0 0 1px rgba(14,169,84,.08);
}

/* ════════════════════════════════════════════════════════
   WATERMARK
════════════════════════════════════════════════════════ */
.wm-layer {
    position:absolute; inset:0; z-index:1; pointer-events:none;
    overflow:hidden; user-select:none;
}
#ticker, #app-header, #page-content { position:relative; z-index:2; }
.wml-logo {
    position:absolute; width:420px; height:420px;
    top:50%; left:50%; transform:translate(-50%,-50%);
    opacity:0.025; object-fit:contain;
    filter:grayscale(20%) brightness(.5) saturate(.4);
    pointer-events:none;
}

/* ════════════════════════════════════════════════════════
   HERITAGE STRIP
════════════════════════════════════════════════════════ */
.heritage-strip {
    position:absolute; bottom:var(--bnav); left:0; right:0;
    height:80px; z-index:2; pointer-events:none; overflow:hidden; display:flex;
}
.heritage-strip::after {
    content:''; position:absolute; inset:0;
    background:linear-gradient(to bottom,#edfaf3 0%,rgba(237,250,243,.5) 40%,transparent 100%);
    z-index:2;
}
.heritage-strip img {
    width:25%; height:80px; object-fit:cover; object-position:top center;
    opacity:.1; filter:sepia(20%) brightness(.85);
    flex-shrink:0; display:block;
}
.dev-credit {
    position:absolute; bottom:calc(var(--bnav) + 1px);
    left:0; right:0; text-align:center;
    font-size:.42rem; letter-spacing:.12em;
    color:rgba(223,240,229,.18); font-weight:700; text-transform:uppercase;
    z-index:3; pointer-events:none;
}

/* ════════════════════════════════════════════════════════
   SPLASH SCREEN
════════════════════════════════════════════════════════ */
#splash {
    position:absolute; inset:0; z-index:9999;
    background:
        radial-gradient(ellipse 80% 50% at 75% 15%, rgba(0,230,118,.2)  0%, transparent 55%),
        radial-gradient(ellipse 60% 45% at 20% 80%, rgba(0,178,255,.12) 0%, transparent 50%),
        radial-gradient(ellipse 50% 40% at 50% 50%, rgba(0,230,118,.06) 0%, transparent 55%),
        linear-gradient(170deg, #030c06 0%, #071408 30%, #0a1c0c 60%, #030c06 100%);
    display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:1rem;
    transition:opacity .7s ease, transform .7s ease;
    overflow:hidden;
}
#splash.hidden { opacity:0; transform:scale(1.05); pointer-events:none; }

/* Ambient glow blobs */
#splash::before {
    content:''; position:absolute;
    width:300px; height:300px; border-radius:50%;
    background:radial-gradient(circle, rgba(0,230,118,.12) 0%, transparent 65%);
    top:-60px; right:-60px; filter:blur(40px); pointer-events:none;
}
#splash::after {
    content:''; position:absolute;
    width:200px; height:200px; border-radius:50%;
    background:radial-gradient(circle, rgba(0,178,255,.08) 0%, transparent 65%);
    bottom:-40px; left:-30px; filter:blur(30px); pointer-events:none;
}

#splash > *:not(.splash-bg-photos) { position:relative; z-index:1; }

/* Dual spinner */
.splash-ring {
    position:relative; width:120px; height:120px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.splash-ring::before {
    content:''; position:absolute; inset:0; border-radius:50%;
    border:2.5px solid rgba(0,230,118,.1);
    border-top-color:rgba(0,230,118,.95);
    border-right-color:rgba(0,230,118,.35);
    animation:ring-cw 1.1s linear infinite;
    filter:drop-shadow(0 0 6px rgba(0,230,118,.5));
}
.splash-ring::after {
    content:''; position:absolute; inset:9px; border-radius:50%;
    border:2px solid rgba(255,202,40,.08);
    border-top-color:#ffca28;
    border-left-color:rgba(255,202,40,.45);
    animation:ring-ccw .78s linear infinite;
    filter:drop-shadow(0 0 5px rgba(255,202,40,.4));
}
@keyframes ring-cw  { to { transform:rotate(360deg);  } }
@keyframes ring-ccw { to { transform:rotate(-360deg); } }

.splash-logo-img {
    width:86px; height:86px; border-radius:50%; object-fit:cover;
    border:2.5px solid rgba(0,230,118,.6);
    box-shadow:0 0 24px rgba(0,0,0,.6), 0 0 40px rgba(0,230,118,.2);
    position:relative; z-index:1;
}

.splash-name {
    font-size:1.52rem; font-weight:900; letter-spacing:.02em;
    background:linear-gradient(135deg,#fff 0%,rgba(255,255,255,.7) 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.splash-sub {
    font-size:.65rem; letter-spacing:.18em; text-transform:uppercase;
    color:rgba(223,240,229,.42); margin-top:.2rem;
}
.splash-dots { display:flex; gap:6px; align-items:center; margin-top:.2rem; }
.splash-dots span {
    width:5px; height:5px; border-radius:50%;
    background:rgba(223,240,229,.3);
    animation:dot-pop 1.3s ease-in-out infinite;
}
.splash-dots span:nth-child(2) { animation-delay:.22s; }
.splash-dots span:nth-child(3) { animation-delay:.44s; }
@keyframes dot-pop {
    0%,80%,100% { transform:scale(.55); opacity:.28; background:rgba(223,240,229,.3); }
    40%          { transform:scale(1.35); opacity:1;  background:#00e676; box-shadow:0 0 8px rgba(0,230,118,.6); }
}
.splash-devby {
    font-size:.42rem; letter-spacing:.14em;
    color:rgba(223,240,229,.2); text-transform:uppercase; font-weight:700;
}

/* Background cricket photos */
.splash-bg-photos {
    position:absolute; bottom:0; left:0; right:0;
    height:220px; z-index:0; overflow:hidden;
}
.splash-bg-photos-imgs { display:flex; height:100%; }
.splash-bg-photos-imgs img {
    flex:1; min-width:0; height:100%;
    object-fit:cover; object-position:top center; display:block;
    filter:grayscale(100%) brightness(.4) contrast(1.1);
    mix-blend-mode:luminosity;
}
.splash-bg-photos-veil {
    position:absolute; inset:0; pointer-events:none;
    background:linear-gradient(
        to bottom,
        rgba(3,9,7,1)       0%,
        rgba(3,9,7,.96)     8%,
        rgba(5,12,8,.88)   18%,
        rgba(6,14,9,.76)   30%,
        rgba(7,16,10,.58)  44%,
        rgba(8,18,11,.36)  60%,
        rgba(9,20,12,.14)  78%,
        transparent         100%
    );
}

/* ════════════════════════════════════════════════════════
   TICKER
════════════════════════════════════════════════════════ */
#ticker {
    background:linear-gradient(90deg,#0a7a38,#077a3a);
    border-bottom:1px solid rgba(0,0,0,.08);
    padding:.28rem .875rem; display:flex; align-items:center; gap:.6rem;
    flex-shrink:0; overflow:hidden; white-space:nowrap;
}
#ticker .lbl {
    background:linear-gradient(135deg,#00e676,#00d4ff);
    color:#030907; font-size:.52rem; font-weight:800;
    letter-spacing:.12em; text-transform:uppercase;
    padding:.22em .65em; border-radius:var(--r-f); flex-shrink:0;
}
#ticker-track { flex:1; overflow:hidden; }
#ticker-txt {
    display:inline-block; white-space:nowrap;
    font-size:.66rem; color:rgba(255,255,255,.9); font-weight:600;
    animation:tick 38s linear infinite;
}
@keyframes tick { 0%{transform:translateX(100vw)} 100%{transform:translateX(-100%)} }

/* ════════════════════════════════════════════════════════
   HEADER
════════════════════════════════════════════════════════ */
#app-header {
    height:var(--head); flex-shrink:0;
    background:linear-gradient(135deg,#0a7a38 0%,#0ea954 55%,#077a3a 100%);
    border-bottom:1px solid rgba(0,0,0,.08);
    display:flex; align-items:center; padding:0 1rem; gap:.75rem;
    cursor:pointer; text-decoration:none;
}
.hdr-logo-ring {
    width:36px; height:36px; border-radius:50%; flex-shrink:0;
    border:2px solid rgba(255,255,255,.6);
    box-shadow:0 0 14px rgba(255,255,255,.25);
    background:rgba(255,255,255,.1); padding:2px; overflow:hidden;
}
.hdr-logo-ring img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
.hdr-info { flex:1; min-width:0; }
.hdr-title {
    font-size:.82rem; font-weight:800; letter-spacing:.01em; color:#fff;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.hdr-sub {
    font-size:.55rem; color:rgba(255,255,255,.65); letter-spacing:.06em;
    text-transform:uppercase; margin-top:.05rem;
}
.hdr-badges { display:flex; align-items:center; gap:.5rem; flex-shrink:0; }
.hdr-edition {
    font-size:.6rem; font-weight:700; letter-spacing:.04em;
    background:rgba(255,255,255,.2); color:#fff;
    border:1px solid rgba(255,255,255,.3);
    padding:.2em .65em; border-radius:var(--r-f);
}
.live-ring {
    width:22px; height:22px; border-radius:50%;
    background:rgba(255,82,82,.1); border:1.5px solid rgba(255,82,82,.35);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 0 10px rgba(255,82,82,.15);
}
.live-ring .dot {
    width:8px; height:8px; border-radius:50%;
    background:var(--red); box-shadow:0 0 6px var(--red);
    animation:ping 1.1s ease-in-out infinite;
}
@keyframes ping { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.6);opacity:.5} }

/* ════════════════════════════════════════════════════════
   TOAST
════════════════════════════════════════════════════════ */
#toast-wrap { position:absolute; top:.875rem; left:.875rem; right:.875rem; z-index:9998; }
.toast {
    background:#fff; border:1px solid var(--bd);
    border-radius:var(--r); padding:.65rem 1rem;
    margin-bottom:.5rem; font-size:.8rem;
    display:flex; align-items:center; gap:.5rem;
    animation:slide-in .3s ease;
    box-shadow:0 8px 32px rgba(0,0,0,.12);
}
.toast.ok  { border-color:rgba(14,169,84,.35); color:var(--p); }
.toast.err { border-color:rgba(229,57,53,.35);  color:var(--red); }
@keyframes slide-in { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:none} }

/* ════════════════════════════════════════════════════════
   PAGE CONTENT
════════════════════════════════════════════════════════ */
#page-content {
    flex:1; overflow-y:auto; overflow-x:hidden;
    -webkit-overflow-scrolling:touch; scroll-behavior:smooth;
    padding-bottom:calc(var(--bnav) + 12px);
}
#page-content::-webkit-scrollbar { display:none; }
#page-content { scrollbar-width:none; }

/* ════════════════════════════════════════════════════════
   BOTTOM NAV — Floating Pill
════════════════════════════════════════════════════════ */
#bottom-nav {
    position:absolute; bottom:10px; left:10px; right:10px;
    height:56px; z-index:100;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(24px) saturate(1.5);
    border:1px solid rgba(14,169,84,.15);
    border-radius:var(--r-f);
    display:flex; align-items:center; justify-content:space-around;
    padding:0 6px;
    box-shadow:0 8px 32px rgba(0,0,0,.1), 0 2px 8px rgba(14,169,84,.08);
}
.nav-item {
    display:flex; flex-direction:column; align-items:center; gap:3px;
    padding:6px 12px; border-radius:var(--r-f);
    text-decoration:none; color:var(--mut);
    font-size:.52rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase;
    transition:color .2s, background .2s; position:relative;
}
.nav-item i { font-size:1.15rem; line-height:1; }
.nav-item.active { color:var(--p); background:rgba(14,169,84,.1); }
.nav-live-dot {
    position:absolute; top:4px; right:8px;
    width:6px; height:6px; border-radius:50%;
    background:var(--red); box-shadow:0 0 6px var(--red);
    animation:ping 1.1s ease-in-out infinite;
}

/* ════════════════════════════════════════════════════════
   GLASS CARDS & SURFACES
════════════════════════════════════════════════════════ */
.card {
    background:#fff;
    border:1px solid var(--bd);
    border-radius:var(--r);
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.card-hover {
    cursor:pointer;
    transition:transform .22s cubic-bezier(.34,1.56,.64,1), border-color .2s, box-shadow .2s;
}
.card-hover:hover, .card-hover:active {
    transform:translateY(-3px);
    border-color:rgba(14,169,84,.35);
    box-shadow:0 12px 32px rgba(14,169,84,.12), 0 2px 8px rgba(0,0,0,.06);
}

/* ════════════════════════════════════════════════════════
   MATCH CARDS
════════════════════════════════════════════════════════ */
.match-card {
    background:#fff;
    border:1px solid var(--bd);
    border-radius:var(--r);
    padding:.875rem 1rem;
    cursor:pointer;
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}
.match-card:hover {
    border-color:rgba(14,169,84,.3);
    transform:translateY(-2px);
    box-shadow:0 10px 28px rgba(14,169,84,.12);
}
.match-card-live {
    border-color:rgba(229,57,53,.25);
    background:rgba(229,57,53,.02);
    box-shadow:0 4px 20px rgba(229,57,53,.07);
}
.match-card-upcoming { border-color:rgba(217,119,6,.18); }
.match-meta {
    display:flex; gap:.75rem; font-size:.66rem; color:var(--mut);
    flex-wrap:wrap; align-items:center;
}
.teams-row { display:flex; align-items:center; }
.team-name { font-weight:700; font-size:.88rem; color:var(--txt); }
.team-score {
    font-size:1.4rem; font-weight:900; line-height:1;
    background:var(--grad-p);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.vs-pill {
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1);
    border-radius:var(--r-f); padding:.2em .65em;
    font-size:.65rem; font-weight:700; color:var(--mut);
}

/* Live badge */
.live-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.22em .7em; border-radius:var(--r-f);
    background:rgba(255,82,82,.12); color:var(--red);
    border:1px solid rgba(255,82,82,.28);
    font-size:.6rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase;
}
.live-badge::before {
    content:''; width:6px; height:6px; border-radius:50%;
    background:var(--red); box-shadow:0 0 6px var(--red);
    animation:ping 1.1s ease-in-out infinite;
}

/* ════════════════════════════════════════════════════════
   SECTIONS
════════════════════════════════════════════════════════ */
.sec { padding:.875rem 1rem; }
.sec-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:.875rem; }
.sec-title {
    display:flex; align-items:center; gap:.625rem;
    font-weight:800; font-size:.88rem; color:var(--txt); letter-spacing:-.01em;
}
.sec-ico {
    width:28px; height:28px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:.85rem;
}
.sec-link {
    font-size:.72rem; font-weight:700; color:var(--p);
    text-decoration:none; opacity:.75; transition:opacity .2s;
    letter-spacing:.03em;
}
.sec-link:hover { opacity:1; }
.divider { height:1px; background:rgba(255,255,255,.045); margin:0 1rem; }

/* ════════════════════════════════════════════════════════
   STATS / LEADER ROWS
════════════════════════════════════════════════════════ */
.stat-row {
    display:flex; gap:.5rem; overflow-x:auto; padding:.875rem 1rem .25rem;
}
.stat-row::-webkit-scrollbar { display:none; }
.stat-pill {
    flex:1; min-width:70px; background:#fff;
    border:1px solid var(--bd); border-radius:var(--r);
    padding:.75rem .5rem; text-align:center;
}
.stat-pill .val { font-size:1.4rem; font-weight:900; line-height:1; }
.stat-pill .lbl { font-size:.55rem; text-transform:uppercase; letter-spacing:.07em; color:var(--mut); margin-top:.25rem; }

.leader-row {
    display:flex; align-items:center; gap:.625rem;
    padding:.625rem .875rem;
    border-bottom:1px solid var(--bd);
    transition:background .15s;
}
.leader-row:hover { background:var(--s2); }
.leader-rank {
    font-size:.75rem; font-weight:800; color:var(--mut); width:22px;
    text-align:center; flex-shrink:0;
}
.leader-rank.gold { color:var(--g); text-shadow:0 0 10px rgba(255,202,40,.4); }
.leader-avatar {
    width:32px; height:32px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.68rem; font-weight:700; background:rgba(255,255,255,.07);
    overflow:hidden; border:1px solid rgba(255,255,255,.1);
}
.leader-name { font-size:.82rem; font-weight:700; color:var(--txt); }
.leader-sub  { font-size:.66rem; color:var(--mut); margin-top:.1rem; }
.leader-val  { font-size:.95rem; font-weight:900; color:var(--p); }

/* Stat tabs */
.stat-tab {
    flex-shrink:0; padding:.4rem .9rem; border-radius:var(--r-f);
    font-size:.72rem; font-weight:700; cursor:pointer; white-space:nowrap;
    transition:all .2s; border:1px solid var(--bd);
    background:var(--s2); color:var(--mut);
}
.stat-tab.active {
    background:rgba(14,169,84,.12); color:var(--p);
    border-color:rgba(14,169,84,.3);
}
.stat-panel { display:block; }

/* ════════════════════════════════════════════════════════
   TABLES
════════════════════════════════════════════════════════ */
.sc-table { width:100%; border-collapse:collapse; font-size:.77rem; }
.sc-table thead th {
    background:var(--s3); padding:.45rem .5rem;
    font-weight:700; font-size:.6rem; color:var(--mut);
    text-transform:uppercase; letter-spacing:.05em;
}
.sc-table tbody td {
    padding:.5rem; border-bottom:1px solid var(--bd);
    vertical-align:middle; color:var(--txt);
}
.sc-table .not-out { color:var(--p); font-weight:700; }

.pts-table { width:100%; border-collapse:collapse; font-size:.77rem; }
.pts-table thead th {
    background:var(--s3); padding:.45rem .5rem;
    font-weight:700; font-size:.6rem; color:var(--mut);
    text-transform:uppercase; text-align:center;
}
.pts-table thead th:first-child { text-align:left; }
.pts-table tbody td { padding:.5rem; border-bottom:1px solid var(--bd); text-align:center; color:var(--txt); }
.pts-table tbody td:first-child { text-align:left; }
.pts-table .rank { font-weight:700; color:var(--mut); }
.pts-table .nrr-pos { color:var(--p); font-weight:700; }
.pts-table .nrr-neg { color:var(--red); font-weight:700; }
.team-cell { display:flex; align-items:center; gap:.4rem; white-space:nowrap; }
.team-badge-sm { width:22px; height:22px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.5rem; color:#000; }

/* Edition tabs */
.ed-tabs { display:flex; border-bottom:1px solid var(--bd); padding:0 .5rem; gap:.1rem; overflow-x:auto; background:#fff; }
.ed-tabs::-webkit-scrollbar { display:none; }
.ed-tab {
    padding:.6rem .75rem; border:none; background:none;
    color:var(--mut); font-size:.75rem; font-weight:700; cursor:pointer;
    border-bottom:2.5px solid transparent; white-space:nowrap;
    transition:color .2s, border-color .2s; flex-shrink:0;
    font-family:inherit;
}
.ed-tab.active { color:var(--p); border-bottom-color:var(--p); }
.ed-panel { display:none; }
.ed-panel.active { display:block; }

/* ════════════════════════════════════════════════════════
   PILLS & BADGES
════════════════════════════════════════════════════════ */
.pill       { display:inline-flex; align-items:center; padding:.22em .72em; border-radius:var(--r-f); font-size:.6rem; font-weight:700; letter-spacing:.03em; }
.pill-green { background:rgba(0,230,118,.1);   color:var(--p);   border:1px solid rgba(0,230,118,.22); }
.pill-gold  { background:rgba(255,202,40,.1);  color:var(--g);   border:1px solid rgba(255,202,40,.22); }
.pill-red   { background:rgba(255,82,82,.1);   color:var(--red); border:1px solid rgba(255,82,82,.22); }
.pill-muted { background:rgba(255,255,255,.06);color:var(--mut); border:1px solid rgba(255,255,255,.1); }
.pill-blue  { background:rgba(64,196,255,.1);  color:var(--blue);border:1px solid rgba(64,196,255,.22); }

/* ════════════════════════════════════════════════════════
   PLAYER PAGE
════════════════════════════════════════════════════════ */
.player-header {
    padding:1.75rem 1rem 1.25rem; text-align:center;
    background:linear-gradient(180deg,rgba(14,169,84,.08) 0%,transparent 100%);
    border-bottom:1px solid var(--bd);
}
.player-avatar {
    width:82px; height:82px; border-radius:50%; object-fit:cover;
    border:2.5px solid var(--p); margin:0 auto; display:block;
    box-shadow:var(--gp);
}

/* ════════════════════════════════════════════════════════
   POLLS
════════════════════════════════════════════════════════ */
.poll-opt {
    position:relative; border-radius:10px; overflow:hidden;
    background:var(--s2); border:1px solid var(--bd);
    padding:.55rem .875rem; margin-bottom:.4rem; cursor:pointer;
    transition:border-color .2s;
}
.poll-opt:hover { border-color:rgba(14,169,84,.35); }
.poll-bar {
    position:absolute; inset:0; height:100%; border-radius:10px;
    background:linear-gradient(90deg,rgba(14,169,84,.18),rgba(14,169,84,.07));
    transition:width .55s cubic-bezier(.4,0,.2,1);
}
.poll-label {
    position:relative; z-index:1; display:flex;
    justify-content:space-between; align-items:center;
    font-size:.8rem; font-weight:600; color:var(--txt);
}

/* ════════════════════════════════════════════════════════
   GRADIENT TEXT
════════════════════════════════════════════════════════ */
.grad {
    background:var(--grad-p);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}

/* ════════════════════════════════════════════════════════
   SHARE BUTTON
════════════════════════════════════════════════════════ */
.btn-share {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.42rem .9rem; border-radius:var(--r-f);
    border:1px solid rgba(0,230,118,.25);
    background:rgba(0,230,118,.08); color:var(--p);
    font-size:.72rem; font-weight:700; cursor:pointer;
    transition:background .2s, box-shadow .2s; font-family:inherit;
}
.btn-share:hover { background:rgba(0,230,118,.15); box-shadow:0 4px 16px rgba(0,230,118,.15); }
.btn-share:active { transform:scale(.96); }

/* Share card (off-screen html2canvas target) */
#rcl-share-wrap { position:fixed; top:-9999px; left:0; z-index:-999; pointer-events:none; }
#rcl-share-inner { width:400px; background:#fff; font-family:system-ui,-apple-system,'Segoe UI',sans-serif; overflow:hidden; }

/* ════════════════════════════════════════════════════════
   UTILITIES
════════════════════════════════════════════════════════ */
.hide-scroll { -webkit-overflow-scrolling:touch; scrollbar-width:none; }
.hide-scroll::-webkit-scrollbar { display:none; }
.fw7 { font-weight:700; }
.c-p { color:var(--p); }
.c-g { color:var(--g); }
.c-m { color:var(--mut); }
.c-r { color:var(--red); }

    </style>
    @stack('styles')
</head>
<body>
<div id="app-shell">

    {{-- Watermark --}}
    <div class="wm-layer" aria-hidden="true">
        <img src="/logo.jfif" class="wml-logo" alt="">
    </div>

    {{-- Heritage photos (bottom, above nav) --}}
    <div class="heritage-strip" aria-hidden="true">
        <img src="/images/watermarks/imran-khan.jpg"      alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/wc1992.jpg"           alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/pakistan-cricket.jpg" alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/green-caps.jpg"       alt="" onerror="this.style.opacity='0'">
    </div>
    <div class="dev-credit" aria-hidden="true">Developed by Sarfraz Jutt</div>

    {{-- Splash --}}
    <div id="splash">
        <div class="splash-bg-photos" aria-hidden="true">
            <div class="splash-bg-photos-imgs">
                <img src="/images/watermarks/imran-khan.jpg"   alt="" onerror="this.style.opacity='0'">
                <img src="/images/watermarks/imran-khan-2.jpg" alt="" onerror="this.style.opacity='0'">
            </div>
            <div class="splash-bg-photos-veil"></div>
        </div>
        <div class="splash-ring">
            <img src="/logo.jfif" alt="RCL" class="splash-logo-img">
        </div>
        <div style="text-align:center;">
            <div class="splash-name">Royal Champions League</div>
            <div class="splash-sub">Village Cricket Council · Pakistan</div>
        </div>
        <div class="splash-dots"><span></span><span></span><span></span></div>
        <div class="splash-devby">Developed by Sarfraz Jutt</div>
    </div>

    {{-- Ticker --}}
    <div id="ticker">
        <span class="lbl">Live</span>
        <div id="ticker-track"><span id="ticker-txt">Loading updates…</span></div>
    </div>

    {{-- Header --}}
    <div id="app-header" role="banner" onclick="window.location='{{ route('home') }}'">
        <div class="hdr-logo-ring">
            <img src="/logo.jfif" alt="RCL">
        </div>
        <div class="hdr-info">
            <div class="hdr-title">Royal Champions League</div>
            <div class="hdr-sub">Village Cricket Council · Pakistan</div>
        </div>
        <div class="hdr-badges">
            @if($currentEdition ?? null)
                <span class="hdr-edition">{{ $currentEdition->edition_number }}th Ed.</span>
            @endif
            @php $hasLive = \App\Models\CricketMatch::where('status','live')->exists(); @endphp
            @if($hasLive)
                <div class="live-ring" title="Match Live"><div class="dot"></div></div>
            @endif
            @if(auth()->check())
                <button type="button"
                        onclick="event.stopPropagation(); window.location='{{ route('admin.dashboard') }}';"
                        style="color:rgba(0,230,118,.7);font-size:1rem;background:none;border:none;
                               cursor:pointer;padding:.3rem;line-height:1;display:flex;align-items:center;">
                    <i class="bi bi-shield-fill"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast-wrap"></div>

    {{-- Share card (off-screen, captured by html2canvas) --}}
    <div id="rcl-share-wrap" aria-hidden="true">
        <div id="rcl-share-inner">
            <div style="background:linear-gradient(135deg,#030907 0%,#0d2414 50%,#030907 100%);padding:14px 18px;display:flex;align-items:center;gap:12px;">
                <img src="/logo.jfif" alt="RCL" style="width:46px;height:46px;border-radius:50%;border:2px solid rgba(0,230,118,.6);object-fit:cover;flex-shrink:0;box-shadow:0 0 16px rgba(0,230,118,.3);">
                <div>
                    <div style="color:#dff0e5;font-size:15px;font-weight:800;letter-spacing:.02em;line-height:1.2;">Royal Champions League</div>
                    <div style="color:rgba(223,240,229,.5);font-size:9px;letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Village Cricket Council · Pakistan</div>
                </div>
                <div id="share-card-label" style="margin-left:auto;background:linear-gradient(135deg,#00e676,#00d4ff);color:#030907;font-size:8.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:3px 10px;border-radius:99px;flex-shrink:0;"></div>
            </div>
            <div id="share-card-body" style="background:#f8fdf9;min-height:80px;"></div>
            <div style="background:linear-gradient(90deg,#030907,#0d2414);padding:8px 18px;display:flex;align-items:center;justify-content:space-between;">
                <div style="color:rgba(223,240,229,.45);font-size:8px;letter-spacing:.1em;text-transform:uppercase;font-weight:700;">RCL Official App</div>
                <div style="color:rgba(223,240,229,.25);font-size:7.5px;">Generated by RCL App</div>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div id="page-content">
        @yield('content')
    </div>

    {{-- Bottom Nav — Floating Pill --}}
    <nav id="bottom-nav">
        <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i><span>Home</span>
        </a>
        <a href="{{ route('schedule') }}" class="nav-item {{ request()->routeIs('schedule') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i><span>Fixtures</span>
        </a>
        <a href="{{ route('tournaments.index') }}" class="nav-item {{ request()->routeIs('tournaments.*') ? 'active' : '' }}">
            <i class="bi bi-trophy-fill"></i><span>Tourney</span>
            @if($hasLive ?? false)<div class="nav-live-dot"></div>@endif
        </a>
        <a href="{{ route('stats') }}" class="nav-item {{ request()->routeIs('stats') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i><span>Stats</span>
        </a>
        <a href="{{ route('more') }}" class="nav-item {{ request()->routeIs('more') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i><span>More</span>
        </a>
    </nav>

</div>{{-- #app-shell --}}

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
$.ajaxSetup({ headers:{ 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content') } });

/* Wheel redirect → page-content */
(function(){
    var shell = document.getElementById('app-shell');
    var pc    = document.getElementById('page-content');
    if (!shell || !pc) return;
    shell.addEventListener('wheel', function(e){
        if (!pc.contains(e.target)) { pc.scrollTop += e.deltaY; e.preventDefault(); }
    }, { passive:false });
})();

/* Splash */
setTimeout(function(){ $('#splash').addClass('hidden'); setTimeout(function(){ $('#splash').hide(); },700); }, 2000);

/* Ticker */
function loadTicker() {
    $.get('/api/ticker', function(items) {
        if (items && items.length) $('#ticker-txt').text(items.join('   ·   '));
    });
}
loadTicker(); setInterval(loadTicker, 30000);

/* Toast */
function showToast(msg, type) {
    var icon = type==='ok' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    var t = $('<div class="toast '+(type||'ok')+'"><i class="bi '+icon+'"></i> '+msg+'</div>');
    $('#toast-wrap').append(t);
    setTimeout(function(){ t.fadeOut(400, function(){ t.remove(); }); }, 3500);
}
@if(session('success')) $(function(){ showToast(@json(session('success')), 'ok'); }); @endif
@if(session('error'))   $(function(){ showToast(@json(session('error')), 'err'); }); @endif

/* Share as Picture */
async function shareRCL(title, bodyHtml, label) {
    document.getElementById('share-card-label').textContent = label || 'RCL';
    document.getElementById('share-card-body').innerHTML = bodyHtml;
    var wrap = document.getElementById('rcl-share-wrap');
    wrap.style.top = '0'; wrap.style.left = '-9999px'; wrap.style.zIndex = '-999';
    try {
        var canvas = await html2canvas(document.getElementById('rcl-share-inner'), {
            scale:2.5, useCORS:true, allowTaint:false, backgroundColor:'#f8fdf9', logging:false,
        });
        wrap.style.top = '-9999px';
        canvas.toBlob(async function(blob){
            var file = new File([blob], 'rcl-share.png', { type:'image/png' });
            if (navigator.share && navigator.canShare && navigator.canShare({ files:[file] })) {
                try { await navigator.share({ title:'Royal Champions League', text:title, files:[file] }); }
                catch(e) {}
            } else {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url; a.download = 'rcl-share.png'; a.click();
                setTimeout(function(){ URL.revokeObjectURL(url); }, 1500);
                showToast('Image downloaded!', 'ok');
            }
        }, 'image/png');
    } catch(e) { wrap.style.top = '-9999px'; showToast('Could not generate image','err'); }
}
</script>
@stack('scripts')
</body>
</html>
