# ================================================================
#  Royal Champions League — App Launch Teaser Generator
#  Output  : public\coming-soon.mp4   (1280x720 HD, 30 seconds)
#  Requires: FFmpeg 6+  →  https://www.gyan.dev/ffmpeg/builds/
#            Download "ffmpeg-release-essentials.zip", extract,
#            and add the \bin folder to your Windows PATH.
#  Run     : .\generate-teaser.ps1
# ================================================================

$ErrorActionPreference = 'Stop'

# ── Auto-locate ffmpeg (works even without PATH setup) ──────────
$ffmpeg = $null

# 1. Already in PATH?
$inPath = Get-Command ffmpeg -ErrorAction SilentlyContinue
if ($inPath) { $ffmpeg = 'ffmpeg' }

# 2. Search common install locations
if (-not $ffmpeg) {
    $candidates = @(
        "C:\ffmpeg\bin\ffmpeg.exe",
        "C:\Program Files\ffmpeg\bin\ffmpeg.exe",
        "C:\Program Files (x86)\ffmpeg\bin\ffmpeg.exe",
        "$env:USERPROFILE\ffmpeg\bin\ffmpeg.exe",
        "$env:USERPROFILE\Downloads\ffmpeg\bin\ffmpeg.exe"
    )
    # Also scan Downloads for any extracted ffmpeg folder
    $dlDir = "$env:USERPROFILE\Downloads"
    $found  = Get-ChildItem -Path $dlDir -Recurse -Filter "ffmpeg.exe" -ErrorAction SilentlyContinue |
              Where-Object { $_.FullName -match '\\bin\\ffmpeg\.exe$' } |
              Select-Object -First 1
    if ($found) { $candidates += $found.FullName }

    foreach ($c in $candidates) {
        if (Test-Path $c) { $ffmpeg = $c; break }
    }
}

if (-not $ffmpeg) {
    Write-Host ""
    Write-Host "  FFmpeg not found on this machine." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Quick install (no PATH setup needed):" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  1. Go to  https://www.gyan.dev/ffmpeg/builds/" -ForegroundColor Cyan
    Write-Host "     Click 'ffmpeg-release-essentials.zip'" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  2. Extract the zip — you'll get a folder like:" -ForegroundColor Cyan
    Write-Host "     ffmpeg-7.x-essentials_build\" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  3. Copy/move that folder to  C:\ffmpeg" -ForegroundColor Cyan
    Write-Host "     So that  C:\ffmpeg\bin\ffmpeg.exe  exists." -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  4. Run this script again — no PATH changes needed." -ForegroundColor Cyan
    Write-Host ""
    exit 1
}

Write-Host "  Using FFmpeg: $ffmpeg" -ForegroundColor DarkGray

$root = "d:\sarfraz\rcl-app"
$out  = "$root\public\coming-soon.mp4"

Write-Host ""
Write-Host "  ================================================" -ForegroundColor Green
Write-Host "   RCL App Launch Teaser — Generating video..." -ForegroundColor Green
Write-Host "  ================================================" -ForegroundColor Green
Write-Host ""

# ------------------------------------------------------------------
# Timeline (30 s total):
#   0– 4s  Scene 1  dark bg    "Village cricket has always been..."
#   4– 8s  Scene 2  dark bg    "raw.  passionate.  real."  (gold)
#   8–13s  Scene 3  green bg   "It deserves more than a paper scoreboard."
#  13–18s  Scene 4  dark bg    Huge  R C L  in gold (launch reveal)
#  18–23s  Scene 5  dark bg    Full title + tagline
#  23–27s  Scene 6  dark bg    Feature list
#  27–30s  Scene 7  green bg   COMING SOON
# ------------------------------------------------------------------

# Font paths — escape colon for FFmpeg on Windows
$fb  = 'C\:/Windows/Fonts/ariblk.ttf'   # Arial Black  (titles)
$fbd = 'C\:/Windows/Fonts/arialbd.ttf'  # Arial Bold   (sub-text)
$fr  = 'C\:/Windows/Fonts/arial.ttf'    # Arial Regular(body)

