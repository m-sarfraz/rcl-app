<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Gives the 16 affiliated clubs their real identities.
 *
 * Only placeholder rows are touched — anything still called "Village N" from
 * the original scaffold. Clubs that have already been named by hand or by an
 * earlier seeder are left exactly as they are.
 */
class VillageClubSeeder extends Seeder
{
    /** team id ⇒ [name, village, short code, primary, secondary] */
    private const CLUBS = [
        2  => ['Azad Cricket Club',    'Village 348',      'AZ8', '#0EA5E9', '#075985'],
        3  => ['Friends CC 417',       'Village 417',      'FC7', '#8B5CF6', '#4C1D95'],
        12 => ['Surajpur CC',          'Suraj Pur 351',    'SP1', '#F59E0B', '#B45309'],
        13 => ['Shola Cricket Club',   'Shola 183',        'SHO', '#EF4444', '#7F1D1D'],
        14 => ['Shahbaz Shaheed CC',   'Village 786',      'SSC', '#10B981', '#065F46'],
        15 => ['Azad CC 213',          'Village 213',      'AZ3', '#6366F1', '#312E81'],
        16 => ['Shaheen Cricket Club', 'Village 449',      'SHN', '#F97316', '#9A3412'],
    ];

    public function run(): void
    {
        $renamed = 0;

        foreach (self::CLUBS as $id => [$name, $village, $code, $primary, $secondary]) {
            $team = Team::find($id);

            // Only rewrite scaffold placeholders — never a club someone has named.
            if (! $team || ! preg_match('/^Village \d+$/', (string) $team->village_name)) {
                continue;
            }

            $team->update([
                'name'            => $name,
                'village_name'    => $village,
                'short_code'      => $code,
                'primary_color'   => $primary,
                'secondary_color' => $secondary,
                'is_active'       => true,
            ]);
            $renamed++;
        }

        // Every active club plays the current edition.
        $edition = Edition::where('is_current', true)->first();
        if ($edition) {
            $edition->teams()->syncWithoutDetaching(
                Team::active()->where('short_code', '!=', 'TBD')->pluck('id')->all()
            );
        }

        $this->command?->info("✅ Village clubs seeded ({$renamed} placeholder(s) named).");
    }
}
