/**
 * RCL App Launch Video — Enhanced Edition
 * Run    : node generate-teaser.cjs
 * Output : public/coming-soon.mp4   (1280×720 HD, 33 seconds)
 * Needs  : FFmpeg 6+  |  logo.jfif in project root
 *
 * Features:
 *  - Suspense synthesised audio (drone + rising pad + shimmer)
 *  - Logo image overlaid at 3 positions / times
 *  - Mobile phone mockup: splash loader + home page
 *  - "THE APP IS LIVE · JUNE 2026" launch reveal
 *  - "Developed by Sarfraz Jutt" credit
 */

const { spawnSync, execFileSync } = require('child_process');
const path = require('path');
const fs   = require('fs');

// ── Locate FFmpeg ─────────────────────────────────────────────
function findFfmpeg() {
    try { execFileSync('ffmpeg', ['-version'], { stdio: 'pipe' }); return 'ffmpeg'; } catch {}
    const candidates = [
        'C:\\ffmpeg\\bin\\ffmpeg.exe',
        (process.env.USERPROFILE || '') + '\\ffmpeg\\bin\\ffmpeg.exe',
    ];
    for (const c of candidates) if (fs.existsSync(c)) return c;
    return null;
}

const ffmpeg = findFfmpeg();
if (!ffmpeg) {
    console.error('\n  ERROR: FFmpeg not found.\n  Install: https://www.gyan.dev/ffmpeg/builds/\n');
    process.exit(1);
}

const W      = 1280;
const H      = 720;
const TOTAL  = 33;
const output = path.join(__dirname, 'public', 'coming-soon.mp4');
const logo   = path.join(__dirname, 'logo.jfif');

if (!fs.existsSync(logo)) {
    console.error('\n  ERROR: logo.jfif not found at', logo, '\n');
    process.exit(1);
}

console.log('\n  ================================================');
console.log('   RCL App Launch Video — Enhanced Edition');
console.log('  ================================================');
console.log('  FFmpeg :', ffmpeg);
console.log('  Logo   :', logo);
console.log('  Output :', output);
console.log('  Running FFmpeg (please wait ~90 seconds)...\n');

// Font paths (FFmpeg Windows — backslash-escape the colon)
const fb  = 'C\\:/Windows/Fonts/ariblk.ttf';
const fbd = 'C\\:/Windows/Fonts/arialbd.ttf';
const fr  = 'C\\:/Windows/Fonts/arial.ttf';

// ── Phone mockup geometry ─────────────────────────────────────
const px  = 810;                    // phone left
const py  = 58;                     // phone top
const pw  = 282;                    // phone width
const ph  = 600;                    // phone height
const sx  = px + 9;                 // screen left  = 819
const sy  = py + 34;                // screen top   = 92
const sw  = pw - 18;                // screen width = 264
const sh  = ph - 50;                // screen height= 550
const pcx = Math.round(px + pw/2); // phone centre x = 951
const hh  = 44;                    // header height
const hby = sy + hh;               // header bottom = 136
const tky = hby;                   // ticker top    = 136
const tkh = 15;                    // ticker height
const cty = tky + tkh;             // content top   = 151
const bnh = 46;                    // bottom-nav height
const bny = sy + sh - bnh;         // bottom-nav top= 596

// Fade-in / fade-out alpha helper
function f(inT, fullT, outT, endT) {
    return `if(lt(t,${inT}),0,if(lt(t,${fullT}),(t-${inT})/${fullT-inT},if(lt(t,${outT}),1,if(lt(t,${endT}),(${endT}-t)/${endT-outT},0))))`;
}

// ── BG COLOUR OVERRIDES ───────────────────────────────────────
const BG = [
    `drawbox=color=0x051409:w=iw:h=ih:t=fill:enable='between(t,6.5,9.5)'`,
    `drawbox=color=0x1B8A4E:w=iw:h=ih:t=fill:enable='between(t,25,28)'`,
    `drawbox=color=0x030A05:w=iw:h=ih:t=fill:enable='between(t,31,${TOTAL})'`,
];

