<?php

namespace Database\Seeders;

use App\Models\Edition;
use Illuminate\Database\Seeder;

class EditionSeeder extends Seeder
{
    public function run(): void
    {
        Edition::firstOrCreate(
            ['edition_number' => 35],
            [
                'name'         => '35th Edition',
                'host_village' => 'Village Host (TBD)',
                'status'       => 'upcoming',
                'is_current'   => true,
                'description'  => 'The 35th Edition of the Royal Champions League, organized by the Village Cricket Council (VCC). This marks a fresh start with digital record-keeping from Edition 35 onwards.',
            ]
        );
    }
}