# Build filter. Double-quoted here-string so $fb/$fbd/$fr are interpolated.
# Single quotes INSIDE protect FFmpeg option values (commas, colons etc.).
$vf = @"
drawbox=color=0x1B8A4E:width=iw:height=ih:t=fill:enable='between(t,8,13)',
drawbox=color=0x1B8A4E:width=iw:height=ih:t=fill:enable='between(t,27,30)',
drawtext=fontfile='$fb':text='Village cricket has always been...':fontcolor=white:fontsize=50:x=(w-text_w)/2:y=h/2-50:alpha='if(lt(t,1),t,if(gt(t,3),4-t,1))':enable='between(t,0,4)',
drawtext=fontfile='$fr':text='across Pakistan.':fontcolor=white@0.65:fontsize=30:x=(w-text_w)/2:y=h/2+28:alpha='if(lt(t,2),if(gt(t,1),t-1,0),if(gt(t,3),4-t,1))':enable='between(t,0,4)',
drawtext=fontfile='$fb':text='raw.   passionate.   real.':fontcolor=0xD4900A:fontsize=58:x=(w-text_w)/2:y=h/2:alpha='if(lt(t,5),t-4,if(gt(t,7),8-t,1))':enable='between(t,4,8)',
drawtext=fontfile='$fb':text='It deserves more':fontcolor=white:fontsize=56:x=(w-text_w)/2:y=h/2-52:alpha='if(lt(t,9),t-8,if(gt(t,12),13-t,1))':enable='between(t,8,13)',
drawtext=fontfile='$fbd':text='than a scoreboard on paper.':fontcolor=white@0.85:fontsize=30:x=(w-text_w)/2:y=h/2+30:alpha='if(lt(t,10),if(gt(t,9),t-9,0),if(gt(t,12),13-t,1))':enable='between(t,8,13)',
drawtext=fontfile='$fb':text='R  C  L':fontcolor=0xD4900A:fontsize=170:x=(w-text_w)/2:y=h/2-85:shadowcolor=0xD4900A@0.4:shadowx=6:shadowy=6:alpha='if(lt(t,14),t-13,if(gt(t,17),18-t,1))':enable='between(t,13,18)',
drawtext=fontfile='$fr':text='Royal  Champions  League':fontcolor=white@0.45:fontsize=26:x=(w-text_w)/2:y=h/2+80:letterSpacing=8:alpha='if(lt(t,15),if(gt(t,14),t-14,0),if(gt(t,17),18-t,1))':enable='between(t,13,18)',
drawtext=fontfile='$fb':text='ROYAL CHAMPIONS LEAGUE':fontcolor=white:fontsize=54:x=(w-text_w)/2:y=h/2-58:alpha='if(lt(t,19),t-18,if(gt(t,22),23-t,1))':enable='between(t,18,23)',
drawtext=fontfile='$fbd':text='The Premier Village Cricket Platform':fontcolor=0xD4900A:fontsize=28:x=(w-text_w)/2:y=h/2+28:alpha='if(lt(t,19.5),if(gt(t,19),t-19,0),if(gt(t,22),23-t,1))':enable='between(t,18,23)',
drawtext=fontfile='$fr':text='Pakistan':fontcolor=white@0.4:fontsize=20:x=(w-text_w)/2:y=h/2+76:alpha='if(lt(t,20),if(gt(t,19.5),t-19.5,0),if(gt(t,22),23-t,1))':enable='between(t,18,23)',
drawtext=fontfile='$fb':text='LIVE SCORES':fontcolor=white:fontsize=38:x=(w-text_w)/2:y=h/2-80:alpha='if(lt(t,24),t-23,if(gt(t,26.2),27-t,1))':enable='between(t,23,27)',
drawtext=fontfile='$fbd':text='Player Stats   |   Teams   |   Points Table':fontcolor=0xD4900A:fontsize=28:x=(w-text_w)/2:y=h/2-8:alpha='if(lt(t,24.5),if(gt(t,24),t-24,0),if(gt(t,26.2),27-t,1))':enable='between(t,23,27)',
drawtext=fontfile='$fr':text='Track every ball. Every match. Every player.':fontcolor=white@0.55:fontsize=22:x=(w-text_w)/2:y=h/2+58:alpha='if(lt(t,25.2),if(gt(t,24.8),t-24.8,0),if(gt(t,26.2),27-t,1))':enable='between(t,23,27)',
drawtext=fontfile='$fb':text='COMING SOON':fontcolor=white:fontsize=82:x=(w-text_w)/2:y=h/2-60:alpha='if(lt(t,28),t-27,1)':enable='between(t,27,30)',
drawtext=fontfile='$fr':text='Stay tuned for the official launch':fontcolor=white@0.75:fontsize=28:x=(w-text_w)/2:y=h/2+48:alpha='if(lt(t,28.5),if(gt(t,28),t-28,0),1)':enable='between(t,27,30)'
"@

# Collapse to a single line — FFmpeg filter cannot contain newlines
$vf = ($vf -split "`n" | ForEach-Object { $_.Trim() }) -join ''

Write-Host "  Running FFmpeg — please wait (~30-60 seconds)..." -ForegroundColor Cyan
Write-Host ""

& $ffmpeg -y `
    -f lavfi `
    -i "color=c=0x0D1F16:size=1280x720:rate=30" `
    -vf $vf `
    -t 30 `
    -c:v libx264 `
    -preset medium `
    -crf 18 `
    -pix_fmt yuv420p `
    $out

if ($LASTEXITCODE -eq 0) {
    $size = [math]::Round((Get-Item $out).Length / 1MB, 1)
    Write-Host ""
    Write-Host "  ================================================" -ForegroundColor Green
    Write-Host "   Done!  Video saved successfully." -ForegroundColor Green
    Write-Host "   Path  : public\coming-soon.mp4" -ForegroundColor Cyan
    Write-Host "   Size  : ${size} MB  |  1280x720 HD  |  30s" -ForegroundColor Cyan
    Write-Host "  ================================================" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "  FFmpeg failed — see errors above." -ForegroundColor Red
    Write-Host "  Common fix: ensure Arial Black (ariblk.ttf) exists in C:\Windows\Fonts" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}
