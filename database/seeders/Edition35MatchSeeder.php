<?php

namespace Database\Seeders;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Edition35MatchSeeder extends Seeder
{
    /** Club code → existing team ID (all 16 confirmed). */
    private array $clubMap = [
        183 => 13,  // Shola CC
        213 => 15,  // Azad CC 213
        305 => 1,   // Ghazi CC
        348 => 2,   // Azad Cricket Club
        351 => 12,  // Surajpur CC
        353 => 10,  // Friends CC
        354 => 11,  // Qadirabad CC
        355 => 8,   // United CC
        356 => 9,   // Khalsabad Tigers CC
        417 => 3,   // Friends CC 417
        418 => 4,   // Sarja Sixers CC
        419 => 5,   // Goraya Cricket Club
        420 => 6,   // Syed Jalal CC
        421 => 7,   // Al Haider Cricket Club
        449 => 16,  // Shaheen CC
        786 => 14,  // Shahbaz Shaheed CC (host venue)
    ];

    private function team(int $code): Team
    {
        if (isset($this->clubMap[$code])) {
            return Team::findOrFail($this->clubMap[$code]);
        }

        return Team::firstOrCreate(
            ['short_code' => (string) $code],
            [
                'name'            => "Club {$code}",
                'village_name'    => 'TBD',
                'primary_color'   => '#1B8A4E',
                'secondary_color' => '#D4900A',
                'is_active'       => true,
            ]
        );
    }

