<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VccCabinetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vcc_cabinets')->delete();

        $members = [
            // ── RCL Cabinet (Leadership & Executive) ────────────────
            [
                'display_order' => 1,
                'name'          => 'Muhammad Tanveer Rajpoot',
                'role_title'    => 'Founder',
                'village'       => null,
                'photo'         => 'cabinet/m_tanveer_rajpoot.jpg',
            ],
            [
                'display_order' => 2,
                'name'          => 'Naseer Ahmad Rajpoot',
                'role_title'    => 'Patron-in-Chief',
                'village'       => null,
                'photo'         => 'cabinet/naseer_ahmad_rajpoot.jpg',
            ],
            [
                'display_order' => 3,
                'name'          => 'Habib Akhtar Rajpoot',
                'role_title'    => 'Chief Executive Officer (CEO)',
                'village'       => null,
                'photo'         => 'cabinet/habib_akhtar_rajpoot.jpg',
            ],
            [
                'display_order' => 4,
                'name'          => 'Muhammad Iqbal Gajja',
                'role_title'    => 'President',
                'village'       => null,
                'photo'         => 'cabinet/m_iqbal_gajja.jpg',
            ],
            [
                'display_order' => 5,
                'name'          => 'Muhammad Farooq Numberdar',
                'role_title'    => 'Vice President',
                'village'       => null,
                'photo'         => 'cabinet/m_farooq_numberdar.jpg',
            ],
            [
                'display_order' => 6,
                'name'          => 'Husnain Khan Sial',
                'role_title'    => 'Chairman',
                'village'       => null,
                'photo'         => 'cabinet/husnain_khan_sial.jpg',
            ],
            [
                'display_order' => 7,
                'name'          => 'Chaudhry Zain Warraich',
                'role_title'    => 'Vice Chairman',
                'village'       => null,
                'photo'         => 'cabinet/ch_zain_warraich.jpg',
            ],
            [
                'display_order' => 8,
                'name'          => 'Chaudhry Sarfraz Goraya',
                'role_title'    => 'Secretary General',
                'village'       => null,
                'photo'         => 'cabinet/ch_sarfraz_goraya.jpg',
            ],
            [
                'display_order' => 9,
                'name'          => 'Waqas Lail',
                'role_title'    => 'Finance Secretary',
                'village'       => null,
                'photo'         => 'cabinet/waqas_lail.jpg',
            ],
            [
                'display_order' => 10,
                'name'          => 'Abid Gill',
                'role_title'    => 'Chairman Supreme Council',
                'village'       => null,
                'photo'         => 'cabinet/abid_gill.jpg',
            ],
            [
                'display_order' => 11,
                'name'          => 'Muhammad Awais Sunny',
                'role_title'    => 'Director General',
                'village'       => null,
                'photo'         => 'cabinet/m_awais_sunny.jpg',
            ],
            [
                'display_order' => 12,
                'name'          => 'Arshad Rajpoot',
                'role_title'    => 'Supreme Body Member',
                'village'       => null,
                'photo'         => 'cabinet/arshad_rajpoot.jpg',
            ],
            [
                'display_order' => 13,
                'name'          => 'Muhammad Afrahim Sargana',
                'role_title'    => 'Supreme Body Member',
                'village'       => null,
                'photo'         => 'cabinet/afrahim_sargana.jpg',
            ],
            [
                'display_order' => 14,
                'name'          => 'Momin Warraich',
                'role_title'    => 'Supreme Body Member',
                'village'       => null,
                'photo'         => 'cabinet/momin_warraich.jpg',
            ],
            [
                'display_order' => 15,
                'name'          => 'Sajjad Gujjar',
                'role_title'    => 'Supreme Body Member',
                'village'       => null,
                'photo'         => 'cabinet/sajjad_gujjar.jpg',
            ],
            [
                'display_order' => 16,
                'name'          => 'Tanveer Sial',
                'role_title'    => 'Media Coordinator',
                'village'       => null,
                'photo'         => 'cabinet/tanveer_sial.jpg',
            ],
            [
                'display_order' => 17,
                'name'          => 'Zahid Baloch',
                'role_title'    => 'Broadcaster',
                'village'       => null,
                'photo'         => 'cabinet/zahid_baloch.jpg',
            ],
            [
                'display_order' => 18,
                'name'          => 'Akbar Akki',
                'role_title'    => 'Commentator',
                'village'       => null,
                'photo'         => 'cabinet/akbar_akki.jpg',
            ],
            [
                'display_order' => 19,
                'name'          => 'Iftikhar Thakur',
                'role_title'    => 'Cameraman',
                'village'       => null,
                'photo'         => 'cabinet/iftikhar_thakur.jpg',
            ],

            // ── Overseas Patrons & Head Sponsors ─────────────────────
            [
                'display_order' => 20,
                'name'          => 'Mian Awais Sarwar Hanif',
                'role_title'    => 'Overseas Patron & Head Sponsor',
                'village'       => 'Australia',
                'photo'         => 'cabinet/mian_awais_sarwar_hanif.jpg',
            ],
            [
                'display_order' => 21,
                'name'          => 'Master Faraz Ahmad Zia',
                'role_title'    => 'Overseas Patron & Head Sponsor',
                'village'       => 'Dubai, UAE',
                'photo'         => 'cabinet/master_faraz_ahmad_zia.jpg',
            ],
            [
                'display_order' => 22,
                'name'          => 'Ch. Khalid Manzoor Gill',
                'role_title'    => 'Overseas Patron & Head Sponsor',
                'village'       => 'Saudi Arabia (KSA)',
                'photo'         => 'cabinet/ch_khalid_manzoor_gill.jpg',
            ],
            [
                'display_order' => 23,
                'name'          => 'Waqar Shah Dil',
                'role_title'    => 'Overseas Patron & Head Sponsor',
                'village'       => 'South Africa',
                'photo'         => 'cabinet/waqar_shah_dil.jpg',
            ],
            [
                'display_order' => 24,
                'name'          => 'Muhammad Abdullah Rajpoot',
                'role_title'    => 'Patron & Head Sponsor',
                'village'       => 'Village 418',
                'photo'         => 'cabinet/m_abdullah_rajpoot.jpg',
            ],
        ];

        foreach ($members as $m) {
            DB::table('vcc_cabinets')->insert([
                'name'          => $m['name'],
                'role_title'    => $m['role_title'],
                'bio'           => null,
                'photo'         => $m['photo'] ?? null,
                'village'       => $m['village'],
                'phone'         => null,
                'display_order' => $m['display_order'],
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
