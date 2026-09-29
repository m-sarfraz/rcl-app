<?php

namespace App\Console\Commands;

use App\Models\BallByBallLog;
use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoMatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan match:demo --create   (creates live demo match at first place)
     *   php artisan match:demo --delete   (safely removes the demo match & related records)
     *   php artisan match:demo --fresh    (wipes existing demo match and creates fresh one)
     */
    protected $signature = 'match:demo
                            {--create : Create the live demo match for live scoring test}
                            {--delete : Safely delete the demo match and its test records}
                            {--fresh  : Delete existing demo match first if present, then recreate}';

    /**
     * The console command description.
     */
    protected $description = 'Create or delete a demo live match for testing the live scorecard and mobile scoring console';

    public function handle(): int
    {
        $create = $this->option('create');
        $delete = $this->option('delete');
        $fresh  = $this->option('fresh');

        if ($delete) {
            return $this->deleteDemoMatch();
        }

        if ($fresh) {
            $this->deleteDemoMatch(silent: true);
            return $this->createDemoMatch();
        }

        if ($create) {
            return $this->createDemoMatch();
        }

        // Default behavior if no flag passed:
        $existing = CricketMatch::withTrashed()
            ->where(function ($q) {
                $q->where('match_number', 'DEMO')
                  ->orWhere('notes', 'like', '%[DEMO_MATCH]%');
            })
            ->first();

        if ($existing) {
            $this->warn("A demo match already exists (ID #{$existing->id}, Status: {$existing->status}).");
            $this->line("Run with <info>php artisan match:demo --delete</info> to remove it.");
            $this->line("Run with <info>php artisan match:demo --fresh</info> to recreate it from scratch.\n");
            $this->displayMatchDetails($existing);
            return 0;
        }

        // If none exists, create it
        return $this->createDemoMatch();
    }

    /**
     * Create the live demo match with squads, toss, innings, and deliveries.
     */
    protected function createDemoMatch(): int
    {
        $existing = CricketMatch::withTrashed()
            ->where(function ($q) {
                $q->where('match_number', 'DEMO')
                  ->orWhere('notes', 'like', '%[DEMO_MATCH]%');
            })
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $this->info("Demo match already exists (ID #{$existing->id}).");
            $this->displayMatchDetails($existing);
            return 0;
        }

        $edition = Edition::where('is_current', true)->first()
            ?? Edition::orderByDesc('edition_number')->first();

        if (! $edition) {
            $this->error('No tournament edition found in database.');
            return 1;
        }

        // Teams: 183 (Sholah CC) & 355 (United CC), or fallback to active teams
        $homeTeam = Team::where('short_code', '183')->first()
            ?? Team::active()->where('id', 15)->first()
            ?? Team::active()->first();

        $awayTeam = Team::where('short_code', '355')->first()
            ?? Team::active()->where('id', 7)->first()
            ?? Team::active()->where('id', '!=', $homeTeam->id)->first();

        if (! $homeTeam || ! $awayTeam) {
            $this->error('Could not find suitable teams to create the demo match.');
            return 1;
        }

        // Roster / Players
        $homePlayers = PlayerEditionTeam::with('player')
            ->where('team_id', $homeTeam->id)
            ->where('edition_id', $edition->id)
            ->get()
            ->pluck('player')
            ->filter();

        if ($homePlayers->count() < 11) {
            $extraHome = Player::where('deleted_at', null)->take(11)->get();
            $homePlayers = $homePlayers->concat($extraHome)->unique('id')->take(11);
        }

        $awayPlayers = PlayerEditionTeam::with('player')
            ->where('team_id', $awayTeam->id)
            ->where('edition_id', $edition->id)
            ->get()
            ->pluck('player')
            ->filter();

        if ($awayPlayers->count() < 11) {
            $extraAway = Player::where('deleted_at', null)->whereNotIn('id', $homePlayers->pluck('id'))->take(11)->get();
            $awayPlayers = $awayPlayers->concat($extraAway)->unique('id')->take(11);
        }

        // Specific match players (matching reference graphic if available)
        $striker = Player::where('name', 'like', '%Rafaqat%')->first() ?? $homePlayers->first();
        $nonStriker = Player::where('name', 'like', '%Mujahid%')->first() ?? $homePlayers->skip(1)->first();
        $bowler = Player::where('name', 'like', '%Faisal%')->first() ?? $awayPlayers->first();

        $match = DB::transaction(function () use (
            $edition, $homeTeam, $awayTeam, $homePlayers, $awayPlayers, $striker, $nonStriker, $bowler
        ) {
            // 1. Create Match - scheduled before other matches so it ranks #1 at the top of live/fixtures list
            $m = CricketMatch::create([
                'edition_id'         => $edition->id,
                'home_team_id'       => $homeTeam->id,
                'away_team_id'       => $awayTeam->id,
                'match_number'       => 'DEMO',
                'match_type'         => 'group',
                'venue'              => 'Chak No 183 Stadium',
                'scheduled_at'       => now()->subMinutes(20),
                'status'             => 'live',
                'overs_per_side'     => 10,
                'toss_winner_id'     => $awayTeam->id,
                'toss_decision'      => 'field',
                'is_super_over'      => false,
                'notes'              => '[DEMO_MATCH] 183 | 355 WON THE TOSS AND DECIDED TO BOWL FIRST',
            ]);

            // 2. Named Squads for both teams
            foreach ($homePlayers->values() as $idx => $p) {
                MatchSquad::create([
                    'match_id'         => $m->id,
                    'team_id'          => $homeTeam->id,
                    'player_id'        => $p->id,
                    'batting_order'    => $idx + 1,
                    'is_captain'       => $idx === 0,
                    'is_wicket_keeper' => $idx === 1,
                ]);
            }

            foreach ($awayPlayers->values() as $idx => $p) {
                MatchSquad::create([
                    'match_id'         => $m->id,
                    'team_id'          => $awayTeam->id,
                    'player_id'        => $p->id,
                    'batting_order'    => $idx + 1,
                    'is_captain'       => $idx === 0,
                    'is_wicket_keeper' => $idx === 1,
                ]);
            }

            // 3. Innings 1 (Current Innings: 39-0 in 1.5 overs)
            $innings = Innings::create([
                'match_id'          => $m->id,
                'batting_team_id'   => $homeTeam->id,
                'bowling_team_id'   => $awayTeam->id,
                'innings_number'    => 1,
                'total_runs'        => 39,
                'total_wickets'     => 0,
                'total_balls'       => 11,
                'overs_faced'       => 1.5,
                'run_rate'          => 21.27,
                'extras_wides'      => 0,
                'extras_no_balls'   => 0,
                'extras_byes'       => 0,
                'extras_leg_byes'   => 0,
                'extras_penalty'    => 0,
                'is_completed'      => false,
            ]);

            // 4. Batting Scorecards
            // Striker: 19 runs off 7 balls
            BattingScorecard::create([
                'innings_id'       => $innings->id,
                'match_id'         => $m->id,
                'player_id'        => $striker->id,
                'team_id'          => $homeTeam->id,
                'batting_position' => 1,
                'runs_scored'      => 19,
                'balls_faced'      => 7,
                'fours'            => 2,
                'sixes'            => 1,
                'strike_rate'      => 271.43,
                'dismissal_type'   => 'not_out',
            ]);

            // Non-Striker: 17 runs off 4 balls
            BattingScorecard::create([
                'innings_id'       => $innings->id,
                'match_id'         => $m->id,
                'player_id'        => $nonStriker->id,
                'team_id'          => $homeTeam->id,
                'batting_position' => 2,
                'runs_scored'      => 17,
                'balls_faced'      => 4,
                'fours'            => 2,
                'sixes'            => 1,
                'strike_rate'      => 425.0,
                'dismissal_type'   => 'not_out',
            ]);

            // 5. Bowling Scorecard: Bowler 0-17 in 0.5 overs (5 balls)
            BowlingScorecard::create([
                'innings_id'         => $innings->id,
                'match_id'           => $m->id,
                'player_id'          => $bowler->id,
                'team_id'            => $awayTeam->id,
                'overs_bowled_balls' => 5,
                'overs_bowled'       => 0.5,
                'maidens'            => 0,
                'runs_conceded'      => 17,
                'wickets'            => 0,
                'wides'              => 0,
                'no_balls'           => 0,
                'economy'            => 20.4,
            ]);

            // 6. Ball by Ball logs (Outcome sequence: 1, 4, 6, 1, 4, 0)
            $deliveries = [
                ['runs' => 1, 'is_four' => false, 'is_six' => false, 'batsman' => $striker,    'comm' => '1 run taken, clipped towards mid-wicket.'],
                ['runs' => 4, 'is_four' => true,  'is_six' => false, 'batsman' => $nonStriker, 'comm' => 'FOUR! Slashed backward of point.'],
                ['runs' => 6, 'is_four' => false, 'is_six' => true,  'batsman' => $nonStriker, 'comm' => 'SIX! Dispatched high over deep mid-wicket!'],
                ['runs' => 1, 'is_four' => false, 'is_six' => false, 'batsman' => $nonStriker, 'comm' => 'Single pushed into the off-side.'],
                ['runs' => 4, 'is_four' => true,  'is_six' => false, 'batsman' => $striker,    'comm' => 'FOUR! Driven beautifully past mid-off.'],
                ['runs' => 0, 'is_four' => false, 'is_six' => false, 'batsman' => $striker,    'comm' => 'Dot ball to conclude the spell, beaten by pace.'],
            ];

            $runningScore = 23;
            foreach ($deliveries as $idx => $d) {
                $runningScore += $d['runs'];
                BallByBallLog::create([
                    'client_uuid'                => (string) Str::uuid(),
                    'innings_id'                 => $innings->id,
                    'match_id'                   => $m->id,
                    'bowler_id'                  => $bowler->id,
                    'batsman_id'                 => $d['batsman']->id,
                    'non_striker_id'             => ($d['batsman']->id === $striker->id) ? $nonStriker->id : $striker->id,
                    'over_number'                => 1,
                    'ball_number'                => $idx + 1,
                    'runs_scored'                => $d['runs'],
                    'is_wicket'                  => false,
                    'is_wide'                    => false,
                    'is_no_ball'                 => false,
                    'is_bye'                     => false,
                    'is_leg_bye'                 => false,
                    'is_penalty'                 => false,
                    'extra_runs'                 => 0,
                    'is_four'                    => $d['is_four'],
                    'is_six'                     => $d['is_six'],
                    'batting_team_score_after'   => $runningScore,
                    'batting_team_wickets_after' => 0,
                    'commentary'                 => $d['comm'],
                ]);
            }

            Cache::forget("live_match_{$m->id}");

            return $m;
        });

        $this->info(" Demo match created successfully at the #1 position!");
        $this->displayMatchDetails($match);

        return 0;
    }

    /**
     * Safely delete the demo match and all its associated test data.
     */
    protected function deleteDemoMatch(bool $silent = false): int
    {
        $matches = CricketMatch::withTrashed()
            ->where(function ($q) {
                $q->where('match_number', 'DEMO')
                  ->orWhere('notes', 'like', '%[DEMO_MATCH]%');
            })
            ->get();

        if ($matches->isEmpty()) {
            if (! $silent) {
                $this->warn('No demo match found in database to delete.');
            }
            return 0;
        }

        foreach ($matches as $m) {
            $id = $m->id;
            DB::transaction(function () use ($m, $id) {
                BallByBallLog::where('match_id', $id)->delete();
                BattingScorecard::where('match_id', $id)->delete();
                BowlingScorecard::where('match_id', $id)->delete();
                MatchSquad::where('match_id', $id)->delete();
                Innings::where('match_id', $id)->delete();
                Cache::forget("live_match_{$id}");
                $m->forceDelete();
            });

            if (! $silent) {
                $this->info(" Deleted demo match #{$id} and all related test data. Tournament schedule untouched.");
            }
        }

        return 0;
    }

    /**
     * Display match details, mobile routes, scoring passkey and URLs.
     */
    protected function displayMatchDetails(CricketMatch $match): void
    {
        $match->loadMissing(['homeTeam', 'awayTeam', 'innings']);
        $home = $match->homeTeam?->name ?? 'Team 1';
        $away = $match->awayTeam?->name ?? 'Team 2';
        $homeCode = $match->homeTeam?->short_code ?? '183';
        $awayCode = $match->awayTeam?->short_code ?? '355';
        $passkey = \App\Models\SiteSetting::get('scoring_secret_key') ?? '&58+@@34AZ';

        $this->table(
            ['Key', 'Value'],
            [
                ['Match ID', (string) $match->id],
                ['Status', strtoupper($match->status)],
                ['Teams', "{$home} ({$homeCode}) vs {$away} ({$awayCode})"],
                ['Scoreline', "39-0 (1.5 / 10 ov)"],
                ['Toss', "{$homeCode} | {$awayCode} won toss & elected to bowl"],
                ['App Live Route', "app/live/{$match->id}"],
                ['App Score Route', "app/score/{$match->id}"],
                ['Scoring Passkey', $passkey],
                ['Web Broadcast URL', url("/embed/match/{$match->id}?layout=overlay&transparent=1")],
                ['API Endpoint', url("/api/v1/matches/{$match->id}/live")],
            ]
        );

        $this->line("\n<comment>To delete this demo match anytime, simply run:</comment>");
        $this->line("  <info>php artisan match:demo --delete</info>\n");
    }
}