// ── PHONE FRAME ───────────────────────────────────────────────
const PHONE = [
    `drawbox=x=${px+5}:y=${py+6}:w=${pw}:h=${ph}:color=black@0.5:t=fill:enable='between(t,10,21)'`,
    `drawbox=x=${px}:y=${py}:w=${pw}:h=${ph}:color=0x191924:t=fill:enable='between(t,10,21)'`,
    `drawbox=x=${sx}:y=${py+2}:w=${sw}:h=${sy-py-2}:color=0x0E0E1A:t=fill:enable='between(t,10,21)'`,
    `drawtext=fontfile='${fr}':text='9.41 AM':fontcolor=white@0.85:fontsize=9:x=${sx+7}:y=${py+12}:enable='between(t,10,21)'`,
    `drawtext=fontfile='${fr}':text='4G  100':fontcolor=white@0.7:fontsize=8:x=${sx+sw-52}:y=${py+13}:enable='between(t,10,21)'`,
    `drawbox=x=${px-4}:y=${py+85}:w=4:h=32:color=0x252530:t=fill:enable='between(t,10,21)'`,
    `drawbox=x=${px-4}:y=${py+128}:w=4:h=32:color=0x252530:t=fill:enable='between(t,10,21)'`,
    `drawbox=x=${px+pw}:y=${py+108}:w=4:h=48:color=0x252530:t=fill:enable='between(t,10,21)'`,
    `drawbox=x=${pcx-24}:y=${py+ph-11}:w=48:h=5:color=white@0.3:t=fill:enable='between(t,10,21)'`,
];

// ── SPLASH INSIDE PHONE (t 10–15) ────────────────────────────
const SPLASH_PHONE = [
    `drawbox=x=${sx}:y=${sy}:w=${sw}:h=${sh}:color=0x092A14:t=fill:enable='between(t,10,15)'`,
    `drawbox=x=${sx+sw-80}:y=${sy}:w=80:h=80:color=0xD4900A@0.12:t=fill:enable='between(t,10,15)'`,
    `drawbox=x=${sx}:y=${sy+sh-100}:w=100:h=100:color=0x1B8A4E@0.15:t=fill:enable='between(t,10,15)'`,
    `drawbox=x=${pcx-38}:y=${sy+160}:w=76:h=76:color=0x1B8A4E@0.55:t=fill:enable='between(t,10,15)'`,
    `drawtext=fontfile='${fb}':text='Royal Champions':fontcolor=white:fontsize=16:x=${pcx}-text_w/2:y=${sy+256}:alpha='${f(10.5,11.8,13.5,15)}':enable='between(t,10,15)'`,
    `drawtext=fontfile='${fr}':text='Village Cricket Council':fontcolor=white@0.6:fontsize=10:x=${pcx}-text_w/2:y=${sy+278}:alpha='${f(11,12.2,13.5,15)}':enable='between(t,10,15)'`,
    `drawtext=fontfile='${fr}':text='Pakistan':fontcolor=white@0.38:fontsize=9:x=${pcx}-text_w/2:y=${sy+294}:enable='between(t,11,15)'`,
    `drawbox=x=${pcx-38}:y=${sy+316}:w=76:h=3:color=white@0.15:t=fill:enable='between(t,10,15)'`,
    `drawbox=x=${pcx-38}:y=${sy+316}:w=53:h=3:color=white:t=fill:enable='between(t,11.5,15)'`,
    `drawtext=fontfile='${fr}':text='Heritage of Champions':fontcolor=0xD4900A@0.65:fontsize=8:x=${pcx}-text_w/2:y=${sy+sh-50}:alpha='${f(12,13,14.5,15)}':enable='between(t,12,15)'`,
    `drawtext=fontfile='${fr}':text='by Sarfraz Jutt':fontcolor=white@0.28:fontsize=7:x=${pcx}-text_w/2:y=${sy+sh-35}:enable='between(t,12.5,15)'`,
];

