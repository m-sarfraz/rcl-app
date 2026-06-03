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
            // Row 1 — Patrons / Overseas
            ['display_order' => 1,  'name' => 'Muhammad Tobra Rajput',    'role_title' => 'Founder',               'village' => null],
            ['display_order' => 2,  'name' => 'Nasir Ahmad Rajput',       'role_title' => 'Patron-in-Chief',       'village' => null],
            ['display_order' => 3,  'name' => 'Owais Sarwar Hanif',       'role_title' => 'Vice Chairman',         'village' => 'Australia'],
            ['display_order' => 4,  'name' => 'Ch. Khalid Manzoor Gul',   'role_title' => 'Patron',                'village' => 'Saudi Arabia'],
            ['display_order' => 5,  'name' => 'Muhammad Abdullah Rajput', 'role_title' => 'Patron (418)',          'village' => null],
            ['display_order' => 6,  'name' => 'Master Faraz Ahmad Zia',   'role_title' => 'Patron',                'village' => 'Dubai'],
            ['display_order' => 7,  'name' => 'Waqar Ahmad',              'role_title' => 'Patron',                'village' => 'South Africa'],

            // Row 2 — Core Leadership
            ['display_order' => 10, 'name' => 'Hassnain Khan Sial',       'role_title' => 'Chairman',              'village' => null],
            ['display_order' => 11, 'name' => 'Habib Akhtar Rajput',      'role_title' => 'Chief Executive',       'village' => null],
            ['display_order' => 12, 'name' => 'Muhammad Iqbal Guja',      'role_title' => 'President',             'village' => null],

            // Row 3 — Executive
            ['display_order' => 20, 'name' => 'Ch. Nain Watanch',         'role_title' => 'Vice Chairman',         'village' => null],
            ['display_order' => 21, 'name' => 'Ch. Sarfaraz Grana',       'role_title' => 'General Secretary',     'village' => null],
            ['display_order' => 22, 'name' => 'Waqas Younas Lail',        'role_title' => 'Finance Secretary',     'village' => null],
            ['display_order' => 23, 'name' => 'Abid Gul',                 'role_title' => 'Chairman PEM Fund',     'village' => null],
            ['display_order' => 24, 'name' => 'Muhammad Ramzan',          'role_title' => 'Director General',      'village' => null],

            // Row 4 — Media & Protocol
            ['display_order' => 30, 'name' => 'Raja Yaqoob Lohanch',      'role_title' => 'Protocol Manager',      'village' => null],
            ['display_order' => 31, 'name' => 'Muhammad Sajjad Bagar',    'role_title' => 'Protocol Manager',      'village' => null],
            ['display_order' => 32, 'name' => 'Muhammad Tanveer Siddiqui','role_title' => 'Media Coordinator',     'village' => null],
            ['display_order' => 33, 'name' => 'Zahid Khan Baloch',        'role_title' => 'Broadcaster',           'village' => null],
            ['display_order' => 34, 'name' => 'Akbar Rajput',             'role_title' => 'Commentator',           'village' => null],
        ];

        foreach ($members as $m) {
            DB::table('vcc_cabinets')->insert([
                'name'          => $m['name'],
                'role_title'    => $m['role_title'],
                'bio'           => null,
                'photo'         => null,
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
