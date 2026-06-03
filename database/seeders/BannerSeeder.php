<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe broken placeholder records first
        Banner::truncate();

        $banners = [
            [
                'title'         => 'Royal Champions League',
                'subtitle'      => '35th Edition — The Premier Village Cricket Tournament',
                'image_path'    => null,
                'link_url'      => null,
                'is_active'     => true,
                'display_order' => 1,
            ],
            [
                'title'         => 'Season 35 is Live!',
                'subtitle'      => '16 Teams · 8 Villages · 1 Champion',
                'image_path'    => null,
                'link_url'      => null,
                'is_active'     => true,
                'display_order' => 2,
            ],
            [
                'title'         => 'VCC Championship',
                'subtitle'      => "Pakistan's finest village cricket — live scores & stats",
                'image_path'    => null,
                'link_url'      => null,
                'is_active'     => true,
                'display_order' => 3,
            ],
        ];

        Banner::insert($banners);

        $this->command->info('Banners seeded: ' . Banner::count());
    }
}
