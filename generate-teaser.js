/**
 * RCL App Launch Teaser — Video Generator
 * Run    : node generate-teaser.js
 * Output : public/coming-soon.mp4   (1280×720 HD, 30 seconds)
 * Needs  : FFmpeg 6+ in PATH  or  C:\ffmpeg\bin\ffmpeg.exe
 */

const { spawnSync, execFileSync } = require('child_process');
const path = require('path');
const fs   = require('fs');

// ── Locate FFmpeg ────────────────────────────────────────────────
function findFfmpeg() {
    try { execFileSync('ffmpeg', ['-version'], { stdio: 'pipe' }); return 'ffmpeg'; } catch {}
    const candidates = [
        'C:\\ffmpeg\\bin\\ffmpeg.exe',
        (process.env.USERPROFILE || '') + '\\ffmpeg\\bin\\ffmpeg.exe',
    ];
    for (const c of candidates) { if (fs.existsSync(c)) return c; }
    return null;
}

const ffmpeg = findFfmpeg();
if (!ffmpeg) {
    console.error('\n  ERROR: FFmpeg not found.\n  Install: https://www.gyan.dev/ffmpeg/builds/\n');
    process.exit(1);
}

const output = path.join(__dirname, 'public', 'coming-soon.mp4');

console.log('\n  ================================================');
console.log('   RCL App Launch Teaser — Generating...');
console.log('  ================================================');
console.log('  FFmpeg  :', ffmpeg);
console.log('  Output  :', output);
console.log('  Running FFmpeg (please wait ~60 seconds)...\n');

// Font paths: backslash before colon = FFmpeg Windows escape
const fb  = 'C\\:/Windows/Fonts/ariblk.ttf';   // Arial Black
const fbd = 'C\\:/Windows/Fonts/arialbd.ttf';  // Arial Bold
const fr  = 'C\\:/Windows/Fonts/arial.ttf';    // Arial Regular

// ── Timeline ─────────────────────────────────────────────────────
//  0– 4s  Scene 1  dark bg   "Village cricket has always been..."
//  4– 8s  Scene 2  dark bg   "raw.  passionate.  real."  (gold)
//  8–13s  Scene 3  green bg  "It deserves more than a scoreboard."
// 13–18s  Scene 4  dark bg   Huge "R C L" in gold with glow
// 18–23s  Scene 5  dark bg   Full title + tagline
// 23–27s  Scene 6  dark bg   Feature list
// 27–30s  Scene 7  green bg  "COMING SOON"
// ─────────────────────────────────────────────────────────────────

