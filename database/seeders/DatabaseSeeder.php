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

            /* ── League structure ───────────────────────── */
            EditionSeeder::class,
            TeamSeeder::class,
            VillageClubSeeder::class,
            VccCabinetSeeder::class,

            /* ──
             | Squads. Order matters: CompleteRosterSeeder creates the player
             | rows that RealTeamDataSeeder then renames to the real village
             | names, and RosterSeeder finally tops every club up to a full
             | squad so no fixture is unplayable.
             ── */
            CompleteRosterSeeder::class,
            RealTeamDataSeeder::class,
            RosterSeeder::class,

            /* ── Fixtures ───────────────────────────────── */
            Edition35MatchSeeder::class,

            /* ── Governance ─────────────────────────────── */
            DisciplinarySeeder::class,

            /* ── Club crests and cabinet portraits ─────────── */
            MediaSeeder::class,

            /* ── Fan-facing content ─────────────────────── */
            BannerSeeder::class,
            EngagementSeeder::class,

            /* ── Results & statistics (runs the real engine) ─ */
            MatchStatisticsSeeder::class,
        ]);

        $this->command?->newLine();
        $this->command?->info('🏏  Royal Champions League database ready.');
        $this->command?->line('    Admin login   : /admin/auth/'.config('app.admin_access_token'));
        $this->command?->line('    Credentials   : superadmin@rcl.com / RCL@SuperAdmin#2024');
        $this->command?->line('    Scoring key   : '.\App\Models\SiteSetting::get('scoring_secret_key'));
        $this->command?->line('    Mobile API    : '.url('/api/v1'));
    }
}
