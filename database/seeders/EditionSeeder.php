<?php

namespace Database\Seeders;

use App\Models\Edition;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EditionSeeder extends Seeder
{
    public function run(): void
    {
        // Edition 36 (Archived - No records)
        Edition::updateOrCreate(
            ['edition_number' => 36],
            [
                'name'         => '36th Edition',
                'host_village' => 'N/A',
                'status'       => 'completed',
                'is_current'   => false,
                'description'  => '36th Edition of Royal Champions League.',
            ]
        );

        // Edition 37 (Active 2026 Tournament)
        Edition::updateOrCreate(
            ['edition_number' => 37],
            [
                'name'         => '37th Edition 2026',
                'host_village' => 'Chak No 183',
                'status'       => 'upcoming',
                'is_current'   => true,
                'start_date'   => Carbon::parse('2026-10-02'),
                'end_date'     => Carbon::parse('2026-10-09'),
                'description'  => 'Royal Champions League 37th Edition 2026. Chairman: Husnain Khan Sial 421. Host: Chak No 183 Shohla Cricket Club. Venue: Chak No 183.',
            ]
        );
    }
}