// ── HOME PAGE INSIDE PHONE (t 15–21) ─────────────────────────
const HOME_PHONE = [
    `drawbox=x=${sx}:y=${sy}:w=${sw}:h=${sh}:color=0xEBF5F0:t=fill:enable='between(t,15,21)'`,
    // Header
    `drawbox=x=${sx}:y=${sy}:w=${sw}:h=${hh}:color=0x1B8A4E:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='Royal Champions':fontcolor=white:fontsize=9:x=${sx+38}:y=${sy+11}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Village Cricket Council':fontcolor=white@0.6:fontsize=7:x=${sx+38}:y=${sy+24}:enable='between(t,15,21)'`,
    `drawbox=x=${sx+sw-50}:y=${sy+12}:w=46:h=16:color=0xD4900A:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='1st Ed.':fontcolor=white:fontsize=7:x=${sx+sw-47}:y=${sy+16}:enable='between(t,15,21)'`,
    // Ticker
    `drawbox=x=${sx}:y=${hby}:w=${sw}:h=${tkh}:color=0x16703E:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='LIVE  Challengers 142/6 (18.4)  vs  Warriors  |  Next - Tomorrow 3PM':fontcolor=white@0.9:fontsize=6:x=${sx+sw}-(t-15)*38:y=${tky+4}:enable='between(t,15,21)'`,
    // Live match card
    `drawbox=x=${sx+5}:y=${cty+6}:w=${sw-10}:h=72:color=0xFFF2F2:t=fill:enable='between(t,15,21)'`,
    `drawbox=x=${sx+5}:y=${cty+6}:w=${sw-10}:h=72:color=0xDC2626@0.28:t=2:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='LIVE':fontcolor=0xDC2626:fontsize=7:x=${sx+12}:y=${cty+13}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='Challengers':fontcolor=0x1A2E20:fontsize=9:x=${sx+12}:y=${cty+26}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='142/6':fontcolor=0x1B8A4E:fontsize=13:x=${sx+sw-60}:y=${cty+22}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='vs':fontcolor=0x6B8F74:fontsize=8:x=${pcx-6}:y=${cty+28}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='Warriors':fontcolor=0x1A2E20:fontsize=9:x=${sx+12}:y=${cty+44}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='18.4 Ov  Match 7':fontcolor=0x6B8F74:fontsize=7:x=${sx+12}:y=${cty+60}:enable='between(t,15,21)'`,
    // Upcoming card
    `drawbox=x=${sx+5}:y=${cty+84}:w=${sw-10}:h=56:color=0xFFFAED:t=fill:enable='between(t,15,21)'`,
    `drawbox=x=${sx+5}:y=${cty+84}:w=${sw-10}:h=56:color=0xD4900A@0.25:t=2:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='UPCOMING':fontcolor=0xD4900A:fontsize=6:x=${sx+12}:y=${cty+91}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='Lions vs Panthers':fontcolor=0x1A2E20:fontsize=8:x=${sx+12}:y=${cty+104}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Tomorrow  3.00 PM  Ground A':fontcolor=0x6B8F74:fontsize=7:x=${sx+12}:y=${cty+120}:enable='between(t,15,21)'`,
    // Stats section
    `drawtext=fontfile='${fbd}':text='Top Players':fontcolor=0x1A2E20:fontsize=8:x=${sx+8}:y=${cty+150}:enable='between(t,15,21)'`,
    `drawbox=x=${sx+5}:y=${cty+164}:w=78:h=60:color=white:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='342':fontcolor=0x1B8A4E:fontsize=16:x=${sx+22}:y=${cty+174}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Runs':fontcolor=0x6B8F74:fontsize=7:x=${sx+24}:y=${cty+196}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Ahmed Ali':fontcolor=0x1A2E20:fontsize=6:x=${sx+9}:y=${cty+212}:enable='between(t,15,21)'`,
    `drawbox=x=${sx+91}:y=${cty+164}:w=78:h=60:color=white:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='18':fontcolor=0xD4900A:fontsize=16:x=${sx+114}:y=${cty+174}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Wkts':fontcolor=0x6B8F74:fontsize=7:x=${sx+109}:y=${cty+196}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Hamza Khan':fontcolor=0x1A2E20:fontsize=6:x=${sx+95}:y=${cty+212}:enable='between(t,15,21)'`,
    `drawbox=x=${sx+177}:y=${cty+164}:w=78:h=60:color=white:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='12':fontcolor=0x1B8A4E:fontsize=16:x=${sx+200}:y=${cty+174}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Matches':fontcolor=0x6B8F74:fontsize=7:x=${sx+184}:y=${cty+196}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Season 1':fontcolor=0x1A2E20:fontsize=6:x=${sx+186}:y=${cty+212}:enable='between(t,15,21)'`,
    // Bottom nav
    `drawbox=x=${sx}:y=${bny}:w=${sw}:h=${bnh}:color=white:t=fill:enable='between(t,15,21)'`,
    `drawbox=x=${sx}:y=${bny}:w=${sw}:h=1:color=0xDDE8E2:t=fill:enable='between(t,15,21)'`,
    `drawbox=x=${sx+12}:y=${bny}:w=38:h=2:color=0x1B8A4E:t=fill:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Home':fontcolor=0x1B8A4E:fontsize=6:x=${sx+18}:y=${bny+26}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Fixtures':fontcolor=0xA0B8A6:fontsize=6:x=${sx+60}:y=${bny+26}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Tourney':fontcolor=0xA0B8A6:fontsize=6:x=${sx+104}:y=${bny+26}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Stats':fontcolor=0xA0B8A6:fontsize=6:x=${sx+152}:y=${bny+26}:enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='More':fontcolor=0xA0B8A6:fontsize=6:x=${sx+197}:y=${bny+26}:enable='between(t,15,21)'`,
];

