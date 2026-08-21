<?php

namespace App\Services;

use App\Models\CricketMatch;

/**
 * Renders the match scoreboard as a PNG.
 *
 * This is what a social crawler picks up as `og:image`, so it has to carry the
 * whole story on its own: both sides, the live score, and the state of the
 * game. 1200×630 is the size Facebook and X both crop cleanly.
 *
 * Fonts are bundled under resources/fonts rather than taken from the host, so
 * the image looks identical wherever this is deployed.
 */
class ScoreboardImageService
{
    private const W = 1200;
    private const H = 630;

    public function __construct(private readonly ScoreboardService $scoreboard) {}

    public function render(CricketMatch $match): string
    {
        $data = $this->scoreboard->snapshot($match);

        $im = imagecreatetruecolor(self::W, self::H);
        imageantialias($im, true);

        $this->paintBackground($im, $data);
        $this->paintChrome($im, $data);
        $this->paintTeams($im, $data);
        $this->paintFooter($im, $data);

        ob_start();
        imagepng($im, null, 6);
        $png = (string) ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    /* ── Painting ──────────────────────────────────────────────── */

    private function paintBackground($im, array $data): void
    {
        // A diagonal wash. Painted per pixel-row-and-column rather than with
        // translucent overlays: stacked alpha shapes compound in GD and show up
        // as visible banding.
        [$r1, $g1, $b1] = [16, 150, 110];   // top-left, lighter
        [$r2, $g2, $b2] = [5, 68, 52];      // bottom-right, deep

        $step = 4;
        for ($y = 0; $y < self::H; $y += $step) {
            for ($x = 0; $x < self::W; $x += $step) {
                $t = (($x / self::W) * 0.38) + (($y / self::H) * 0.62);
                $colour = imagecolorallocate(
                    $im,
                    (int) ($r1 + ($r2 - $r1) * $t),
                    (int) ($g1 + ($g2 - $g1) * $t),
                    (int) ($b1 + ($b2 - $b1) * $t),
                );
                imagefilledrectangle($im, $x, $y, $x + $step, $y + $step, $colour);
            }
        }

        // Top rule split between the two clubs' colours.
        $mid = (int) (self::W / 2);
        imagefilledrectangle($im, 0, 0, $mid, 9, $this->hexColour($im, $data['teams']['home']['colour'] ?? null));
        imagefilledrectangle($im, $mid, 0, self::W, 9, $this->hexColour($im, $data['teams']['away']['colour'] ?? null));
    }

    private function paintChrome($im, array $data): void
    {
        $white = imagecolorallocate($im, 255, 255, 255);
        $faint = $this->alpha($im, 255, 255, 255, 55);

        // League mark, top left.
        $this->text($im, 34, 46, 74, $white, 'ROYAL CHAMPIONS LEAGUE', bold: true, spacing: 3);
        $this->text($im, 20, 46, 108, $faint, strtoupper(
            trim(($data['match']['edition'] ?? 'RCL').'  ·  '.($data['match']['type'] ?? ''))
        ), spacing: 2);

        // Status pill, top right — sized from the text it actually holds.
        $status  = $data['status_line'];
        $isLive  = $data['match']['is_live'];
        $fontPx  = 24;
        $spacing = 3;

        $textW = $this->trackedWidth($fontPx, $status, $spacing);
        $dotW  = $isLive ? 34 : 0;
        $padX  = 26;

        $x2 = self::W - 46;
        $x1 = $x2 - ($textW + $dotW + $padX * 2);

        // Solid fills — a translucent rounded rect would show its corner
        // ellipses where the alpha doubles up.
        $pill = $isLive
            ? imagecolorallocate($im, 239, 68, 68)
            : imagecolorallocate($im, 22, 116, 90);

        $this->roundedRect($im, $x1, 42, $x2, 94, 26, $pill);

        $textX = $x1 + $padX;

        if ($isLive) {
            imagefilledellipse($im, (int) ($textX + 8), 68, 15, 15, $white);
            $textX += $dotW;
        }

        $this->text($im, $fontPx, $textX, 77, $white, $status, bold: true, spacing: $spacing);
    }

    private function paintTeams($im, array $data): void
    {
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = $this->alpha($im, 255, 255, 255, 62);

        $home = $data['teams']['home'];
        $away = $data['teams']['away'];

        $this->paintTeamBlock($im, $home, 60, 175, 'left');
        $this->paintTeamBlock($im, $away, self::W - 60, 175, 'right');

        // Divider.
        $this->text($im, 30, self::W / 2 - 22, 225, $muted, 'vs', bold: true);

        $rule = $this->alpha($im, 255, 255, 255, 112);
        imagefilledrectangle($im, 300, 320, self::W - 300, 321, $rule);

        // Headline across the middle.
        $headline = $data['headline'];
        $size = strlen($headline) > 52 ? 26 : (strlen($headline) > 38 ? 30 : 34);
        $this->centred($im, $size, 396, $white, $headline, bold: true);

        $current = $data['current'] ?? null;

        // Chase line, in gold, because it is the fact that matters most.
        if (! empty($data['sub_headline'])) {
            $gold = imagecolorallocate($im, 252, 211, 77);
            $this->centred($im, 22, 436, $gold, $data['sub_headline'], bold: true);
        }

        if (! $current) {
            return;
        }

        // Who is at the crease, and who is bowling.
        $people = [];
        foreach ([$current['striker'] ?? null, $current['non_striker'] ?? null] as $bat) {
            if ($bat) {
                $people[] = sprintf('%s %s', $bat['short'], $bat['display']);
            }
        }
        if (! empty($current['bowler'])) {
            $people[] = sprintf('%s %s', $current['bowler']['short'], $current['bowler']['line']);
        }

        if ($people) {
            $this->centred($im, 21, 476, $muted, implode('     ·     ', $people));
        }

        // This over, as chips.
        if (! empty($current['this_over'])) {
            $this->paintOver($im, array_slice($current['this_over'], -8), 522);
        }
    }

    /** @param array<int, array{label:string, kind:string}> $balls */
    private function paintOver($im, array $balls, int $y): void
    {
        if (! $balls) {
            return;
        }

        $chip = 34;
        $gap  = 9;
        $total = count($balls) * $chip + (count($balls) - 1) * $gap;
        $x = (self::W - $total) / 2;

        $palette = [
            'wicket' => [239, 68, 68],
            'six'    => [245, 158, 11],
            'four'   => [14, 165, 233],
            'extra'  => [124, 92, 252],
            'run'    => [16, 185, 129],
            'dot'    => [100, 116, 139],
        ];

        foreach ($balls as $ball) {
            [$r, $g, $b] = $palette[$ball['kind']] ?? $palette['dot'];

            $fill = imagecolorallocate($im, (int) ($r * 0.32), (int) ($g * 0.32), (int) ($b * 0.32));
            $edge = imagecolorallocate($im, $r, $g, $b);
            $text = imagecolorallocate($im, min(255, $r + 70), min(255, $g + 70), min(255, $b + 70));

            $this->roundedRect($im, $x, $y - $chip / 2, $x + $chip, $y + $chip / 2, (int) ($chip / 2), $edge);
            $this->roundedRect($im, $x + 2, $y - $chip / 2 + 2, $x + $chip - 2, $y + $chip / 2 - 2, (int) ($chip / 2) - 2, $fill);

            $label = $ball['label'];
            $fs    = mb_strlen($label) > 2 ? 12 : 15;
            $this->text(
                $im, $fs,
                $x + ($chip - $this->width($fs, $label, true)) / 2,
                $y + $fs / 2 - 1,
                $text, $label, bold: true,
            );

            $x += $chip + $gap;
        }
    }

    /** @param array<string, mixed> $team */
    private function paintTeamBlock($im, array $team, int $x, int $y, string $align): void
    {
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = $this->alpha($im, 255, 255, 255, 70);

        $crestSize = 96;
        $crestX = $align === 'left' ? $x : $x - $crestSize;

        $this->roundedRect($im, $crestX, $y, $crestX + $crestSize, $y + $crestSize, 26,
            $this->hexColour($im, $team['colour']));

        $short = (string) ($team['short'] ?? '—');
        $this->text($im, 34, $crestX + ($crestSize - $this->width(34, $short, true)) / 2,
            $y + 63, $white, $short, bold: true);

        $textX = $align === 'left' ? $x + $crestSize + 24 : $x - $crestSize - 24;

        // Names must stop short of the "vs" in the middle, or long club names
        // run straight through it.
        $gutter   = 54;
        $centre   = self::W / 2;
        $maxWidth = $align === 'left'
            ? ($centre - $gutter) - $textX
            : $textX - ($centre + $gutter);

        [$name, $nameSize] = $this->fit((string) $team['name'], $maxWidth, 24, 16);

        $hasScore = ! empty($team['score']);
        $score    = $hasScore ? (string) $team['score'] : 'yet to bat';
        $scoreSize = $hasScore ? 58 : 26;
        $overs    = $hasScore && $team['overs'] ? "({$team['overs']})" : '';

        if ($align === 'left') {
            $this->text($im, $nameSize, $textX, $y + 26, $muted, $name, bold: true);
            $this->text($im, $scoreSize, $textX, $y + 92, $hasScore ? $white : $muted, $score, bold: true);
            if ($overs) {
                $this->text($im, 22, $textX + $this->width($scoreSize, $score, true) + 14,
                    $y + 92, $muted, $overs);
            }
        } else {
            $this->rightText($im, $nameSize, $textX, $y + 26, $muted, $name, bold: true);
            $oversW = $overs ? $this->width(22, $overs, false) + 14 : 0;
            $this->text($im, $scoreSize, $textX - $this->width($scoreSize, $score, true) - $oversW,
                $y + 92, $hasScore ? $white : $muted, $score, bold: true);
            if ($overs) {
                $this->rightText($im, 22, $textX, $y + 92, $muted, $overs);
            }
        }
    }

    /**
     * Shrink then, only if it still will not go, ellipsize.
     *
     * @return array{0:string, 1:float} the text to draw and the size to draw it at
     */
    private function fit(string $text, float $maxWidth, float $size, float $minSize): array
    {
        if ($maxWidth <= 0) {
            return [$text, $size];
        }

        while ($size > $minSize && $this->width($size, $text, true) > $maxWidth) {
            $size -= 1;
        }

        while (mb_strlen($text) > 4 && $this->width($size, $text, true) > $maxWidth) {
            $text = rtrim(mb_substr($text, 0, mb_strlen($text) - 2)).'…';
        }

        return [$text, $size];
    }

    private function paintFooter($im, array $data): void
    {
        $muted = $this->alpha($im, 255, 255, 255, 80);
        $rule  = $this->alpha($im, 255, 255, 255, 112);

        imagefilledrectangle($im, 60, 578, self::W - 60, 579, $rule);

        $left = trim(implode('   ·   ', array_filter([
            $data['match']['venue'],
            $data['match']['toss'],
        ])));

        if ($left !== '') {
            if (mb_strlen($left) > 62) {
                $left = mb_substr($left, 0, 61).'…';
            }
            $this->text($im, 19, 60, 608, $muted, $left);
        }

        $this->rightText($im, 19, self::W - 60, 608, $muted, 'Village Cricket Council');
    }

    /* ── Drawing helpers ───────────────────────────────────────── */

    private function font(bool $bold = false): string
    {
        $path = resource_path('fonts/'.($bold ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf'));

        if (is_readable($path)) {
            return $path;
        }

        // Bundled font missing — fall back to anything the host provides.
        foreach ([
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ] as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }

    private function text($im, float $size, float $x, float $y, int $colour, string $text, bool $bold = false, int $spacing = 0): void
    {
        if ($spacing > 0) {
            // GD has no letter-spacing, so wide-tracked labels are drawn per glyph.
            $cursor = $x;
            foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
                imagettftext($im, $size, 0, (int) $cursor, (int) $y, $colour, $this->font($bold), $char);
                $cursor += $this->width($size, $char, $bold) + $spacing;
            }

            return;
        }

        imagettftext($im, $size, 0, (int) $x, (int) $y, $colour, $this->font($bold), $text);
    }

    private function rightText($im, float $size, float $x, float $y, int $colour, string $text, bool $bold = false): void
    {
        $this->text($im, $size, $x - $this->width($size, $text, $bold), $y, $colour, $text, $bold);
    }

    private function centred($im, float $size, float $y, int $colour, string $text, bool $bold = false): void
    {
        $this->text($im, $size, (self::W - $this->width($size, $text, $bold)) / 2, $y, $colour, $text, $bold);
    }

    /** Width of a string once per-glyph tracking is applied. */
    private function trackedWidth(float $size, string $text, int $spacing): float
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $total = 0.0;

        foreach ($chars as $char) {
            $total += $this->width($size, $char, true) + $spacing;
        }

        return max(0, $total - $spacing);
    }

    private function width(float $size, string $text, bool $bold): float
    {
        $box = imagettfbbox($size, 0, $this->font($bold), $text);

        return abs($box[2] - $box[0]);
    }

    private function roundedRect($im, float $x1, float $y1, float $x2, float $y2, int $radius, int $colour): void
    {
        $d = $radius * 2;

        imagefilledrectangle($im, (int) ($x1 + $radius), (int) $y1, (int) ($x2 - $radius), (int) $y2, $colour);
        imagefilledrectangle($im, (int) $x1, (int) ($y1 + $radius), (int) $x2, (int) ($y2 - $radius), $colour);

        imagefilledellipse($im, (int) ($x1 + $radius), (int) ($y1 + $radius), $d, $d, $colour);
        imagefilledellipse($im, (int) ($x2 - $radius), (int) ($y1 + $radius), $d, $d, $colour);
        imagefilledellipse($im, (int) ($x1 + $radius), (int) ($y2 - $radius), $d, $d, $colour);
        imagefilledellipse($im, (int) ($x2 - $radius), (int) ($y2 - $radius), $d, $d, $colour);
    }

    /** GD alpha runs 0 (opaque) to 127 (invisible); clamp so it can never throw. */
    private function alpha($im, int $r, int $g, int $b, int $alpha): int
    {
        return imagecolorallocatealpha($im, $r, $g, $b, max(0, min(127, $alpha)));
    }

    private function hexColour($im, ?string $hex): int
    {
        $hex = ltrim((string) $hex, '#');

        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            $hex = '0EA47A';
        }

        return imagecolorallocate(
            $im,
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        );
    }
}
