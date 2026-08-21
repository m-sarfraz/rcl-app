<?php

namespace App\Services;

use App\Models\BallByBallLog;
use App\Models\BattingScorecard;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Support\Overs;
use Illuminate\Support\Collection;

/**
 * One broadcast-grade snapshot of a match, shared by every scoreboard surface:
 * the embeddable widgets, the stream overlays, the Open Graph image, and the
 * JSON the browser polls.
 *
 * The shape here is driven by what a cricket broadcast actually puts on screen —
 * not just the score, but who is at the crease, what the bowler's figures are,
 * how the partnership is going, when the last wicket fell, and what the chase
 * needs. An overlay that has to make three requests to fill a lower third is an
 * overlay that flickers, so this is deliberately one payload.
 */
class ScoreboardService
{
    public function __construct(private readonly ScoringService $scoring) {}

    /** @return array<string, mixed> */
    public function snapshot(CricketMatch $match): array
    {
        $match->loadMissing([
            'homeTeam', 'awayTeam', 'winner', 'edition',
            'innings.battingTeam', 'innings.bowlingTeam',
        ]);

        $innings = $match->innings->sortBy('innings_number')->values();
        $current = $innings->firstWhere('is_completed', false) ?? $innings->last();

        return [
            'match' => [
                'id'         => $match->id,
                'number'     => $match->match_number,
                'type'       => $match->match_type_label ?? ucwords(str_replace('_', ' ', (string) $match->match_type)),
                'status'     => $match->status,
                'is_live'    => $match->status === 'live',
                'is_done'    => $match->status === 'completed',
                'venue'      => $match->venue,
                'edition'    => $match->edition?->name,
                'overs'      => (int) $match->overs_per_side,
                'toss'       => $this->tossLine($match),
                'result'     => $match->result_description,
                'starts_at'  => $match->scheduled_at?->toIso8601String(),
                'is_super_over' => (bool) $match->is_super_over,
            ],
            'teams' => [
                'home' => $this->teamLine($match, $match->homeTeam, $innings),
                'away' => $this->teamLine($match, $match->awayTeam, $innings),
            ],
            'current'     => $current ? $this->inningsLine($match, $current) : null,
            'innings'     => $innings->map(fn ($i) => $this->inningsSummary($i))->all(),
            'headline'    => $this->headline($match, $current),
            'sub_headline'=> $this->subHeadline($match, $current),
            'status_line' => $this->statusLine($match, $current),
            'updated_at'  => now()->toIso8601String(),
            'revision'    => $this->revision($match, $innings),
        ];
    }

    /* ── Teams ─────────────────────────────────────────────────── */

    /** @return array<string, mixed> */
    private function teamLine(CricketMatch $match, $team, Collection $innings): array
    {
        $own = $innings->firstWhere('batting_team_id', $team?->id);

        return [
            'id'        => $team?->id,
            'name'      => $team?->name ?? 'TBD',
            'short'     => $team?->short_code ?? '—',
            'village'   => $team?->village_name,
            'colour'    => $this->colour($team?->primary_color),
            'colour_2'  => $this->colour($team?->secondary_color, '#064E3B'),
            'logo'      => $team?->logo ? asset('storage/'.$team->logo) : null,
            'score'     => $own ? "{$own->total_runs}/{$own->total_wickets}" : null,
            'runs'      => $own ? (int) $own->total_runs : null,
            'wickets'   => $own ? (int) $own->total_wickets : null,
            'overs'     => $own ? Overs::display((int) $own->total_balls) : null,
            'run_rate'  => $own ? round((float) $own->run_rate, 2) : null,
            'batting'   => $own && ! $own->is_completed && $match->status === 'live',
            'is_winner' => $match->winner_id !== null && $match->winner_id === $team?->id,
        ];
    }

    /* ── Innings ───────────────────────────────────────────────── */

    /** @return array<string, mixed> */
    private function inningsSummary(Innings $innings): array
    {
        return [
            'number'    => (int) $innings->innings_number,
            'team'      => $innings->battingTeam?->short_code,
            'team_name' => $innings->battingTeam?->name,
            'score'     => "{$innings->total_runs}/{$innings->total_wickets}",
            'runs'      => (int) $innings->total_runs,
            'wickets'   => (int) $innings->total_wickets,
            'overs'     => Overs::display((int) $innings->total_balls),
            'run_rate'  => round((float) $innings->run_rate, 2),
            'extras'    => (int) $innings->total_extras,
            'completed' => (bool) $innings->is_completed,
        ];
    }