// ── LEFT TEXT (during phone scenes) ──────────────────────────
const PHONE_LABELS = [
    `drawtext=fontfile='${fb}':text='Meet the':fontcolor=white:fontsize=34:x=60:y=h/2-58:alpha='${f(10,11,13.5,15)}':enable='between(t,10,15)'`,
    `drawtext=fontfile='${fb}':text='Loader.':fontcolor=0xD4900A:fontsize=56:x=60:y=h/2-12:alpha='${f(10.3,11.3,13.5,15)}':enable='between(t,10,15)'`,
    `drawtext=fontfile='${fr}':text='Elegant. Fast. Cricket-first.':fontcolor=white@0.5:fontsize=18:x=60:y=h/2+55:alpha='${f(11,12,13.5,15)}':enable='between(t,10,15)'`,
    `drawtext=fontfile='${fb}':text='Live Scores.':fontcolor=white:fontsize=40:x=60:y=h/2-94:alpha='${f(15,16,20,21)}':enable='between(t,15,21)'`,
    `drawtext=fontfile='${fbd}':text='Real-time updates':fontcolor=0xD4900A:fontsize=22:x=60:y=h/2-40:alpha='${f(15.5,16.5,20,21)}':enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Player stats. Teams.':fontcolor=white@0.55:fontsize=18:x=60:y=h/2+4:alpha='${f(16,17,20,21)}':enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Points table. History.':fontcolor=white@0.45:fontsize=17:x=60:y=h/2+34:alpha='${f(16.5,17.5,20,21)}':enable='between(t,15,21)'`,
    `drawtext=fontfile='${fr}':text='Everything cricket.':fontcolor=0xD4900A@0.7:fontsize=19:x=60:y=h/2+72:alpha='${f(17,18,20,21)}':enable='between(t,15,21)'`,
];

// ── SCENE 1: Logo reveal (0–3s) ───────────────────────────────
const S1 = [
    `drawbox=x=${W/2-68}:y=${H/2-186}:w=136:h=136:color=0xD4900A@0.09:t=fill:enable='between(t,0,3)'`,
    `drawtext=fontfile='${fb}':text='ROYAL CHAMPIONS LEAGUE':fontcolor=0xD4900A:fontsize=28:x=(w-text_w)/2:y=${H/2+24}:alpha='${f(0.8,2,2.5,3)}':enable='between(t,0,3)'`,
    `drawtext=fontfile='${fr}':text='Village Cricket  ·  Pakistan':fontcolor=white@0.5:fontsize=18:x=(w-text_w)/2:y=${H/2+62}:alpha='${f(1.2,2.2,2.5,3)}':enable='between(t,0,3)'`,
];

// ── SCENE 2: Heritage (3–6.5s) ───────────────────────────────
const S2 = [
    `drawtext=fontfile='${fb}':text='Village cricket has always been...':fontcolor=white:fontsize=44:x=(w-text_w)/2:y=h/2-40:alpha='${f(3,4,5.8,6.5)}':enable='between(t,3,6.5)'`,
    `drawtext=fontfile='${fr}':text='played across the villages of Pakistan.':fontcolor=white@0.58:fontsize=24:x=(w-text_w)/2:y=h/2+26:alpha='${f(3.8,4.6,5.8,6.5)}':enable='between(t,3,6.5)'`,
];

