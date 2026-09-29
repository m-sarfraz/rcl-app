<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Full bootstrap for a fresh database.
 *
 * Order matters: identity and access first, then the league structure, then
 * the rosters that fixtures depend on, and only then the simulated match data
 * that everything statistical is derived from.
 *
 * Every seeder in this chain is idempotent — running `db:seed` twice will not
 * duplicate a club, a player or a fixture. `MatchStatisticsSeeder` is the one
 * exception worth knowing about: it only ever picks up fixtures still marked
 * `upcoming`, so a second run simply plays the next batch.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            /* ── Access control ─────────────────────────── */
            RoleSeeder::class,
            SuperAdminSeeder::class,

            /* ── System settings, incl. the scoring passkey ─ */
            SiteSettingSeeder::class,

            /* ── League structure & Schedule ─────────────── */
            EditionSeeder::class,
            TeamSeeder::class,
            Edition37ScheduleSeeder::class,

            /* ── Cabinet & Sponsors ─────────────────────── */
            VccCabinetSeeder::class,
            SponsorSeeder::class,

            /* ── Real Team Squads ────────────────────────── */
            TeamSquadSeeder::class,

            /*
             * Squads, Fixtures, and Statistics are commented out.
             * We will seed squads and real match data when provided.
             */
            // VillageClubSeeder::class,
            // VccCabinetSeeder::class,
            // CompleteRosterSeeder::class,
            // RealTeamDataSeeder::class,
            // RosterSeeder::class,
            // Edition35MatchSeeder::class,
            // DisciplinarySeeder::class,
            // MediaSeeder::class,
            // BannerSeeder::class,
            // EngagementSeeder::class,
            // MatchStatisticsSeeder::class,
        ]);

        $this->command?->newLine();
        $this->command?->info('🏏  Royal Champions League database ready.');
        $this->command?->line('    Admin login   : /admin/auth/'.config('app.admin_access_token'));
        $this->command?->line('    Credentials   : superadmin@rcl.com / RCL@SuperAdmin#2024');
        $this->command?->line('    Scoring key   : '.\App\Models\SiteSetting::get('scoring_secret_key'));
        $this->command?->line('    Mobile API    : '.url('/api/v1'));
    }
}
