<?php

namespace Database\Seeders;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class Edition37ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $edition = Edition::where('edition_number', 37)->first()
            ?? Edition::where('is_current', true)->firstOrFail();

        // 1. Map team short_code to ID
        $teamMap = Team::pluck('id', 'short_code')->all();

        $team = function (string $code) use ($teamMap): int {
            if ($code === 'GM') {
                $code = '786GM';
            }
            if (isset($teamMap[$code])) {
                return $teamMap[$code];
            }
            $t = Team::firstOrCreate(
                ['short_code' => $code],
                ['name' => "Team {$code}", 'village_name' => "Village {$code}", 'is_active' => true]
            );
            return $t->id;
        };

        // 2. Knockout placeholder teams (marked is_active => false so they don't show in 18 club list)
        $knockoutTeams = [
            'A1'    => ['name' => 'Winner Pool A (A1)',         'village_name' => 'Pool A',   'short_code' => 'A1'],
            'D2'    => ['name' => 'Runner-up Pool D (D2)',     'village_name' => 'Pool D',   'short_code' => 'D2'],
            'A2'    => ['name' => 'Runner-up Pool A (A2)',     'village_name' => 'Pool A',   'short_code' => 'A2'],
            'D1'    => ['name' => 'Winner Pool D (D1)',         'village_name' => 'Pool D',   'short_code' => 'D1'],
            'B1'    => ['name' => 'Winner Pool B (B1)',         'village_name' => 'Pool B',   'short_code' => 'B1'],
            'C2'    => ['name' => 'Runner-up Pool C (C2)',     'village_name' => 'Pool C',   'short_code' => 'C2'],
            'B2'    => ['name' => 'Runner-up Pool B (B2)',     'village_name' => 'Pool B',   'short_code' => 'B2'],
            'C1'    => ['name' => 'Winner Pool C (C1)',         'village_name' => 'Pool C',   'short_code' => 'C1'],
            'W_QF1' => ['name' => 'Winner QF 1 (A1 vs D2)',     'village_name' => 'Knockout', 'short_code' => 'W_QF1'],
            'W_QF3' => ['name' => 'Winner QF 3 (B1 vs C2)',     'village_name' => 'Knockout', 'short_code' => 'W_QF3'],
            'W_QF2' => ['name' => 'Winner QF 2 (A2 vs D1)',     'village_name' => 'Knockout', 'short_code' => 'W_QF2'],
            'W_QF4' => ['name' => 'Winner QF 4 (B2 vs C1)',     'village_name' => 'Knockout', 'short_code' => 'W_QF4'],
            'W_SF1' => ['name' => 'Winner 1st Semifinal',      'village_name' => 'Knockout', 'short_code' => 'W_SF1'],
            'W_SF2' => ['name' => 'Winner 2nd Semifinal',      'village_name' => 'Knockout', 'short_code' => 'W_SF2'],
        ];

        $koMap = [];
        foreach ($knockoutTeams as $key => $data) {
            $t = Team::firstOrCreate(
                ['short_code' => $data['short_code']],
                array_merge($data, ['is_active' => false])
            );
            $koMap[$key] = $t->id;
        }

        // 3. Clear existing matches for Edition 37
        CricketMatch::where('edition_id', $edition->id)->forceDelete();

        // 4. All 32 Group Matches from PDF
        $groupMatches = [
            // DAY 1 — 02-10-2026 Friday (Day Duty: Momin, Abid)
            [1, '353', '354', '2026-10-02 08:00:00', '07:45 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Momin, Abid'],
            [2, '214G', '356', '2026-10-02 09:50:00', '09:35 AM', 'AWAIS & ASAD', 'AMAN', 'MOMIN', 'Day Duty: Momin, Abid'],
            [3, '420', '426', '2026-10-02 11:40:00', '11:25 AM', 'AZHAR & RAMZAN', 'AFZAAL', 'MOMIN', 'Day Duty: Momin, Abid'],
            [4, '348S', '417', '2026-10-02 13:30:00', '01:15 PM', 'ARSHAD & JAMSHIAD', 'NOMAN', 'ABID', 'Day Duty: Momin, Abid'],
            [5, '418', '420', '2026-10-02 15:20:00', '03:05 PM', 'DASTAGEER & SAQLAIN', 'SHAHZAD', 'ABID', 'Day Duty: Momin, Abid'],

            // DAY 2 — 03-10-2026 Saturday (Day Duty: Afrahim, Hasnain, Sarfraz)
            [6, '419', '420', '2026-10-03 07:30:00', '07:15 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Afrahim, Hasnain, Sarfraz'],
            [7, '213', '426', '2026-10-03 09:10:00', '08:55 AM', 'ADIL & ARSHAD', 'NOMAN', 'AFRAHEEM', 'Day Duty: Afrahim, Hasnain, Sarfraz'],
            [8, '353', '356', '2026-10-03 10:50:00', '10:35 AM', 'BABAR & JAMSHAID', 'WAQAS', 'AFRAHEEM', 'Day Duty: Afrahim, Hasnain, Sarfraz'],
            [9, '421', '786GM', '2026-10-03 12:30:00', '12:15 PM', 'AWAIS & RAMZAN', 'AFZAAL', 'AFRAHEEM', 'Day Duty: Afrahim, Hasnain, Sarfraz'],
            [10, '418', '426', '2026-10-03 14:10:00', '01:55 PM', 'ASGHAR & SHOBAN', 'RAZZAQ', 'HASNAIN', 'Day Duty: Afrahim, Hasnain, Sarfraz'],
            [11, '183', '421', '2026-10-03 15:50:00', '03:35 PM', 'WASEEM & JAMSHAID', 'HAIDER', 'SARFRAZ', 'Day Duty: Afrahim, Hasnain, Sarfraz'],

            // DAY 3 — 04-10-2026 Sunday (Day Duty: Awais, Iqbal, Sajjad)
            [12, '348S', '449', '2026-10-04 07:30:00', '07:15 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Awais, Iqbal, Sajjad'],
            [13, '355', '786GM', '2026-10-04 09:10:00', '08:55 AM', 'DASTAGEER & YASEEN', 'SHAHZAD', 'AWAIS', 'Day Duty: Awais, Iqbal, Sajjad'],
            [14, '213', '418', '2026-10-04 10:50:00', '10:35 AM', 'NAVEED & SHOBAN', 'RAZZAQ', 'IQBAL', 'Day Duty: Awais, Iqbal, Sajjad'],
            [15, '355', '421', '2026-10-04 12:30:00', '12:15 PM', 'BABAR & WASEEM', 'HAIDER', 'AWAIS', 'Day Duty: Awais, Iqbal, Sajjad'],
            [16, '418', '419', '2026-10-04 14:10:00', '01:55 PM', 'NAVEED & ASGHAR', 'NAJAM', 'SAJJAD', 'Day Duty: Awais, Iqbal, Sajjad'],
            [17, '348A', '421', '2026-10-04 15:50:00', '03:35 PM', 'WASEEM & ADIL', 'TALHA', 'SAJJAD', 'Day Duty: Awais, Iqbal, Sajjad'],

            // DAY 4 — 05-10-2026 Monday (Day Duty: Arshad, Abid)
            [18, '213', '420', '2026-10-05 08:00:00', '07:45 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Arshad, Abid'],
            [19, '214G', '353', '2026-10-05 09:50:00', '09:35 AM', 'BABAR & AFRAHIM', 'NOMAN', 'ARSHAD', 'Day Duty: Arshad, Abid'],
            [20, '305', '449', '2026-10-05 11:40:00', '11:25 AM', 'AZHAR & AWAIS', 'FAISAL', 'ARSHAD', 'Day Duty: Arshad, Abid'],
            [21, '354', '356', '2026-10-05 13:30:00', '01:15 PM', 'ABUBAKAR & YASEEN', 'AWAIS', 'ARSHAD', 'Day Duty: Arshad, Abid'],
            [22, '183', '786GM', '2026-10-05 15:20:00', '03:05 PM', 'ASAD & RAMZAN', 'AMAN', 'ABID', 'Day Duty: Arshad, Abid'],

            // DAY 5 — 06-10-2026 Tuesday (Day Duty: Farooq, Awais)
            [23, '348A', '786GM', '2026-10-06 08:00:00', '07:45 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Farooq, Awais'],
            [24, '213', '419', '2026-10-06 09:50:00', '09:35 AM', 'TALHA & SHOBAN', 'SHAHID', 'FAROOQ', 'Day Duty: Farooq, Awais'],
            [25, '417', '449', '2026-10-06 11:40:00', '11:25 AM', 'BABAR & ADIL', 'TALHA', 'FAROOQ', 'Day Duty: Farooq, Awais'],
            [26, '419', '426', '2026-10-06 13:30:00', '01:15 PM', 'SAQLAIN & YASEEN', 'NAVEED', 'AWAIS', 'Day Duty: Farooq, Awais'],
            [27, '183', '348A', '2026-10-06 15:20:00', '03:05 PM', 'ADIL & JAMSHAID', 'UMERBILLA', 'AWAIS', 'Day Duty: Farooq, Awais'],

            // DAY 6 — 07-10-2026 Wednesday (Day Duty: Waqas, Iqbal, Farooq)
            [28, '214G', '354', '2026-10-07 08:00:00', '07:45 AM', 'NABIA & MUJAHID', 'QAZAFI', 'RAFAQAT', 'Day Duty: Waqas, Iqbal, Farooq'],
            [29, '305', '417', '2026-10-07 09:50:00', '09:35 AM', 'AZHAR & ASAD', 'FAISAL', 'WAQAS', 'Day Duty: Waqas, Iqbal, Farooq'],
            [30, '348A', '355', '2026-10-07 11:40:00', '11:25 AM', 'ABUBAKAR & SAQLAIN', 'NAVEED', 'WAQAS', 'Day Duty: Waqas, Iqbal, Farooq'],
            [31, '305', '348S', '2026-10-07 13:30:00', '01:15 PM', 'TAHLA & NAVEED', 'SAAD', 'IQBAL', 'Day Duty: Waqas, Iqbal, Farooq'],
            [32, '183', '355', '2026-10-07 15:20:00', '03:05 PM', 'ABUBAKAR & DASTAGEER', 'SHAHZAD', 'FAROOQ', 'Day Duty: Waqas, Iqbal, Farooq'],
        ];

        foreach ($groupMatches as $m) {
            [$num, $home, $away, $datetime, $tossTime, $umpires, $scorer, $referee, $duty] = $m;
            $notes = "Toss: {$tossTime} | Umpires: {$umpires} | Scorer: {$scorer} | Referee: {$referee} | {$duty}";

            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $team($home),
                'away_team_id'   => $team($away),
                'match_number'   => (string) $num,
                'match_type'     => 'group',
                'venue'          => 'Chak No 183',
                'scheduled_at'   => Carbon::parse($datetime),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => $notes,
            ]);
        }

        // 5. Quarter Finals — DAY 7: 08-10-2026 Thursday (Day Duty: All Cabinet)
        $qfs = [
            [33, 'A1', 'D2', '2026-10-08 09:00:00', '08:45 AM', 'Quarter Final 1: Pool A Winner (A1) vs Pool D Runner-up (D2)'],
            [34, 'A2', 'D1', '2026-10-08 11:00:00', '10:45 AM', 'Quarter Final 2: Pool A Runner-up (A2) vs Pool D Winner (D1)'],
            [35, 'B1', 'C2', '2026-10-08 13:00:00', '12:45 PM', 'Quarter Final 3: Pool B Winner (B1) vs Pool C Runner-up (C2)'],
            [36, 'B2', 'C1', '2026-10-08 15:00:00', '02:45 PM', 'Quarter Final 4: Pool B Runner-up (B2) vs Pool C Winner (C1)'],
        ];

        foreach ($qfs as [$num, $hKo, $aKo, $datetime, $tossTime, $desc]) {
            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $koMap[$hKo],
                'away_team_id'   => $koMap[$aKo],
                'match_number'   => (string) $num,
                'match_type'     => 'quarter_final',
                'venue'          => 'Chak No 183',
                'scheduled_at'   => Carbon::parse($datetime),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => "Toss: {$tossTime} | {$desc} | Day Duty: All Cabinet",
            ]);
        }

        // 6. Semifinals & Final — DAY 8: 09-10-2026 Friday (Day Duty: All Cabinet)
        $semis = [
            [37, 'W_QF1', 'W_QF3', '2026-10-09 09:00:00', '08:45 AM', '1st Semifinal: Winner (A1 vs D2) vs Winner (B1 vs C2)'],
            [38, 'W_QF2', 'W_QF4', '2026-10-09 11:00:00', '10:45 AM', '2nd Semifinal: Winner (A2 vs D1) vs Winner (B2 vs C1)'],
        ];

        foreach ($semis as [$num, $hKo, $aKo, $datetime, $tossTime, $desc]) {
            CricketMatch::create([
                'edition_id'     => $edition->id,
                'home_team_id'   => $koMap[$hKo],
                'away_team_id'   => $koMap[$aKo],
                'match_number'   => (string) $num,
                'match_type'     => 'semi_final',
                'venue'          => 'Chak No 183',
                'scheduled_at'   => Carbon::parse($datetime),
                'status'         => 'upcoming',
                'overs_per_side' => 10,
                'notes'          => "Toss: {$tossTime} | {$desc} | Day Duty: All Cabinet",
            ]);
        }

        // FINAL
        CricketMatch::create([
            'edition_id'     => $edition->id,
            'home_team_id'   => $koMap['W_SF1'],
            'away_team_id'   => $koMap['W_SF2'],
            'match_number'   => '39',
            'match_type'     => 'final',
            'venue'          => 'Chak No 183',
            'scheduled_at'   => Carbon::parse('2026-10-09 15:00:00'),
            'status'         => 'upcoming',
            'overs_per_side' => 10,
            'notes'          => 'FINAL: Winner 1st Semifinal vs Winner 2nd Semifinal | Toss: 02:45 PM | Day Duty: All Cabinet',
        ]);
    }
}
