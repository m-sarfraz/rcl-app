<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Sarja Sixer',    'village_name' => 'Village 418',       'short_code' => '418',   'primary_color' => '#1e3a8a', 'secondary_color' => '#3b82f6'],
            ['name' => 'Team 417',       'village_name' => 'Village 417',       'short_code' => '417',   'primary_color' => '#831843', 'secondary_color' => '#ec4899'],
            ['name' => 'Friends Cricket Club', 'village_name' => 'Village 353', 'short_code' => '353', 'primary_color' => '#044728', 'secondary_color' => '#d4af37'],
            ['name' => 'Azad Cricket Club', 'village_name' => 'Village 348A',  'short_code' => '348A',  'primary_color' => '#701a75', 'secondary_color' => '#d946ef'],
            ['name' => 'Maqbulpur Strikers', 'village_name' => 'Village 348S',  'short_code' => '348S',  'primary_color' => '#7c2d12', 'secondary_color' => '#f97316'],
            ['name' => 'Qadirabad Cricket Club', 'village_name' => 'Village 354', 'short_code' => '354', 'primary_color' => '#0d9488', 'secondary_color' => '#14b8a6'],
            ['name' => 'United Cricket Club', 'village_name' => 'Village 355',  'short_code' => '355',   'primary_color' => '#312e81', 'secondary_color' => '#6366f1'],
            ['name' => 'KCC 356 JB',     'village_name' => 'Village 356 JB',    'short_code' => '356',   'primary_color' => '#713f12', 'secondary_color' => '#eab308'],
            ['name' => 'Goraya Cricket Club', 'village_name' => 'Village 419',  'short_code' => '419',   'primary_color' => '#064e3b', 'secondary_color' => '#10b981'],
            ['name' => 'Syed Jalal Cricket Club', 'village_name' => 'Village 420', 'short_code' => '420', 'primary_color' => '#881337', 'secondary_color' => '#f43f5e'],
            ['name' => 'Al Haider Cricket Club', 'village_name' => 'Village 421', 'short_code' => '421', 'primary_color' => '#1e1b4b', 'secondary_color' => '#4f46e5'],
            ['name' => 'Al Sadiq Cricket Club', 'village_name' => 'Village 426', 'short_code' => '426', 'primary_color' => '#365314', 'secondary_color' => '#84cc16'],
            ['name' => 'Team 213',       'village_name' => 'Village 213',       'short_code' => '213',   'primary_color' => '#0c4a6e', 'secondary_color' => '#0ea5e9'],
            ['name' => '786 GM Cricket Club', 'village_name' => 'Village 786GM', 'short_code' => '786GM', 'primary_color' => '#4c1d95', 'secondary_color' => '#8b5cf6'],
            ['name' => 'Sholah Cricket Club', 'village_name' => 'Village 183',  'short_code' => '183',   'primary_color' => '#78350f', 'secondary_color' => '#f59e0b'],
            ['name' => 'Team 214G',      'village_name' => 'Village 214G',      'short_code' => '214G',  'primary_color' => '#0f766e', 'secondary_color' => '#2dd4bf'],
            ['name' => 'Shaheen Cricket Club', 'village_name' => 'Village 449', 'short_code' => '449', 'primary_color' => '#b91c1c', 'secondary_color' => '#f97316'],
            ['name' => 'Ghazi Cricket Club', 'village_name' => 'Village 305',  'short_code' => '305',   'primary_color' => '#0f172a', 'secondary_color' => '#334155'],
        ];

        $poolMap = [
            // Pool A (Group 1)
            '183'   => 1,
            '348A'  => 1,
            '355'   => 1,
            '421'   => 1,
            '786GM' => 1,

            // Pool B (Group 2)
            '305'   => 2,
            '348S'  => 2,
            '417'   => 2,
            '449'   => 2,

            // Pool C (Group 3)
            '214G'  => 3,
            '353'   => 3,
            '354'   => 3,
            '356'   => 3,

            // Pool D (Group 4)
            '213'   => 4,
            '418'   => 4,
            '419'   => 4,
            '420'   => 4,
            '426'   => 4,
        ];

        $edition37 = Edition::where('edition_number', 37)->first() 
            ?? Edition::where('is_current', true)->first();

        foreach ($teams as $data) {
            $team = Team::updateOrCreate(
                ['short_code' => $data['short_code']],
                array_merge($data, ['is_active' => true])
            );

            if ($edition37) {
                $group = $poolMap[$data['short_code']] ?? null;
                $edition37->teams()->syncWithoutDetaching([
                    $team->id => ['group_number' => $group]
                ]);
            }
        }
    }
}