    /** @return array<string, mixed> */
    private function inningsLine(CricketMatch $match, Innings $innings): array
    {
        // One pass over the log feeds the partnership, the last wicket, the
        // over strip and the recent-overs summary.
        $balls = BallByBallLog::where('innings_id', $innings->id)
            ->orderBy('id')
            ->get();

        $last       = $balls->last();
        $overNumber = $last?->over_number ?? 1;
        $thisOver   = $balls->filter(fn ($b) => $b->over_number === $overNumber && ! $b->is_penalty)->values();

        [$strikerId, $nonStrikerId] = $this->atCrease($innings, $last);

        $legalBalls = (int) $innings->total_balls;
        $quota      = $this->quota($match, $innings);
        $ballsLeft  = max(0, $quota - $legalBalls);

        return [
            'number'       => (int) $innings->innings_number,
            'team'         => $innings->battingTeam?->short_code,
            'team_name'    => $innings->battingTeam?->name,
            'team_colour'  => $this->colour($innings->battingTeam?->primary_color),
            'bowling_team' => $innings->bowlingTeam?->short_code,
            'bowling_team_name' => $innings->bowlingTeam?->name,

            'runs'      => (int) $innings->total_runs,
            'wickets'   => (int) $innings->total_wickets,
            'score'     => "{$innings->total_runs}/{$innings->total_wickets}",
            'overs'     => Overs::display($legalBalls),
            'overs_max' => (int) ($quota / 6),
            'balls'     => $legalBalls,
            'balls_left'=> $ballsLeft,
            'run_rate'  => round((float) $innings->run_rate, 2),

            'extras'    => (int) $innings->total_extras,
            'extras_breakdown' => [
                'wides'    => (int) $innings->extras_wides,
                'no_balls' => (int) $innings->extras_no_balls,
                'byes'     => (int) $innings->extras_byes,
                'leg_byes' => (int) $innings->extras_leg_byes,
                'penalty'  => (int) $innings->extras_penalty,
            ],

            'completed' => (bool) $innings->is_completed,

            'striker'     => $this->batterLine($innings, $strikerId, true),
            'non_striker' => $this->batterLine($innings, $nonStrikerId, false),
            'bowler'      => $last ? $this->bowlerLine($innings, (int) $last->bowler_id) : null,

            'partnership'  => $this->partnership($balls, $strikerId, $nonStrikerId),
            'last_wicket'  => $this->lastWicket($innings, $balls),

            'this_over'    => $thisOver->map(fn ($b) => $this->ballChip($b))->all(),
            'over_number'  => $overNumber,
            'recent_overs' => $this->recentOvers($balls),

            'target'         => $innings->target ? (int) $innings->target : null,
            'need'           => $innings->target ? max(0, (int) $innings->target - (int) $innings->total_runs) : null,
            'required_rate'  => $innings->target ? $this->scoring->requiredRunRate($innings) : null,
            'projected'      => $this->projected($innings, $legalBalls, $quota),

            'top_scorer'  => $this->topScorer($innings),
            'top_bowler'  => $this->topBowler($innings),
        ];
    }

    /**
     * Who is at the crease. Prefers the last delivery, because that is the
     * authority mid-over; falls back to whoever is not out on the card so the
     * overlay is not blank before the first ball.
     *
     * @return array{0:?int, 1:?int}
     */
    private function atCrease(Innings $innings, ?BallByBallLog $last): array
    {
        if ($last) {
            return [(int) $last->batsman_id, $last->non_striker_id ? (int) $last->non_striker_id : null];
        }

        $notOut = $innings->battingScorecards()
            ->whereIn('dismissal_type', ['not_out', 'retired_hurt'])
            ->where('balls_faced', '>', 0)
            ->orderBy('batting_position')
            ->limit(2)
            ->pluck('player_id');

        return [$notOut->first(), $notOut->skip(1)->first()];
    }

