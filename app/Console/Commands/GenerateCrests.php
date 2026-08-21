<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\VccCabinet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Draws a crest for every club and a portrait for every cabinet member.
 *
 * These are SVGs generated from the club's own short code and colours, so a
 * crest always matches the club it belongs to. The set that shipped with the
 * project was generic — the badge for Qadirabad Strikers read "QDC" — which is
 * worse than no logo at all on a broadcast overlay.
 *
 * Re-run it after renaming a club or changing its colours.
 */
class GenerateCrests extends Command
{
    protected $signature = 'rcl:crests {--force : Redraw crests even if one already exists}';

    protected $description = 'Generate club crests and cabinet portraits as SVG';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $made = 0;

        $this->info('Clubs');
        foreach (Team::orderBy('id')->get() as $team) {
            if ($team->short_code === 'TBD') {
                continue;
            }

            $path = "teams/crest-{$team->id}.svg";

            if (! $this->option('force') && $disk->exists($path) && $team->logo === $path) {
                continue;
            }

            $disk->put($path, $this->crest(
                $team->short_code,
                $team->primary_color ?: '#0EA47A',
                $team->secondary_color ?: '#064E3B',
            ));

            $team->update(['logo' => $path]);
            $this->line(sprintf('  %-6s %s', $team->short_code, $team->name));
            $made++;
        }

        $this->newLine();
        $this->info('Cabinet');
        $palette = ['#0EA47A', '#0EA5E9', '#7C5CFC', '#F59E0B', '#F43F5E', '#65A30D', '#06B6D4', '#EC4899'];

        foreach (VccCabinet::orderBy('display_order')->orderBy('id')->get() as $i => $member) {
            $path = "vcc/portrait-{$member->id}.svg";

            if (! $this->option('force') && $disk->exists($path) && $member->photo === $path) {
                continue;
            }

            $disk->put($path, $this->portrait(
                $this->initials($member->name),
                $palette[$i % count($palette)],
            ));

            $member->update(['photo' => $path]);
            $made++;
        }
        $this->line("  {$made} file(s) written in total");

        $this->newLine();
        $this->info('Done. Old generic files are left in place — delete them if you want.');

        return self::SUCCESS;
    }

    /** A roundel: club gradient, a seam arc, and the short code. */
    private function crest(string $code, string $primary, string $secondary): string
    {
        $code = htmlspecialchars(mb_strtoupper(mb_substr($code, 0, 4)), ENT_QUOTES);
        $size = match (mb_strlen($code)) { 1, 2 => 40, 3 => 32, default => 25 };
        $id   = substr(md5($code.$primary), 0, 6);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="200" height="200" role="img" aria-label="{$code}">
  <defs>
    <linearGradient id="g{$id}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$primary}"/>
      <stop offset="100%" stop-color="{$secondary}"/>
    </linearGradient>
    <clipPath id="c{$id}"><circle cx="50" cy="50" r="50"/></clipPath>
  </defs>
  <g clip-path="url(#c{$id})">
    <circle cx="50" cy="50" r="50" fill="url(#g{$id})"/>
    <path d="M22 -6 A 46 62 0 0 1 22 106" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="2.5"/>
    <path d="M78 -6 A 46 62 0 0 0 78 106" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="2.5"/>
  </g>
  <circle cx="50" cy="50" r="47" fill="none" stroke="rgba(255,255,255,.42)" stroke-width="3"/>
  <text x="50" y="50" text-anchor="middle" dominant-baseline="central"
        font-family="'Segoe UI',Roboto,Helvetica,Arial,sans-serif"
        font-size="{$size}" font-weight="800" fill="#fff" letter-spacing="-0.5">{$code}</text>
</svg>
SVG;
    }

    /** A portrait placeholder: a soft disc with the member's initials. */
    private function portrait(string $initials, string $colour): string
    {
        $initials = htmlspecialchars($initials, ENT_QUOTES);
        $id = substr(md5($initials.$colour), 0, 6);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="200" height="200" role="img" aria-label="{$initials}">
  <defs>
    <linearGradient id="p{$id}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$colour}" stop-opacity="0.20"/>
      <stop offset="100%" stop-color="{$colour}" stop-opacity="0.42"/>
    </linearGradient>
  </defs>
  <circle cx="50" cy="50" r="50" fill="url(#p{$id})"/>
  <circle cx="50" cy="50" r="48" fill="none" stroke="{$colour}" stroke-opacity="0.5" stroke-width="2.5"/>
  <text x="50" y="50" text-anchor="middle" dominant-baseline="central"
        font-family="'Segoe UI',Roboto,Helvetica,Arial,sans-serif"
        font-size="34" font-weight="800" fill="{$colour}">{$initials}</text>
</svg>
SVG;
    }

    private function initials(string $name): string
    {
        $parts = array_values(array_filter(preg_split('/\s+/', trim($name)) ?: []));

        if (count($parts) === 0) {
            return '?';
        }
        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1));
    }
}
