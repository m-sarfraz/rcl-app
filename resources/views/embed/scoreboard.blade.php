{{--
    RCL live scoreboard — every embeddable surface in one self-contained file.

    Deliberately one file with no layout, no build step and no external asset:
    it has to work as an <iframe> on someone else's site *and* as a browser
    source in PRISM Live Studio or OBS, where nothing but this HTML is loaded.

    Layouts
      broadcast  1920×180   full bottom bar — the professional look
      lower       900×170   compact lower third
      bug         420×150   corner score bug, TV style
      scorecard  1920×1080  full-screen, for between overs
      card        560×400   website embed
      ticker      900×56    thin strip

    It polls its own JSON endpoint and repaints only when the revision
    fingerprint moves, so it never flickers between deliveries.
--}}
<!DOCTYPE html>
<html lang="en" data-layout="{{ $options['layout'] }}"@if($options['theme'] !== 'auto') data-theme="{{ $options['theme'] }}"@endif>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ $data['teams']['home']['short'] }} v {{ $data['teams']['away']['short'] }} — live score</title>
<style>
:root {
    --accent:  {{ $options['accent'] }};
    --home:    {{ $data['teams']['home']['colour'] }};
    --away:    {{ $data['teams']['away']['colour'] }};

    --ink:     #0B1220;
    --muted:   #64748B;
    --line:    #E6EDF5;
    --surface: #FFFFFF;
    --canvas:  #F6F8FB;

    --live:    #EF4444;
    --gold:    #F59E0B;
    --sky:     #0EA5E9;
    --violet:  #7C5CFC;
    --green:   #10B981;

    /* Broadcast panels sit over video, so they are dark whatever the theme. */
    --panel:      rgba(9, 17, 28, 0.94);
    --panel-2:    rgba(15, 27, 43, 0.94);
    --panel-text: #FFFFFF;
    --panel-mute: rgba(255, 255, 255, 0.62);
    --panel-line: rgba(255, 255, 255, 0.14);
}

@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        --ink: #F8FAFC; --muted: #94A3B8; --line: #1E293B;
        --surface: #0F172A; --canvas: #020617;
    }
}
:root[data-theme="dark"] {
    --ink: #F8FAFC; --muted: #94A3B8; --line: #1E293B;
    --surface: #0F172A; --canvas: #020617;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    font-variant-numeric: tabular-nums;
    color: var(--ink);
    background: {{ $options['transparent'] ? 'transparent' : 'var(--canvas)' }};
}

/* ══ shared atoms ══════════════════════════════════════════════ */

.pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 999px;
    font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
    white-space: nowrap;
}
.pill.live   { background: var(--live); color: #fff; }
.pill.result { background: rgba(16,185,129,.16); color: var(--green); }
.pill.idle   { background: rgba(100,116,139,.16); color: var(--muted); }

.dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex: 0 0 auto; }
.pill.live .dot { background: #fff; animation: pulse 1.3s ease-in-out infinite; }
@keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .3; transform: scale(.7); } }

.crest {
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; letter-spacing: -.4px; color: #fff; flex: 0 0 auto;
    border-radius: 12px; overflow: hidden;
}
.crest img { width: 100%; height: 100%; object-fit: cover; }

