<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealTeamDataSeeder extends Seeder
{
    public function run(): void
    {
        $edition35Id = DB::table('editions')->where('is_current', true)->value('id');
        $now = now();

        // ── 1. Update team names / village names ─────────────────────
        $teamUpdates = [
            1  => ['name' => 'Ghazi Cricket Club',       'village_name' => 'Chak 305',       'short_code' => 'GCC'],
            4  => ['name' => 'Sarja Sixers CC',          'village_name' => 'Village 418',     'short_code' => 'SS4'],
            5  => ['name' => 'Goraya Cricket Club',      'village_name' => 'Goraya Pur 419',  'short_code' => 'GOR'],
            6  => ['name' => 'Gojra More CC',            'village_name' => 'Gojra More',      'short_code' => 'GMC'],
            7  => ['name' => 'Al Haider Cricket Club',   'village_name' => 'Village 421',     'short_code' => 'AHC'],
            8  => ['name' => 'Khalsabad Tigers',         'village_name' => 'Khalsabad',       'short_code' => 'KHT'],
            9  => ['name' => 'Suraj Pur CC',             'village_name' => 'Suraj Pur',       'short_code' => 'SPC'],
            10 => ['name' => 'Friends Cricket Club',     'village_name' => 'Village 353 JB',  'short_code' => 'FCC'],
            11 => ['name' => 'Qadirabad Strikers',       'village_name' => 'Qadirabad 354',   'short_code' => 'QS4'],
        ];

        foreach ($teamUpdates as $teamId => $data) {
            DB::table('teams')->where('id', $teamId)->update($data);
        }
        $this->command->info('✅ Team names updated.');

        // ── 2. Real player lists per team ────────────────────────────
        // Format: [existing_ids_range_start, existing_ids_range_end, captain_pos (0-based), vc_pos, players_array]
        $realPlayers = [

            // Team 1 — Ghazi Cricket Club 305 (existing IDs: 26–36)
            1 => [
                'existing' => range(26, 36),
                'captain'  => 0,
                'vc'       => -1,
                'players'  => [
                    'Zeeshan Sipra', 'Sajjad Toor', 'Abubakar Warraich', 'Jaffar Sipra',
                    'Hafiz Sadaqat', 'Wajid Ali', 'Shahzaib Toor', 'Ali Hassan Cheema',
                    'Asim Cheema', 'Aftab Sipra', 'Asad Sipra', 'Asad Cheema',
                    'Kamran Cheema', 'Abdul Rehman Cheema', 'Ali Hassan Cheema II',
                    'Umair Cheema', 'Usman Butt', 'Tanzeb Haider', 'Abdullah Bagri',
                    'Mian Bilal', 'Jamshaid Sipra', 'Nadeem Andy', 'Mian Haider',
                    'Nafy Toor', 'Ali Abdullah',
                ],
            ],

            // Team 4 — Sarja Sixers CC 418 (existing IDs: 48–58)
            4 => [
                'existing' => range(48, 58),
                'captain'  => 0,
                'vc'       => 2,
                'players'  => [
                    'Sarfraz Goraya', 'Naseer Rajput', 'Waseem Goraya', 'Kaleem Goraya',
                    'Ali Goraya', 'Sikandar Goraya', 'Shareef Rajput', 'Usman Qazi',
                    'Umar Goraya', 'Saleem Rajput', 'Noman Goraya', 'Haider Goraya',
                    'Qasim Rajput', 'Hafiz Usman', 'M Farooq', 'Bilal Asghar',
                    'Bilal Asif', 'Majid Rajput', 'Ikram Rajput', 'Shabir Rajput',
                    'Asif', 'Basharat Rajput', 'Usman Goraya', 'Raja Kabir', 'Zain Goraya',
                ],
            ],

            // Team 5 — Goraya Cricket Club 419 (existing IDs: 59–69)
            5 => [
                'existing' => range(59, 69),
                'captain'  => 0,
                'vc'       => 1,
                'players'  => [
                    'Nafees Goraya', 'Adil Goraya', 'Tauqir Goraya', 'Gufran Goraya',
                    'Atif Goraya', 'Talha Goraya', 'Subhan Goraya', 'Zaman Ghumman',
                    'Bilal', 'Malik Saleem', 'Kashif', 'Zeeshan Raja',
                    'Ali Goraya', 'Awais Goraya', 'Asim Goraya', 'Saqlain Goraya',
                    'Touseef Goraya', 'Kamran', 'Muzamil', 'Hafiz Jameel',
                    'Ajmal', 'Hussnain Mirza', 'Hassan', 'Azeem',
                ],
            ],

            // Team 6 — Gojra More CC (existing IDs: 70–80)
            6 => [
                'existing' => range(70, 80),
                'captain'  => 0,
                'vc'       => -1,
                'players'  => [
                    'Irfan Qadri', 'Malik Azhar', 'Malik Shohban', 'Malik Shahzaib',
                    'Malik Wajid', 'Malik Ijaz', 'Shabir Rashid', 'Ch Shabir',
                    'Usama Khan', 'Razaq Khan', 'Hamyo Khan', 'Noshair Khan',
                    'Nasir Khan', 'Noor Khan', 'Rehman Khan', 'Shahzad Khan',
                    'Sharo Khan', 'Sajid', 'Mahr Sanaullah', 'Mahr Asad Jug',
                    'Ali Raza', 'Ch Faisal', 'Shahid', 'Babu Khan', 'Rakha',
                ],
            ],

            // Team 7 — Al Haider Cricket Club 421 (existing IDs: 81–91)
            7 => [
                'existing' => range(81, 91),
                'captain'  => 1,
                'vc'       => -1,
                'players'  => [
                    'Asghar Ali', 'Najam Banto', 'Husnain Khan', 'Touseef',
                    'Ali Khan', 'Amjad Pathan', 'Nouman', 'Usman Haider',
                    'Abbas Khan', 'Raees Abbas', 'Zeeshan Shani', 'Babar',
                    'Hammad', 'Tajmal', 'Mohsin', 'Riaz',
                    'Shahid Imran', 'Amir', 'Adeel', 'Farman',
                    'Fizan', 'Mureed Sultan', 'Ali Raza', 'Ali Shahbaz', 'Asif Ladi',
                ],
            ],

            // Team 8 — Khalsabad Tigers (existing IDs: 92–102)
            8 => [
                'existing' => range(92, 102),
                'captain'  => 0,
                'vc'       => 1,
                'players'  => [
                    'Ramzan', 'Tariq', 'Abid Gill', 'Ateeq',
                    'Asghar Raju', 'Mosin Goraya', 'Afzaal', 'Mian Bilal',
                    'Zain', 'Adnan', 'Atif', 'Umair',
                    'Athsham', 'Faraz', 'Bilal', 'Majid',
                    'Tahir', 'Asim', 'Zubair', 'Ali',
                    'Ali Javed', 'Asif', 'Abdullah', 'Abdullah Whala',
                ],
            ],

            // Team 9 — Suraj Pur CC (existing IDs: 103–113)
            9 => [
                'existing' => range(103, 113),
                'captain'  => 0,
                'vc'       => -1,
                'players'  => [
                    'Abdul Qudoos', 'Abu Yousafian', 'Amal', 'Fahad',
                    'Mehran Kantool', 'Sultan', 'Fayyaz', 'Tayyab',
                    'Hussain Toota', 'Imran', 'Aleem', 'Imran Chaudhary',
                    'Aamir', 'Haider Ali', 'Allah Rakha', 'Mujahid',
                    'Hammad', 'Osama', 'Irfan', 'Dalipshan',
                    'Hamza', 'Saleh', 'Salool', 'Qurban',
                ],
            ],

            // Team 10 — Friends Cricket Club 353 JB (existing IDs: 114–124)
            10 => [
                'existing' => range(114, 124),
                'captain'  => 0,
                'vc'       => -1,
                'players'  => [
                    'Noman Waince', 'Zahid Manzoor', 'Zeeshan Waince', 'Umair',
                    'Faisal', 'Salman', 'Akhtar Bodi', 'Ali Abass',
                    'Sufyan', 'Sohail', 'Faizan', 'Ahmad',
                    'Abdullah', 'Rehman Liaqat', 'Awais', 'Zain Warraich',
                    'Moon', 'Sarfraz', 'Afzal', 'Waqas Toor',
                    'Waqas Janno', 'Nouman', 'Hassan', 'Arbaz', 'Ali',
                ],
            ],

            // Team 11 — Qadirabad Strikers 354 (existing IDs: 125–135)
            11 => [
                'existing' => range(125, 135),
                'captain'  => 0,
                'vc'       => -1,
                'players'  => [
                    'Ali Zaman', 'Momin Ali', 'Usama', 'Umar Butt',
                    'Asad', 'Tayyab Gill', 'Abdullah', 'Aman Gill',
                    'Abdulrehman Jutt', 'Waqas Sahotra', 'Dilshad Mithu', 'Haroon Shafique',
                    'Sharoon Shafique', 'Awais Mughal', 'Ali Bhai', 'Zeeshan',
                    'Sajid Bhati', 'Abubakar Bhalo', 'Salman', 'Bakar MG',
                    'Abdullah MG', 'Wasif Ali Jutt', 'Sufiyan Lefty', 'Jawad Butt',
                    'Hasnain',
                ],
            ],
        ];

        $roles      = ['batsman', 'batsman', 'batsman', 'bowler', 'bowler', 'all_rounder', 'all_rounder', 'wicket_keeper'];
        $batStyles  = ['right_hand', 'right_hand', 'right_hand', 'left_hand'];
        $bowlStyles = ['right_arm_fast', 'right_arm_medium', 'right_arm_spin', 'left_arm_fast', 'left_arm_medium', 'none'];

        foreach ($realPlayers as $teamId => $data) {
            $existingIds = $data['existing'];
            $playerNames = $data['players'];
            $captainPos  = $data['captain'];
            $vcPos       = $data['vc'];

            // Update existing player names in-place (keeps FK refs intact)
            foreach ($existingIds as $seq => $pid) {
                if (isset($playerNames[$seq])) {
                    DB::table('players')->where('id', $pid)->update([
                        'name'          => $playerNames[$seq],
                        'jersey_number' => (string)($seq + 1),
                        'role'          => $roles[$seq % count($roles)],
                        'batting_style' => $batStyles[$seq % count($batStyles)],
                        'bowling_style' => $bowlStyles[$seq % count($bowlStyles)],
                        'is_active'     => true,
                    ]);
                }
            }

            // Create new players for those beyond the 11 existing slots
            $newPlayerIds = [];
            for ($i = count($existingIds); $i < count($playerNames); $i++) {
                $pid = DB::table('players')->insertGetId([
                    'name'          => $playerNames[$i],
                    'jersey_number' => (string)($i + 1),
                    'role'          => $roles[$i % count($roles)],
                    'batting_style' => $batStyles[$i % count($batStyles)],
                    'bowling_style' => $bowlStyles[$i % count($bowlStyles)],
                    'is_active'     => true,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $newPlayerIds[$i] = $pid;
            }

            // Build complete ordered list: existing IDs + new IDs
            $allIds = $existingIds;
            foreach ($newPlayerIds as $idx => $pid) {
                $allIds[$idx] = $pid;
            }

            // Seed player_team_editions for edition 35
            if ($edition35Id) {
                foreach ($allIds as $seq => $pid) {
                    DB::table('player_team_editions')->updateOrInsert(
                        ['player_id' => $pid, 'edition_id' => $edition35Id],
                        [
                            'team_id'     => $teamId,
                            'is_captain'  => ($seq === $captainPos),
                            'is_vice_captain' => ($vcPos >= 0 && $seq === $vcPos),
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ]
                    );
                }
            }

            $total = count($existingIds) + count($newPlayerIds);
            $this->command->info("  Team {$teamId}: updated {$total} players.");
        }

        // ── 3. Azad CC (team 2) — link existing players to edition 35
        if ($edition35Id) {
            $azadPlayerIds = DB::table('players')->whereIn('id', range(1, 25))->pluck('id');
            foreach ($azadPlayerIds as $seq => $pid) {
                DB::table('player_team_editions')->updateOrInsert(
                    ['player_id' => $pid, 'edition_id' => $edition35Id],
                    [
                        'team_id'         => 2,
                        'is_captain'      => ($seq === 0),
                        'is_vice_captain' => false,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]
                );
            }
            $this->command->info('  Team 2 (Azad CC): linked 25 players to edition 35.');
        }

        $this->command->info('✅ Real team data seeded successfully.');
    }
}
