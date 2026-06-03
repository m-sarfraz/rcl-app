<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B8A4E">
    <title>@yield('title','Royal Champions League') — RCL</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   DESIGN TOKENS — Light theme
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
:root {
    /* Brand */
    --p:   #1B8A4E;   /* cricket green   */
    --p2:  #16703E;
    --g:   #D4900A;   /* warm gold       */
    --g2:  #B37A06;
    --red: #DC2626;
    --blue:#0284C7;
    --pur: #7C3AED;

    /* Surfaces */
    --dark: #F0F7F2;   /* page background */
    --s1:   #FFFFFF;   /* card / surface  */
    --s2:   #F4F9F5;   /* input / chip bg */
    --s3:   #E6F2EA;   /* hover / strip   */

    /* Borders */
    --bd:   rgba(27,138,78,.18);
    --bd2:  rgba(212,144,10,.2);

    /* Text */
    --txt:  #1A2E20;
    --mut:  #6B8F74;

    /* Shadows / glows */
    --gp: 0 4px 18px rgba(27,138,78,.22);
    --gg: 0 4px 18px rgba(212,144,10,.22);

    --vw:   550px;
    --bnav: 64px;
    --head: 62px;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   BASE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html,body { height:100%; overflow:hidden; background:linear-gradient(145deg,#7DAE98,#8EBEA5,#6BA890,#A5CBBA,#7DB59A,#92C4AA,#6FA895); }
body { font-family:'Quicksand',sans-serif; color:var(--txt); font-size:14px; line-height:1.55; letter-spacing:.015em; }

/* ── Shell ─────────────────────────────────────────── */
#app-shell {
    width:100%; max-width:var(--vw); height:100dvh;
    margin:0 auto;
    background:
        radial-gradient(ellipse 90% 40% at 0% 0%,   rgba(27,138,78,.18)  0%, transparent 65%),
        radial-gradient(ellipse 75% 45% at 100% 100%,rgba(212,144,10,.14) 0%, transparent 65%),
        radial-gradient(ellipse 55% 30% at 55% 48%,  rgba(13,115,99,.09)  0%, transparent 60%),
        radial-gradient(ellipse 40% 25% at 20% 80%,  rgba(77,160,130,.1)  0%, transparent 55%),
        linear-gradient(165deg,#EAFAF2 0%,#F0FFFC 18%,#FAFFF8 38%,#F2FBF5 58%,#EBF7F0 78%,#E4F3EC 100%);
    display:flex; flex-direction:column;
    position:relative; overflow:hidden;
    box-shadow:0 0 80px rgba(0,0,0,.22);
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   WATERMARK LAYER (app shell — logo only)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.wm-layer {
    position:absolute; inset:0;
    z-index:1; pointer-events:none;
    overflow:hidden; user-select:none;
}
/* All content elements stay above watermark */
#ticker, #app-header, #page-content { position:relative; z-index:2; }
/* bottom-nav already has z-index:100 */
.wml-logo {
    position:absolute;
    width:380px; height:380px;
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    opacity:0.038;
    object-fit:contain;
    filter:grayscale(8%) saturate(1.2);
    pointer-events:none;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   HERITAGE STRIP  (bottom of app — cricket legends)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.heritage-strip {
    position:absolute;
    bottom:var(--bnav); left:0; right:0;
    height:90px;
    z-index:2; pointer-events:none;
    overflow:hidden; display:flex;
}
.heritage-strip::after {
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(
        to bottom,
        rgba(234,250,242,1) 0%,
        rgba(234,250,242,.55) 28%,
        rgba(234,250,242,.0) 60%,
        rgba(234,250,242,.0) 100%
    );
    pointer-events:none; z-index:2;
}
.heritage-strip img {
    width:25%; height:90px;
    object-fit:cover; object-position:top center;
    opacity:.13; filter:sepia(25%) contrast(1.05) brightness(.9);
    flex-shrink:0; display:block;
}
.heritage-strip img:not(:last-child) {
    border-right:1px solid rgba(27,138,78,.1);
}
/* Credit label */
.dev-credit {
    position:absolute;
    bottom:calc(var(--bnav) + 2px);
    left:0; right:0;
    text-align:center;
    font-size:.48rem; letter-spacing:.1em;
    color:rgba(107,143,116,.55);
    font-weight:700; text-transform:uppercase;
    z-index:3; pointer-events:none;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   SPLASH  (spinner loader + photos blended into green)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#splash {
    position:absolute; inset:0; z-index:9999;
    /* Clean green gradient — consistent bottom colour (#0C2E16) for seamless photo blend */
    background:
        radial-gradient(ellipse 72% 44% at 78% 12%, rgba(212,144,10,.34) 0%, transparent 52%),
        radial-gradient(ellipse 60% 42% at 16% 68%, rgba(30,180,100,.38) 0%, transparent 52%),
        linear-gradient(175deg, #0F3A1E 0%, #1A7A45 28%, #1B8A4E 52%, #127040 74%, #0C2E16 100%);
    display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:.95rem;
    transition:opacity .65s ease, transform .65s ease;
    overflow:hidden;
}
#splash.hidden { opacity:0; transform:scale(1.04); pointer-events:none; }

/* All direct children above bg photos */
#splash > *:not(.splash-bg-photos) { position:relative; z-index:1; }

/* ── Dual-ring spinner wrapping the logo ─────────────── */
.splash-ring {
    position:relative;
    width:116px; height:116px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
/* Outer ring — white, clockwise, 1.1 s */
.splash-ring::before {
    content:''; position:absolute; inset:0; border-radius:50%;
    border:3px solid rgba(255,255,255,.1);
    border-top-color:rgba(255,255,255,.92);
    border-right-color:rgba(255,255,255,.32);
    animation:ring-cw 1.1s linear infinite;
}
/* Inner ring — gold, counter-clockwise, 0.78 s */
.splash-ring::after {
    content:''; position:absolute; inset:8px; border-radius:50%;
    border:2.5px solid rgba(212,144,10,.1);
    border-top-color:#D4900A;
    border-left-color:rgba(212,144,10,.48);
    animation:ring-ccw 0.78s linear infinite;
}
@keyframes ring-cw  { to { transform:rotate(360deg);  } }
@keyframes ring-ccw { to { transform:rotate(-360deg); } }

/* Logo sits inside the rings, no pulse — the rings animate instead */
.splash-logo-img {
    width:84px; height:84px;
    border-radius:50%; object-fit:cover;
    border:2.5px solid rgba(255,255,255,.7);
    box-shadow:0 0 22px rgba(0,0,0,.45), 0 0 36px rgba(212,144,10,.22);
    position:relative; z-index:1;
}

/* Text */
.splash-name { font-size:1.52rem; font-weight:800; letter-spacing:.04em; color:#fff; text-shadow:0 2px 18px rgba(0,0,0,.5); }
.splash-sub  { font-size:.67rem; letter-spacing:.16em; text-transform:uppercase; color:rgba(255,255,255,.52); margin-top:.18rem; }

/* Three pulsing loading dots */
.splash-dots { display:flex; gap:5px; align-items:center; margin-top:.18rem; }
.splash-dots span {
    width:5px; height:5px; border-radius:50%;
    background:rgba(255,255,255,.42);
    animation:dot-pop 1.25s ease-in-out infinite;
}
.splash-dots span:nth-child(2) { animation-delay:.22s; }
.splash-dots span:nth-child(3) { animation-delay:.44s; }
@keyframes dot-pop {
    0%,80%,100% { transform:scale(.6);  opacity:.32; background:rgba(255,255,255,.42); }
    40%          { transform:scale(1.3); opacity:1;   background:#D4900A; }
}

/* Developed-by */
.splash-devby {
    font-size:.44rem; letter-spacing:.12em;
    color:rgba(255,255,255,.26); text-transform:uppercase; font-weight:600;
}

/* ── Background photos — blended seamlessly into the green gradient ── */
.splash-bg-photos {
    position:absolute;
    bottom:0; left:0; right:0;
    height:230px;
    z-index:0; overflow:hidden;
}
.splash-bg-photos-imgs {
    display:flex; height:100%;
}
.splash-bg-photos-imgs img {
    flex:1; min-width:0; height:100%;
    object-fit:cover; object-position:top center;
    display:block;
    /* Fully desaturate + darken → image is now pure luminosity */
    filter:grayscale(100%) brightness(0.5) contrast(1.05);
    /*
     * luminosity blend: takes hue+saturation from the green background,
     * luminosity from the photo → result is always a shade of green,
     * so there can be no "colour cut" at the join line.
     */
    mix-blend-mode: luminosity;
}
/*
 * Veil: controls how strongly the photo texture shows.
 * Uses a neutral semi-transparent green that matches the approximate
 * background colour at the 70-100% zone of the splash gradient,
 * then fades to fully transparent at the bottom.
 * Because the images use mix-blend-mode:luminosity, the veil colour
 * merges continuously with the surrounding green — no hard edge.
 */
.splash-bg-photos-veil {
    position:absolute; inset:0; pointer-events:none;
    background:linear-gradient(
        to bottom,
        rgba(20,112,58,1)   0%,   /* ~mid-green of splash at ~70% height */
        rgba(18,104,54,.96) 8%,
        rgba(17, 96,50,.90) 16%,
        rgba(16, 88,46,.80) 26%,
        rgba(15, 78,42,.65) 38%,
        rgba(14, 66,36,.45) 52%,
        rgba(13, 55,30,.22) 68%,
        rgba(12, 46,25,.07) 84%,
        transparent         100%
    );
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   TICKER
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#ticker {
    background:linear-gradient(90deg, #1B8A4E, #16703E);
    padding:.3rem .875rem; display:flex; align-items:center; gap:.6rem;
    flex-shrink:0; overflow:hidden; white-space:nowrap;
}
#ticker .lbl {
    background:var(--g);
    color:#fff; font-size:.55rem; font-weight:700;
    letter-spacing:.12em; text-transform:uppercase;
    padding:.2em .6em; border-radius:4px; flex-shrink:0;
}
#ticker-track { flex:1; overflow:hidden; }
#ticker-txt   { display:inline-block; white-space:nowrap; font-size:.68rem; color:rgba(255,255,255,.9); font-weight:600; animation:tick 38s linear infinite; }
@keyframes tick { 0%{transform:translateX(100vw)} 100%{transform:translateX(-100%)} }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   HEADER
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#app-header {
    height:var(--head); flex-shrink:0;
    background:linear-gradient(135deg, #1F9658 0%, #1B8A4E 50%, #155E38 100%);
    display:flex; align-items:center; padding:0 1rem; gap:.75rem;
    box-shadow:0 3px 16px rgba(0,0,0,.18);
    text-decoration:none;
    cursor:pointer;
}
.hdr-logo-ring {
    width:38px; height:38px; border-radius:50%; flex-shrink:0;
    border:2px solid rgba(255,255,255,.6);
    box-shadow:0 0 14px rgba(255,255,255,.25);
    background:rgba(255,255,255,.12); padding:2px;
    transition:box-shadow .3s;
}
.hdr-logo-ring img { width:100%; height:100%; border-radius:50%; object-fit:cover; }
.hdr-info  { flex:1; min-width:0; }
.hdr-title { font-weight:700; font-size:.88rem; letter-spacing:.01em; line-height:1.2; color:#fff; }
.hdr-sub   { font-size:.6rem; color:rgba(255,255,255,.65); margin-top:.05rem; letter-spacing:.03em; }
.hdr-badges { display:flex; align-items:center; gap:.4rem; flex-shrink:0; }
.hdr-edition {
    background:linear-gradient(135deg,var(--g),var(--g2));
    color:#fff; font-size:.58rem; font-weight:700;
    padding:.28em .7em; border-radius:20px;
    text-transform:uppercase; letter-spacing:.08em; white-space:nowrap;
    box-shadow:0 2px 8px rgba(0,0,0,.2);
}
.live-ring {
    width:26px; height:26px; border-radius:50%;
    background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.5);
    display:flex; align-items:center; justify-content:center;
}
.live-ring .dot { width:8px; height:8px; border-radius:50%; background:#fff; animation:blink 1s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.65)} }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   PAGE CONTENT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#page-content {
    flex:1; overflow-y:auto; overflow-x:hidden;
    -webkit-overflow-scrolling:touch; scroll-behavior:smooth;
    padding-bottom:var(--bnav);
}
#page-content::-webkit-scrollbar { display:none; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   BOTTOM NAV
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#bottom-nav {
    position:absolute; bottom:0; left:0; right:0;
    height:var(--bnav);
    background:#fff;
    border-top:1px solid var(--bd);
    display:grid; grid-template-columns:repeat(5,1fr);
    z-index:100; padding-bottom:env(safe-area-inset-bottom);
    box-shadow:0 -4px 20px rgba(0,0,0,.08);
}
.nav-item {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:.15rem; text-decoration:none; color:#A0B8A6;
    transition:color .2s, transform .15s; position:relative; padding:.35rem 0;
}
.nav-item:active { transform:scale(.9); }
.nav-item.active { color:var(--p); }
.nav-item.active::before {
    content:''; position:absolute; top:0; left:18%; right:18%; height:2.5px;
    background:linear-gradient(90deg,transparent,var(--p),transparent);
    border-radius:0 0 4px 4px;
}
.nav-item i    { font-size:1.15rem; }
.nav-item span { font-size:.54rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
.nav-live-dot  {
    position:absolute; top:.32rem; right:24%;
    width:6px; height:6px; border-radius:50%; background:var(--red);
    animation:blink 1s infinite;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   LAYOUT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.sec     { padding:1.1rem 1rem; }
.sec-hd  { display:flex; align-items:center; justify-content:space-between; margin-bottom:.875rem; }
.sec-title { font-weight:700; font-size:.9rem; display:flex; align-items:center; gap:.45rem; color:var(--txt); }
.sec-ico {
    width:28px; height:28px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:.88rem;
}
.sec-link { font-size:.72rem; color:var(--p); text-decoration:none; font-weight:700; }

.divider {
    height:1px; margin:0 1rem;
    background:linear-gradient(90deg, transparent, var(--bd), transparent);
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   CARDS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.card {
    background:linear-gradient(145deg,#ffffff 0%,#F2FAF5 55%,#EBF5F0 100%);
    border:1px solid rgba(27,138,78,.15);
    border-radius:16px; overflow:hidden;
    box-shadow:0 2px 14px rgba(27,138,78,.09), 0 1px 4px rgba(0,0,0,.04);
}
.card-body { padding:1rem; }
.card-hover {
    transition: border-color .25s ease,
                transform .22s cubic-bezier(.34,1.56,.64,1),
                box-shadow .25s ease;
    cursor:pointer;
}
.card-hover:hover, .card-hover:active {
    border-color:rgba(27,138,78,.5);
    transform:translateY(-5px);
    box-shadow:0 16px 36px rgba(27,138,78,.18), 0 4px 10px rgba(0,0,0,.06);
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   MATCH CARDS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.match-card {
    background:linear-gradient(145deg,#ffffff 0%,#F2FAF6 100%);
    border:1px solid rgba(27,138,78,.14);
    border-radius:16px; padding:.9rem; margin-bottom:.625rem;
    box-shadow:0 2px 12px rgba(27,138,78,.08), 0 1px 3px rgba(0,0,0,.04);
}
.match-card-live {
    background:linear-gradient(135deg,#FFF2F2 0%,#FFE8E8 55%,#FFF5F5 100%);
    border-color:rgba(220,38,38,.32);
    box-shadow:0 4px 20px rgba(220,38,38,.14);
}
.match-card-upcoming {
    background:linear-gradient(135deg,#FFFCF0 0%,#FFF6D6 55%,#FFFBF0 100%);
    border-color:rgba(212,144,10,.28);
    box-shadow:0 2px 12px rgba(212,144,10,.1);
}
.live-badge {
    display:inline-flex; align-items:center; gap:.3rem;
    background:rgba(220,38,38,.1); color:var(--red);
    font-size:.6rem; font-weight:700; padding:.22em .7em; border-radius:20px;
    text-transform:uppercase; letter-spacing:.07em; border:1px solid rgba(220,38,38,.25);
}
.live-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--red); animation:blink 1s infinite; }
.teams-row   { display:flex; align-items:center; justify-content:space-between; gap:.5rem; margin:.45rem 0; }
.team-name   { font-weight:700; font-size:.86rem; flex:1; color:var(--txt); }
.team-score  { font-size:1.2rem; font-weight:700; font-variant-numeric:tabular-nums; color:var(--p); }
.vs-pill     { background:var(--s3); border:1px solid var(--bd); padding:.22em .65em; border-radius:20px; font-size:.62rem; font-weight:700; color:var(--mut); }
.match-meta  { font-size:.67rem; color:var(--mut); display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   STAT PILLS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.stat-pill {
    background:linear-gradient(145deg,#ffffff 0%,#EFF8F3 100%);
    border:1px solid rgba(27,138,78,.15);
    border-radius:14px; padding:.8rem 1rem;
    text-align:center; flex-shrink:0;
    box-shadow:0 2px 10px rgba(27,138,78,.09), 0 1px 3px rgba(0,0,0,.04);
}
.stat-pill .val { font-size:1.45rem; font-weight:700; line-height:1; }
.stat-pill .lbl { font-size:.58rem; color:var(--mut); margin-top:.28rem; text-transform:uppercase; letter-spacing:.07em; }
.stat-row { display:flex; gap:.5rem; overflow-x:auto; padding:.875rem 1rem .25rem; }
.stat-row::-webkit-scrollbar { display:none; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   POLL
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.poll-opt {
    background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15);
    border-radius:12px; padding:.65rem .9rem; margin-bottom:.5rem; cursor:pointer;
    position:relative; overflow:hidden; color:#fff;
    transition:border-color .2s, transform .15s, background .2s;
}
.poll-opt:hover { border-color:rgba(77,235,160,.6); transform:translateX(3px); background:rgba(255,255,255,.12); }
.poll-bar  { position:absolute; left:0; top:0; bottom:0; background:linear-gradient(90deg,rgba(77,235,160,.28),rgba(27,138,78,.1)); transition:width .7s cubic-bezier(.4,0,.2,1); }
.poll-label { position:relative; font-size:.83rem; font-weight:600; display:flex; align-items:center; justify-content:space-between; gap:.5rem; color:#fff; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   LEADERBOARD
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.leader-row { display:flex; align-items:center; gap:.75rem; padding:.65rem .9rem; border-bottom:1px solid var(--bd); transition:background .2s; }
.leader-row:last-child { border-bottom:none; }
.leader-row:hover { background:var(--s3); }
.leader-rank  { font-size:.77rem; font-weight:700; color:var(--mut); width:22px; flex-shrink:0; }
.leader-rank.gold { color:var(--g); }
.leader-avatar { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; flex-shrink:0; overflow:hidden; padding:0; }
.leader-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; display:block; }
.leader-name  { font-size:.83rem; font-weight:700; color:var(--txt); }
.leader-sub   { font-size:.65rem; color:var(--mut); }
.leader-val   { font-size:1.05rem; font-weight:700; color:var(--p); }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   SCORECARD TABLE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.sc-table { width:100%; border-collapse:collapse; font-size:.77rem; }
.sc-table thead th { background:var(--s3); padding:.45rem .5rem; font-weight:700; font-size:.62rem; color:var(--mut); text-transform:uppercase; letter-spacing:.05em; }
.sc-table tbody td { padding:.5rem; border-bottom:1px solid var(--bd); vertical-align:middle; color:var(--txt); }
.sc-table .not-out { color:var(--p); font-weight:700; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   POINTS TABLE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.pts-table { width:100%; border-collapse:collapse; font-size:.77rem; }
.pts-table thead th { background:var(--s3); padding:.45rem .5rem; font-weight:700; font-size:.62rem; color:var(--mut); text-transform:uppercase; text-align:center; }
.pts-table thead th:first-child { text-align:left; }
.pts-table tbody td { padding:.5rem; border-bottom:1px solid var(--bd); text-align:center; color:var(--txt); }
.pts-table tbody td:first-child { text-align:left; }
.pts-table .rank { font-weight:700; color:var(--mut); }
.pts-table .nrr-pos { color:var(--p); font-weight:700; }
.pts-table .nrr-neg { color:var(--red); font-weight:700; }
.team-cell { display:flex; align-items:center; gap:.4rem; white-space:nowrap; }
.team-badge-sm { width:22px; height:22px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.5rem; color:#fff; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   PILLS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.pill       { display:inline-block; padding:.22em .7em; border-radius:20px; font-size:.62rem; font-weight:700; }
.pill-green { background:rgba(27,138,78,.12);  color:var(--p); border:1px solid rgba(27,138,78,.22); }
.pill-gold  { background:rgba(212,144,10,.12); color:var(--g); border:1px solid rgba(212,144,10,.22); }
.pill-red   { background:rgba(220,38,38,.1);   color:var(--red); border:1px solid rgba(220,38,38,.2); }
.pill-muted { background:rgba(107,143,116,.1); color:var(--mut); border:1px solid rgba(107,143,116,.2); }
.pill-blue  { background:rgba(2,132,199,.1);   color:var(--blue); border:1px solid rgba(2,132,199,.2); }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   EDITION TABS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.ed-tabs { display:flex; border-bottom:1px solid var(--bd); padding:0 .5rem; gap:.1rem; overflow-x:auto; background:#fff; }
.ed-tabs::-webkit-scrollbar { display:none; }
.ed-tab { padding:.6rem .75rem; border:none; background:none; color:var(--mut); font-size:.77rem; font-weight:600; cursor:pointer; border-bottom:2.5px solid transparent; white-space:nowrap; transition:color .2s, border-color .2s; flex-shrink:0; }
.ed-tab.active { color:var(--p); border-bottom-color:var(--p); }
.ed-panel { display:none; }
.ed-panel.active { display:block; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   PLAYER PAGE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.player-header {
    padding:1.5rem 1rem 1.1rem; text-align:center;
    background:linear-gradient(180deg, rgba(27,138,78,.1) 0%, transparent 100%);
    border-bottom:1px solid var(--bd);
}
.player-avatar { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--p); margin:0 auto; display:block; box-shadow:var(--gp); }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   GRADIENT TEXT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.grad { background:linear-gradient(135deg,var(--p) 0%,#1BA460 50%,var(--g) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   TOAST
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
#toast-wrap { position:absolute; top:.75rem; left:.75rem; right:.75rem; z-index:9998; }
.toast { background:#fff; border:1px solid var(--bd); border-radius:12px; padding:.65rem .9rem; margin-bottom:.5rem; font-size:.8rem; display:flex; align-items:center; gap:.5rem; animation:slide-in .3s ease; box-shadow:0 4px 16px rgba(0,0,0,.1); }
.toast.ok  { border-color:rgba(27,138,78,.4); color:var(--p); }
.toast.err { border-color:rgba(220,38,38,.4); color:var(--red); }
@keyframes slide-in { from{opacity:0;transform:translateY(-12px)} to{opacity:1;transform:none} }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   UTILITIES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.hide-scroll { -webkit-overflow-scrolling:touch; scrollbar-width:none; }
.hide-scroll::-webkit-scrollbar { display:none; }
.fw7 { font-weight:700; }
.c-p { color:var(--p); }
.c-g { color:var(--g); }
.c-m { color:var(--mut); }
.c-r { color:var(--red); }

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   SECTION ICON
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.sec-ico {
    width:28px; height:28px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:.88rem;
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   SHARE BUTTON
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
.btn-share {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.4rem .9rem; border-radius:20px; border:1px solid rgba(27,138,78,.3);
    background:rgba(27,138,78,.08); color:var(--p);
    font-size:.75rem; font-weight:700; cursor:pointer;
    transition:background .2s, box-shadow .2s;
    font-family:inherit;
}
.btn-share:hover { background:rgba(27,138,78,.15); box-shadow:var(--gp); }
.btn-share:active { transform:scale(.97); }
.btn-share i { font-size:.85rem; }

/* Share card overlay (used only for html2canvas capture) */
#rcl-share-wrap {
    position:fixed; top:-9999px; left:0; z-index:-999;
    pointer-events:none;
}
#rcl-share-inner {
    width:400px;
    background:#fff;
    font-family:system-ui,-apple-system,'Segoe UI',sans-serif;
    border-radius:0;
    overflow:hidden;
}
    </style>
    @stack('styles')
</head>
<body>
<div id="app-shell">

    {{-- ── App-shell watermark layer (logo only) ─────────── --}}
    <div class="wm-layer" aria-hidden="true">
        <img src="/logo.jfif" class="wml-logo" alt="">
    </div>

    {{-- ── Heritage photo strip (above bottom nav) ─────── --}}
    <div class="heritage-strip" aria-hidden="true">
        <img src="/images/watermarks/imran-khan.jpg"   alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/wc1992.jpg"        alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/pakistan-cricket.jpg" alt="" onerror="this.style.opacity='0'">
        <img src="/images/watermarks/green-caps.jpg"    alt="" onerror="this.style.opacity='0'">
    </div>
    {{-- Developed by credit --}}
    <div class="dev-credit" aria-hidden="true">Developed by Sarfraz Jutt</div>

    {{-- ── Splash loader ──────────────────────────────── --}}
    <div id="splash">

        {{-- Photos blended into the green gradient (z-index:0, behind everything) --}}
        <div class="splash-bg-photos" aria-hidden="true">
            <div class="splash-bg-photos-imgs">
                <img src="/images/watermarks/imran-khan.jpg"  alt=""
                     onerror="this.style.opacity='0'">
                <img src="/images/watermarks/imran-khan-2.jpg" alt=""
                     onerror="this.style.opacity='0'">
            </div>
            <div class="splash-bg-photos-veil"></div>
        </div>

        {{-- Dual-ring spinner with logo inside --}}
        <div class="splash-ring">
            <img src="/logo.jfif" alt="RCL" class="splash-logo-img">
        </div>

        {{-- App name --}}
        <div style="text-align:center;">
            <div class="splash-name">Royal Champions League</div>
            <div class="splash-sub">Village Cricket Council · Pakistan</div>
        </div>

        {{-- Animated loading dots --}}
        <div class="splash-dots">
            <span></span><span></span><span></span>
        </div>

        {{-- Credit --}}
        <div class="splash-devby">Developed by Sarfraz Jutt</div>
    </div>

    {{-- ── Ticker ──────────────────────────────────────── --}}
    <div id="ticker">
        <span class="lbl">Live</span>
        <div id="ticker-track"><span id="ticker-txt">Loading updates…</span></div>
    </div>

    {{-- ── Header ──────────────────────────────────────── --}}
    {{-- Outer div is clickable to home; inner admin button avoids nested <a> --}}
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
                        style="color:rgba(255,255,255,.85);font-size:1.05rem;background:none;border:none;
                               cursor:pointer;padding:.3rem;line-height:1;display:flex;align-items:center;">
                    <i class="bi bi-shield-fill"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- ── Toast container ────────────────────────────── --}}
    <div id="toast-wrap"></div>

    {{-- ── Share card (off-screen, captured by html2canvas) ── --}}
    <div id="rcl-share-wrap" aria-hidden="true">
        <div id="rcl-share-inner">
            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#0F3A1E 0%,#1B8A4E 50%,#127040 100%);padding:14px 18px;display:flex;align-items:center;gap:12px;">
                <img src="/logo.jfif" alt="RCL" style="width:46px;height:46px;border-radius:50%;border:2px solid rgba(255,255,255,.7);object-fit:cover;flex-shrink:0;">
                <div>
                    <div style="color:#fff;font-size:15px;font-weight:800;letter-spacing:.02em;line-height:1.2;">Royal Champions League</div>
                    <div style="color:rgba(255,255,255,.62);font-size:9px;letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Village Cricket Council · Pakistan</div>
                </div>
                <div id="share-card-label" style="margin-left:auto;background:rgba(212,144,10,.9);color:#fff;font-size:8.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:3px 9px;border-radius:20px;flex-shrink:0;"></div>
            </div>
            {{-- Body --}}
            <div id="share-card-body" style="background:#F0F7F2;min-height:80px;"></div>
            {{-- Footer --}}
            <div style="background:linear-gradient(90deg,#0F3A1E,#1B8A4E);padding:8px 18px;display:flex;align-items:center;justify-content:space-between;">
                <div style="color:rgba(255,255,255,.55);font-size:8px;letter-spacing:.1em;text-transform:uppercase;font-weight:600;">RCL Official App</div>
                <div style="color:rgba(255,255,255,.35);font-size:7.5px;">Generated by RCL App</div>
            </div>
        </div>
    </div>

    {{-- ── Page content ───────────────────────────────── --}}
    <div id="page-content">
        @yield('content')
    </div>

    {{-- ── Bottom Nav ──────────────────────────────────── --}}
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
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
$.ajaxSetup({ headers:{ 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content') } });

/* ── Share as Picture ─────────────────────────────── */
async function shareRCL(title, bodyHtml, label) {
    document.getElementById('share-card-label').textContent = label || 'RCL';
    document.getElementById('share-card-body').innerHTML = bodyHtml;

    var wrap = document.getElementById('rcl-share-wrap');
    /* Briefly move on-screen (same-origin images still need to be "rendered") */
    wrap.style.top = '0';
    wrap.style.left = '-9999px';
    wrap.style.zIndex = '-999';

    try {
        var canvas = await html2canvas(document.getElementById('rcl-share-inner'), {
            scale: 2.5,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#F0F7F2',
            logging: false,
        });
        wrap.style.top = '-9999px';

        canvas.toBlob(async function(blob) {
            var file = new File([blob], 'rcl-share.png', { type: 'image/png' });
            if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({ title: 'Royal Champions League', text: title, files: [file] });
                } catch(e) { /* user cancelled — ok */ }
            } else {
                /* Fallback: download */
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url; a.download = 'rcl-share.png'; a.click();
                setTimeout(function(){ URL.revokeObjectURL(url); }, 1500);
                showToast('Image downloaded!', 'ok');
            }
        }, 'image/png');
    } catch(e) {
        wrap.style.top = '-9999px';
        console.error(e);
        showToast('Could not generate image', 'err');
    }
}

// Redirect wheel/trackpad scroll to #page-content when cursor is over shell chrome
(function() {
    var shell = document.getElementById('app-shell');
    var pc    = document.getElementById('page-content');
    if (!shell || !pc) return;
    shell.addEventListener('wheel', function(e) {
        if (!pc.contains(e.target)) {
            pc.scrollTop += e.deltaY;
            e.preventDefault();
        }
    }, { passive: false });
})();

// Splash
setTimeout(function(){ $('#splash').addClass('hidden'); setTimeout(function(){ $('#splash').hide(); },650); }, 2000);

// Ticker
function loadTicker() {
    $.get('/api/ticker', function(items) {
        if (items && items.length) $('#ticker-txt').text(items.join('   ·   '));
    });
}
loadTicker(); setInterval(loadTicker, 30000);

// Toast
function showToast(msg, type) {
    var icon = type==='ok' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    var t = $('<div class="toast '+(type||'ok')+'"><i class="bi '+icon+'"></i> '+msg+'</div>');
    $('#toast-wrap').append(t);
    setTimeout(function(){ t.fadeOut(400, function(){ t.remove(); }); }, 3500);
}
@if(session('success')) $(function(){ showToast(@json(session('success')), 'ok'); }); @endif
@if(session('error'))   $(function(){ showToast(@json(session('error')), 'err'); }); @endif
</script>
@stack('scripts')
</body>
</html>
