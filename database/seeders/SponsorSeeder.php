<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        Sponsor::query()->delete();

        $sponsors = [
            [
                'name'          => 'Mian Awais Sarwar Hanif',
                'logo'          => 'sponsors/mian_awais_sarwar_hanif.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Head Sponsor — Australia',
                'display_order' => 1,
                'is_active'     => true,
            ],
            [
                'name'          => 'Master Faraz Ahmad Zia',
                'logo'          => 'sponsors/master_faraz_ahmad_zia.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Head Sponsor — Dubai, UAE',
                'display_order' => 2,
                'is_active'     => true,
            ],
            [
                'name'          => 'Ch. Khalid Manzoor Gill',
                'logo'          => 'sponsors/ch_khalid_manzoor_gill.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Head Sponsor — Saudi Arabia (KSA)',
                'display_order' => 3,
                'is_active'     => true,
            ],
            [
                'name'          => 'Waqar Shah Dil',
                'logo'          => 'sponsors/waqar_shah_dil.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Head Sponsor — South Africa',
                'display_order' => 4,
                'is_active'     => true,
            ],
            [
                'name'          => 'Haji Tanveer Ahmad Rajpoot',
                'logo'          => 'sponsors/haji_tanveer_ahmad_rajpoot.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Founder & Head Sponsor — KSA',
                'display_order' => 5,
                'is_active'     => true,
            ],
            [
                'name'          => 'Habib Akhtar Rajpoot',
                'logo'          => 'sponsors/habib_akhtar_rajpoot.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Chief Executive & Head Sponsor — KSA',
                'display_order' => 6,
                'is_active'     => true,
            ],
            [
                'name'          => 'Muhammad Abdullah Rajpoot',
                'logo'          => 'sponsors/m_abdullah_rajpoot.jpg',
                'website'       => null,
                'tier'          => 'title',
                'description'   => 'Head Sponsor — Village 418',
                'display_order' => 7,
                'is_active'     => true,
            ],
        ];

        foreach ($sponsors as $s) {
            Sponsor::create($s);
        }
    }
}
