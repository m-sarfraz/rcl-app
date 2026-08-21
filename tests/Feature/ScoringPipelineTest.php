<?php

namespace Tests\Feature;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\PlayerEditionStat;
use App\Models\PlayerEditionTeam;
use App\Models\SiteSetting;
use App\Models\Team;
use App\Services\MatchStatisticsService;
use App\Services\ScoringAccessService;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * End-to-end cover for the scoring pipeline: passkey gate → ball log →
 * rebuilt scorecards → finalised result → edition aggregates.
 *
 * These are the paths the seeder's random simulation cannot reliably exercise
 * (a maiden over is roughly a one-in-a-thousand event at village scoring
 * rates), so they are constructed deliberately here.
 */
class ScoringPipelineTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;
    private Team $home;
    private Team $away;
    private CricketMatch $match;
    /** @var array<int, Player> */
    private array $homeXI = [];
    /** @var array<int, Player> */
    private array $awayXI = [];

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::set(ScoringAccessService::SETTING_KEY, ScoringAccessService::DEFAULT_KEY);

        $this->edition = Edition::create([
            'name' => 'Test Edition', 'edition_number' => 99,
            'host_village' => 'Testville', 'status' => 'active', 'is_current' => true,
        ]);

        $this->home = Team::create(['name' => 'Home CC', 'village_name' => 'Home', 'short_code' => 'HOM']);
        $this->away = Team::create(['name' => 'Away CC', 'village_name' => 'Away', 'short_code' => 'AWY']);
        $this->edition->teams()->attach([$this->home->id, $this->away->id]);

        $this->homeXI = $this->buildSquad($this->home, 'H');
        $this->awayXI = $this->buildSquad($this->away, 'A');

        $this->match = CricketMatch::create([
            'edition_id'     => $this->edition->id,
            'home_team_id'   => $this->home->id,
            'away_team_id'   => $this->away->id,
            'match_number'   => '1',
            'venue'          => 'Test Ground',
            'scheduled_at'   => now(),
            'status'         => 'upcoming',
            'overs_per_side' => 10,
        ]);

        foreach ([[$this->home, $this->homeXI], [$this->away, $this->awayXI]] as [$team, $xi]) {
            foreach ($xi as $i => $player) {
                MatchSquad::create([
                    'match_id' => $this->match->id,
                    'team_id'  => $team->id,
                    'player_id'=> $player->id,
                    'batting_order' => $i + 1,
                ]);
            }
        }
    }

    /** @return array<int, Player> */
    private function buildSquad(Team $team, string $prefix): array
    {
        $players = [];

        for ($i = 1; $i <= 11; $i++) {
            $player = Player::create([
                'name'          => "{$prefix} Player {$i}",
                'jersey_number' => (string) $i,
                'role'          => $i > 7 ? 'bowler' : 'batsman',
                'is_active'     => true,
            ]);

            PlayerEditionTeam::create([
                'player_id'  => $player->id,
                'team_id'    => $team->id,
                'edition_id' => $this->edition->id,
            ]);

            $players[] = $player;
        }

        return $players;
    }

    /** @param array<string, mixed> $overrides */
    private function ball(Innings $innings, int $over, int $ballNo, array $overrides = []): array
    {
        return array_merge([
            'client_uuid'    => (string) Str::uuid(),
            'batsman_id'     => $this->homeXI[0]->id,
            'non_striker_id' => $this->homeXI[1]->id,
            'bowler_id'      => $this->awayXI[8]->id,
            'over_number'    => $over,
            'ball_number'    => $ballNo,
            'runs_scored'    => 0,
            'extra_runs'     => 0,
        ], $overrides);
    }

    private function startFirstInnings(): Innings
    {
        return app(ScoringService::class)->startInnings(
            $this->match->id, $this->home->id, $this->away->id, 1
        );
    }

    /* ══ Passkey gate ══════════════════════════════════════════════ */

    #[Test]
    public function the_correct_passkey_returns_a_scoring_token(): void
    {
        $response = $this->postJson('/api/v1/scoring/verify', [
            'passkey' => ScoringAccessService::DEFAULT_KEY,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'expires_at', 'expires_in']]);
    }

    #[Test]
    public function a_wrong_passkey_is_rejected_and_grants_nothing(): void
    {
        $this->postJson('/api/v1/scoring/verify', ['passkey' => 'not-the-key'])
            ->assertStatus(401)
            ->assertJsonPath('code', 'invalid_passkey');
    }

    #[Test]
    public function scoring_writes_are_refused_without_a_token(): void
    {
        $this->postJson("/api/v1/scoring/matches/{$this->match->id}/toss", [
            'toss_winner_id' => $this->home->id,
            'decision'       => 'bat',
        ])->assertStatus(401);
    }

    #[Test]
    public function rotating_the_passkey_invalidates_tokens_already_issued(): void
    {
        $access = app(ScoringAccessService::class);
        $token  = $access->issueToken('scorer')['token'];

        $this->assertTrue($access->inspectToken($token)['valid']);

        SiteSetting::set(ScoringAccessService::SETTING_KEY, 'a-brand-new-key');

        $result = $access->inspectToken($token);
        $this->assertFalse($result['valid']);
        $this->assertSame('revoked', $result['reason']);
    }

    /* ══ Ball log → scorecards ═════════════════════════════════════ */

    #[Test]
    public function a_wide_does_not_count_as_a_ball_faced_but_does_add_a_run(): void
    {
        $innings = $this->startFirstInnings();

        app(ScoringService::class)->syncDeliveries($innings->id, [
            $this->ball($innings, 1, 1, ['is_wide' => true, 'extra_runs' => 1]),
            $this->ball($innings, 1, 1, ['runs_scored' => 4, 'is_four' => true]),
        ]);

        $innings->refresh();

        $this->assertSame(5, (int) $innings->total_runs);
        $this->assertSame(1, (int) $innings->total_balls, 'A wide is not a legal delivery.');
        $this->assertSame(1, (int) $innings->extras_wides);

        $batting = $innings->battingScorecards()->where('player_id', $this->homeXI[0]->id)->first();
        $this->assertSame(4, (int) $batting->runs_scored);
        $this->assertSame(1, (int) $batting->balls_faced);
    }

    #[Test]
    public function byes_are_extras_and_are_never_credited_to_the_batter(): void
    {
        $innings = $this->startFirstInnings();

        app(ScoringService::class)->syncDeliveries($innings->id, [
            $this->ball($innings, 1, 1, ['is_bye' => true, 'extra_runs' => 2]),
        ]);

        $innings->refresh();
        $batting = $innings->battingScorecards()->where('player_id', $this->homeXI[0]->id)->first();
        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();

        $this->assertSame(2, (int) $innings->total_runs);
        $this->assertSame(2, (int) $innings->extras_byes);
        $this->assertSame(0, (int) $batting->runs_scored, 'Byes never go on the batter\'s score.');
        $this->assertSame(1, (int) $batting->balls_faced);
        $this->assertSame(0, (int) $bowling->runs_conceded, 'Byes are not charged to the bowler.');
    }

    #[Test]
    public function a_wicketless_scoreless_over_is_recorded_as_a_maiden(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [];
        for ($b = 1; $b <= 6; $b++) {
            $balls[] = $this->ball($innings, 1, $b);
        }

        app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();

        $this->assertSame(1, (int) $bowling->maidens);
        $this->assertSame(6, (int) $bowling->overs_bowled_balls);
        $this->assertSame(0, (int) $bowling->runs_conceded);
    }

    #[Test]
    public function an_over_containing_a_wide_is_not_a_maiden(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [$this->ball($innings, 1, 1, ['is_wide' => true, 'extra_runs' => 1])];
        for ($b = 1; $b <= 6; $b++) {
            $balls[] = $this->ball($innings, 1, $b);
        }

        app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();
        $this->assertSame(0, (int) $bowling->maidens);
    }

    #[Test]
    public function three_wickets_in_three_deliveries_is_a_hat_trick(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [];
        foreach ([1, 2, 3] as $i => $ballNo) {
            $balls[] = $this->ball($innings, 1, $ballNo, [
                'batsman_id'    => $this->homeXI[$i]->id,
                'is_wicket'     => true,
                'wicket_type'   => 'bowled',
                'out_player_id' => $this->homeXI[$i]->id,
            ]);
        }

        app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();

        $this->assertTrue((bool) $bowling->hat_trick_wickets);
        $this->assertSame(3, (int) $bowling->wickets);
    }

    #[Test]
    public function a_run_out_is_not_credited_to_the_bowler(): void
    {
        $innings = $this->startFirstInnings();

        app(ScoringService::class)->syncDeliveries($innings->id, [
            $this->ball($innings, 1, 1, [
                'is_wicket'     => true,
                'wicket_type'   => 'run_out',
                'out_player_id' => $this->homeXI[1]->id,   // the non-striker went
                'fielder_id'    => $this->awayXI[3]->id,
            ]),
        ]);

        $innings->refresh();
        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();

        $this->assertSame(0, (int) $bowling->wickets, 'A run-out is not the bowler\'s wicket.');
        $this->assertSame(1, (int) $innings->total_wickets, 'It still counts against the batting side.');

        $dismissed = $innings->battingScorecards()->where('player_id', $this->homeXI[1]->id)->first();
        $this->assertSame('run_out', $dismissed->dismissal_type);
    }

    #[Test]
    public function five_sixes_in_one_over_is_flagged(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [];
        for ($b = 1; $b <= 5; $b++) {
            $balls[] = $this->ball($innings, 1, $b, ['runs_scored' => 6, 'is_six' => true]);
        }
        $balls[] = $this->ball($innings, 1, 6);

        app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $batting = $innings->battingScorecards()->where('player_id', $this->homeXI[0]->id)->first();

        $this->assertTrue((bool) $batting->five_sixes_in_over);
        $this->assertTrue((bool) $batting->hat_trick_sixes);
        $this->assertSame(30, (int) $batting->runs_scored);
    }

    #[Test]
    public function a_penalty_award_adds_runs_without_a_delivery_being_bowled(): void
    {
        $innings = $this->startFirstInnings();

        app(ScoringService::class)->syncDeliveries($innings->id, [
            $this->ball($innings, 1, 1, ['is_penalty' => true, 'extra_runs' => 5]),
            $this->ball($innings, 1, 1, ['runs_scored' => 1]),
        ]);

        $innings->refresh();

        $this->assertSame(6, (int) $innings->total_runs);
        $this->assertSame(1, (int) $innings->total_balls, 'A penalty is not a delivery.');
        $this->assertSame(5, (int) $innings->extras_penalty);

        $batting = $innings->battingScorecards()->where('player_id', $this->homeXI[0]->id)->first();
        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();

        $this->assertSame(1, (int) $batting->balls_faced, 'Nobody faces a penalty.');
        $this->assertSame(1, (int) $bowling->runs_conceded, 'A penalty is not charged to the bowler.');
        $this->assertSame(1, (int) $bowling->overs_bowled_balls);
    }

    #[Test]
    public function a_penalty_inside_an_over_does_not_spoil_the_maiden(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [$this->ball($innings, 1, 1, ['is_penalty' => true, 'extra_runs' => 5])];
        for ($b = 1; $b <= 6; $b++) {
            $balls[] = $this->ball($innings, 1, $b);
        }

        app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $bowling = $innings->bowlingScorecards()->where('player_id', $this->awayXI[8]->id)->first();
        $this->assertSame(1, (int) $bowling->maidens);
    }

    #[Test]
    public function a_penalty_recorded_with_bat_runs_is_rejected(): void
    {
        $innings = $this->startFirstInnings();
        $access  = app(ScoringAccessService::class);
        $token   = $access->issueToken('test')['token'];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/scoring/innings/{$innings->id}/balls", [
                'batsman_id'  => $this->homeXI[0]->id,
                'bowler_id'   => $this->awayXI[8]->id,
                'runs_scored' => 4,
                'extra_runs'  => 5,
                'is_penalty'  => true,
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['runs_scored']]);
    }

    #[Test]
    public function a_super_over_is_one_over_and_ends_on_the_second_wicket(): void
    {
        $scoring = app(ScoringService::class);

        // Two level innings, then the eliminator.
        foreach ([1, 2] as $number) {
            $inn = $scoring->startInnings(
                $this->match->id,
                $number === 1 ? $this->home->id : $this->away->id,
                $number === 1 ? $this->away->id : $this->home->id,
                $number,
            );
            $scoring->syncDeliveries($inn->id, [
                $this->ball($inn, 1, 1, [
                    'batsman_id'     => ($number === 1 ? $this->homeXI : $this->awayXI)[0]->id,
                    'non_striker_id' => ($number === 1 ? $this->homeXI : $this->awayXI)[1]->id,
                    'bowler_id'      => ($number === 1 ? $this->awayXI : $this->homeXI)[8]->id,
                    'runs_scored'    => 4,
                    'is_four'        => true,
                ]),
            ]);
            $inn->fresh()->update(['is_completed' => true]);
        }

        $super = $scoring->startInnings($this->match->id, $this->away->id, $this->home->id, 3);

        // Six singles is a full super over.
        $balls = [];
        for ($b = 1; $b <= 6; $b++) {
            $balls[] = $this->ball($super, 1, $b, [
                'batsman_id'     => $this->awayXI[0]->id,
                'non_striker_id' => $this->awayXI[1]->id,
                'bowler_id'      => $this->homeXI[8]->id,
                'runs_scored'    => 1,
            ]);
        }

        $result = app(ScoringService::class)->syncDeliveries($super->id, $balls);

        $this->assertTrue($result['is_innings_over'], 'Six legal balls closes a super over.');
        $this->assertSame(6, (int) $super->fresh()->total_runs);
    }

    /* ══ Idempotency ═══════════════════════════════════════════════ */

    #[Test]
    public function replaying_the_offline_queue_never_double_counts(): void
    {
        $innings = $this->startFirstInnings();

        $balls = [
            $this->ball($innings, 1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball($innings, 1, 2, ['runs_scored' => 6, 'is_six' => true]),
        ];

        $first  = app(ScoringService::class)->syncDeliveries($innings->id, $balls);
        $second = app(ScoringService::class)->syncDeliveries($innings->id, $balls);

        $this->assertSame(2, $first['accepted']);
        $this->assertSame(0, $second['accepted']);
        $this->assertSame(2, $second['duplicates']);
        $this->assertSame(10, (int) $innings->fresh()->total_runs);
    }

    #[Test]
    public function undoing_a_ball_rolls_every_derived_total_back(): void
    {
        $innings = $this->startFirstInnings();

        app(ScoringService::class)->syncDeliveries($innings->id, [
            $this->ball($innings, 1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball($innings, 1, 2, ['runs_scored' => 6, 'is_six' => true]),
        ]);

        $this->assertSame(10, (int) $innings->fresh()->total_runs);

        app(ScoringService::class)->undoLastBall($innings->id);

        $innings->refresh();
        $batting = $innings->battingScorecards()->where('player_id', $this->homeXI[0]->id)->first();

        $this->assertSame(4, (int) $innings->total_runs);
        $this->assertSame(1, (int) $innings->total_balls);
        $this->assertSame(0, (int) $batting->sixes, 'The six must disappear from the scorecard too.');
        $this->assertSame(1, (int) $batting->fours);
    }

    /* ══ Result & aggregates ═══════════════════════════════════════ */

    #[Test]
    public function finalising_decides_the_result_and_updates_edition_statistics(): void
    {
        $scoring = app(ScoringService::class);

        // Home bat first and make 40.
        $inn1 = $scoring->startInnings($this->match->id, $this->home->id, $this->away->id, 1);
        $first = [];
        for ($b = 1; $b <= 6; $b++) {
            $first[] = $this->ball($inn1, 1, $b, ['runs_scored' => 6, 'is_six' => true]);
        }
        $first[] = $this->ball($inn1, 2, 1, ['runs_scored' => 4, 'is_four' => true]);
        $scoring->syncDeliveries($inn1->id, $first);

        // Away chase and fall short.
        $inn2 = $scoring->startInnings(
            $this->match->id, $this->away->id, $this->home->id, 2,
            (int) $inn1->fresh()->total_runs + 1
        );
        $scoring->syncDeliveries($inn2->id, [
            array_merge($this->ball($inn2, 1, 1), [
                'batsman_id'     => $this->awayXI[0]->id,
                'non_striker_id' => $this->awayXI[1]->id,
                'bowler_id'      => $this->homeXI[8]->id,
                'runs_scored'    => 4,
                'is_four'        => true,
            ]),
        ]);
        $inn2->fresh()->update(['is_completed' => true]);

        $outcome = $scoring->finalizeMatch($this->match, ['finalized_by' => 'test']);

        $this->match->refresh();

        $this->assertSame('completed', $this->match->status);
        $this->assertSame($this->home->id, (int) $this->match->winner_id);
        $this->assertSame('runs', $this->match->result_type);
        $this->assertSame(36, (int) $this->match->result_margin);
        $this->assertNotNull($this->match->finalized_at);
        $this->assertGreaterThan(0, $outcome['players_updated']);

        $topScorer = PlayerEditionStat::where('player_id', $this->homeXI[0]->id)
            ->where('edition_id', $this->edition->id)
            ->first();

        $this->assertNotNull($topScorer, 'Edition stats must exist once a match is finalised.');
        $this->assertSame(40, (int) $topScorer->total_runs);
        $this->assertSame(6, (int) $topScorer->total_sixes);
        $this->assertSame(1, (int) $topScorer->matches_played);
        $this->assertGreaterThan(0, (float) $topScorer->mvp_points);
    }

    #[Test]
    public function a_chase_completed_with_wickets_in_hand_reports_the_margin_in_wickets(): void
    {
        $scoring = app(ScoringService::class);

        $inn1 = $scoring->startInnings($this->match->id, $this->home->id, $this->away->id, 1);
        $scoring->syncDeliveries($inn1->id, [
            $this->ball($inn1, 1, 1, ['runs_scored' => 4, 'is_four' => true]),
        ]);
        $inn1->fresh()->update(['is_completed' => true]);

        $inn2 = $scoring->startInnings(
            $this->match->id, $this->away->id, $this->home->id, 2,
            (int) $inn1->fresh()->total_runs + 1
        );
        $scoring->syncDeliveries($inn2->id, [
            array_merge($this->ball($inn2, 1, 1), [
                'batsman_id'     => $this->awayXI[0]->id,
                'non_striker_id' => $this->awayXI[1]->id,
                'bowler_id'      => $this->homeXI[8]->id,
                'runs_scored'    => 6,
                'is_six'         => true,
            ]),
        ]);

        $scoring->finalizeMatch($this->match);
        $this->match->refresh();

        $this->assertSame($this->away->id, (int) $this->match->winner_id);
        $this->assertSame('wickets', $this->match->result_type);
        $this->assertSame(10, (int) $this->match->result_margin, 'An 11-player XI has ten wickets to lose.');
    }

    #[Test]
    public function level_scores_are_reported_as_a_tie(): void
    {
        $scoring = app(ScoringService::class);

        $inn1 = $scoring->startInnings($this->match->id, $this->home->id, $this->away->id, 1);
        $scoring->syncDeliveries($inn1->id, [
            $this->ball($inn1, 1, 1, ['runs_scored' => 4, 'is_four' => true]),
        ]);

        $inn2 = $scoring->startInnings($this->match->id, $this->away->id, $this->home->id, 2, 5);
        $scoring->syncDeliveries($inn2->id, [
            array_merge($this->ball($inn2, 1, 1), [
                'batsman_id'     => $this->awayXI[0]->id,
                'non_striker_id' => $this->awayXI[1]->id,
                'bowler_id'      => $this->homeXI[8]->id,
                'runs_scored'    => 4,
                'is_four'        => true,
            ]),
        ]);

        $scoring->finalizeMatch($this->match);
        $this->match->refresh();

        $this->assertNull($this->match->winner_id);
        $this->assertSame('tie', $this->match->result_type);
    }

    #[Test]
    public function the_points_table_charges_a_bowled_out_side_the_full_quota(): void
    {
        $stats = app(MatchStatisticsService::class);

        $inn1 = app(ScoringService::class)->startInnings(
            $this->match->id, $this->home->id, $this->away->id, 1
        );

        // Ten wickets inside two overs — a genuine collapse.
        $balls = [];
        for ($i = 0; $i < 10; $i++) {
            $balls[] = $this->ball($inn1, intdiv($i, 6) + 1, ($i % 6) + 1, [
                'batsman_id'    => $this->homeXI[$i]->id,
                'is_wicket'     => true,
                'wicket_type'   => 'bowled',
                'out_player_id' => $this->homeXI[$i]->id,
            ]);
        }
        app(ScoringService::class)->syncDeliveries($inn1->id, $balls);

        $this->assertSame(10, (int) $inn1->fresh()->total_wickets);

        $result = $stats->resolveResult($this->match->fresh(['innings']));
        $this->assertSame('no_result', $result['result_type'], 'One innings alone cannot decide a match.');
    }
}
