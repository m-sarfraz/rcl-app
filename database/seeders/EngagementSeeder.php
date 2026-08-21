<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Notification;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fan-facing content: prediction polls, the ticker, and sponsors.
 */
class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $edition = Edition::where('is_current', true)->first() ?? Edition::first();

        if (! $edition) {
            return;
        }

        mt_srand(9_001);

        $this->seedPolls($edition->id);
        $this->seedTicker();
        $this->seedSponsors();

        $this->command?->info('✅ Engagement content seeded (polls, ticker, sponsors).');
    }

    private function seedPolls(int $editionId): void
    {
        $teams = Team::active()->where('short_code', '!=', 'TBD')->inRandomOrder()->limit(4)->get();

        if ($teams->count() < 4) {
            return;
        }

        $polls = [
            [
                'question'    => 'Who lifts the 35th Edition trophy?',
                'description' => 'Cast your vote before the knockout stage begins.',
                'options'     => $teams->pluck('name')->all(),
                'active'      => true,
                'ends_at'     => now()->addWeeks(3),
            ],
            [
                'question'    => 'Which milestone falls first this season?',
                'description' => 'The VCC record book is wide open at Edition 35.',
                'options'     => ['A century', 'A hat-trick of wickets', 'Five sixes in one over', 'A maiden in the final over'],
                'active'      => true,
                'ends_at'     => now()->addWeeks(2),
            ],
            [
                'question'    => 'Best format for Edition 36?',
                'description' => 'The council is consulting clubs before the next draw.',
                'options'     => ['10 overs a side', '15 overs a side', '20 overs a side', 'Keep it at 10'],
                'active'      => false,
                'ends_at'     => now()->subWeek(),
            ],
        ];

        foreach ($polls as $spec) {
            $poll = Poll::updateOrCreate(
                ['edition_id' => $editionId, 'question' => $spec['question']],
                [
                    'description'     => $spec['description'],
                    'starts_at'       => now()->subWeek(),
                    'ends_at'         => $spec['ends_at'],
                    'is_active'       => $spec['active'],
                    'allow_anonymous' => true,
                ]
            );

            foreach ($spec['options'] as $i => $text) {
                $option = PollOption::updateOrCreate(
                    ['poll_id' => $poll->id, 'option_text' => $text],
                    ['display_order' => $i + 1]
                );

                // Seed a plausible vote spread, one row per vote, each with a
                // distinct session token — the same shape a real vote produces.
                $existing = PollVote::where('poll_option_id', $option->id)->count();
                $target   = mt_rand(8, 55);

                for ($v = $existing; $v < $target; $v++) {
                    PollVote::create([
                        'poll_id'        => $poll->id,
                        'poll_option_id' => $option->id,
                        'ip_address'     => '10.0.'.mt_rand(0, 255).'.'.mt_rand(1, 254),
                        'session_token'  => 'seed-'.$poll->id.'-'.$option->id.'-'.$v,
                    ]);
                }
            }
        }
    }

    private function seedTicker(): void
    {
        $adminId = User::value('id');

        $items = [
            ['Live scoring has moved to the RCL mobile app — ask the VCC office for the scoring passkey.', 'announcement'],
            ['Edition 35 group stage under way — 24 fixtures across four match days.', 'info'],
            ['Reminder: players with unpaid fines cannot be named in a playing XI.', 'fine'],
            ['Bowling actions under review are published on the Banned Bowlers page.', 'suspension'],
            ['Points table now uses the VCC rule — one ball counts as 0.17 of an over.', 'info'],
        ];

        foreach ($items as [$message, $type]) {
            Notification::updateOrCreate(
                ['message' => $message],
                [
                    'type'       => $type,
                    'is_active'  => true,
                    'is_ticker'  => true,
                    'expires_at' => now()->addMonths(3),
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedSponsors(): void
    {
        $sponsors = [
            ['Chenab Agri Traders',      'title',     'Title sponsor of the 35th Edition.'],
            ['Rajput Motors',            'platinum',  'Official transport partner for all four match days.'],
            ['Goraya Sports House',      'platinum',  'Match balls and playing kit across the tournament.'],
            ['Al Haider Foods',          'gold',      'Refreshments for players and officials.'],
            ['Sipra Electronics',        'gold',      'Scoreboard and public-address equipment.'],
            ['Cheema Builders',          'silver',    'Ground preparation and pitch maintenance.'],
            ['Village Pharmacy 419',     'silver',    'On-ground medical support.'],
            ['Toor Communications',      'general',   'Mobile data for the live scoring team.'],
        ];

        foreach ($sponsors as $i => [$name, $tier, $description]) {
            Sponsor::updateOrCreate(
                ['name' => $name],
                [
                    'tier'          => $tier,
                    'description'   => $description,
                    'display_order' => $i + 1,
                    'is_active'     => true,
                ]
            );
        }
    }
}