.balls { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.ball {
    min-width: 26px; height: 26px; padding: 0 6px; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
    border: 1.5px solid var(--line); color: var(--muted); background: var(--surface);
    animation: pop .22s ease-out;
}
@keyframes pop { from { transform: scale(.6); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.ball.run    { border-color: var(--green);  color: var(--green);  background: rgba(16,185,129,.10); }
.ball.four   { border-color: var(--sky);    color: var(--sky);    background: rgba(14,165,233,.12); }
.ball.six    { border-color: var(--gold);   color: var(--gold);   background: rgba(245,158,11,.14); }
.ball.wicket { border-color: var(--live);   color: var(--live);   background: rgba(239,68,68,.14); }
.ball.extra  { border-color: var(--violet); color: var(--violet); background: rgba(124,92,252,.10); }

/* On a dark broadcast panel the chips invert. */
.panel .ball { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.22); color: #fff; }
.panel .ball.four   { border-color: var(--sky);   color: #7DD3FC; background: rgba(14,165,233,.22); }
.panel .ball.six    { border-color: var(--gold);  color: #FCD34D; background: rgba(245,158,11,.26); }
.panel .ball.wicket { border-color: var(--live);  color: #FCA5A5; background: rgba(239,68,68,.28); }
.panel .ball.extra  { border-color: var(--violet);color: #C4B5FD; background: rgba(124,92,252,.22); }
.panel .ball.dot    { opacity: .6; }

/* A short flash when a boundary or wicket lands — the broadcast tell. */
.flash { animation: flash .9s ease-out; }
@keyframes flash {
    0%   { box-shadow: inset 0 0 0 0 rgba(255,255,255,0); }
    18%  { box-shadow: inset 0 0 90px 0 var(--flash-colour, rgba(245,158,11,.55)); }
    100% { box-shadow: inset 0 0 0 0 rgba(255,255,255,0); }
}

.fade { transition: opacity .16s ease; }
.fade.out { opacity: .45; }

/* ══ broadcast — the full bottom bar (White, Blue, Navy, Red Gradients) ═════════════════════ */

[data-layout="broadcast"] body,
[data-layout="lower"] body { padding: 0; }
[data-layout="broadcast"] .board,
[data-layout="lower"] .board {
    display: flex; align-items: center;
    height: 140px; max-width: 1920px;
    border-radius: 16px; overflow: hidden;
    background: #FFFFFF;
    box-shadow: 0 16px 48px rgba(0,0,0,.5);
    border: 1px solid #CBD5E1;
    padding: 0 6px;
}

/* 1. Left & 5. Right Team Badges (Gradients: Royal Blue to Navy) */
.bc-team-badge {
    width: 200px; flex: 0 0 auto;
    height: calc(100% - 12px);
    display: flex; justify-content: center; align-items: center;
    padding: 0 16px; color: #FFFFFF;
    border-radius: 12px;
}
.bc-team-left {
    background: linear-gradient(135deg, #1E40AF 0%, #1D4ED8 50%, #0F172A 100%);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
}
.bc-team-right {
    background: linear-gradient(135deg, #0F172A 0%, #1D4ED8 50%, #1E40AF 100%);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
}
.bc-team-badge .code {
    font-size: 38px; font-weight: 900; letter-spacing: 1px; line-height: 1;
    text-transform: uppercase; text-shadow: 0 2px 4px rgba(0,0,0,0.4);
    text-align: center;
}

/* 2. Current Batters Section (White BG, Increased Font Sizes) */
.bc-batters {
    flex: 1.25; min-width: 0;
    height: 100%;
    display: flex; flex-direction: column; justify-content: center; gap: 10px;
    padding: 0 20px;
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
    color: #0F172A;
    border-right: 1px solid #F1F5F9;
}
.bc-batter {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 21px; font-weight: 800;
}
.bc-batter .name {
    display: flex; align-items: center; gap: 8px; color: #0F172A;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 230px;
}
.bc-batter.striker .name::before {
    content: '▶'; color: #DC2626; font-size: 15px; margin-right: 2px;
}
.bc-batter .score { font-size: 23px; font-weight: 900; color: #0F172A; }
.bc-batter .balls { font-size: 16px; font-weight: 700; color: #64748B; margin-left: 4px; }

/* 3. Center Red Capsule (Total Score & Overs & Target, Rounded Corners) */
.bc-score-capsule {
    flex: 1.5; min-width: 0;
    height: calc(100% - 14px);
    display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 4px;
    padding: 6px 24px;
    margin: 0 8px;
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 50%, #991B1B 100%);
    color: #FFFFFF;
    border-radius: 20px;
    box-shadow: 0 6px 18px rgba(220,38,38,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
    border: 1.5px solid rgba(254,202,202,0.45);
}
.bc-score-main { display: flex; align-items: baseline; justify-content: center; gap: 8px; }
.bc-score-team { font-size: 24px; font-weight: 900; color: #FEF08A; letter-spacing: 0.5px; }
.bc-score-val  { font-size: 46px; font-weight: 900; letter-spacing: -1.2px; line-height: 1; color: #FFFFFF; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.bc-score-ov   { font-size: 20px; font-weight: 800; color: #FEF08A; }
.bc-score-sub  { font-size: 13.5px; font-weight: 800; color: #FFFFFF; letter-spacing: 0.4px; text-shadow: 0 1px 2px rgba(0,0,0,0.4); text-transform: uppercase; }

/* 4. Bowler Figures & Current Over Section */
.bc-bowler {
    flex: 1.25; min-width: 0;
    height: 100%;
    display: flex; flex-direction: column; justify-content: center; gap: 8px;
    padding: 0 20px;
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
    color: #0F172A;
    border-left: 1px solid #F1F5F9;
}
.bc-bowler-top {
    display: flex; align-items: baseline; justify-content: space-between;
}
.bc-bowler-top .name {
    font-size: 21px; font-weight: 800; color: #0F172A;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;
}
.bc-bowler-top .fig { font-size: 23px; font-weight: 900; color: #1D4ED8; }
.bc-bowler-top .ov  { font-size: 16px; font-weight: 700; color: #64748B; margin-left: 4px; }
.bc-bowler-balls {
    display: flex; align-items: center; gap: 6px;
}
.bc-bowler-balls.empty-over {
    font-size: 14px; font-weight: 700; color: #64748B;
}

/* ══ bug — corner score ════════════════════════════════════════ */

[data-layout="bug"] body { padding: 0; }
[data-layout="bug"] .board {
    display: inline-block; width: 400px;
    border-radius: 12px; overflow: hidden;
    background: var(--panel); color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,.4);
}
.bug-row { display: flex; align-items: center; gap: 10px; padding: 9px 14px; }
.bug-row + .bug-row { border-top: 1px solid var(--panel-line); }
.bug-row.batting { background: linear-gradient(90deg, var(--team-colour, var(--accent)) 0%, transparent 70%); }
.bug-row .code  { font-size: 15px; font-weight: 900; width: 52px; letter-spacing: .5px; }
.bug-row .score { flex: 1; text-align: right; font-size: 20px; font-weight: 900; letter-spacing: -.5px; }
.bug-row .overs { font-size: 11px; font-weight: 700; color: var(--panel-mute); width: 42px; text-align: right; }
.bug-foot {
    padding: 7px 14px; background: rgba(0,0,0,.32);
    font-size: 11px; font-weight: 700; color: #FCD34D;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ══ scorecard — full screen between overs ═════════════════════ */

[data-layout="scorecard"] body { padding: 0; }
[data-layout="scorecard"] .board {
    min-height: 100vh; padding: 46px 60px;
    background: linear-gradient(140deg, #0B7F5F 0%, #064E3B 55%, #052E1F 100%);
    color: #fff; display: flex; flex-direction: column; gap: 26px;
}
.sc-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; }
.sc-head .title { font-size: 34px; font-weight: 900; letter-spacing: -.8px; }
.sc-head .meta  { font-size: 15px; color: rgba(255,255,255,.66); margin-top: 5px; font-weight: 600; }
.sc-scores { display: flex; gap: 20px; flex-wrap: wrap; }
.sc-score {
    flex: 1; min-width: 300px;
    background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.14);
    border-radius: 18px; padding: 22px 26px;
}
.sc-score.batting { background: rgba(255,255,255,.16); border-color: rgba(255,255,255,.32); }
.sc-score .club   { display: flex; align-items: center; gap: 13px; margin-bottom: 12px; }
.sc-score .crest  { width: 46px; height: 46px; font-size: 15px; background: rgba(255,255,255,.2); }
.sc-score .club b { font-size: 19px; font-weight: 800; }
.sc-score .runs   { font-size: 56px; font-weight: 900; letter-spacing: -2.2px; line-height: 1; }
.sc-score .ov     { font-size: 17px; font-weight: 700; color: rgba(255,255,255,.7); margin-left: 10px; }
.sc-score .rate   { font-size: 13px; color: rgba(255,255,255,.62); margin-top: 8px; font-weight: 600; }

.sc-panels { display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start; }
.sc-panel {
    flex: 1; min-width: 320px; align-self: stretch;
    background: rgba(0,0,0,.24); border: 1px solid rgba(255,255,255,.1);
    border-radius: 18px; padding: 20px 24px;
}
.sc-panel h3 {
    font-size: 11px; font-weight: 800; letter-spacing: 1.4px;
    text-transform: uppercase; color: rgba(255,255,255,.55); margin-bottom: 14px;
}
.sc-line { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; padding: 8px 0; }
.sc-line + .sc-line { border-top: 1px solid rgba(255,255,255,.08); }
.sc-line .who { font-size: 17px; font-weight: 700; }
.sc-line .who small { display: block; font-size: 11px; color: rgba(255,255,255,.5); font-weight: 600; margin-top: 2px; }
.sc-line .val { font-size: 21px; font-weight: 900; white-space: nowrap; }
.sc-line .val small { font-size: 12px; font-weight: 700; color: rgba(255,255,255,.6); }

.sc-chase {
    background: rgba(245,158,11,.2); border: 1px solid rgba(245,158,11,.45);
    border-radius: 18px; padding: 20px 26px; text-align: center;
}
.sc-chase .big { font-size: 28px; font-weight: 900; color: #FCD34D; }
.sc-chase .small { font-size: 14px; color: rgba(255,255,255,.72); margin-top: 5px; font-weight: 600; }
.sc-foot {
    margin-top: auto;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    font-size: 13px; color: rgba(255,255,255,.55); font-weight: 600;
    border-top: 1px solid rgba(255,255,255,.12); padding-top: 18px;
}

/* ══ card — website embed ══════════════════════════════════════ */

[data-layout="card"] body { padding: 10px; }
[data-layout="card"] .wrap { max-width: 620px; margin: 0 auto; }
[data-layout="card"] .board {
    background: var(--surface); border: 1px solid var(--line);
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 8px 28px rgba(11,18,32,.08);
}
.cd-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 11px 16px; color: #fff;
    background: var(--accent);
    background: linear-gradient(120deg, var(--home), var(--away));
}
.cd-head .meta { font-size: 11px; font-weight: 700; letter-spacing: .4px; opacity: .95; }
.cd-head .pill { background: rgba(255,255,255,.26); color: #fff; }
.cd-head .pill.live { background: var(--live); }

.cd-side { display: flex; align-items: center; gap: 12px; padding: 13px 16px; }
.cd-side + .cd-side { border-top: 1px solid var(--line); }
.cd-side .crest { width: 40px; height: 40px; font-size: 13px; }
.cd-side .name  { flex: 1; min-width: 0; }
.cd-side .name b { display: block; font-size: 14px; font-weight: 700; }
.cd-side .name span { display: block; font-size: 11px; color: var(--muted); margin-top: 1px; }
.cd-side .score { text-align: right; white-space: nowrap; }
.cd-side .score b { font-size: 21px; font-weight: 800; letter-spacing: -.6px; }
.cd-side .score span { display: block; font-size: 11px; color: var(--muted); }
.cd-side.batting .score b { color: var(--green); }

.cd-strip { padding: 12px 16px; border-top: 1px solid var(--line);
            background: var(--canvas); display: flex; flex-direction: column; gap: 10px; }
.cd-headline { font-size: 13.5px; font-weight: 800; }
.cd-chase { font-size: 12.5px; font-weight: 700; color: var(--gold); }
.cd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 18px; }
.cd-item { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; font-size: 12px; }
.cd-item .k { color: var(--muted); font-weight: 600; }
.cd-item .v { font-weight: 800; }
.cd-players { display: flex; flex-direction: column; gap: 5px; }
.cd-players .p { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; font-size: 12.5px; }
.cd-players .p .n { font-weight: 700; }
.cd-players .p .n em { font-style: normal; color: var(--green); }
.cd-players .p .f { font-weight: 800; color: var(--muted); }
.cd-foot { padding: 9px 16px; border-top: 1px solid var(--line);
           display: flex; align-items: center; justify-content: space-between; gap: 8px;
           font-size: 10.5px; color: var(--muted); }
.cd-foot a { color: var(--green); text-decoration: none; font-weight: 700; }

/* ══ ticker ════════════════════════════════════════════════════ */

[data-layout="ticker"] body { padding: 0; }
[data-layout="ticker"] .board {
    display: flex; align-items: center; gap: 14px;
    padding: 10px 16px; background: var(--surface);
    border-bottom: 2px solid var(--accent);
    font-size: 13px; font-weight: 700; white-space: nowrap;
    overflow-x: auto; scrollbar-width: none;
}
[data-layout="ticker"] .board::-webkit-scrollbar { display: none; }
.tk-sep { width: 1px; height: 16px; background: var(--line); flex: 0 0 auto; }
.tk-mute { color: var(--muted); font-weight: 600; }
</style>
</head>
<body>
<div class="wrap"><div id="board" class="board fade"></div></div>

<script>
(function () {
    'use strict';

    var STATE_URL = @json($stateUrl);
    var LAYOUT    = @json($options['layout']);
    var REFRESH   = @json($options['refresh']) * 1000;
    var SHOW_LOGO = @json($options['showLogo']);
    var COMPACT   = @json($options['compact']);
    var SHARE_URL = @json(route('scorecard', $match));

    var PANEL_LAYOUTS = { broadcast: 1, lower: 1, bug: 1, scorecard: 1 };

    var board    = document.getElementById('board');
    var revision = null;
    var lastOverLength = 0;

    if (PANEL_LAYOUTS[LAYOUT]) board.classList.add('panel');

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function crest(team, cls) {
        var inner = team.logo
            ? '<img src="' + esc(team.logo) + '" alt="">'
            : esc(team.short);
        return '<div class="crest ' + (cls || '') + '" style="background:' + esc(team.colour) + '">' + inner + '</div>';
    }

    function statusPill(d) {
        var cls = d.match.is_live ? 'live' : (d.match.is_done ? 'result' : 'idle');
        return '<span class="pill ' + cls + '"><span class="dot"></span>' + esc(d.status_line) + '</span>';
    }

    function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

    function chips(list, limit) {
        if (!list || !list.length) return '';
        var use = limit ? list.slice(-limit) : list;
        return use.map(function (b) {
            return '<span class="ball ' + esc(b.kind) + '">' + esc(b.label) + '</span>';
        }).join('');
    }

    function battingSide(d) {
        if (d.teams.home.batting) return 'home';
        if (d.teams.away.batting) return 'away';
        // Not live: whichever side batted most recently leads the display.
        return d.teams.away.score ? 'away' : 'home';
    }

    /* ── broadcast ─────────────────────────────────────────── */
    function renderBroadcast(d) {
        var c = d.current;
        var bat = battingSide(d);
        var bTeam = d.teams[bat];
        var bowlSide = (bat === 'home') ? 'away' : 'home';
        var oTeam = d.teams[bowlSide];

        // 1. Left Team (Batting Team) - Team name only with larger font
        var left = '<div class="bc-team-badge bc-team-left">'
                 +   '<div class="code">' + esc(bTeam.short || bTeam.name) + '</div>'
                 + '</div>';

        // 2. Batters Section (Current batters with score and balls played, increased font sizes)
        var batters = '<div class="bc-batters">';
        if (c && c.striker) {
            batters += '<div class="bc-batter striker">'
                    +    '<span class="name">' + esc(c.striker.short || c.striker.name) + '</span>'
                    +    '<span class="score">' + esc(c.striker.runs) + '<span class="balls">(' + esc(c.striker.balls) + ')</span></span>'
                    +  '</div>';
        } else {
            batters += '<div class="bc-batter"><span class="name">Striker</span><span class="score">-</span></div>';
        }
        if (c && c.non_striker) {
            batters += '<div class="bc-batter">'
                    +    '<span class="name" style="padding-left:14px">' + esc(c.non_striker.short || c.non_striker.name) + '</span>'
                    +    '<span class="score">' + esc(c.non_striker.runs) + '<span class="balls">(' + esc(c.non_striker.balls) + ')</span></span>'
                    +  '</div>';
        } else {
            batters += '<div class="bc-batter"><span class="name" style="padding-left:14px">Non-Striker</span><span class="score">-</span></div>';
        }
        batters += '</div>';

        // 3. Center Red Capsule (Total Score & Overs & Target, Rounded Corners)
        var scoreLine = ((c ? c.score : bTeam.score) || '0-0').replace('/', '-');
        var liveOvers = (c && c.overs) ? c.overs : (bTeam.overs || '0.0');
        var targetText = '';
        if (c && c.target) {
            var need = c.need != null ? c.need : Math.max(0, c.target - (c.runs || 0));
            var ballsLeft = c.balls_left != null ? c.balls_left : 0;
            targetText = 'NEED ' + need + ' IN ' + ballsLeft + 'b (TARGET ' + c.target + ')';
        } else {
            targetText = '1ST INNINGS • MAX ' + (d.match.overs || 10) + ' OV';
        }

        var scoreCapsule = '<div class="bc-score-capsule">'
                         +   '<div class="bc-score-main">'
                         +     '<span class="bc-score-team">' + esc(bTeam.short) + '.</span>'
                         +     '<span class="bc-score-val">' + esc(scoreLine) + '</span>'
                         +     '<span class="bc-score-ov">' + esc(liveOvers) + ' ov</span>'
                         +   '</div>'
                         +   '<div class="bc-score-sub">' + esc(targetText) + '</div>'
                         + '</div>';

        // 4. Bowler Figures Section (Bowler name, score conceded with over, under bowler name current over ball by ball goings)
        var bowler = '<div class="bc-bowler">';
        var bowlerFigures = '';
        if (c && c.bowler) {
            bowlerFigures = '<div class="bc-bowler-top">'
                          +   '<span class="name">' + esc(c.bowler.short || c.bowler.name) + '</span>'
                          +   '<span class="fig">' + esc(c.bowler.wickets) + '-' + esc(c.bowler.runs)
                          +     '<span class="ov">(' + esc(c.bowler.overs) + ')</span>'
                          +   '</span>'
                          + '</div>';
        } else {
            bowlerFigures = '<div class="bc-bowler-top">'
                          +   '<span class="name">Bowler</span>'
                          +   '<span class="fig">0-0<span class="ov">(0.0)</span></span>'
                          + '</div>';
        }
        var thisOverChips = (c && c.this_over && c.this_over.length)
            ? '<div class="bc-bowler-balls balls">' + chips(c.this_over, 8) + '</div>'
            : '<div class="bc-bowler-balls empty-over">' + (c && c.bowler ? 'Econ ' + c.bowler.economy.toFixed(2) : 'Over in progress') + '</div>';

        bowler += bowlerFigures + thisOverChips + '</div>';

        // 5. Right Team (Bowling Team) - Team name only with larger font
        var right = '<div class="bc-team-badge bc-team-right">'
                  +   '<div class="code">' + esc(oTeam.short || oTeam.name) + '</div>'
                  + '</div>';

        return left + batters + scoreCapsule + bowler + right;
    }

    /* ── lower third ───────────────────────────────────────── */
    function renderLower(d) {
        var c = d.current;

        var team = function (key) {
            var t = d.teams[key];
            return '<div class="lw-team' + (t.batting ? ' batting' : '') + '" style="--team-colour:' + esc(t.colour) + '">'
                + '<span class="code">' + esc(t.short) + '</span>'
                + (t.score ? '<span class="score">' + esc(t.score) + '</span><span class="overs">' + esc(t.overs) + '</span>' : '')
                + '</div>';
        };

        var sub = '';
        if (c && c.striker) {
            sub = esc(c.striker.short) + ' ' + esc(c.striker.display);
            if (c.bowler) sub += '  ·  ' + esc(c.bowler.short) + ' ' + esc(c.bowler.figures);
        } else if (d.sub_headline) {
            sub = esc(d.sub_headline);
        } else if (d.match.toss) {
            sub = esc(d.match.toss);
        }

        return team('home')
            + '<div class="lw-mid"><span class="top">' + esc(d.headline) + '</span>'
            +   (sub ? '<span class="sub">' + sub + '</span>' : '')
            + '</div>'
            + team('away')
            + (c && c.this_over && c.this_over.length
                ? '<div class="lw-strip">' + chips(c.this_over, 6) + '</div>' : '');
    }

    /* ── corner bug ────────────────────────────────────────── */
    function renderBug(d) {
        var row = function (key) {
            var t = d.teams[key];
            return '<div class="bug-row' + (t.batting ? ' batting' : '') + '" style="--team-colour:' + esc(t.colour) + '">'
                + '<span class="code">' + esc(t.short) + '</span>'
                + '<span class="score">' + esc(t.score || '—') + '</span>'
                + '<span class="overs">' + esc(t.overs || '') + '</span></div>';
        };
        var foot = d.sub_headline || d.match.toss || d.match.venue || '';
        return row('home') + row('away')
            + (foot ? '<div class="bug-foot">' + esc(foot) + '</div>' : '');
    }

    /* ── full-screen scorecard ─────────────────────────────── */
    function renderScorecard(d) {
        var c = d.current;

        var score = function (key) {
            var t = d.teams[key];
            return '<div class="sc-score' + (t.batting ? ' batting' : '') + '">'
                + '<div class="club">' + crest(t) + '<b>' + esc(t.name) + '</b></div>'
                + (t.score
                    ? '<div><span class="runs">' + esc(t.score) + '</span><span class="ov">(' + esc(t.overs) + ' ov)</span></div>'
                      + '<div class="rate">Run rate ' + (t.run_rate != null ? t.run_rate.toFixed(2) : '—') + '</div>'
                    : '<div class="runs" style="font-size:30px;opacity:.6">Yet to bat</div>')
                + '</div>';
        };

        var batting = '';
        if (c) {
            [c.striker, c.non_striker].forEach(function (b) {
                if (!b) return;
                batting += '<div class="sc-line"><span class="who">' + esc(b.name)
                        +  (b.on_strike ? ' <span style="color:#6EE7B7">●</span>' : '')
                        +  '<small>' + plural(b.fours, 'four', 'fours') + ' · ' + plural(b.sixes, 'six', 'sixes')
                        +  ' · SR ' + b.sr.toFixed(0) + '</small></span>'
                        +  '<span class="val">' + b.runs + '<small> (' + b.balls + ')</small></span></div>';
            });
        }

        var bowling = '';
        if (c && c.bowler) {
            bowling = '<div class="sc-line"><span class="who">' + esc(c.bowler.name)
                    + '<small>Economy ' + c.bowler.economy.toFixed(2)
                    + (c.bowler.maidens ? ' · ' + c.bowler.maidens + ' maiden' : '') + '</small></span>'
                    + '<span class="val">' + esc(c.bowler.figures) + '<small> (' + esc(c.bowler.overs) + ')</small></span></div>';
        }
        if (c && c.top_bowler) {
            bowling += '<div class="sc-line"><span class="who">' + esc(c.top_bowler.name)
                    +  '<small>Best figures this innings</small></span>'
                    +  '<span class="val">' + esc(c.top_bowler.figures) + '<small> (' + esc(c.top_bowler.overs) + ')</small></span></div>';
        }

        var facts = '';
        if (c) {
            var rows = [
                ['Partnership', c.partnership ? c.partnership.display : '—'],
                ['Extras', String(c.extras)],
                ['Last wicket', c.last_wicket ? c.last_wicket.display : 'none'],
                ['Projected', c.projected ? String(c.projected) : '—']
            ];
            facts = rows.map(function (r) {
                return '<div class="sc-line"><span class="who">' + esc(r[0]) + '</span>'
                     + '<span class="val" style="font-size:16px">' + esc(r[1]) + '</span></div>';
            }).join('');
        }

        var chase = '';
        if (c && c.target && c.need > 0) {
            chase = '<div class="sc-chase"><div class="big">Need ' + c.need + ' from ' + c.balls_left + ' balls</div>'
                  + '<div class="small">Target ' + c.target + '  ·  Required rate ' + c.required_rate.toFixed(2) + '</div></div>';
        } else if (d.match.is_done) {
            chase = '<div class="sc-chase"><div class="big">' + esc(d.headline) + '</div></div>';
        }

        return '<div class="sc-head"><div>'
            +    '<div class="title">' + esc(d.teams.home.name) + ' v ' + esc(d.teams.away.name) + '</div>'
            +    '<div class="meta">' + esc([d.match.edition, d.match.type, d.match.venue].filter(Boolean).join('  ·  ')) + '</div>'
            +  '</div>' + statusPill(d) + '</div>'
            + '<div class="sc-scores">' + score('home') + score('away') + '</div>'
            + (chase || '')
            + '<div class="sc-panels">'
            +   (batting ? '<div class="sc-panel"><h3>At the crease</h3>' + batting + '</div>' : '')
            +   (bowling ? '<div class="sc-panel"><h3>Bowling</h3>' + bowling + '</div>' : '')
            +   (facts   ? '<div class="sc-panel"><h3>Innings</h3>' + facts + '</div>' : '')
            + '</div>'
            + '<div class="sc-foot"><span>' + esc(d.match.toss || 'Royal Champions League') + '</span>'
            +   '<span>' + (c && c.this_over && c.this_over.length
                    ? '<span class="balls">' + chips(c.this_over, 8) + '</span>' : 'Village Cricket Council') + '</span></div>';
    }

    /* ── website card ──────────────────────────────────────── */
    function renderCard(d) {
        var c = d.current;

        var side = function (key) {
            var t = d.teams[key];
            return '<div class="cd-side' + (t.batting ? ' batting' : '') + '">'
                + crest(t)
                + '<div class="name"><b>' + esc(t.name) + '</b><span>' + esc(t.village || '') + '</span></div>'
                + '<div class="score"><b>' + esc(t.score || '—') + '</b>'
                +   (t.overs ? '<span>(' + esc(t.overs) + ' ov)</span>' : '') + '</div></div>';
        };

        var players = '';
        if (c) {
            [c.striker, c.non_striker].forEach(function (b) {
                if (!b) return;
                players += '<div class="p"><span class="n">' + esc(b.name)
                        +  (b.on_strike ? ' <em>●</em>' : '') + '</span>'
                        +  '<span class="f">' + b.runs + ' (' + b.balls + ')  ·  ' + b.fours + '×4  ' + b.sixes + '×6</span></div>';
            });
            if (c.bowler) {
                players += '<div class="p"><span class="n">' + esc(c.bowler.name) + '</span>'
                        +  '<span class="f">' + esc(c.bowler.line) + '</span></div>';
            }
        }

        var grid = '';
        if (c) {
            var items = [['CRR', c.run_rate.toFixed(2)]];
            if (c.required_rate != null && c.need > 0) items.push(['RRR', c.required_rate.toFixed(2)]);
            if (c.partnership) items.push(['Partnership', c.partnership.display]);
            items.push(['Extras', String(c.extras)]);
            if (c.projected) items.push(['Projected', String(c.projected)]);
            if (c.last_wicket) items.push(['Last wicket', c.last_wicket.short + ' ' + c.last_wicket.runs]);
            grid = '<div class="cd-grid">' + items.map(function (i) {
                return '<div class="cd-item"><span class="k">' + esc(i[0]) + '</span><span class="v">' + esc(i[1]) + '</span></div>';
            }).join('') + '</div>';
        }

        return '<div class="cd-head">'
            +    '<span class="meta">' + esc([d.match.edition, d.match.type].filter(Boolean).join(' · ')) + '</span>'
            +    statusPill(d) + '</div>'
            + side('home') + side('away')
            + (COMPACT ? '' :
                '<div class="cd-strip">'
                + '<div class="cd-headline">' + esc(d.headline) + '</div>'
                + (d.sub_headline ? '<div class="cd-chase">' + esc(d.sub_headline) + '</div>' : '')
                + (players ? '<div class="cd-players">' + players + '</div>' : '')
                + grid
                + (c && c.this_over && c.this_over.length
                    ? '<div class="balls">' + chips(c.this_over, 8) + '</div>' : '')
                + '</div>')
            + (SHOW_LOGO
                ? '<div class="cd-foot"><span>' + esc(d.match.venue || 'Royal Champions League') + '</span>'
                  + '<a href="' + esc(SHARE_URL) + '" target="_blank" rel="noopener">Full scorecard →</a></div>'
                : '');
    }

    /* ── ticker ────────────────────────────────────────────── */
    function renderTicker(d) {
        var c = d.current;
        var bits = [
            statusPill(d),
            '<span>' + esc(d.teams.home.short) + ' ' + esc(d.teams.home.score || '—') + '</span>',
            '<span class="tk-mute">v</span>',
            '<span>' + esc(d.teams.away.short) + ' ' + esc(d.teams.away.score || '—') + '</span>',
            '<span class="tk-sep"></span>',
            '<span class="tk-mute">' + esc(d.sub_headline || d.headline) + '</span>'
        ];
        if (c && c.striker) {
            bits.push('<span class="tk-sep"></span>');
            bits.push('<span>' + esc(c.striker.short) + ' ' + esc(c.striker.display) + '</span>');
        }
        if (c && c.this_over && c.this_over.length) {
            bits.push('<span class="tk-sep"></span>');
            bits.push('<span class="balls">' + chips(c.this_over, 6) + '</span>');
        }
        return bits.join('');
    }

    var RENDERERS = {
        broadcast: renderBroadcast,
        lower:     renderBroadcast, // Also uses 5-section broadcast bar
        bug:       renderBug,
        scorecard: renderScorecard,
        ticker:    renderTicker,
        card:      renderCard
    };

    function paint(d) {
        board.innerHTML = (RENDERERS[LAYOUT] || renderCard)(d);

        // Accent follows whoever is batting, so the widget matches the side on
        // strike rather than sitting on one fixed colour all match.
        var side = battingSide(d);
        document.documentElement.style.setProperty('--accent', d.teams[side].colour);

        // Flash on a boundary or a wicket — the broadcast tell.
        var c = d.current;
        if (c && c.this_over && c.this_over.length > lastOverLength) {
            var latest = c.this_over[c.this_over.length - 1];
            if (latest.kind === 'six' || latest.kind === 'four' || latest.kind === 'wicket') {
                board.style.setProperty('--flash-colour',
                    latest.kind === 'wicket' ? 'rgba(239,68,68,.6)'
                    : latest.kind === 'six'  ? 'rgba(245,158,11,.55)'
                    : 'rgba(14,165,233,.5)');
                board.classList.remove('flash');
                void board.offsetWidth;              // restart the animation
                board.classList.add('flash');
            }
        }
        lastOverLength = c && c.this_over ? c.this_over.length : 0;
    }

    function poll() {
        fetch(STATE_URL, { cache: 'no-store', headers: { Accept: 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d || d.revision === revision) return;
                revision = d.revision;
                board.classList.add('out');
                setTimeout(function () { paint(d); board.classList.remove('out'); }, 110);
            })
            .catch(function () { /* a dropped poll is not worth surfacing */ });
    }

    var initial = @json($data);
    paint(initial);
    revision = initial.revision;

    setInterval(poll, REFRESH);

    // Catch up straight away when a tab or browser source regains focus.
    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
}());
</script>
</body>
</html>
