<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Village Warriors',   'village_name' => 'Village 1',  'short_code' => 'VW',  'primary_color' => '#00e676', 'secondary_color' => '#004d40'],
            ['name' => 'Royal Strikers',     'village_name' => 'Village 2',  'short_code' => 'RS',  'primary_color' => '#ffd600', 'secondary_color' => '#e65100'],
            ['name' => 'Thunder Bolts',      'village_name' => 'Village 3',  'short_code' => 'TB',  'primary_color' => '#7c4dff', 'secondary_color' => '#1a237e'],
            ['name' => 'Green Eagles',       'village_name' => 'Village 4',  'short_code' => 'GE',  'primary_color' => '#69f0ae', 'secondary_color' => '#2e7d32'],
            ['name' => 'Red Lions',          'village_name' => 'Village 5',  'short_code' => 'RL',  'primary_color' => '#ef4444', 'secondary_color' => '#7f1d1d'],
            ['name' => 'Blue Sharks',        'village_name' => 'Village 6',  'short_code' => 'BS',  'primary_color' => '#40c4ff', 'secondary_color' => '#01579b'],
            ['name' => 'Golden Hawks',       'village_name' => 'Village 7',  'short_code' => 'GH',  'primary_color' => '#ffa000', 'secondary_color' => '#e65100'],
            ['name' => 'Silver Wolves',      'village_name' => 'Village 8',  'short_code' => 'SW',  'primary_color' => '#b0bec5', 'secondary_color' => '#37474f'],
            ['name' => 'Phoenix Rising',     'village_name' => 'Village 9',  'short_code' => 'PR',  'primary_color' => '#ff7043', 'secondary_color' => '#bf360c'],
            ['name' => 'Storm Riders',       'village_name' => 'Village 10', 'short_code' => 'SR',  'primary_color' => '#80d8ff', 'secondary_color' => '#006064'],
            ['name' => 'Desert Kings',       'village_name' => 'Village 11', 'short_code' => 'DK',  'primary_color' => '#d4e157', 'secondary_color' => '#827717'],
            ['name' => 'Mountain Tigers',    'village_name' => 'Village 12', 'short_code' => 'MT',  'primary_color' => '#ff6e40', 'secondary_color' => '#bf360c'],
            ['name' => 'River Champions',    'village_name' => 'Village 13', 'short_code' => 'RC',  'primary_color' => '#00b0ff', 'secondary_color' => '#01579b'],
            ['name' => 'Forest Rangers',     'village_name' => 'Village 14', 'short_code' => 'FR',  'primary_color' => '#00e676', 'secondary_color' => '#1b5e20'],
            ['name' => 'Iron Stallions',     'village_name' => 'Village 15', 'short_code' => 'IS',  'primary_color' => '#78909c', 'secondary_color' => '#263238'],
            ['name' => 'Blazing Falcons',    'village_name' => 'Village 16', 'short_code' => 'BF',  'primary_color' => '#ff8f00', 'secondary_color' => '#e65100'],
        ];

        foreach ($teams as $data) {
            Team::firstOrCreate(['short_code' => $data['short_code']], array_merge($data, ['is_active' => true]));
        }
    }
}