    /** @return array<string, mixed>|null */
    private function batterLine(Innings $innings, ?int $playerId, bool $onStrike): ?array
    {
        if (! $playerId) {
            return null;
        }

        $card = $innings->battingScorecards()
            ->with('player:id,name')
            ->where('player_id', $playerId)
            ->first();

        if (! $card) {
            return null;
        }

        return [
            'id'        => (int) $card->player_id,
            'name'      => $card->player?->name,
            'short'     => $this->shortName($card->player?->name),
            'runs'      => (int) $card->runs_scored,
            'balls'     => (int) $card->balls_faced,
            'fours'     => (int) $card->fours,
            'sixes'     => (int) $card->sixes,
            'sr'        => round((float) $card->strike_rate, 1),
            'on_strike' => $onStrike,
            'milestone' => $card->is_century ? 'century' : ($card->is_fifty ? 'fifty' : null),
            'display'   => sprintf('%d%s (%d)', $card->runs_scored, $onStrike ? '*' : '', $card->balls_faced),
        ];
    }

    /** @return array<string, mixed>|null */
    private function bowlerLine(Innings $innings, int $playerId): ?array
    {
        $card = $innings->bowlingScorecards()
            ->with('player:id,name')
            ->where('player_id', $playerId)
            ->first();

        if (! $card) {
            return null;
        }

        return [
            'id'      => (int) $card->player_id,
            'name'    => $card->player?->name,
            'short'   => $this->shortName($card->player?->name),
            'wickets' => (int) $card->wickets,
            'runs'    => (int) $card->runs_conceded,
            'overs'   => Overs::display((int) $card->overs_bowled_balls),
            'maidens' => (int) $card->maidens,
            'economy' => round((float) $card->economy, 2),
            'figures' => "{$card->wickets}/{$card->runs_conceded}",
            /* The full O-M-R-W line a broadcast shows. */
            'line'    => sprintf(
                '%s-%d-%d-%d',
                Overs::display((int) $card->overs_bowled_balls),
                $card->maidens,
                $card->runs_conceded,
                $card->wickets,
            ),
            'milestone' => $card->five_wicket_haul ? 'five_for' : ($card->hat_trick_wickets ? 'hat_trick' : null),
        ];
    }

    /**
     * Runs and balls added since the last wicket fell. Broadcast staple — it is
     * how a viewer reads whether a stand is rebuilding or stalling.
     *
     * @return array<string, mixed>|null
     */
    private function partnership(Collection $balls, ?int $strikerId, ?int $nonStrikerId): ?array
    {
        if (! $strikerId && ! $nonStrikerId) {
            return null;
        }

        $sinceWicket = [];
        foreach ($balls as $ball) {
            if ($ball->is_wicket && $ball->wicket_type !== 'retired_hurt') {
                $sinceWicket = [];
                continue;
            }
            $sinceWicket[] = $ball;
        }

        $runs  = 0;
        $legal = 0;

        foreach ($sinceWicket as $ball) {
            $runs += (int) $ball->runs_scored + (int) $ball->extra_runs;
            if (! $ball->is_wide && ! $ball->is_no_ball && ! $ball->is_penalty) {
                $legal++;
            }
        }

        if ($runs === 0 && $legal === 0) {
            return null;
        }

        return [
            'runs'    => $runs,
            'balls'   => $legal,
            'overs'   => Overs::display($legal),
            'display' => "{$runs} ({$legal})",
        ];
    }

    /** @return array<string, mixed>|null */
    private function lastWicket(Innings $innings, Collection $balls): ?array
    {
        $ball = $balls->last(fn ($b) => $b->is_wicket && $b->wicket_type !== 'retired_hurt');

        if (! $ball) {
            return null;
        }

        $outId = $ball->out_player_id ?: $ball->batsman_id;

        $card = BattingScorecard::with('player:id,name')
            ->where('innings_id', $innings->id)
            ->where('player_id', $outId)
            ->first();

        $name = $card?->player?->name ?? 'Batter';

        return [
            'name'    => $name,
            'short'   => $this->shortName($name),
            'runs'    => (int) ($card->runs_scored ?? 0),
            'balls'   => (int) ($card->balls_faced ?? 0),
            'how'     => str_replace('_', ' ', (string) $ball->wicket_type),
            'at'      => "{$ball->batting_team_score_after}/{$ball->batting_team_wickets_after}",
            'over'    => "{$ball->over_number}.{$ball->ball_number}",
            'display' => sprintf(
                '%s %d (%d) — %s, %s/%d in %d.%d',
                $this->shortName($name),
                (int) ($card->runs_scored ?? 0),
                (int) ($card->balls_faced ?? 0),
                str_replace('_', ' ', (string) $ball->wicket_type),
                $ball->batting_team_score_after,
                $ball->batting_team_wickets_after,
                $ball->over_number,
                $ball->ball_number,
            ),
        ];
    }

