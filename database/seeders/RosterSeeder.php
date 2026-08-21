<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Guarantees every club can actually field a side.
 *
 * Several clubs were carrying five or six registered players — not enough for
 * an XI, which meant the scoring console could never be opened for their
 * fixtures. This tops each roster up to a full squad and makes sure exactly one
 * captain and one vice-captain are appointed.
 */
class RosterSeeder extends Seeder
{
    private const SQUAD_TARGET = 14;

    /** Name pool used only to fill gaps; real names always win. */
    private const FIRST_NAMES = [
        'Adnan', 'Aftab', 'Ahsan', 'Akbar', 'Amir', 'Arslan', 'Asif', 'Bilal',
        'Danish', 'Ehsan', 'Faisal', 'Farhan', 'Ghulam', 'Habib', 'Haris',
        'Imran', 'Irfan', 'Jamil', 'Junaid', 'Kamran', 'Khalid', 'Luqman',
        'Mudassar', 'Naveed', 'Nadeem', 'Noman', 'Owais', 'Qasim', 'Rashid',
        'Rizwan', 'Sajid', 'Salman', 'Shahid', 'Shoaib', 'Tahir', 'Talha',
        'Umar', 'Usman', 'Waqas', 'Yasir', 'Zahid', 'Zeeshan',
    ];

    private const SURNAMES = [
        'Rajput', 'Cheema', 'Sipra', 'Toor', 'Warraich', 'Gujjar', 'Sial',
        'Bhatti', 'Chattha', 'Dogar', 'Goraya', 'Hanjra', 'Kharal', 'Mann',
        'Nagra', 'Randhawa', 'Sandhu', 'Tarar', 'Virk', 'Wattoo',
    ];

    private const ROLES = [
        'batsman', 'batsman', 'batsman', 'batsman', 'all_rounder', 'all_rounder',
        'wicket_keeper', 'bowler', 'bowler', 'bowler', 'all_rounder',
        'batsman', 'bowler', 'all_rounder',
    ];

    private const BOWLING = [
        'right_arm_medium', 'right_arm_fast', 'right_arm_spin',
        'left_arm_medium', 'left_arm_fast', 'left_arm_spin', 'none',
    ];

    public function run(): void
    {
        $edition = Edition::where('is_current', true)->first()
            ?? Edition::orderByDesc('edition_number')->first();

        if (! $edition) {
            $this->command?->warn('No edition found — skipping roster seeding.');

            return;
        }

        // Deterministic: re-running the seeder produces the same squads.
        mt_srand(35_000 + $edition->id);

        $added = 0;

        foreach (Team::active()->where('short_code', '!=', 'TBD')->orderBy('id')->get() as $team) {
            $existing = PlayerEditionTeam::where('team_id', $team->id)
                ->where('edition_id', $edition->id)
                ->count();

            for ($i = $existing; $i < self::SQUAD_TARGET; $i++) {
                $this->createPlayer($team, $edition->id, $i);
                $added++;
            }

            $this->appointLeadership($team->id, $edition->id);
        }

        $this->command?->info("✅ Rosters complete — {$added} player(s) added to reach squads of ".self::SQUAD_TARGET.'.');
    }

    private function createPlayer(Team $team, int $editionId, int $index): void
    {
        $name = sprintf(
            '%s %s',
            self::FIRST_NAMES[mt_rand(0, count(self::FIRST_NAMES) - 1)],
            self::SURNAMES[mt_rand(0, count(self::SURNAMES) - 1)]
        );

        // Keep names unique per club so scorers never see two identical entries.
        $suffix = 1;
        $candidate = $name;
        while (
            Player::where('name', $candidate)
                ->whereHas('rosterEntries', fn ($q) => $q->where('team_id', $team->id))
                ->exists()
        ) {
            $candidate = $name.' '.(++$suffix);
        }

        $role = self::ROLES[$index % count(self::ROLES)];

        $player = Player::create([
            'name'          => $candidate,
            'jersey_number' => (string) ($index + 1),
            'role'          => $role,
            'batting_style' => mt_rand(1, 4) === 1 ? 'left_hand' : 'right_hand',
            'bowling_style' => $role === 'batsman'
                ? 'none'
                : self::BOWLING[mt_rand(0, count(self::BOWLING) - 2)],
            'bowling_action_status' => 'legal',
            'is_active'     => true,
        ]);

        PlayerEditionTeam::updateOrCreate(
            ['player_id' => $player->id, 'edition_id' => $editionId],
            ['team_id' => $team->id]
        );
    }

    /** Exactly one captain and one vice-captain per club per edition. */
    private function appointLeadership(int $teamId, int $editionId): void
    {
        $roster = PlayerEditionTeam::where('team_id', $teamId)
            ->where('edition_id', $editionId)
            ->orderBy('id')
            ->get();

        if ($roster->isEmpty()) {
            return;
        }

        $hasCaptain = $roster->contains(fn ($r) => (bool) $r->is_captain);
        $hasVice    = $roster->contains(fn ($r) => (bool) $r->is_vice_captain);

        if ($hasCaptain && $hasVice) {
            return;
        }

        if (! $hasCaptain) {
            $roster->first()->update(['is_captain' => true, 'is_vice_captain' => false]);
        }

        if (! $hasVice) {
            $vice = $roster->first(fn ($r) => ! $r->is_captain);
            $vice?->update(['is_vice_captain' => true]);
        }
    }
}