    public function run(): void
    {
        $edition = Edition::where('edition_number', '=', 35)->firstOrFail();

        // Clear existing matches + pool assignments for fresh seed
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        CricketMatch::where('edition_id', '=', $edition->id)->forceDelete();
        DB::table('edition_teams')->where('edition_id', '=', $edition->id)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Pool assignments ──────────────────────────────────────────
        $pools = [
            1 => [213, 351, 355, 420],  // Pool A
            2 => [305, 356, 418, 449],  // Pool B
            3 => [348, 354, 421, 786],  // Pool C
            4 => [183, 353, 417, 419],  // Pool D
        ];

        foreach ($pools as $poolNum => $codes) {
            foreach ($codes as $code) {
                DB::table('edition_teams')->insertOrIgnore([
                    'edition_id'   => $edition->id,
                    'team_id'      => $this->team($code)->id,
                    'group_number' => $poolNum,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // ── Group stage fixtures ──────────────────────────────────────
        // [match#, day(May), home_code, away_code, start_time, umpires, scorer, referee]
        $fixtures = [
            // DAY 1 — 23 May 2026  (Duty: Ramzan, Zain, Tayyab)
            [ 1, 23, 417, 419, '07:30', 'USAMA & RAZZAQ',      'SHAHZAD', 'IRAFAN'],
            [ 2, 23, 351, 355, '09:20', 'SAQLAIN & NAFEES',    'USMAN',   'RAMZAN'],
            [ 3, 23, 183, 353, '11:10', 'NAVEED & FIAZ',       'MUJEEB',  'RAMZAN'],
            [ 4, 23, 305, 449, '13:00', 'QAZAFI & UMAIR',      'MUJAHID', 'ZAIN'],
            [ 5, 23, 348, 354, '14:50', 'ABUBAKAR & MUZAMIL',  'SUNNY',   'ZAIN'],
            [ 6, 23, 305, 356, '16:40', 'ALI ZAMAN & FAROOQ',  'SHAHID',  'TAYYAB'],

            // DAY 2 — 24 May 2026  (Duty: Naseer, Sajjad, Arshad)
            [ 7, 24, 418, 449, '07:30', 'USAMA & RAZZAQ',      'SHAHZAD', 'IRAFAN'],
            [ 8, 24, 355, 213, '09:20', 'SARFRAZ & MUZAMIL',   'HAIDER',  'NASEER'],
            [ 9, 24, 420, 351, '11:10', 'NAVEED & BABAR',      'MUJEEB',  'SAJJAD'],
            [10, 24, 353, 419, '13:00', 'JAVED & FIAZ',        'TANVEER', 'ARSHAD'],
            [11, 24, 183, 417, '14:50', 'UMAIR & NAFEES',      'NOUMAN',  'ARSHAD'],
            [12, 24, 348, 786, '16:40', 'QAZAFI & SAQLAIN',    'USMAN',   'ARSHAD'],

            // DAY 3 — 25 May 2026  (Duty: Waqas, Abid, Hasnain)
            [13, 25, 213, 420, '07:30', 'USAMA & RAZZAQ',      'SHAHZAD', 'IRAFAN'],
            [14, 25, 356, 418, '09:20', 'BABAR & ARSHAD',      'TANVEER', 'WAQAS'],
            [15, 25, 183, 419, '11:10', 'RAMZAN & SARFRAZ',    'AFZAL',   'WAQAS'],
            [16, 25, 353, 417, '13:00', 'QAZAFI & NAFEES',     'MUJAHID', 'ABID'],
            [17, 25, 348, 421, '14:50', 'UMAIR & SAQLAIN',     'NOUMAN',  'ABID'],
            [18, 25, 354, 786, '16:40', 'FAROOQ & ALI',        'SHAHID',  'HASNAIN'],

            // DAY 4 — 26 May 2026  (Duty: Waqas, Iqbal, Hasnain, Naseer)
            [19, 26, 213, 351, '07:30', 'USAMA & RAZZAQ',      'SHAHZAD', 'IRAFAN'],
            [20, 26, 355, 420, '09:20', 'BABAR & FIAZ',        'SUFYAN',  'WAQAS'],
            [21, 26, 356, 449, '11:10', 'NAVEED & ARSHAD',     'TANVEER', 'IQBAL'],
            [22, 26, 354, 421, '13:00', 'RAMZAN & MUZAMIL',    'AFZAL',   'IQBAL'],
            [23, 26, 305, 418, '14:50', 'ALI ZAMAN & TAYYAB',  'AMJID',   'HASNAIN'],
            [24, 26, 421, 786, '16:40', 'ABU BAKAR & SARFRAZ', 'HAIDER',  'NASEER'],
        ];

        foreach ($fixtures as [$num, $day, $home, $away, $time, $umpires, $scorer, $referee]) {
            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $this->team($home)->id,
                'away_team_id'   => $this->team($away)->id,
                'match_number'   => (string) $num,
                'match_type'     => 'group',
                'venue'          => 'Gojra More',
                'scheduled_at'   => Carbon::parse("2026-05-{$day} {$time}:00"),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => "Umpires: {$umpires} | Scorer: {$scorer} | Referee: {$referee}",
            ]);
        }

        // ── Knockout stage placeholders (Day 5-6) ────────────────────
        // Quarter Finals — 28 May 2026
        $qfSlots = [
            [25, '09:00', 'A1 vs D2'],
            [26, '11:00', 'A2 vs D1'],
            [27, '13:00', 'B1 vs C2'],
            [28, '15:00', 'B2 vs C1'],
        ];

        // We need real team IDs for FK; use a placeholder team for now
        $placeholder = Team::firstOrCreate(
            ['short_code' => 'TBD'],
            ['name' => 'TBD', 'village_name' => 'TBD', 'is_active' => false]
        );

        foreach ($qfSlots as [$num, $time, $label]) {
            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $placeholder->id,
                'away_team_id'   => $placeholder->id,
                'match_number'   => (string) $num,
                'match_type'     => 'quarter_final',
                'venue'          => 'Gojra More',
                'scheduled_at'   => Carbon::parse("2026-05-28 {$time}:00"),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => $label,
            ]);
        }

        // Semifinals — 29 May 2026
        foreach ([[29, '09:00', '1st SF: W(A1vD2) vs W(B1vC2)'], [30, '11:00', '2nd SF: W(A2vD1) vs W(B2vC1)']] as [$num, $time, $label]) {
            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $placeholder->id,
                'away_team_id'   => $placeholder->id,
                'match_number'   => (string) $num,
                'match_type'     => 'semi_final',
                'venue'          => 'Gojra More',
                'scheduled_at'   => Carbon::parse("2026-05-29 {$time}:00"),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => $label,
            ]);
        }

        // Final — 29 May 2026
        CricketMatch::create([
            'edition_id'     => $edition->id,
            'home_team_id'   => $placeholder->id,
            'away_team_id'   => $placeholder->id,
            'match_number'   => '31',
            'match_type'     => 'final',
            'venue'          => 'Gojra More',
            'scheduled_at'   => Carbon::parse('2026-05-29 14:30:00'),
            'status'         => 'upcoming',
            'overs_per_side' => 10,
            'notes'          => 'FINAL',
        ]);

        $total = CricketMatch::where('edition_id', '=', $edition->id)->count();
        $this->command->info("Edition 35 fixtures seeded: {$total} matches (24 group + 4 QF + 2 SF + 1 Final)");
        $this->command->warn('NOTE: 13 teams were auto-created as placeholders (Club NNN). Update their names in admin → Teams.');
    }
}