    /**
     * Runs conceded in each of the last few overs — the strip a broadcast shows
     * along the bottom so a viewer can see momentum at a glance.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentOvers(Collection $balls, int $count = 5): array
    {
        return $balls->reject(fn ($b) => $b->is_penalty)
            ->groupBy('over_number')
            ->map(fn (Collection $over, $number) => [
                'over'    => (int) $number,
                'runs'    => (int) $over->sum(fn ($b) => (int) $b->runs_scored + (int) $b->extra_runs),
                'wickets' => $over->filter(fn ($b) => $b->is_wicket && $b->wicket_type !== 'retired_hurt')->count(),
                'complete'=> $over->reject(fn ($b) => $b->is_wide || $b->is_no_ball)->count() >= 6,
            ])
            ->values()
            ->take(-$count)
            ->values()
            ->all();
    }

    /** Where this innings lands if the current rate holds. */
    private function projected(Innings $innings, int $legalBalls, int $quota): ?int
    {
        if ($legalBalls < 6 || $innings->is_completed || $legalBalls >= $quota) {
            return null;
        }

        $rate = Overs::decimal($legalBalls) > 0
            ? (int) $innings->total_runs / Overs::decimal($legalBalls)
            : 0;

        return (int) round($rate * Overs::decimal($quota));
    }

    /** @return array<string, mixed>|null */
    private function topScorer(Innings $innings): ?array
    {
        $card = $innings->battingScorecards()
            ->with('player:id,name')
            ->orderByDesc('runs_scored')
            ->first();

        if (! $card || (int) $card->runs_scored === 0) {
            return null;
        }

        return [
            'name'    => $card->player?->name,
            'short'   => $this->shortName($card->player?->name),
            'runs'    => (int) $card->runs_scored,
            'balls'   => (int) $card->balls_faced,
            'display' => "{$card->player?->name} {$card->runs_scored} ({$card->balls_faced})",
        ];
    }

    /** @return array<string, mixed>|null */
    private function topBowler(Innings $innings): ?array
    {
        $card = $innings->bowlingScorecards()
            ->with('player:id,name')
            ->orderByDesc('wickets')
            ->orderBy('runs_conceded')
            ->first();

        if (! $card || (int) $card->overs_bowled_balls === 0) {
            return null;
        }

        return [
            'name'    => $card->player?->name,
            'short'   => $this->shortName($card->player?->name),
            'figures' => "{$card->wickets}/{$card->runs_conceded}",
            'overs'   => Overs::display((int) $card->overs_bowled_balls),
            'display' => "{$card->player?->name} {$card->wickets}/{$card->runs_conceded}",
        ];
    }

    /* ── Chips & lines ─────────────────────────────────────────── */

    /** @return array<string, string> */
    private function ballChip(BallByBallLog $ball): array
    {
        return ['label' => $this->ballLabel($ball), 'kind' => $this->ballKind($ball)];
    }

    private function ballLabel(BallByBallLog $ball): string
    {
        if ($ball->is_wicket && $ball->wicket_type !== 'retired_hurt') return 'W';
        if ($ball->is_penalty) return 'P';
        if ($ball->is_wide)    return $ball->extra_runs > 1 ? ($ball->extra_runs - 1).'wd' : 'wd';
        if ($ball->is_no_ball) return $ball->runs_scored > 0 ? $ball->runs_scored.'nb' : 'nb';
        if ($ball->is_bye)     return $ball->extra_runs.'b';
        if ($ball->is_leg_bye) return $ball->extra_runs.'lb';

        return (string) $ball->runs_scored;
    }

