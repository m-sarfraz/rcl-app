<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Draws the club crests and cabinet portraits.
 *
 * Delegates to `rcl:crests` so a fresh install and a later `--force` redraw run
 * exactly the same code. Crests are generated from each club's own short code
 * and colours, so a badge always matches the club it belongs to — the generic
 * set that shipped with the project had Qadirabad Strikers wearing "QDC".
 */
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder::call() runs other seeders, so the command goes through Artisan.
        Artisan::call('rcl:crests');

        $this->command?->info('✅ Club crests and cabinet portraits generated.');
    }
}