const filters = [

    // ── Background colour switches ──────────────────────────────
    `drawbox=color=0x1B8A4E:width=iw:height=ih:t=fill:enable='between(t,8,13)'`,
    `drawbox=color=0x1B8A4E:width=iw:height=ih:t=fill:enable='between(t,27,30)'`,

    // ── Scene 1 (0–4 s) ────────────────────────────────────────
    `drawtext=fontfile='${fb}':text='Village cricket has always been...':fontcolor=white:fontsize=50:x=(w-text_w)/2:y=h/2-50:alpha='if(lt(t,1),t,if(gt(t,3),4-t,1))':enable='between(t,0,4)'`,
    `drawtext=fontfile='${fr}':text='across Pakistan.':fontcolor=white@0.65:fontsize=30:x=(w-text_w)/2:y=h/2+28:alpha='if(lt(t,2),if(gt(t,1),t-1,0),if(gt(t,3),4-t,1))':enable='between(t,0,4)'`,

    // ── Scene 2 (4–8 s) ────────────────────────────────────────
    `drawtext=fontfile='${fb}':text='raw.   passionate.   real.':fontcolor=0xD4900A:fontsize=58:x=(w-text_w)/2:y=h/2:alpha='if(lt(t,5),t-4,if(gt(t,7),8-t,1))':enable='between(t,4,8)'`,

    // ── Scene 3 (8–13 s) ───────────────────────────────────────
    `drawtext=fontfile='${fb}':text='It deserves more':fontcolor=white:fontsize=56:x=(w-text_w)/2:y=h/2-52:alpha='if(lt(t,9),t-8,if(gt(t,12),13-t,1))':enable='between(t,8,13)'`,
    `drawtext=fontfile='${fbd}':text='than a scoreboard on paper.':fontcolor=white@0.85:fontsize=30:x=(w-text_w)/2:y=h/2+30:alpha='if(lt(t,10),if(gt(t,9),t-9,0),if(gt(t,12),13-t,1))':enable='between(t,8,13)'`,

    // ── Scene 4 (13–18 s) — Big RCL reveal ─────────────────────
    `drawtext=fontfile='${fb}':text='R  C  L':fontcolor=0xD4900A:fontsize=170:x=(w-text_w)/2:y=h/2-85:shadowcolor=0xD4900A@0.4:shadowx=6:shadowy=6:alpha='if(lt(t,14),t-13,if(gt(t,17),18-t,1))':enable='between(t,13,18)'`,
    `drawtext=fontfile='${fr}':text='Royal  Champions  League':fontcolor=white@0.4:fontsize=26:x=(w-text_w)/2:y=h/2+82:alpha='if(lt(t,15),if(gt(t,14),t-14,0),if(gt(t,17),18-t,1))':enable='between(t,13,18)'`,

    // ── Scene 5 (18–23 s) — Full title ──────────────────────────
    `drawtext=fontfile='${fb}':text='ROYAL CHAMPIONS LEAGUE':fontcolor=white:fontsize=54:x=(w-text_w)/2:y=h/2-58:alpha='if(lt(t,19),t-18,if(gt(t,22),23-t,1))':enable='between(t,18,23)'`,
    `drawtext=fontfile='${fbd}':text='The Premier Village Cricket Platform':fontcolor=0xD4900A:fontsize=28:x=(w-text_w)/2:y=h/2+28:alpha='if(lt(t,19.5),if(gt(t,19),t-19,0),if(gt(t,22),23-t,1))':enable='between(t,18,23)'`,
    `drawtext=fontfile='${fr}':text='Pakistan':fontcolor=white@0.4:fontsize=20:x=(w-text_w)/2:y=h/2+76:alpha='if(lt(t,20),if(gt(t,19.5),t-19.5,0),if(gt(t,22),23-t,1))':enable='between(t,18,23)'`,

    // ── Scene 6 (23–27 s) — Features ────────────────────────────
    `drawtext=fontfile='${fb}':text='LIVE SCORES':fontcolor=white:fontsize=38:x=(w-text_w)/2:y=h/2-80:alpha='if(lt(t,24),t-23,if(gt(t,26.2),27-t,1))':enable='between(t,23,27)'`,
    `drawtext=fontfile='${fbd}':text='Player Stats   |   Teams   |   Points Table':fontcolor=0xD4900A:fontsize=28:x=(w-text_w)/2:y=h/2-8:alpha='if(lt(t,24.5),if(gt(t,24),t-24,0),if(gt(t,26.2),27-t,1))':enable='between(t,23,27)'`,
    `drawtext=fontfile='${fr}':text='Track every ball. Every match. Every player.':fontcolor=white@0.55:fontsize=22:x=(w-text_w)/2:y=h/2+58:alpha='if(lt(t,25.2),if(gt(t,24.8),t-24.8,0),if(gt(t,26.2),27-t,1))':enable='between(t,23,27)'`,

    // ── Scene 7 (27–30 s) — Coming Soon ─────────────────────────
    `drawtext=fontfile='${fb}':text='COMING SOON':fontcolor=white:fontsize=82:x=(w-text_w)/2:y=h/2-60:alpha='if(lt(t,28),t-27,1)':enable='between(t,27,30)'`,
    `drawtext=fontfile='${fr}':text='Stay tuned for the official launch':fontcolor=white@0.75:fontsize=28:x=(w-text_w)/2:y=h/2+48:alpha='if(lt(t,28.5),if(gt(t,28),t-28,0),1)':enable='between(t,27,30)'`,
];

const result = spawnSync(ffmpeg, [
    '-y',
    '-f',      'lavfi',
    '-i',      'color=c=0x0D1F16:size=1280x720:rate=30',
    '-vf',     filters.join(','),
    '-t',      '30',
    '-c:v',    'libx264',
    '-preset', 'medium',
    '-crf',    '18',
    '-pix_fmt','yuv420p',
    output,
], { stdio: 'inherit' });

if (result.status === 0) {
    const mb = (fs.statSync(output).size / 1024 / 1024).toFixed(1);
    console.log('\n  ================================================');
    console.log('   Done!  Video saved successfully.');
    console.log('   Path  : public\\coming-soon.mp4');
    console.log(`   Size  : ${mb} MB  |  1280x720 HD  |  30 sec`);
    console.log('  ================================================\n');
} else {
    console.error('\n  FFmpeg failed — check errors above.\n');
    process.exit(1);
}
