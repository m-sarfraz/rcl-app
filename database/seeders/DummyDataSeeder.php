<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Player;
use App\Models\PlayerEditionStat;
use App\Models\PlayerEditionTeam;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Update 16 teams with real village names ────────────────
        $villageTeams = [
            ['short_code' => 'VW', 'name' => 'Chak 305 United',       'village_name' => 'Village 305',  'primary_color' => '#00e676', 'secondary_color' => '#004d40'],
            ['short_code' => 'RS', 'name' => 'Azad Cricket Club',      'village_name' => 'Maqbool pur',  'primary_color' => '#ef4444', 'secondary_color' => '#7f1d1d'],
            ['short_code' => 'TB', 'name' => 'Chak 417 Thunders',      'village_name' => 'Village 417',  'primary_color' => '#7c4dff', 'secondary_color' => '#1a237e'],
            ['short_code' => 'GE', 'name' => 'Chak 418 Eagles',        'village_name' => 'Village 418',  'primary_color' => '#69f0ae', 'secondary_color' => '#1b5e20'],
            ['short_code' => 'RL', 'name' => 'Chak 419 Royals',        'village_name' => 'Village 419',  'primary_color' => '#ffd600', 'secondary_color' => '#e65100'],
            ['short_code' => 'BS', 'name' => 'Chak 420 Sharks',        'village_name' => 'Village 420',  'primary_color' => '#40c4ff', 'secondary_color' => '#01579b'],
            ['short_code' => 'GH', 'name' => 'Chak 421 Hawks',         'village_name' => 'Village 421',  'primary_color' => '#ffa000', 'secondary_color' => '#e65100'],
            ['short_code' => 'SW', 'name' => 'Chak 355 Wolves',        'village_name' => 'Village 355',  'primary_color' => '#b0bec5', 'secondary_color' => '#37474f'],
            ['short_code' => 'PR', 'name' => 'Chak 356 Phoenix',       'village_name' => 'Village 356',  'primary_color' => '#ff7043', 'secondary_color' => '#bf360c'],
            ['short_code' => 'SR', 'name' => 'Chak 353 Riders',        'village_name' => 'Village 353',  'primary_color' => '#80d8ff', 'secondary_color' => '#006064'],
            ['short_code' => 'DK', 'name' => 'Chak 354 Kings',         'village_name' => 'Village 354',  'primary_color' => '#d4e157', 'secondary_color' => '#827717'],
            ['short_code' => 'MT', 'name' => 'Chak 351 Tigers',        'village_name' => 'Village 351',  'primary_color' => '#ff6e40', 'secondary_color' => '#bf360c'],
            ['short_code' => 'RC', 'name' => 'Chak 183 Champions',     'village_name' => 'Village 183',  'primary_color' => '#00b0ff', 'secondary_color' => '#01579b'],
            ['short_code' => 'FR', 'name' => 'Village 786 Rangers',    'village_name' => 'Village 786',  'primary_color' => '#00e676', 'secondary_color' => '#1b5e20'],
            ['short_code' => 'IS', 'name' => 'Chak 213 Stallions',     'village_name' => 'Village 213',  'primary_color' => '#78909c', 'secondary_color' => '#263238'],
            ['short_code' => 'BF', 'name' => 'Chak 449 Falcons',       'village_name' => 'Village 449',  'primary_color' => '#ff8f00', 'secondary_color' => '#e65100'],
        ];

        foreach ($villageTeams as $data) {
            Team::where('short_code', $data['short_code'])->update([
                'name'            => $data['name'],
                'village_name'    => $data['village_name'],
                'primary_color'   => $data['primary_color'],
                'secondary_color' => $data['secondary_color'],
            ]);
        }

        // ── 2. Create Edition 34 (completed / historical) ────────────
        $edition34 = Edition::firstOrCreate(
            ['edition_number' => 34],
            [
                'name'         => '34th Edition',
                'host_village' => 'Village 348',
                'start_date'   => '2023-06-01',
                'end_date'     => '2023-08-15',
                'status'       => 'completed',
                'is_current'   => false,
                'description'  => 'The 34th edition of the Royal Champions League. A memorable season with outstanding performances.',
            ]
        );

        // Get current edition 35 (already seeded)
        $edition35 = Edition::where('is_current', true)->first();

        // ── 3. Get Azad Cricket Club team (village 348) ──────────────
        $maqboolTeam = Team::where('short_code', 'RS')->first();
        if (!$maqboolTeam) {
            $maqboolTeam = Team::first();
        }

        // ── 4. Add 25 players for Maqbool pur ────────────────────────
        $playersList = [
            ['name' => 'Farooq Numberdar',  'role' => 'batsman',        'jersey_number' => '1'],
            ['name' => 'Jawad Ahmad Zia',   'role' => 'all_rounder',    'jersey_number' => '2'],
            ['name' => 'Talha DJ',          'role' => 'bowler',         'jersey_number' => '3'],
            ['name' => 'Zahid DJ',          'role' => 'bowler',         'jersey_number' => '4'],
            ['name' => 'Bilal Malang',      'role' => 'batsman',        'jersey_number' => '5'],
            ['name' => 'Ahmad Ali',         'role' => 'all_rounder',    'jersey_number' => '6'],
            ['name' => 'Asim Shah',         'role' => 'batsman',        'jersey_number' => '7'],
            ['name' => 'Shan Ali Chak',     'role' => 'all_rounder',    'jersey_number' => '8'],
            ['name' => 'Shan Naseer',       'role' => 'wicket_keeper',  'jersey_number' => '9'],
            ['name' => 'Tahir Shah',        'role' => 'bowler',         'jersey_number' => '10'],
            ['name' => 'Shoaib Akhter',     'role' => 'bowler',         'jersey_number' => '11'],
            ['name' => 'Shahzad Kohli',     'role' => 'batsman',        'jersey_number' => '12'],
            ['name' => 'Shabbir Ali',       'role' => 'all_rounder',    'jersey_number' => '13'],
            ['name' => 'Irfan Rath',        'role' => 'bowler',         'jersey_number' => '14'],
            ['name' => 'Dastgeer Shah',     'role' => 'all_rounder',    'jersey_number' => '15'],
            ['name' => 'Shahid Ali',        'role' => 'batsman',        'jersey_number' => '16'],
            ['name' => 'Mohsin Ali',        'role' => 'bowler',         'jersey_number' => '17'],
            ['name' => 'Muneeb Ali',        'role' => 'batsman',        'jersey_number' => '18'],
            ['name' => 'Hussain',           'role' => 'all_rounder',    'jersey_number' => '19'],
            ['name' => 'Ahad Ali',          'role' => 'bowler',         'jersey_number' => '20'],
            ['name' => 'Akhtar Shah',       'role' => 'batsman',        'jersey_number' => '21'],
            ['name' => 'Abdul Rehman',      'role' => 'all_rounder',    'jersey_number' => '22'],
            ['name' => 'Abdullah KC',       'role' => 'bowler',         'jersey_number' => '23'],
            ['name' => 'Arsalan Shan',      'role' => 'batsman',        'jersey_number' => '24'],
            ['name' => 'Usama Muna',        'role' => 'all_rounder',    'jersey_number' => '25'],
        ];

        $createdPlayers = [];
        foreach ($playersList as $pData) {
            $player = Player::firstOrCreate(
                ['name' => $pData['name']],
                [
                    'role'          => $pData['role'],
                    'jersey_number' => $pData['jersey_number'],
                    'is_active'     => true,
                    'batting_style' => 'right_hand',
                    'bowling_style' => 'right_arm_fast',
                ]
            );
            $createdPlayers[] = $player;
        }

        // ── 5. Assign players to Maqbool pur in editions 34 & 35 ─────
        $editions = array_filter([$edition34, $edition35]);
        foreach ($editions as $edition) {
            foreach ($createdPlayers as $player) {
                PlayerEditionTeam::firstOrCreate(
                    ['player_id' => $player->id, 'edition_id' => $edition->id],
                    ['team_id' => $maqboolTeam->id]
                );
            }
        }

        // ── 6. Add dummy edition stats for Edition 34 ─────────────────
        $statsData = [
            ['runs' => 287, 'wickets' => 3,  'matches' => 8, 'hs' => 62, 'avg' => 35.88, 'sr' => 142.0, 'fifties' => 2, 'eco' => 7.5,  'overs' => 4.0],
            ['runs' => 241, 'wickets' => 12, 'matches' => 8, 'hs' => 54, 'avg' => 30.13, 'sr' => 125.0, 'fifties' => 1, 'eco' => 6.2,  'overs' => 18.0],
            ['runs' => 189, 'wickets' => 8,  'matches' => 7, 'hs' => 48, 'avg' => 27.0,  'sr' => 118.0, 'fifties' => 0, 'eco' => 7.8,  'overs' => 14.0],
            ['runs' => 162, 'wickets' => 14, 'matches' => 8, 'hs' => 41, 'avg' => 23.14, 'sr' => 108.0, 'fifties' => 0, 'eco' => 5.9,  'overs' => 20.0],
            ['runs' => 145, 'wickets' => 2,  'matches' => 6, 'hs' => 55, 'avg' => 24.17, 'sr' => 135.0, 'fifties' => 1, 'eco' => 8.0,  'overs' => 3.0],
            ['runs' => 131, 'wickets' => 6,  'matches' => 8, 'hs' => 38, 'avg' => 18.71, 'sr' => 112.0, 'fifties' => 0, 'eco' => 6.5,  'overs' => 10.0],
            ['runs' => 98,  'wickets' => 11, 'matches' => 7, 'hs' => 34, 'avg' => 16.33, 'sr' => 105.0, 'fifties' => 0, 'eco' => 6.1,  'overs' => 16.0],
            ['runs' => 76,  'wickets' => 4,  'matches' => 6, 'hs' => 28, 'avg' => 15.2,  'sr' => 98.0,  'fifties' => 0, 'eco' => 7.2,  'overs' => 8.0],
            ['runs' => 54,  'wickets' => 0,  'matches' => 5, 'hs' => 22, 'avg' => 13.5,  'sr' => 90.0,  'fifties' => 0, 'eco' => 0.0,  'overs' => 0.0],
            ['runs' => 42,  'wickets' => 9,  'matches' => 7, 'hs' => 18, 'avg' => 10.5,  'sr' => 88.0,  'fifties' => 0, 'eco' => 6.8,  'overs' => 12.0],
        ];

        foreach ($createdPlayers as $idx => $player) {
            if ($idx >= count($statsData)) break;
            $s = $statsData[$idx];

            PlayerEditionStat::firstOrCreate(
                ['player_id' => $player->id, 'edition_id' => $edition34->id],
                [
                    'team_id'              => $maqboolTeam->id,
                    'matches_played'       => $s['matches'],
                    'innings_batted'       => $s['matches'],
                    'total_runs'           => $s['runs'],
                    'highest_score'        => $s['hs'],
                    'batting_average'      => $s['avg'],
                    'batting_strike_rate'  => $s['sr'],
                    'fifties'              => $s['fifties'],
                    'centuries'            => 0,
                    'total_fours'          => intval($s['runs'] / 8),
                    'total_sixes'          => intval($s['runs'] / 25),
                    'innings_bowled'       => $s['wickets'] > 0 ? $s['matches'] : 0,
                    'overs_bowled'         => $s['overs'],
                    'total_wickets'        => $s['wickets'],
                    'bowling_economy'      => $s['eco'],
                    'bowling_average'      => $s['wickets'] > 0 ? round($s['runs'] / max(1, $s['wickets']), 2) : 0,
                ]
            );
        }

        // ── 7. Create 4 Polls ─────────────────────────────────────────
        $allTeams = Team::where('is_active', true)->orderBy('name')->get();
        $topTeams = $allTeams->take(4)->pluck('name')->toArray();

        $pollsData = [
            [
                'edition'   => $edition35,
                'question'  => 'Who will win the 35th Edition?',
                'options'   => array_slice($topTeams, 0, 4),
            ],
            [
                'edition'   => $edition35,
                'question'  => 'Best batsman this season?',
                'options'   => ['Farooq Numberdar', 'Jawad Ahmad Zia', 'Bilal Malang', 'Shahzad Kohli'],
            ],
            [
                'edition'   => $edition34,
                'question'  => 'Best team of Edition 34?',
                'options'   => array_slice($topTeams, 0, 4),
            ],
            [
                'edition'   => $edition34,
                'question'  => 'Player of Edition 34?',
                'options'   => ['Farooq Numberdar', 'Jawad Ahmad Zia', 'Tahir Shah', 'Irfan Rath'],
            ],
        ];

        foreach ($pollsData as $pollData) {
            if (!$pollData['edition']) continue;

            $poll = Poll::firstOrCreate(
                [
                    'edition_id' => $pollData['edition']->id,
                    'question'   => $pollData['question'],
                ],
                [
                    'is_active'       => true,
                    'allow_anonymous' => true,
                    'starts_at'       => now()->subDays(7),
                    'ends_at'         => now()->addDays(30),
                ]
            );

            foreach ($pollData['options'] as $order => $optText) {
                PollOption::firstOrCreate(
                    ['poll_id' => $poll->id, 'option_text' => $optText],
                    ['display_order' => $order]
                );
            }
        }

        $this->command->info('DummyDataSeeder: teams updated, Edition 34 created, 25 players added, 4 polls created.');
    }
}
