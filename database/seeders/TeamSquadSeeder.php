<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSquadSeeder extends Seeder
{
    public function run(): void
    {
        $edition37 = Edition::where('edition_number', 37)->first()
            ?? Edition::where('is_current', true)->firstOrFail();

        $now = now();

        $teamsData = [
            // ── Team 1: 418 Sarja Sixer ──────────────────────────────
            '418' => [
                'team_name'    => 'Sarja Sixer',
                'village_name' => 'Village 418',
                'description'  => 'Sarja Sixer Cricket Club — Village 418',
                'players'      => [
                    [
                        'name'            => 'Sarfraz Goraya',
                        'father_name'     => 'Naaem Ullah',
                        'is_captain'      => true,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '7',
                    ],
                    [
                        'name'            => 'Naseer Rajput',
                        'father_name'     => 'Bashir Ahmad',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '10',
                    ],
                    [
                        'name'            => 'Waseem Goraya',
                        'father_name'     => 'Altaf Goraya',
                        'is_captain'      => false,
                        'is_vice_captain' => true,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '18',
                    ],
                    [
                        'name'            => 'Kaleem Goraya',
                        'father_name'     => 'Altaf Goraya',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '12',
                    ],
                    [
                        'name'            => 'Ali Goraya',
                        'father_name'     => 'Abdul Razaq',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'bowler',
                        'jersey_number'   => '9',
                    ],
                    [
                        'name'            => 'Sikandar Goraya',
                        'father_name'     => 'Akhtar Goraya',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '11',
                    ],
                    [
                        'name'            => 'Shareef Rajput',
                        'father_name'     => 'Nazar Hussain',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '14',
                    ],
                    [
                        'name'            => 'Usman Qazi',
                        'father_name'     => 'Zafar',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '8',
                    ],
                    [
                        'name'            => 'Umar Goraya',
                        'father_name'     => 'Akhtar Goraya',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'bowler',
                        'jersey_number'   => '99',
                    ],
                    [
                        'name'            => 'Saleem Rajput',
                        'father_name'     => 'Muneer Rajput',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '23',
                    ],
                    [
                        'name'            => 'Haider Goraya',
                        'father_name'     => 'Zafar Iqbal',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '17',
                    ],
                    [
                        'name'            => 'Qasim Rajput',
                        'father_name'     => 'Akbar',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '21',
                    ],
                    [
                        'name'            => 'M Farooq',
                        'father_name'     => 'Tariq',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '77',
                    ],
                    [
                        'name'            => 'Bilal Asif',
                        'father_name'     => 'M Asif',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'bowler',
                        'jersey_number'   => '33',
                    ],
                    [
                        'name'            => 'Ikram Rajput',
                        'father_name'     => 'Mansha',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'batsman',
                        'jersey_number'   => '45',
                    ],
                    [
                        'name'            => 'Talha Goraya',
                        'father_name'     => 'Basharat Goraya',
                        'is_captain'      => false,
                        'is_vice_captain' => false,
                        'role'            => 'all_rounder',
                        'jersey_number'   => '19',
                    ],
                ],
            ],

            // ── Team 2: 353 - Friends Cricket Club ──────────────────
            '353' => [
                'team_name'    => 'Friends Cricket Club',
                'village_name' => 'Village 353',
                'description'  => 'Official Umpires: Awais, Faisal | Scorers: Faisal, Noman',
                'players'      => [
                    // Column 1
                    ['name' => 'Akhtar Abbas',    'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Zeeshan Manzoor', 'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Awais Lefty',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'batting_style' => 'left_hand', 'bowling_style' => 'left_arm_medium', 'jersey_number' => '3'],
                    ['name' => 'Noman',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '4'],
                    ['name' => 'Waqas J',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Waqas',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Faisal Rajpoot',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '7'],
                    ['name' => 'Faisal',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '8'],
                    ['name' => 'Umair',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '9'],

                    // Column 2
                    ['name' => 'Ali Abbas',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '10'],
                    ['name' => 'Rehman',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '11'],
                    ['name' => 'Mujahid Manzoor', 'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '12'],
                    ['name' => 'Salman Bajwa',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '13'],
                    ['name' => 'Faizan',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '14'],
                    ['name' => 'Sufyan',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '15'],
                    ['name' => 'Zeeshan',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Ahmad',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Sohail',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '18'],

                    // Column 3
                    ['name' => 'Zain Wairrach',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '19'],
                    ['name' => 'Zahid Manzoor',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Shahid Manzoor',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '21'],
                    ['name' => 'Abdullah',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '22'],
                    ['name' => 'Ali Raza',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '23'],
                    ['name' => 'Ahsan',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Noman',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '25'],
                ],
            ],

            // ── Team 3: 449 -> Shaheen Cricket Club ──────────────────
            '449' => [
                'team_name'    => 'Shaheen Cricket Club',
                'village_name' => 'Village 449',
                'description'  => 'Shaheen Cricket Club — Village 449',
                'players'      => [
                    ['name' => 'Yaseen',       'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Muzammil',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Kashif',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Hafiz Abid',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '4'],
                    ['name' => 'Rauf',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '5'],
                    ['name' => 'Sajjad',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Naeem',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '7'],
                    ['name' => 'Hafeez',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Zubair',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Arbab',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '10'],
                    ['name' => 'Asad',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '11'],
                    ['name' => 'Asif',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '12'],
                    ['name' => 'Mazhar',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '13'],
                    ['name' => 'Shan',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '14'],
                    ['name' => 'Awais Sunny',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Nadeem',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '16'],
                    ['name' => 'Mazhar S',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '17'],
                    ['name' => 'Kafeel',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '18'],
                    ['name' => 'Kashif',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '19'],
                    ['name' => 'Waseem',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Niaz Ali',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Ansar',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Munawar',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '23'],
                    ['name' => 'Malik Saleem', 'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '24'],
                    ['name' => 'Khurram',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '25'],
                ],
            ],

            // ── Team 4: 354 -> Qadirabad Cricket Club ────────────────
            '354' => [
                'team_name'    => 'Qadirabad Cricket Club',
                'village_name' => 'Village 354',
                'description'  => 'Qadirabad Cricket Club — Village 354',
                'players'      => [
                    ['name' => 'Ali Zaman',         'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Momin Ali',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Umar Butt',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Asad',              'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Usama',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Tayyab Gill',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Yusaf',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '7'],
                    ['name' => 'Ausaf',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Aman Gill',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Abdulrehman Jutt',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '10'],
                    ['name' => 'Waqas Sahotra',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '11'],
                    ['name' => 'Dilshad Mithu',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Haroon Shafique',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Sharoon Shafique',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Awais Mughal',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Saqib',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Zeeshan',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Sajid Bhati',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '18'],
                    ['name' => 'Abubakar Bhalo',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Salman',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '20'],
                    ['name' => 'Bakar Mg',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '21'],
                    ['name' => 'Abdullah',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '22'],
                    ['name' => 'Abdullah Mg',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '23'],
                    ['name' => 'Sufiyan Lefty',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'batting_style' => 'left_hand', 'bowling_style' => 'left_arm_medium', 'jersey_number' => '24'],
                    ['name' => 'Jawad Butt',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '25'],
                ],
            ],

            // ── Team 5: 356 -> KCC 356 JB ────────────────────────────
            '356' => [
                'team_name'    => 'KCC 356 JB',
                'village_name' => 'Village 356 JB',
                'description'  => 'KCC 356 JB Cricket Club — Village 356 JB',
                'players'      => [
                    ['name' => 'H. Tariq Gujjar',   'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Afzaal Goraya',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Abid Gill',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'Asghar Raju',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Ateeq Grewal',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Ramzan Goraya',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Majid Gujjar',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Ahtisham Mirza',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '8'],
                    ['name' => 'Adnan Mirza',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Zain Warraich',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '10'],
                    ['name' => 'Mian Bilal',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '11'],
                    ['name' => 'M. Umair Gill',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '12'],
                    ['name' => 'M. Atif Gill',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '13'],
                    ['name' => 'M. Faraz Gill',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '14'],
                    ['name' => 'Asad Gill',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Rana Muneeb',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '16'],
                    ['name' => 'M. Mahtab',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '17'],
                    ['name' => 'Ali Javed',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '18'],
                    ['name' => 'Ali Azeem',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '19'],
                    ['name' => 'Abdullah Mirza',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '20'],
                    ['name' => 'Abdullah Wahla',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '21'],
                    ['name' => 'M. Bilal Bhatti',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '22'],
                    ['name' => 'M. Asif',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '23'],
                    ['name' => 'Arham Gujjar',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '24'],
                    ['name' => 'Usman Malanga',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '25'],
                ],
            ],

            // ── Team 6: 419 -> Goraya Cricket Club ───────────────────
            '419' => [
                'team_name'    => 'Goraya Cricket Club',
                'village_name' => 'Village 419',
                'description'  => 'Goraya Cricket Club — Village 419 | Umpires: Adil, Touqeer, Zaman | Scorers: Atif, Talha, Subhan',
                'players'      => [
                    ['name' => 'Dr Adil',          'father_name' => 'Ghulam Murtaza',   'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Touqeer Goraya',   'father_name' => 'Arshad Ali',       'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Atif Goraya',      'father_name' => 'Ghulam Murtaza',   'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'Subhan Ali',       'father_name' => 'M Nadeem',        'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Talha Goraya',     'father_name' => 'M Nadeem',        'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Gufran Goraya',    'father_name' => 'Asghar Ali',       'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Zaman Ghuman',     'father_name' => 'Abdul Kareem',     'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '7'],
                    ['name' => 'Mirza Bilal',      'father_name' => 'Babar',            'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Zeeshan Raja',     'father_name' => 'Raja Salamat Ali', 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Muzammal Goraya',  'father_name' => 'Anwar Ali',        'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '10'],
                    ['name' => 'Mirza Ismaeel',    'father_name' => 'Mirza Iqbal',      'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '11'],
                    ['name' => 'Shoaib Iqbal',     'father_name' => 'Iqbal Shaheen',    'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '12'],
                    ['name' => 'Malik Saleem',     'father_name' => 'Latif',            'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '13'],
                    ['name' => 'Awais Goraya',     'father_name' => 'Munawar Hussain',  'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Asim Goraya',      'father_name' => 'Munawar Hussain',  'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '15'],
                    ['name' => 'Umair Khalid',     'father_name' => 'Khalid Saeed',     'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '16'],
                    ['name' => 'Rehman',           'father_name' => 'Imran Raja',       'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Mirza Sajid',      'father_name' => 'Mirza Nawaz',      'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '18'],
                    ['name' => 'Mirza Ubaidullah', 'father_name' => 'Mirza Nawaz',      'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '19'],
                    ['name' => 'Mirza Azeem',      'father_name' => 'Mirza Iqbal',      'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '20'],
                ],
            ],

            // ── Team 7: 426 -> Al Sadiq Cricket Club ─────────────────
            '426' => [
                'team_name'    => 'Al Sadiq Cricket Club',
                'village_name' => 'Village 426',
                'description'  => 'Al Sadiq Cricket Club — Village 426',
                'players'      => [
                    ['name' => 'H. Abdul Jabbar', 'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Jamshed Bombom',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Umar Billa',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'Kamran Rajpoot',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Iftikhar Rajput', 'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Nadeem King',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Junaid Rajput',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Waqas',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '8'],
                    ['name' => 'Shamsher',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Usman Rajpoot',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Haider Boss',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '11'],
                    ['name' => 'Mr. Abrar',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Aslam King',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Khalid Rajpoot',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Asghar Ballu',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Yousaf Rajput',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '16'],
                    ['name' => 'Abbas Rajpoot',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '17'],
                    ['name' => 'Usman Junior',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Kaka',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '19'],
                    ['name' => 'Arif Rajpoot',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '20'],
                    ['name' => 'Shafiq Munna',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '21'],
                    ['name' => 'Saleem Rajpoot',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '22'],
                    ['name' => 'Waqar Rajpoot',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '23'],
                    ['name' => 'Basit Rajpoot',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '24'],
                    ['name' => 'Noman Rajpoot',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '25'],
                ],
            ],

            // ── Team 8: 421 -> Al Haider Cricket Club ────────────────
            '421' => [
                'team_name'    => 'Al Haider Cricket Club',
                'village_name' => 'Village 421',
                'description'  => 'Al Haider Cricket Club — Village 421',
                'players'      => [
                    ['name' => 'Asghar Ali',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Najam Banto',    'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Husnain Khan',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'Touseef',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Ali Khan',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Amjad Pathan',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Nouman',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Usman Haider',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '8'],
                    ['name' => 'Abbas Khan',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Atif Bhuta',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Zeeshan Shani',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '11'],
                    ['name' => 'Babar',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Hammad',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Tajmal',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '14'],
                    ['name' => 'Mohsin',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '15'],
                    ['name' => 'Riaz',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Shahid Imran',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '17'],
                    ['name' => 'Amir',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Adeel',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Farman',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Fizan',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Mureed Sultan',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Ali Raza',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Ali Shahbaz',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Asif Ladi',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 9: 355 -> United Cricket Club ───────────────────
            '355' => [
                'team_name'    => 'United Cricket Club',
                'village_name' => 'Village 355',
                'description'  => 'United Cricket Club — Village 355',
                'players'      => [
                    ['name' => 'H. Naveed',    'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Sajjad',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Iqbal',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'A. Hameed',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Iftikhar Q.',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Khalid',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Abid',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Mubashar',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '8'],
                    ['name' => 'Murtaza',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Ali',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Saad',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '11'],
                    ['name' => 'Mujeeb',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Farhan',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Shehzad',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '14'],
                    ['name' => 'Bilal',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '15'],
                    ['name' => 'Muzammal S.',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Hassan Ali',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '17'],
                    ['name' => 'Umar',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Ali Hassan',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Faisal',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Umair Jutt',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Usman',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Fakhar',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Rafay Jutt',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Mujtaba',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 10: 420 -> Syed Jalal Cricket Club ──────────────
            '420' => [
                'team_name'    => 'Syed Jalal Cricket Club',
                'village_name' => 'Village 420',
                'description'  => 'Syed Jalal Cricket Club — Village 420',
                'players'      => [
                    ['name' => 'Afrahim',        'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Arshid',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Nouman',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Usman',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Arfan',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Aun Lefty',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'batting_style' => 'left_hand', 'bowling_style' => 'left_arm_medium', 'jersey_number' => '6'],
                    ['name' => 'Sagir',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Hussnain',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '8'],
                    ['name' => 'Saleem',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Tanveer Sial',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Arif',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '11'],
                    ['name' => 'Umer',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '12'],
                    ['name' => 'Rehman',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Boota',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Tanveer Rajput', 'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Kashif',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Zeeshan J',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Zeeshan S',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '18'],
                    ['name' => 'Qasim',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Ch Arslan',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '20'],
                    ['name' => 'Tahir',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '21'],
                    ['name' => 'Shahbaz',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Kamran',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '23'],
                    ['name' => 'Amanat Sial',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '24'],
                    ['name' => 'Faizan',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 11: 348A -> Azad Cricket Club ───────────────────
            '348A' => [
                'team_name'    => 'Azad Cricket Club',
                'village_name' => 'Village 348A',
                'description'  => 'Azad Cricket Club — Village 348A',
                'players'      => [
                    ['name' => 'Farooq Numberdar', 'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Jawad Ahmad Zia',  'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Talha DJ',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Zahid DJ',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Bilal Malang',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Ahmad Ali',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Asim Shah',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '7'],
                    ['name' => 'Shan Ali Chak',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Shan Naseer',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Tahir Shah',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '10'],
                    ['name' => 'Akmal',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '11'],
                    ['name' => 'Bazail',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Shabbir Ali',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '13'],
                    ['name' => 'Irfan Rath',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Faizan',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '15'],
                    ['name' => 'Shahid Ali',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '16'],
                    ['name' => 'Mohsin Ali',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Saqlain',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '18'],
                    ['name' => 'Usama Munna',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '19'],
                    ['name' => 'Ahad Ali',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '20'],
                    ['name' => 'Akhtar Shah',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '21'],
                    ['name' => 'Abdullah',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '22'],
                    ['name' => 'Abdullah KC',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Usama Kaka',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '24'],
                    ['name' => 'Shamaz',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '25'],
                ],
            ],

            // ── Team 12: 786GM -> 786 GM Cricket Club ────────────────
            '786GM' => [
                'team_name'    => '786 GM Cricket Club',
                'village_name' => 'Village 786GM',
                'description'  => '786 GM Cricket Club — Village 786GM',
                'players'      => [
                    ['name' => 'Muhammad Shohban', 'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Irfan Qadri',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Malik Wajid',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Shabir Chadhar',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Sharyar Khan',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Asad Jugg',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Ijaz Ahmad',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '7'],
                    ['name' => 'Nosher Khan',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Razaq Khan',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '9'],
                    ['name' => 'Sherzaman Khan',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '10'],
                    ['name' => 'Rehman Khan',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '11'],
                    ['name' => 'Nasir Khan',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Noor Khan',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Shabir Manj',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '14'],
                    ['name' => 'Ali Raza',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '15'],
                    ['name' => 'Sanaullah',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Malik Azhar',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '17'],
                    ['name' => 'Malik Shahzaib',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Wajeh Ali',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Zainul Abedin',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Usman Daoud',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Mahr Saad',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Babu Khan',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Shahzad Khan',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Bodi Khan',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 13: 305 -> Ghazi Cricket Club ───────────────────
            '305' => [
                'team_name'    => 'Ghazi Cricket Club',
                'village_name' => 'Village 305',
                'description'  => 'Ghazi Cricket Club — Village 305',
                'players'      => [
                    ['name' => 'Zeeshan Sipra',          'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Sajjad Toor',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Abubakar Warraich',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Zaighum Cheema',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Hafiz Sadaqat',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Wajid Ali',              'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Shahzaib Toor',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Ali Hassan Cheema',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '8'],
                    ['name' => 'Asim Cheema',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Aftab Sipra',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Asad Sipra',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '11'],
                    ['name' => 'Asad Cheema',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '12'],
                    ['name' => 'Kamran Cheema',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '13'],
                    ['name' => 'Abdul Rehman Cheema',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Ali Hassan Cheema 2',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '15'],
                    ['name' => 'Umair Cheema',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '16'],
                    ['name' => 'Usman Butt',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '17'],
                    ['name' => 'Tanzeb Haider',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '18'],
                    ['name' => 'Hassan Bagri',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Mian Bilal',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '20'],
                    ['name' => 'Jamshaid Sipra',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '21'],
                    ['name' => 'Nadeem Andy',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Talal Butt',             'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '23'],
                    ['name' => 'Nafy Toor',              'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '24'],
                    ['name' => 'Ali Abdullah',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 14: 183 -> Sholah Cricket Club ──────────────────
            '183' => [
                'team_name'    => 'Sholah Cricket Club',
                'village_name' => 'Village 183',
                'description'  => 'Sholah Cricket Club — Village 183',
                'players'      => [
                    ['name' => 'M. Khan Azad',    'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Mujahid Khan',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '2'],
                    ['name' => 'Naeem Khan',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '3'],
                    ['name' => 'Qaisar',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Sajid Nabia',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '5'],
                    ['name' => 'Qazafi',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '6'],
                    ['name' => 'Haji Qurban',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '7'],
                    ['name' => 'Adnan',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '8'],
                    ['name' => 'Aqib',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Salman',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '10'],
                    ['name' => 'Asghar',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '11'],
                    ['name' => 'Rafaqat Khan',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '12'],
                    ['name' => 'Qaisar Junior',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '13'],
                    ['name' => 'Salman Sallu',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Hasnain Gora',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '15'],
                    ['name' => 'Numan',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '16'],
                    ['name' => 'Saqlain',         'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '17'],
                    ['name' => 'Safdar',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Butta',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Danish',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Affan',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Saqib',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Akash',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Zain',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Mateen',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],

            // ── Team 15: 348S -> Maqbulpur Strikers ──────────────────
            '348S' => [
                'team_name'    => 'Maqbulpur Strikers',
                'village_name' => 'Village 348S',
                'description'  => 'Maqbulpur Strikers — Village 348S',
                'players'      => [
                    ['name' => 'Shahzad Kohli',   'father_name' => null, 'is_captain' => true,  'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '1'],
                    ['name' => 'Dastgeer Shah',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => true,  'role' => 'all_rounder', 'jersey_number' => '2'],
                    ['name' => 'Sajid Jutt',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '3'],
                    ['name' => 'Hamza Malik',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '4'],
                    ['name' => 'Akhtar Shah',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '5'],
                    ['name' => 'Tahir Shah',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '6'],
                    ['name' => 'Asim Shah',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '7'],
                    ['name' => 'Shoaib Akhtar',   'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '8'],
                    ['name' => 'Jabar',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '9'],
                    ['name' => 'Ahmad',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '10'],
                    ['name' => 'Abdul Rahman',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '11'],
                    ['name' => 'Haider',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '12'],
                    ['name' => 'Shamaz',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '13'],
                    ['name' => 'Usman Ghani',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '14'],
                    ['name' => 'Hussnain',        'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '15'],
                    ['name' => 'Hamid Ali',       'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '16'],
                    ['name' => 'Saqlain Shah',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '17'],
                    ['name' => 'Asghar',          'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '18'],
                    ['name' => 'Asad',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '19'],
                    ['name' => 'Umer',            'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '20'],
                    ['name' => 'Ali Haider',      'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '21'],
                    ['name' => 'Umair',           'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '22'],
                    ['name' => 'Zeeshan Ali',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'all_rounder', 'jersey_number' => '23'],
                    ['name' => 'Shan Naseer',     'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'batsman',     'jersey_number' => '24'],
                    ['name' => 'Muneeb Mehar',    'father_name' => null, 'is_captain' => false, 'is_vice_captain' => false, 'role' => 'bowler',      'jersey_number' => '25'],
                ],
            ],
        ];

        foreach ($teamsData as $code => $data) {
            $team = Team::where('short_code', $code)->first();
            if ($team) {
                $team->update([
                    'name'         => $data['team_name'],
                    'village_name' => $data['village_name'],
                    'description'  => $data['description'] ?? $team->description,
                ]);
            } else {
                $team = Team::create([
                    'name'            => $data['team_name'],
                    'village_name'    => $data['village_name'],
                    'description'     => $data['description'] ?? null,
                    'short_code'      => $code,
                    'is_active'       => true,
                    'primary_color'   => '#044728',
                    'secondary_color' => '#d4af37',
                ]);
            }

            $this->command?->info("Seeding squad for Team {$team->name} ({$code})...");

            // Track added player IDs for this team's roster in edition 37 to avoid duplicates
            $addedPlayerIds = [];

            foreach ($data['players'] as $pData) {
                // 1. Check if this player is ALREADY on THIS team's roster for this edition
                $existingTeamPlayerId = DB::table('player_team_editions')
                    ->where('team_id', $team->id)
                    ->where('edition_id', $edition37->id)
                    ->whereIn('player_id', function ($q) use ($pData) {
                        $q->select('id')->from('players')->where('name', $pData['name']);
                        if (!empty($pData['father_name'])) {
                            $q->where('father_name', $pData['father_name']);
                        }
                    })
                    ->whereNotIn('player_id', $addedPlayerIds)
                    ->value('player_id');

                if ($existingTeamPlayerId) {
                    $player = Player::find($existingTeamPlayerId);
                } else {
                    // 2. Check for an unassigned player with this name not in ANY team for edition 37
                    $unassignedPlayer = Player::where('name', $pData['name'])
                        ->when(!empty($pData['father_name']), fn($q) => $q->where('father_name', $pData['father_name']))
                        ->whereDoesntHave('rosterEntries', fn($q) => $q->where('edition_id', $edition37->id))
                        ->first();

                    if ($unassignedPlayer && !in_array($unassignedPlayer->id, $addedPlayerIds, true)) {
                        $player = $unassignedPlayer;
                    } else {
                        // 3. Create a new player record for this team
                        $player = new Player();
                        $player->name        = $pData['name'];
                        $player->father_name = $pData['father_name'] ?? null;
                    }
                }

                $player->role                  = $pData['role'] ?? 'all_rounder';
                $player->jersey_number         = $pData['jersey_number'] ?? null;
                $player->batting_style         = $pData['batting_style'] ?? 'right_hand';
                $player->bowling_style         = $pData['bowling_style'] ?? (($player->role === 'bowler') ? 'right_arm_fast' : 'right_arm_medium');
                $player->bowling_action_status = 'legal';
                $player->is_active             = true;
                $player->save();

                $addedPlayerIds[] = $player->id;

                // Link player to team and edition
                DB::table('player_team_editions')->updateOrInsert(
                    [
                        'player_id'  => $player->id,
                        'edition_id' => $edition37->id,
                    ],
                    [
                        'team_id'         => $team->id,
                        'is_captain'      => (bool) ($pData['is_captain'] ?? false),
                        'is_vice_captain' => (bool) ($pData['is_vice_captain'] ?? false),
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]
                );
            }

            $this->command?->info("  -> Added " . count($addedPlayerIds) . " players to {$team->name}.");
        }
    }
}
