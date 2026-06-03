<?php

namespace Database\Seeders;

use App\Models\BattingScorecard;
use App\Models\BowlingScorecard;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Fine;
use App\Models\FinanceTransaction;
use App\Models\Innings;
use App\Models\Notification;
use App\Models\Player;
use App\Models\PlayerEditionStat;
use App\Models\PlayerEditionTeam;
use App\Models\PollOption;
use App\Models\Team;
use App\Models\User;
use App\Models\VccCabinet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveDataSeeder extends Seeder
{
    private int $adminUserId;

    public function run(): void
    {
        $this->adminUserId = User::first()?->id ?? 1;
        $edition34 = Edition::where('edition_number', 34)->first();
        $edition35 = Edition::where('is_current', true)->first();

        $this->command->info('→ Seeding players for all teams...');
        $this->seedPlayersForAllTeams($edition34, $edition35);

        $this->command->info('→ Seeding VCC Cabinet...');
        $this->seedVccCabinet();

        $this->command->info('→ Seeding Edition 34 matches + scorecards...');
        $this->seedEdition34Matches($edition34);

        $this->command->info('→ Seeding Edition 35 matches...');
        $this->seedEdition35Matches($edition35);

        $this->command->info('→ Seeding fines...');
        $this->seedFines($edition35);

        $this->command->info('→ Seeding finance transactions...');
        $this->seedFinance($edition34, $edition35);

        $this->command->info('→ Seeding notifications...');
        $this->seedNotifications();

        $this->command->info('→ Seeding poll votes...');
        $this->seedPollVotes();

        $this->command->info('✅ Comprehensive data seeded successfully!');
    }

    // ── Players for all 16 teams ─────────────────────────────────────
    private function seedPlayersForAllTeams(Edition $edition34, Edition $edition35): void
    {
        $teams = Team::orderBy('id')->get();

        $teamPlayerNames = [
            // Index 0 → Chak 305 United
            ['Zulfiqar Ahmed', 'Naveed Iqbal', 'Rashid Mehmood', 'Kamran Butt', 'Sohail Raza',
             'Usman Tariq', 'Faisal Malik', 'Javed Akhtar', 'Wasim Arif', 'Rana Khalid', 'Babar Naeem'],

            // Index 1 → Azad CC (already seeded — skip, just reference)
            [],

            // Index 2 → Chak 417 Thunders
            ['Adnan Shafiq', 'Khurram Shahzad', 'Waqas Saeed', 'Tariq Javed', 'Hamza Rauf',
             'Muzammil Ali', 'Saeed Anwar Jr', 'Raza ul Haq', 'Imtiaz Qadir', 'Noman Butt', 'Amjad Pervez'],

            // Index 3 → Chak 418 Eagles
            ['Dawood Shah', 'Mudassar Nazar Jr', 'Ghulam Mustafa', 'Asad Mehmood', 'Bilal Asghar',
             'Zaheer Hussain', 'Pervaiz Akhtar', 'Hafiz Usman', 'Shafiq Rehman', 'Naseer Malik', 'Qasim Raza'],

            // Index 4 → Chak 419 Royals
            ['Sultan Ahmed', 'Arshad Mehmood', 'Riaz Hussain', 'Khalid Javed', 'Tariq Mehmood',
             'Shahzaib Khan', 'Umar Gul Jr', 'Malik Irfan', 'Yasir Shah Jr', 'Fahim Ashraf Jr', 'Nauman Ali Jr'],

            // Index 5 → Chak 420 Sharks
            ['Shoaib Malik Jr', 'Imran Butt', 'Babar Azam Jr', 'Fakhar Zaman Jr', 'Haris Rauf Jr',
             'Nasim Shah Jr', 'Shaheen Shah Jr', 'Mohammad Rizwan Jr', 'Shadab Khan Jr', 'Usman Qadir Jr', 'Hasan Ali Jr'],

            // Index 6 → Chak 421 Hawks
            ['Mubashir Rehman', 'Ansar Abbas', 'Ghulam Sarwar', 'Tanveer Ahmed', 'Majid Khan Jr',
             'Sikander Butt', 'Yousuf Baig', 'Nadir Shah', 'Zahid Mehmood', 'Parvez Mir', 'Aftab Nabi'],

            // Index 7 → Chak 355 Wolves
            ['Shahid Nazir Jr', 'Waqar Younis Jr', 'Wasim Akram Jr', 'Inzamam ul Haq Jr', 'Younis Khan Jr',
             'Mohammad Yousuf Jr', 'Misbah ul Haq Jr', 'Saeed Anwar III', 'Ijaz Ahmed Jr', 'Rashid Latif Jr', 'Moin Khan Jr'],

            // Index 8 → Chak 356 Phoenix
            ['Amir Sohail Jr', 'Ramiz Raja Jr', 'Aamir Malik', 'Masood Anwar', 'Irfan Khalid',
             'Tahir Javed', 'Sajid Shah', 'Rameez Hussain', 'Faisal Iqbal Jr', 'Atif Rauf', 'Qaiser Abbas'],

            // Index 9 → Chak 353 Riders
            ['Saboor Ali', 'Aamir Nazir', 'Danish Aziz', 'Samiullah Mengal', 'Zia ul Haq',
             'Fida Ali', 'Hamid Hassan Jr', 'Anwar Ali Jr', 'Junaid Khan Jr', 'Sohail Tanvir Jr', 'Wahab Riaz Jr'],

            // Index 10 → Chak 354 Kings
            ['Taimur Shah', 'Zeeshan Malik', 'Furqan Ahmed', 'Ibrar ul Haq', 'Saqib Mehmood',
             'Farrukh Nawaz', 'Kashif Ali', 'Habib Rehman', 'Mustansar Hussain', 'Asim Riaz', 'Haider Ali Jr'],

            // Index 11 → Chak 351 Tigers
            ['Kamran Akmal Jr', 'Salman Butt Jr', 'Shan Masood Jr', 'Abid Ali Jr', 'Imam ul Haq Jr',
             'Azhar Ali Jr', 'Haris Sohail Jr', 'Asad Shafiq Jr', 'Fawad Alam Jr', 'Sarfaraz Ahmed Jr', 'Shoaib Ahmed'],

            // Index 12 → Chak 183 Champions
            ['Abdul Razzaq Jr', 'Umer Gul', 'Aizaz Cheema Jr', 'Saeed Ajmal Jr', 'Abdur Rehman Jr',
             'Zulfiqar Babar Jr', 'Sohail Khan Jr', 'Rahat Ali Jr', 'Ehsan Adil Jr', 'Imad Wasim Jr', 'Yasir Hameed Jr'],

            // Index 13 → Village 786 Rangers
            ['Bahadur Khan', 'Dost Mohammad', 'Ghulam Haider', 'Haji Noor', 'Mehboob Ali',
             'Sardar Ahmed', 'Talib Hussain', 'Bashir Ahmad', 'Peer Bakhsh', 'Nabi Ahmad', 'Wali Mohammad'],

            // Index 14 → Chak 213 Stallions
            ['Saifullah Bangash', 'Imranullah Bhatti', 'Khalilur Rehman', 'Noorullah Hasan',
             'Zubairullah Khan', 'Hizbullah Afridi', 'Rahimullah Yusufzai', 'Attaullah Butt',
             'Faridullah Jan', 'Azizullah Gul', 'Qalandar Shah'],

            // Index 15 → Chak 449 Falcons
            ['Liaquat Ali', 'Ghulam Nabi', 'Sher Mohammad', 'Abdul Ghani', 'Fazal Ur Rehman',
             'Mohammad Gul', 'Amir Mohammad', 'Noor Rehman', 'Karam Shah', 'Sikander Ali', 'Sabar Gul'],
        ];

        $roles       = ['batsman', 'batsman', 'batsman', 'bowler', 'bowler', 'all_rounder', 'all_rounder', 'wicket_keeper'];
        $batStyles   = ['right_hand', 'right_hand', 'right_hand', 'left_hand'];
        $bowlStyles  = ['right_arm_fast', 'right_arm_medium', 'right_arm_spin', 'left_arm_fast', 'left_arm_medium', 'none'];

        foreach ($teams as $idx => $team) {
            $names = $teamPlayerNames[$idx] ?? [];
            if (empty($names)) continue; // Azad CC already seeded

            $teamPlayers = [];
            foreach ($names as $i => $name) {
                $player = Player::firstOrCreate(
                    ['name' => $name],
                    [
                        'role'          => $roles[$i % count($roles)],
                        'batting_style' => $batStyles[$i % count($batStyles)],
                        'bowling_style' => $bowlStyles[$i % count($bowlStyles)],
                        'jersey_number' => (string)($i + 1),
                        'is_active'     => true,
                    ]
                );
                $teamPlayers[] = $player;
            }

            // Assign to edition 34
            if ($edition34) {
                foreach ($teamPlayers as $player) {
                    PlayerEditionTeam::firstOrCreate(
                        ['player_id' => $player->id, 'edition_id' => $edition34->id],
                        ['team_id' => $team->id]
                    );
                }
            }
            // Assign to edition 35
            if ($edition35) {
                foreach ($teamPlayers as $player) {
                    PlayerEditionTeam::firstOrCreate(
                        ['player_id' => $player->id, 'edition_id' => $edition35->id],
                        ['team_id' => $team->id]
                    );
                }
            }
        }
    }

    // ── VCC Cabinet ─────────────────────────────────────────────────
    private function seedVccCabinet(): void
    {
        $members = [
            ['name' => 'Malik Sarfraz Ahmad',   'role_title' => 'President',           'village' => 'Village 348', 'display_order' => 1],
            ['name' => 'Chaudhry Imran Butt',   'role_title' => 'General Secretary',   'village' => 'Village 305', 'display_order' => 2],
            ['name' => 'Haji Liaquat Ali',      'role_title' => 'Treasurer',           'village' => 'Village 420', 'display_order' => 3],
            ['name' => 'Rana Khalid Mehmood',   'role_title' => 'Joint Secretary',     'village' => 'Village 353', 'display_order' => 4],
            ['name' => 'Mohammad Arif Bajwa',   'role_title' => 'Chief Selector',      'village' => 'Village 786', 'display_order' => 5],
            ['name' => 'Zulfiqar Ahmed Khan',   'role_title' => 'Disciplinary Head',   'village' => 'Village 419', 'display_order' => 6],
        ];

        foreach ($members as $m) {
            VccCabinet::firstOrCreate(
                ['name' => $m['name']],
                array_merge($m, ['is_active' => true, 'bio' => 'Active member of the Village Cricket Council.'])
            );
        }
    }

    // ── Edition 34 Matches ───────────────────────────────────────────
    private function seedEdition34Matches(Edition $edition34): void
    {
        if (!$edition34) return;
        if (CricketMatch::where('edition_id', $edition34->id)->count() > 0) return;

        $teams    = Team::orderBy('id')->get();
        $venues   = ['Village 348 Ground', 'Maqbool pur Maidan', 'Chak 305 Ground', 'Central Ground', 'Village 420 Field'];
        $matchNum = 1;
        $baseDate = now()->subMonths(8);

        // 20 completed matches from edition 34
        $matchPairs = [
            [0,1],[2,3],[4,5],[6,7],[8,9],[10,11],[12,13],[14,15],
            [0,2],[1,3],[4,6],[5,7],[8,10],[9,11],[12,14],[13,15],
            [0,4],[2,6],[1,5],[3,7],
        ];

        foreach ($matchPairs as $pair) {
            $homeTeam = $teams[$pair[0]];
            $awayTeam = $teams[$pair[1]];
            $schedAt  = $baseDate->copy()->addDays($matchNum * 3);

            $match = CricketMatch::create([
                'edition_id'      => $edition34->id,
                'home_team_id'    => $homeTeam->id,
                'away_team_id'    => $awayTeam->id,
                'match_number'    => 'M' . $matchNum,
                'match_type'      => 'group',
                'venue'           => $venues[$matchNum % count($venues)],
                'scheduled_at'    => $schedAt,
                'status'          => 'completed',
                'overs_per_side'  => 10,
                'toss_winner_id'  => $matchNum % 2 === 0 ? $homeTeam->id : $awayTeam->id,
                'toss_decision'   => $matchNum % 2 === 0 ? 'bat' : 'field',
            ]);

            $this->createCompletedMatchData($match, $homeTeam, $awayTeam, $edition34);
            $matchNum++;
        }
    }

    // ── Edition 35 Matches ───────────────────────────────────────────
    private function seedEdition35Matches(Edition $edition35): void
    {
        if (!$edition35) return;
        if (CricketMatch::where('edition_id', $edition35->id)->count() > 0) return;

        $teams    = Team::orderBy('id')->get();
        $venues   = ['Village 348 Ground', 'Maqbool pur Maidan', 'Chak 417 Ground', 'Central Ground'];
        $matchNum = 1;

        // 6 completed matches
        $completedPairs = [[0,2],[1,3],[4,5],[6,7],[8,9],[0,4]];
        foreach ($completedPairs as $pair) {
            $homeTeam = $teams[$pair[0]];
            $awayTeam = $teams[$pair[1]];
            $match = CricketMatch::create([
                'edition_id'     => $edition35->id,
                'home_team_id'   => $homeTeam->id,
                'away_team_id'   => $awayTeam->id,
                'match_number'   => 'M' . $matchNum,
                'match_type'     => 'group',
                'venue'          => $venues[$matchNum % count($venues)],
                'scheduled_at'   => now()->subDays(30 - $matchNum * 4),
                'status'         => 'completed',
                'overs_per_side' => 10,
                'toss_winner_id' => $homeTeam->id,
                'toss_decision'  => 'bat',
            ]);
            $this->createCompletedMatchData($match, $homeTeam, $awayTeam, $edition35);
            $matchNum++;
        }

        // 8 upcoming matches
        $upcomingPairs = [[1,2],[3,5],[6,8],[7,9],[10,11],[12,13],[14,15],[2,5]];
        foreach ($upcomingPairs as $pair) {
            $homeTeam = $teams[$pair[0]];
            $awayTeam = $teams[$pair[1]];
            CricketMatch::create([
                'edition_id'     => $edition35->id,
                'home_team_id'   => $homeTeam->id,
                'away_team_id'   => $awayTeam->id,
                'match_number'   => 'M' . $matchNum,
                'match_type'     => 'group',
                'venue'          => $venues[$matchNum % count($venues)],
                'scheduled_at'   => now()->addDays($matchNum * 3),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
            ]);
            $matchNum++;
        }
    }

    // ── Full match data (innings + scorecards + stats) ───────────────
    private function createCompletedMatchData(CricketMatch $match, Team $homeTeam, Team $awayTeam, Edition $edition): void
    {
        $seed = $match->id * 7 + $homeTeam->id;

        // Generate innings scores
        $homeRuns    = 85 + ($seed % 65);    // 85–149
        $homeWkts    = 4 + ($seed % 7);       // 4–10
        $homeBalls   = min(60, 45 + ($seed % 20));
        $awayWon     = ($seed % 3 !== 0);     // away wins 2/3 of the time for variety

        if ($awayWon) {
            $awayRuns  = $homeRuns + 1 + ($seed % 15);
            $awayWkts  = 2 + ($seed % 5);
            $awayBalls = $homeBalls - 3 - ($seed % 10);
            $winnerId  = $awayTeam->id;
            $margin    = $awayRuns - $homeRuns;
            $resultType = 'runs'; // away team wins by chasing
        } else {
            $awayRuns  = $homeRuns - 5 - ($seed % 20);
            $awayWkts  = 6 + ($seed % 5);
            $awayBalls = 60;
            $winnerId  = $homeTeam->id;
            $margin    = $homeRuns - $awayRuns;
            $resultType = 'runs';
        }
        $awayBalls = max(6, min(60, $awayBalls));

        // Innings 1 – home team bats
        $inn1 = Innings::create([
            'match_id'        => $match->id,
            'batting_team_id' => $homeTeam->id,
            'bowling_team_id' => $awayTeam->id,
            'innings_number'  => 1,
            'total_runs'      => $homeRuns,
            'total_wickets'   => $homeWkts,
            'total_balls'     => $homeBalls,
            'overs_faced'     => floor($homeBalls / 6) + ($homeBalls % 6) / 10,
            'run_rate'        => $homeBalls > 0 ? round($homeRuns / ($homeBalls / 6), 2) : 0,
            'extras_wides'    => 2 + ($seed % 4),
            'extras_no_balls' => $seed % 3,
            'is_completed'    => true,
        ]);

        // Innings 2 – away team bats
        $inn2 = Innings::create([
            'match_id'        => $match->id,
            'batting_team_id' => $awayTeam->id,
            'bowling_team_id' => $homeTeam->id,
            'innings_number'  => 2,
            'total_runs'      => $awayRuns,
            'total_wickets'   => $awayWkts,
            'total_balls'     => $awayBalls,
            'overs_faced'     => floor($awayBalls / 6) + ($awayBalls % 6) / 10,
            'run_rate'        => $awayBalls > 0 ? round($awayRuns / ($awayBalls / 6), 2) : 0,
            'extras_wides'    => 1 + ($seed % 3),
            'extras_no_balls' => ($seed + 1) % 3,
            'is_completed'    => true,
            'target'          => $homeRuns + 1,
        ]);

        // Update match result
        $match->update([
            'winner_id'   => $winnerId,
            'result_type' => $resultType,
            'result_margin' => $margin,
            'result_description' => ($winnerId === $homeTeam->id ? $homeTeam->name : $awayTeam->name)
                . " won by {$margin} runs",
        ]);

        // Create batting scorecards
        $this->createBattingScorecard($inn1, $match, $homeTeam, $homeRuns, $homeWkts, $edition);
        $this->createBattingScorecard($inn2, $match, $awayTeam, $awayRuns, $awayWkts, $edition);

        // Create bowling scorecards
        $this->createBowlingScorecard($inn1, $match, $awayTeam, $homeRuns, $homeBalls, $edition);
        $this->createBowlingScorecard($inn2, $match, $homeTeam, $awayRuns, $awayBalls, $edition);
    }

    private function createBattingScorecard(Innings $innings, CricketMatch $match, Team $team, int $teamRuns, int $teamWkts, Edition $edition): void
    {
        $players = PlayerEditionTeam::where('team_id', $team->id)
            ->where('edition_id', $edition->id)
            ->with('player')
            ->limit(11)
            ->get()
            ->pluck('player')
            ->filter();

        if ($players->isEmpty()) return;

        $remaining   = $teamRuns;
        $dismissals  = ['bowled', 'caught', 'run_out', 'lbw', 'stumped'];
        $dismissed   = 0;

        foreach ($players as $pos => $player) {
            if ($pos >= 11) break;

            $isOut = $dismissed < $teamWkts;

            // Top order get more runs
            $maxRuns = $pos < 3 ? 50 : ($pos < 6 ? 30 : 18);
            $runs    = min($remaining, $pos === 0 ? rand(20, $maxRuns) : rand(0, $maxRuns));
            if ($pos === count($players) - 1) $runs = $remaining; // last batsman gets remainder
            $remaining  = max(0, $remaining - $runs);
            $balls      = $runs > 0 ? max($runs, intval($runs * (0.7 + lcg_value() * 0.8))) : rand(1, 4);
            $balls      = min($balls, 30);
            $fours      = intval($runs / 10);
            $sixes      = intval($runs / 20);

            if ($isOut) {
                $dismissed++;
                $dismissalType = $dismissals[$pos % count($dismissals)];
            } else {
                $dismissalType = 'not_out';
            }

            try {
                BattingScorecard::firstOrCreate(
                    ['innings_id' => $innings->id, 'player_id' => $player->id],
                    [
                        'match_id'        => $match->id,
                        'team_id'         => $team->id,
                        'batting_position' => $pos + 1,
                        'runs_scored'     => $runs,
                        'balls_faced'     => max(1, $balls),
                        'fours'           => $fours,
                        'sixes'           => $sixes,
                        'strike_rate'     => $balls > 0 ? round($runs / $balls * 100, 2) : 0,
                        'dismissal_type'  => $dismissalType,
                        'is_fifty'        => $runs >= 50,
                        'is_century'      => $runs >= 100,
                    ]
                );

                // Update player edition stat
                $this->updateBattingStat($player->id, $team->id, $edition->id, $runs, $balls, $fours, $sixes, $dismissalType);
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }
    }

    private function createBowlingScorecard(Innings $innings, CricketMatch $match, Team $team, int $runsGiven, int $ballsBowled, Edition $edition): void
    {
        $players = PlayerEditionTeam::where('team_id', $team->id)
            ->where('edition_id', $edition->id)
            ->with('player')
            ->limit(11)
            ->get()
            ->pluck('player')
            ->filter();

        if ($players->isEmpty()) return;

        $bowlers        = $players->slice(5, 5); // Use positions 6-10 as bowlers
        $oversRemaining = intval($ballsBowled / 6) + ($ballsBowled % 6 > 0 ? 1 : 0);
        $runsRemaining  = $runsGiven;
        $wicketsLeft    = rand(4, 7);

        foreach ($bowlers as $i => $player) {
            $oversBowled  = min(2, intval($oversRemaining / max(1, $bowlers->count() - $i)));
            $balls         = $oversBowled * 6;
            $runs          = intval($runsRemaining * $balls / max(1, $ballsBowled));
            $wickets       = ($i === 0) ? min(3, $wicketsLeft) : ($wicketsLeft > 0 ? rand(0, min(2, $wicketsLeft)) : 0);
            $wicketsLeft  -= $wickets;
            $runsRemaining = max(0, $runsRemaining - $runs);
            $oversRemaining -= $oversBowled;

            if ($balls <= 0) continue;

            try {
                BowlingScorecard::firstOrCreate(
                    ['innings_id' => $innings->id, 'player_id' => $player->id],
                    [
                        'match_id'          => $match->id,
                        'team_id'           => $team->id,
                        'overs_bowled_balls' => $balls,
                        'overs_bowled'      => $oversBowled,
                        'maidens'           => $runs <= 5 ? 1 : 0,
                        'runs_conceded'     => $runs,
                        'wickets'           => $wickets,
                        'wides'             => rand(0, 2),
                        'no_balls'          => rand(0, 1),
                        'economy'           => $oversBowled > 0 ? round($runs / $oversBowled, 2) : 0,
                        'five_wicket_haul'  => $wickets >= 5,
                        'hat_trick_wickets' => false,
                    ]
                );

                // Update bowling stat
                $this->updateBowlingStat($player->id, $team->id, $edition->id, $runs, $balls, $wickets);
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }
    }

    private function updateBattingStat(int $playerId, int $teamId, int $editionId, int $runs, int $balls, int $fours, int $sixes, string $dismissal): void
    {
        $stat = PlayerEditionStat::firstOrCreate(
            ['player_id' => $playerId, 'edition_id' => $editionId],
            ['team_id' => $teamId]
        );

        $notOut = in_array($dismissal, ['not_out', 'retired_hurt', 'did_not_bat']);

        DB::table('player_edition_stats')
            ->where('id', $stat->id)
            ->update([
                'team_id'             => $teamId,
                'matches_played'      => DB::raw('matches_played + 1'),
                'innings_batted'      => DB::raw('innings_batted + 1'),
                'total_runs'          => DB::raw("total_runs + {$runs}"),
                'total_fours'         => DB::raw("total_fours + {$fours}"),
                'total_sixes'         => DB::raw("total_sixes + {$sixes}"),
                'fifties'             => DB::raw('fifties + ' . ($runs >= 50 && $runs < 100 ? 1 : 0)),
                'centuries'           => DB::raw('centuries + ' . ($runs >= 100 ? 1 : 0)),
                'highest_score'       => DB::raw("GREATEST(highest_score, {$runs})"),
            ]);

        // Recalculate averages
        $fresh = PlayerEditionStat::find($stat->id);
        $outs  = max(1, $fresh->innings_batted - ($notOut ? 1 : 0));
        $fresh->update([
            'batting_average'     => round($fresh->total_runs / $outs, 2),
            'batting_strike_rate' => $balls > 0 ? round($fresh->total_runs / max(1, $fresh->innings_batted * $balls) * 100, 2) : 0,
        ]);
    }

    private function updateBowlingStat(int $playerId, int $teamId, int $editionId, int $runs, int $balls, int $wickets): void
    {
        $stat = PlayerEditionStat::firstOrCreate(
            ['player_id' => $playerId, 'edition_id' => $editionId],
            ['team_id' => $teamId]
        );

        DB::table('player_edition_stats')
            ->where('id', $stat->id)
            ->update([
                'team_id'          => $teamId,
                'innings_bowled'   => DB::raw('innings_bowled + 1'),
                'total_wickets'    => DB::raw("total_wickets + {$wickets}"),
                'overs_bowled'     => DB::raw("overs_bowled + " . round($balls / 6, 2)),
            ]);

        $fresh = PlayerEditionStat::find($stat->id);
        $overs = $fresh->overs_bowled > 0 ? $fresh->overs_bowled : 1;
        $fresh->update([
            'bowling_economy' => round($runs / $overs, 2),
            'bowling_average' => $fresh->total_wickets > 0 ? round($runs / $fresh->total_wickets, 2) : 0,
        ]);
    }

    // ── Fines ────────────────────────────────────────────────────────
    private function seedFines(Edition $edition): void
    {
        if (!$edition) return;

        $players = Player::inRandomOrder()->limit(8)->get();
        $types   = ['chucking', 'code_of_conduct', 'disciplinary_card', 'misconduct', 'other'];
        $cards   = ['none', 'yellow', 'yellow', 'red', 'none'];
        $descs   = [
            'Player used illegal bowling action during match.',
            'Arguing with umpire decision during game.',
            'Yellow card issued for unsportsmanlike behaviour.',
            'Misconduct towards opposition players.',
            'Late arrival for scheduled match.',
            'Using offensive language on the field.',
            'Red card issued for serious violation.',
            'Failure to attend mandatory team meeting.',
        ];

        foreach ($players as $i => $player) {
            $isPaid = $i % 3 !== 0;
            Fine::firstOrCreate(
                ['player_id' => $player->id, 'edition_id' => $edition->id, 'violation_type' => $types[$i % count($types)]],
                [
                    'card_type'   => $cards[$i % count($cards)],
                    'description' => $descs[$i % count($descs)],
                    'amount'      => [200, 300, 500, 1000, 150][$i % 5],
                    'status'      => $isPaid ? 'paid' : 'unpaid',
                    'due_date'    => now()->subDays(20 - $i * 2)->toDateString(),
                    'paid_date'   => $isPaid ? now()->subDays(10)->toDateString() : null,
                    'issued_by'   => $this->adminUserId,
                ]
            );
        }
    }

    // ── Finance Transactions ─────────────────────────────────────────
    private function seedFinance(Edition $edition34, Edition $edition35): void
    {
        $teams   = Team::limit(8)->get();
        $admin   = $this->adminUserId;
        $editions = array_filter([$edition34, $edition35]);

        $transactions = [
            ['type' => 'income',  'category' => 'entry_fee',      'description' => 'Team entry fee - Azad CC',           'amount' => 5000],
            ['type' => 'income',  'category' => 'entry_fee',      'description' => 'Team entry fee - Chak 305 United',    'amount' => 5000],
            ['type' => 'income',  'category' => 'entry_fee',      'description' => 'Team entry fee - Chak 417 Thunders',  'amount' => 5000],
            ['type' => 'income',  'category' => 'sponsorship',    'description' => 'Main sponsor - Village Store',        'amount' => 25000],
            ['type' => 'income',  'category' => 'sponsorship',    'description' => 'Secondary sponsor - Local Business',  'amount' => 10000],
            ['type' => 'income',  'category' => 'fine_collection', 'description' => 'Collected fines - various players',  'amount' => 2500],
            ['type' => 'expense', 'category' => 'umpire_fee',     'description' => 'Umpire fees for group stage',         'amount' => 8000],
            ['type' => 'expense', 'category' => 'groundsman_fee', 'description' => 'Ground maintenance & preparation',    'amount' => 3500],
            ['type' => 'expense', 'category' => 'equipment',      'description' => 'Cricket balls & stumps',              'amount' => 4200],
            ['type' => 'expense', 'category' => 'prize_money',    'description' => 'Winner prize money',                  'amount' => 15000],
            ['type' => 'expense', 'category' => 'scorer_fee',     'description' => 'Scorer fees for season',              'amount' => 2000],
            ['type' => 'income',  'category' => 'entry_fee',      'description' => 'Remaining team entry fees collected', 'amount' => 65000],
        ];

        foreach ($editions as $edition) {
            foreach ($transactions as $i => $tx) {
                FinanceTransaction::firstOrCreate(
                    ['edition_id' => $edition->id, 'description' => $tx['description']],
                    [
                        'type'             => $tx['type'],
                        'category'         => $tx['category'],
                        'amount'           => $tx['amount'],
                        'team_id'          => $i < 3 ? ($teams[$i] ?? null)?->id : null,
                        'transaction_date' => now()->subDays(60 - $i * 4)->toDateString(),
                        'reference_number' => 'TXN-' . strtoupper(substr(md5($edition->id . $i), 0, 8)),
                        'recorded_by'      => $admin,
                    ]
                );
            }
        }
    }

    // ── Notifications ────────────────────────────────────────────────
    private function seedNotifications(): void
    {
        $messages = [
            '🏏 Welcome to the 35th Edition of Royal Champions League! Season is now live.',
            '🏆 Azad Cricket Club won the 34th Edition with an outstanding performance!',
            '📅 Next match: Chak 305 United vs Chak 418 Eagles — Stay tuned!',
            '⚡ Farooq Numberdar smashes 62* off 38 balls — Player of the Match!',
            '🚨 Team registration deadline for all 16 teams: Extended by 3 days.',
            '🎉 Edition 35 fixtures released — Check the schedule now!',
            '💪 Jawad Ahmad Zia takes 4 wickets in a single match against Chak 417!',
            '📢 VCC meeting scheduled — All team captains must attend.',
        ];

        foreach ($messages as $i => $msg) {
            Notification::firstOrCreate(
                ['message' => $msg],
                [
                    'type'       => ['info', 'live_update', 'announcement', 'info'][$i % 4],
                    'is_active'  => true,
                    'is_ticker'  => true,
                    'expires_at' => now()->addDays(30),
                    'created_by' => $this->adminUserId,
                ]
            );
        }
    }

    // ── Poll Votes ───────────────────────────────────────────────────
    private function seedPollVotes(): void
    {
        $options = PollOption::with('poll')->get();
        $voteCounts = [45, 32, 28, 19, 61, 22, 15, 38, 55, 40, 25, 18, 70, 35, 22, 48];

        foreach ($options as $i => $option) {
            $votes = $voteCounts[$i % count($voteCounts)];
            for ($v = 0; $v < $votes; $v++) {
                DB::table('poll_votes')->insert([
                    'poll_id'        => $option->poll_id,
                    'poll_option_id' => $option->id,
                    'ip_address'     => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                    'session_token'  => md5($option->id . $v . rand()),
                    'created_at'     => now()->subHours(rand(1, 120)),
                    'updated_at'     => now(),
                ]);
            }
        }
    }
}
