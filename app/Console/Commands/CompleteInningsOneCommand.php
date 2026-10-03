<?php

namespace App\Console\Commands;

use App\Models\BallByBallLog;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Services\MatchStatisticsService;
use App\Services\ScoreboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CompleteInningsOneCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'match:complete-inn1 {match=164 : The Match ID (defaults to 164)}';

    /**
     * The console command description.
     */
    protected $description = 'Completes 1st innings by adding the last 2 balls (dot + single) from 9.4 to 10.0 overs (110 runs), updates target, and rebuilds scorecards';

    public function handle(MatchStatisticsService $stats, ScoreboardService $scoreboard): int
    {
        $matchId = (int) $this->argument('match');

        $match = CricketMatch::with(['homeTeam', 'awayTeam'])->find($matchId);

        if (! $match) {
            $this->error("Match #{$matchId} not found.");
            return self::FAILURE;
        }

        $this->info("Found Match #{$match->id}: {$match->homeTeam?->name} vs {$match->awayTeam?->name}");

        $inn1 = Innings::where('match_id', $match->id)->where('innings_number', 1)->first();
        if (! $inn1) {
            $this->error("Innings 1 not found for Match #{$match->id}.");
            return self::FAILURE;
        }

        $inn2 = Innings::where('match_id', $match->id)->where('innings_number', 2)->first();

        $this->line("Current Innings 1: {$inn1->total_runs}/{$inn1->total_wickets} in {$inn1->overs_faced} overs ({$inn1->total_balls} legal balls), Completed: " . ($inn1->is_completed ? 'YES' : 'NO'));

        $legalBalls = BallByBallLog::where('innings_id', $inn1->id)
            ->where('is_wide', false)
            ->where('is_no_ball', false)
            ->count();

        if ($legalBalls >= 60 && $inn1->is_completed) {
            $this->warn("Innings 1 already has {$legalBalls} legal balls (10.0 overs) and is completed.");
        } else {
            // Last delivery to get striker & bowler
            $lastBall = BallByBallLog::where('innings_id', $inn1->id)->orderByDesc('id')->first();
            $bowlerId = $lastBall ? $lastBall->bowler_id : 420;
            $strikerId = $lastBall ? ($lastBall->non_striker_id ?: $lastBall->batsman_id) : 143;
            $nonStrikerId = $lastBall ? $lastBall->batsman_id : 146;

            // Ball 59: dot ball (0 runs)
            $this->line("Adding Ball 59: Over 10, Ball 6 — Dot ball (0 runs)...");
            BallByBallLog::create([
                'client_uuid'                => (string) Str::uuid(),
                'innings_id'                 => $inn1->id,
                'match_id'                   => $match->id,
                'bowler_id'                  => $bowlerId,
                'batsman_id'                 => $strikerId,
                'non_striker_id'             => $nonStrikerId,
                'over_number'                => 10,
                'ball_number'                => 6,
                'runs_scored'                => 0,
                'is_wicket'                  => false,
                'is_wide'                    => false,
                'is_no_ball'                 => false,
                'is_bye'                     => false,
                'is_leg_bye'                 => false,
                'is_penalty'                 => false,
                'extra_runs'                 => 0,
                'is_four'                    => false,
                'is_six'                     => false,
                'batting_team_score_after'   => (int) $inn1->total_runs,
                'batting_team_wickets_after' => (int) $inn1->total_wickets,
            ]);

            // Ball 60: single (1 run)
            $this->line("Adding Ball 60: Over 10, Ball 7 — Single (1 run)...");
            BallByBallLog::create([
                'client_uuid'                => (string) Str::uuid(),
                'innings_id'                 => $inn1->id,
                'match_id'                   => $match->id,
                'bowler_id'                  => $bowlerId,
                'batsman_id'                 => $strikerId,
                'non_striker_id'             => $nonStrikerId,
                'over_number'                => 10,
                'ball_number'                => 7,
                'runs_scored'                => 1,
                'is_wicket'                  => false,
                'is_wide'                    => false,
                'is_no_ball'                 => false,
                'is_bye'                     => false,
                'is_leg_bye'                 => false,
                'is_penalty'                 => false,
                'extra_runs'                 => 0,
                'is_four'                    => false,
                'is_six'                     => false,
                'batting_team_score_after'   => (int) $inn1->total_runs + 1,
                'batting_team_wickets_after' => (int) $inn1->total_wickets,
            ]);

            // Mark completed
            $inn1->update(['is_completed' => true]);

            // Rebuild Innings 1 statistics
            $this->line("Rebuilding Innings 1 scorecards and totals...");
            $stats->rebuildInnings($inn1);
        }

        $inn1 = $inn1->fresh();

        // If Innings 2 exists, update target and rebuild
        if ($inn2) {
            $newTarget = $inn1->total_runs + 1;
            $this->line("Updating Innings 2 target to {$newTarget}...");
            $inn2->update(['target' => $newTarget]);
            $stats->rebuildInnings($inn2);
        }

        Cache::forget("live_match_{$match->id}");

        $snapshot = $scoreboard->snapshot($match->fresh());

        $this->newLine();
        $this->info("=========================================");
        $this->info(" MATCH #{$match->id} SUCCESSFULLY UPDATED");
        $this->info("=========================================");
        $this->table(
            ['Innings', 'Score', 'Overs', 'Balls', 'Status'],
            [
                ['Innings 1', "{$inn1->total_runs}/{$inn1->total_wickets}", $inn1->overs_faced, $inn1->total_balls, $inn1->is_completed ? 'Completed' : 'In Progress'],
                ['Innings 2', $inn2 ? "{$inn2->total_runs}/{$inn2->total_wickets}" : 'N/A', $inn2 ? $inn2->overs_faced : 'N/A', $inn2 ? $inn2->total_balls : 'N/A', "Target: " . ($inn2 ? $inn2->target : 'N/A')],
            ]
        );

        $this->info("Live Broadcast Bar Headline: " . ($snapshot['headline'] ?? ''));
        $this->info("Live Broadcast Bar Subtitle: " . ($snapshot['sub_headline'] ?? ''));
        $this->info("Live Broadcast Bar Status:   " . ($snapshot['status_line'] ?? ''));

        return self::SUCCESS;
    }
}