    private function ballKind(BallByBallLog $ball): string
    {
        if ($ball->is_wicket && $ball->wicket_type !== 'retired_hurt') return 'wicket';
        if ($ball->is_six)  return 'six';
        if ($ball->is_four) return 'four';
        if ($ball->is_wide || $ball->is_no_ball || $ball->is_bye || $ball->is_leg_bye) return 'extra';
        if ((int) $ball->runs_scored === 0) return 'dot';

        return 'run';
    }

    private function tossLine(CricketMatch $match): ?string
    {
        if (! $match->toss_winner_id || ! $match->toss_decision) {
            return null;
        }

        $name = $match->toss_winner_id === $match->home_team_id
            ? $match->homeTeam?->short_code
            : $match->awayTeam?->short_code;

        return $name ? "{$name} won the toss, chose to {$match->toss_decision}" : null;
    }

    /** The one line a broadcast puts across the middle. */
    private function headline(CricketMatch $match, ?Innings $current): string
    {
        $home = $match->homeTeam?->short_code ?? 'TBD';
        $away = $match->awayTeam?->short_code ?? 'TBD';

        if ($match->status === 'completed') {
            return $match->result_description ?: "{$home} v {$away} — result";
        }

        if (in_array($match->status, ['abandoned', 'postponed'], true)) {
            return $match->result_description ?: ucfirst($match->status);
        }

        if ($match->status === 'upcoming' || ! $current) {
            return "{$home} v {$away} — "
                .($match->scheduled_at?->format('D j M, g:i A') ?? 'time to be confirmed');
        }

        return sprintf(
            '%s %d/%d (%s)',
            $current->battingTeam?->short_code ?? '—',
            $current->total_runs,
            $current->total_wickets,
            Overs::display((int) $current->total_balls),
        );
    }

    /** The chase line, or whatever else is worth a second row. */
    private function subHeadline(CricketMatch $match, ?Innings $current): ?string
    {
        if (! $current || $match->status !== 'live') {
            return $match->status === 'upcoming' ? $match->venue : null;
        }

        if ($current->target) {
            $need = max(0, (int) $current->target - (int) $current->total_runs);
            $left = max(0, $this->quota($match, $current) - (int) $current->total_balls);

            if ($need <= 0)  return 'Target reached';
            if ($left <= 0)  return 'Overs complete';

            return sprintf(
                'Need %d off %d ball%s  ·  RRR %.2f',
                $need,
                $left,
                $left === 1 ? '' : 's',
                $this->scoring->requiredRunRate($current),
            );
        }

        return sprintf('Run rate %.2f', (float) $current->run_rate);
    }

    private function statusLine(CricketMatch $match, ?Innings $current): string
    {
        return match ($match->status) {
            'live'      => $current && $current->target ? 'CHASING' : 'LIVE',
            'completed' => 'RESULT',
            'upcoming'  => 'UPCOMING',
            'abandoned' => 'ABANDONED',
            'postponed' => 'POSTPONED',
            default     => strtoupper((string) $match->status),
        };
    }

    /** A super over is one over regardless of the match format. */
    private function quota(CricketMatch $match, Innings $innings): int
    {
        return (int) $innings->innings_number >= 3 ? 6 : (int) $match->overs_per_side * 6;
    }

    /** "Muhammad Abdullah Rajput" → "M A Rajput" — fits a lower third. */
    private function shortName(?string $name): string
    {
        if (! $name) {
            return '—';
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) <= 2) {
            return $name;
        }

        $surname = array_pop($parts);
        $initials = implode(' ', array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), $parts));

        return "{$initials} {$surname}";
    }

    private function colour(?string $hex, string $fallback = '#0EA47A'): string
    {
        $hex = '#'.ltrim((string) $hex, '#');

        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : $fallback;
    }

    /**
     * A short fingerprint of everything visible. Unchanged when nothing has
     * moved, which lets the widget skip a repaint and a crawler tell a stale
     * preview from a current one.
     */
    private function revision(CricketMatch $match, Collection $innings): string
    {
        $parts = [$match->status, $match->winner_id, $match->updated_at?->timestamp];

        foreach ($innings as $inn) {
            $parts[] = "{$inn->innings_number}:{$inn->total_runs}:{$inn->total_wickets}:{$inn->total_balls}:{$inn->is_completed}";
        }

        return substr(md5(implode('|', $parts)), 0, 12);
    }
}
