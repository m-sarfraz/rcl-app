<?php

namespace Database\Seeders;

use App\Models\BannedBowler;
use App\Models\Edition;
use App\Models\Fine;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Models\PlayerSuspension;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Penalties and bans.
 *
 * These records are not decoration: an unpaid fine, an active suspension or a
 * banned bowling action all make a player ineligible, and the mobile scoring
 * console reads exactly these tables when it greys a name out of the XI picker.
 * Seeding them is what makes that path testable.
 */
class DisciplinarySeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::first();
        $edition = Edition::where('is_current', true)->first() ?? Edition::first();

        if (! $admin || ! $edition) {
            $this->command?->warn('Need an admin user and an edition before seeding discipline records.');

            return;
        }

        mt_srand(4_242);

        $this->seedBowlingActionBans($admin->id);
        $this->seedFines($admin->id, $edition->id);
        $this->seedSuspensions($admin->id);

        $this->command?->info('✅ Disciplinary records seeded (bowling-action bans, fines, suspensions).');
    }

    /* ── Chucking / illegal action ─────────────────────────────────── */
    private function seedBowlingActionBans(int $adminId): void
    {
        $bowlers = Player::whereIn('role', ['bowler', 'all_rounder'])
            ->where('bowling_style', '!=', 'none')
            ->orderBy('id')
            ->limit(60)
            ->get();

        if ($bowlers->count() < 4) {
            return;
        }

        $cases = [
            [
                'player' => $bowlers[3],
                'reason' => 'Suspect action reported by both on-field umpires; elbow extension beyond the 15° tolerance on video review.',
                'from'   => now()->subMonths(4)->toDateString(),
                'until'  => null,
                'active' => true,
                'notes'  => 'Must pass an independent biomechanical assessment before being cleared to bowl.',
            ],
            [
                'player' => $bowlers[11],
                'reason' => 'Called for throwing twice in one over during the group stage.',
                'from'   => now()->subMonths(2)->toDateString(),
                'until'  => now()->addMonth()->toDateString(),
                'active' => true,
                'notes'  => 'Remedial coaching arranged through the VCC technical panel.',
            ],
            [
                'player' => $bowlers[24],
                'reason' => 'Action reported after a formal review panel flagged the quicker delivery.',
                'from'   => now()->subYear()->toDateString(),
                'until'  => now()->subMonths(6)->toDateString(),
                'active' => false,
                'notes'  => 'Ban served in full. Re-tested and cleared — no further restriction.',
            ],
            [
                'player' => $bowlers[38] ?? $bowlers[1],
                'reason' => 'Umpire report for a suspect doosra; player withdrew the delivery from his repertoire.',
                'from'   => now()->subMonths(9)->toDateString(),
                'until'  => now()->subMonths(3)->toDateString(),
                'active' => false,
                'notes'  => null,
            ],
        ];

        foreach ($cases as $case) {
            /** @var Player $player */
            $player = $case['player'];

            BannedBowler::updateOrCreate(
                ['player_id' => $player->id, 'banned_from' => $case['from']],
                [
                    'reason'       => $case['reason'],
                    'banned_until' => $case['until'],
                    'is_active'    => $case['active'],
                    'notes'        => $case['notes'],
                    'issued_by'    => $adminId,
                ]
            );

            $player->update([
                'bowling_action_status' => $case['active'] ? 'banned' : 'legal',
            ]);
        }

        // A couple of players under review but still allowed to bowl.
        foreach ([$bowlers[7], $bowlers[19]] as $flagged) {
            if ($flagged->bowling_action_status === 'legal') {
                $flagged->update(['bowling_action_status' => 'flagged']);
            }
        }
    }

    /* ── Code-of-conduct fines ─────────────────────────────────────── */
    private function seedFines(int $adminId, int $editionId): void
    {
        $violations = [
            ['code_of_conduct',   'yellow', 'Dissent at an umpire\'s decision after being given out LBW.',            1500, 'unpaid'],
            ['code_of_conduct',   'none',   'Audible obscenity directed at an opposition batter.',                    1000, 'paid'],
            ['disciplinary_card', 'red',    'Physical contact with an opponent during an on-field altercation.',      5000, 'unpaid'],
            ['misconduct',        'none',   'Late arrival — side took the field eleven minutes after the scheduled start.', 2000, 'paid'],
            ['misconduct',        'none',   'Team failed to submit its playing XI before the toss.',                  2500, 'unpaid'],
            ['code_of_conduct',   'yellow', 'Excessive appealing after a formal warning from the standing umpire.',   1200, 'waived'],
            ['other',             'none',   'Damage to boundary rope and sightscreen during celebrations.',            800, 'paid'],
            ['chucking',          'none',   'Failure to attend the mandated bowling-action review session.',          3000, 'unpaid'],
        ];

        $rosterRows = PlayerEditionTeam::where('edition_id', $editionId)
            ->inRandomOrder()
            ->limit(count($violations))
            ->get();

        foreach ($violations as $i => [$type, $card, $description, $amount, $status]) {
            $row = $rosterRows[$i] ?? null;
            if (! $row) {
                continue;
            }

            Fine::updateOrCreate(
                ['player_id' => $row->player_id, 'edition_id' => $editionId, 'description' => $description],
                [
                    'team_id'        => $row->team_id,
                    'violation_type' => $type,
                    'card_type'      => $card,
                    'amount'         => $amount,
                    'status'         => $status,
                    'due_date'       => now()->addDays(21)->toDateString(),
                    'paid_date'      => $status === 'paid' ? now()->subDays(mt_rand(1, 20))->toDateString() : null,
                    'issued_by'      => $adminId,
                    'admin_notes'    => $status === 'waived'
                        ? 'Waived by the disciplinary committee — first offence, formal apology accepted.'
                        : null,
                ]
            );
        }
    }

    /* ── Match suspensions ─────────────────────────────────────────── */
    private function seedSuspensions(int $adminId): void
    {
        $redCardFine = Fine::where('card_type', 'red')->first();

        if ($redCardFine && $redCardFine->player_id) {
            PlayerSuspension::updateOrCreate(
                ['player_id' => $redCardFine->player_id, 'type' => 'suspension'],
                [
                    'fine_id'           => $redCardFine->id,
                    'reason'            => 'Red card — physical contact with an opponent. Two-match suspension imposed by the disciplinary committee.',
                    'start_date'        => now()->subWeeks(2)->toDateString(),
                    'end_date'          => now()->addWeeks(2)->toDateString(),
                    'matches_suspended' => 2,
                    'is_active'         => true,
                    'issued_by'         => $adminId,
                ]
            );
        }

        $served = Player::whereDoesntHave('suspensions')->inRandomOrder()->first();

        if ($served) {
            PlayerSuspension::updateOrCreate(
                ['player_id' => $served->id, 'type' => 'suspension'],
                [
                    'reason'            => 'Accumulation of two yellow cards across the group stage.',
                    'start_date'        => now()->subMonths(3)->toDateString(),
                    'end_date'          => now()->subMonths(2)->toDateString(),
                    'matches_suspended' => 1,
                    'is_active'         => false,
                    'issued_by'         => $adminId,
                ]
            );
        }
    }
}
