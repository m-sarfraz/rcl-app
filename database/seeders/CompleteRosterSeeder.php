<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteRosterSeeder extends Seeder
{
    public function run(): void
    {
        $e35 = DB::table('editions')->where('is_current', true)->value('id');
        $now = now();
        $roles      = ['batsman', 'batsman', 'batsman', 'bowler', 'bowler', 'all_rounder', 'all_rounder', 'wicket_keeper'];
        $batStyles  = ['right_hand', 'right_hand', 'right_hand', 'left_hand'];
        $bowlStyles = ['right_arm_fast', 'right_arm_medium', 'right_arm_spin', 'left_arm_fast', 'left_arm_medium', 'none'];

        // ── Helper: create player + PTE ──────────────────────────────
        $addPlayer = function (string $name, int $teamId, int $jersey, bool $captain = false, bool $vc = false) use ($e35, $now, $roles, $batStyles, $bowlStyles) {
            $seq = $jersey - 1;
            $pid = DB::table('players')->insertGetId([
                'name'          => $name,
                'jersey_number' => (string)$jersey,
                'role'          => $roles[$seq % count($roles)],
                'batting_style' => $batStyles[$seq % count($batStyles)],
                'bowling_style' => $bowlStyles[$seq % count($bowlStyles)],
                'is_active'     => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
            if ($e35) {
                DB::table('player_team_editions')->updateOrInsert(
                    ['player_id' => $pid, 'edition_id' => $e35],
                    ['team_id' => $teamId, 'is_captain' => $captain, 'is_vice_captain' => $vc, 'created_at' => $now, 'updated_at' => $now]
                );
            }
            return $pid;
        };

        // ── 1. Fill the 3 teams with 24 players to 25 ────────────────

        // Team 5 — Goraya CC (24 → 25)
        $addPlayer('Usman Goraya', 5, 25);
        $this->command->info('Team 5 (Goraya CC): added player #25.');

        // Team 8 — Khalsabad Tigers (24 → 25)
        $addPlayer('Hamid', 8, 25);
        $this->command->info('Team 8 (Khalsabad Tigers): added player #25.');

        // Team 9 — Suraj Pur CC (24 → 25)
        $addPlayer('Taimoor', 9, 25);
        $this->command->info('Team 9 (Suraj Pur CC): added player #25.');

        // ── 2. Seed full 25-player rosters for remaining 6 teams ─────

        $remaining = [
            // Team 3 — Chak 417 Thunders
            3 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Shahbaz Aslam', 'Rizwan Tufail', 'Adnan Shafiq', 'Khurram Shahzad', 'Waqas Saeed',
                    'Tariq Javed', 'Hamza Rauf', 'Muzammil Ali', 'Saeed Ahmad', 'Raza ul Haq',
                    'Imtiaz Qadir', 'Noman Butt', 'Amjad Pervez', 'Saleem Nawaz', 'Faisal Butt',
                    'Usman Ghani', 'Zeeshan Mehmood', 'Hassan Zulfiqar', 'Bilal Khalid', 'Waheed Ahmad',
                    'Tauqir Aziz', 'Mubin Shoukat', 'Kashif Latif', 'Ijaz Hussain', 'Naeem Butt',
                ],
            ],
            // Team 12 — Chak 351 Tigers
            12 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Kamran Akmal II', 'Salman Butt II', 'Shan Masood II', 'Abid Ali II', 'Imam Ul Haq II',
                    'Azhar Ali II', 'Haris Sohail II', 'Asad Shafiq II', 'Fawad Alam II', 'Sarfaraz Ahmed II', 'Shoaib Ahmed',
                    'Farrukh Bashir', 'Khizer Hayat', 'Abrar Mehmood', 'Moazzam Raza', 'Danish Butt',
                    'Bilal Asif II', 'Zeeshan Butt', 'Usman Kamal', 'Naveed Mehmood', 'Sajid Butt',
                    'Hamid Ullah', 'Imran Haider', 'Adil Butt', 'Saqib Yousaf',
                ],
            ],
            // Team 13 — Chak 183 Champions
            13 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Abdul Razzaq II', 'Umar Gul II', 'Aizaz Cheema II', 'Saeed Ajmal II', 'Abdur Rehman II',
                    'Zulfiqar Babar II', 'Sohail Khan II', 'Rahat Ali II', 'Ehsan Adil II', 'Imad Wasim II', 'Yasir Hameed II',
                    'Rehan Afridi', 'Jamil Ahmad', 'Mubeen Mehmood', 'Ameer Hamza', 'Sami Ullah',
                    'Zain ul Abideen', 'Arfan Butt', 'Ghulam Murtaza', 'Muhammad Shafiq', 'Shahnawaz Butt',
                    'Zaheer Iqbal', 'Waqar Butt', 'Asif Baloch', 'Akhtar Hayat',
                ],
            ],
            // Team 14 — Village 786 Rangers
            14 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Bahadur Khan', 'Dost Mohammad', 'Ghulam Haider', 'Haji Noor', 'Mehboob Ali',
                    'Sardar Ahmed', 'Talib Hussain', 'Bashir Ahmad', 'Peer Bakhsh', 'Nabi Ahmad', 'Wali Mohammad',
                    'Riaz Ahmed', 'Azeem Khan', 'Shaheen Butt', 'Maqsood Ali', 'Zainul Abidin',
                    'Shaukat Butt', 'Mukhtar Ahmad', 'Ghulam Qasim', 'Sabir Hussain', 'Raheem Butt',
                    'Fazil Akhtar', 'Jamshid Butt', 'Khalid Nawaz', 'Riaz Butt',
                ],
            ],
            // Team 15 — Chak 213 Stallions
            15 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Saifullah Bangash', 'Imranullah Bhatti', 'Khalilur Rehman', 'Noorullah Hasan', 'Zubairullah Khan',
                    'Hizbullah Afridi', 'Rahimullah Yusufzai', 'Attaullah Butt', 'Faridullah Jan', 'Azizullah Gul', 'Qalandar Shah',
                    'Hayatullah Khan', 'Aminullah Butt', 'Khaista Gul', 'Nawabzada Butt', 'Shamsul Haq',
                    'Gulzar Ahmad', 'Tahir Gul', 'Wazeer Khan', 'Shah Nawaz', 'Barkatullah Butt',
                    'Sibghatullah Afridi', 'Tajuddin Khan', 'Iqbalullah Jan', 'Gul Mohammad',
                ],
            ],
            // Team 16 — Chak 449 Falcons
            16 => [
                'cap' => 0, 'vc' => 1,
                'players' => [
                    'Liaquat Ali', 'Ghulam Nabi', 'Sher Mohammad', 'Abdul Ghani', 'Fazal Ur Rehman',
                    'Mohammad Gul', 'Amir Mohammad', 'Noor Rehman', 'Karam Shah', 'Sikander Ali', 'Sabar Gul',
                    'Noor Ahmad', 'Daud Shah', 'Hameedullah Khan', 'Zubaida Butt', 'Mohsin Raza',
                    'Jawad ul Haq', 'Sanaullah Butt', 'Tariq Ullah', 'Farooq Ahmad II', 'Hayat Ullah II',
                    'Abdul Wahab', 'Shahzad Butt', 'Tanveer Shah', 'Arshad Butt',
                ],
            ],
        ];

        foreach ($remaining as $teamId => $data) {
            $tname = DB::table('teams')->where('id', $teamId)->value('name');
            foreach ($data['players'] as $i => $name) {
                $isCap = ($i === $data['cap']);
                $isVc  = ($i === $data['vc']);
                $addPlayer($name, $teamId, $i + 1, $isCap, $isVc);
            }
            $this->command->info("Team {$teamId} ({$tname}): 25 players added.");
        }

        $this->command->info('✅ All rosters completed to 25 players.');
    }
}