// ── SCENE 3: Passion (6.5–9.5s) ──────────────────────────────
const S3 = [
    `drawtext=fontfile='${fb}':text='raw.':fontcolor=0xD4900A:fontsize=88:x=(w-text_w)/2:y=h/2-92:alpha='${f(6.5,7.2,9,9.5)}':enable='between(t,6.5,9.5)'`,
    `drawtext=fontfile='${fb}':text='passionate.':fontcolor=0xD4900A:fontsize=62:x=(w-text_w)/2:y=h/2+5:alpha='${f(7,7.6,9,9.5)}':enable='between(t,6.5,9.5)'`,
    `drawtext=fontfile='${fb}':text='real.':fontcolor=white:fontsize=74:x=(w-text_w)/2:y=h/2+88:alpha='${f(7.5,8.1,9,9.5)}':enable='between(t,6.5,9.5)'`,
];

// ── SCENE 9.5 → 10: Brief "IT DESERVES MORE" bridge ──────────
const BRIDGE = [
    `drawtext=fontfile='${fb}':text='It deserves more':fontcolor=white:fontsize=52:x=(w-text_w)/2:y=h/2-30:alpha='${f(9.5,9.9,9.95,10)}':enable='between(t,9.5,10)'`,
];

// ── SCENE 6: RCL letters (21–25s) ────────────────────────────
const S6 = [
    `drawtext=fontfile='${fb}':text='R  C  L':fontcolor=0xD4900A:fontsize=195:x=(w-text_w)/2:y=h/2-102:shadowcolor=0xD4900A@0.55:shadowx=9:shadowy=9:alpha='${f(21,22.2,24,25)}':enable='between(t,21,25)'`,
    `drawtext=fontfile='${fr}':text='Royal  Champions  League':fontcolor=white@0.42:fontsize=24:x=(w-text_w)/2:y=h/2+96:alpha='${f(22,23,24,25)}':enable='between(t,21,25)'`,
];

// ── SCENE 7: "THE APP IS LIVE" on green (25–28s) ─────────────
const S7 = [
    `drawtext=fontfile='${fb}':text='THE APP IS LIVE':fontcolor=white:fontsize=64:x=(w-text_w)/2:y=h/2-58:alpha='${f(25,25.8,27.5,28)}':enable='between(t,25,28)'`,
    `drawtext=fontfile='${fbd}':text='Royal Champions League is here':fontcolor=0xD4900A:fontsize=26:x=(w-text_w)/2:y=h/2+22:alpha='${f(25.5,26.2,27.5,28)}':enable='between(t,25,28)'`,
    `drawtext=fontfile='${fr}':text='The Premier Village Cricket Platform':fontcolor=white@0.68:fontsize=18:x=(w-text_w)/2:y=h/2+62:alpha='${f(26,26.8,27.5,28)}':enable='between(t,25,28)'`,
];

// ── SCENE 8: Release date (28–31s) ───────────────────────────
const S8 = [
    `drawtext=fontfile='${fb}':text='LAUNCHING':fontcolor=white:fontsize=36:x=(w-text_w)/2:y=h/2-95:alpha='${f(28,28.7,30.3,31)}':enable='between(t,28,31)'`,
    `drawtext=fontfile='${fb}':text='JUNE  2026':fontcolor=0xD4900A:fontsize=82:x=(w-text_w)/2:y=h/2-30:alpha='${f(28,28.8,30.3,31)}':enable='between(t,28,31)'`,
    `drawtext=fontfile='${fbd}':text='LIVE SCORES  ·  PLAYER STATS  ·  TEAMS  ·  STANDINGS':fontcolor=white@0.72:fontsize=16:x=(w-text_w)/2:y=h/2+70:alpha='${f(28.8,29.5,30.3,31)}':enable='between(t,28,31)'`,
    `drawtext=fontfile='${fr}':text='Be part of cricket history.':fontcolor=white@0.42:fontsize=15:x=(w-text_w)/2:y=h/2+100:alpha='${f(29.2,30,30.3,31)}':enable='between(t,28,31)'`,
];

// ── SCENE 9: Credits (31–33s) ────────────────────────────────
const S9 = [
    `drawbox=x=(iw-160)/2:y=h/2-8:w=160:h=2:color=0xD4900A@0.5:t=fill:enable='between(t,31.5,${TOTAL})'`,
    `drawtext=fontfile='${fb}':text='Royal Champions League':fontcolor=0xD4900A:fontsize=36:x=(w-text_w)/2:y=h/2-54:alpha='${f(31,31.8,32.5,TOTAL)}':enable='between(t,31,${TOTAL})'`,
    `drawtext=fontfile='${fr}':text='Developed by Sarfraz Jutt':fontcolor=white@0.75:fontsize=22:x=(w-text_w)/2:y=h/2+16:alpha='${f(31.4,32.1,32.5,TOTAL)}':enable='between(t,31,${TOTAL})'`,
    `drawtext=fontfile='${fr}':text='Pakistan  ·  June 2026':fontcolor=white@0.35:fontsize=15:x=(w-text_w)/2:y=h/2+50:alpha='${f(31.8,32.4,32.7,TOTAL)}':enable='between(t,31,${TOTAL})'`,
];

// ── COMBINE ALL VIDEO FILTERS ─────────────────────────────────
const videoFilters = [
    ...BG,
    ...PHONE, ...SPLASH_PHONE, ...HOME_PHONE,
    ...PHONE_LABELS,
    ...S1, ...S2, ...S3, ...BRIDGE,
    ...S6, ...S7, ...S8, ...S9,
].join(',');

// ── SUSPENSE AUDIO (synthesised) ─────────────────────────────
const suspenseExpr =
    `0.22*sin(2*PI*55*t)*(0.72+0.28*sin(2*PI*0.17*t))` +
    `+0.16*sin(2*PI*82.5*t)*(0.55+0.45*sin(2*PI*0.28*t))` +
    `+0.11*sin(2*PI*110*t)*(0.42+0.58*sin(2*PI*0.21*t))` +
    `+0.07*sin(2*PI*(55+t*1.15)*t)*tanh(t/4)` +
    `+0.04*sin(2*PI*220*t)*exp(-(t-11*floor(t/11))*0.38)` +
    `+0.025*sin(2*PI*440*t)*(0.5+0.5*sin(2*PI*0.75*t))`;

// ── FILTER COMPLEX ────────────────────────────────────────────
const filterComplex = [
    `[1:v]split=3[r1][r2][r3]`,
    `[r1]scale=124:124,format=rgba[logoBig]`,
    `[r2]scale=30:30,format=rgba[logoHdr]`,
    `[r3]scale=52:52,format=rgba[logoCorner]`,
    `[0:v]${videoFilters}[bg]`,
    `[bg][logoBig]overlay=x=${W/2-62}:y=${H/2-196}:enable='between(t,0,3)'[v0]`,
    `[v0][logoHdr]overlay=x=${sx+4}:y=${sy+7}:enable='between(t,10,21)'[v1]`,
    `[v1][logoCorner]overlay=x=${W-72}:y=${H-76}:enable='between(t,25,${TOTAL})'[v]`,
    `aevalsrc='${suspenseExpr}':c=stereo:s=44100,lowpass=f=950,volume=0.66[a]`,
].join(';');

// ── RUN ───────────────────────────────────────────────────────
const result = spawnSync(ffmpeg, [
    '-y',
    '-f',    'lavfi',
    '-i',    `color=c=0x0D1F16:size=${W}x${H}:rate=30`,
    '-loop', '1',
    '-i',    logo,
    '-filter_complex', filterComplex,
    '-map', '[v]',
    '-map', '[a]',
    '-t',    String(TOTAL),
    '-c:v', 'libx264',
    '-preset', 'medium',
    '-crf',  '18',
    '-pix_fmt', 'yuv420p',
    '-c:a', 'aac',
    '-b:a', '128k',
    output,
], { stdio: 'inherit' });

if (result.status === 0) {
    const mb = (fs.statSync(output).size / 1024 / 1024).toFixed(1);
    console.log('\n  ================================================');
    console.log('   Done!  Enhanced video saved successfully.');
    console.log('   Path  : public\\coming-soon.mp4');
    console.log(`   Size  : ${mb} MB  |  ${W}x${H} HD  |  ${TOTAL}s`);
    console.log('   Audio : suspense pad synthesised by FFmpeg');
    console.log('   Scenes: Logo reveal → Heritage → Passion →');
    console.log('           App mockup → RCL reveal →');
    console.log('           THE APP IS LIVE → JUNE 2026 → Credits');
    console.log('  ================================================\n');
} else {
    console.error('\n  FFmpeg failed — see errors above.\n');
    process.exit(1);
}
