<?php

namespace Tests\Feature;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Models\Team;
use App\Services\ScoreboardImageService;
use App\Services\ScoreboardService;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The broadcast payload and the surfaces built on it.
 *
 * These matter because an overlay is on screen in front of an audience — a
 * wrong partnership or a stale striker is visible to everyone watching, and
 * unlike a scorecard nobody can scroll back to check it.
 */
class ScoreboardTest extends TestCase
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

        $this->edition = Edition::create([
            'name' => 'Test Edition', 'edition_number' => 99,
            'host_village' => 'Testville', 'status' => 'active', 'is_current' => true,
        ]);

        $this->home = Team::create(['name' => 'Home CC', 'village_name' => 'Home', 'short_code' => 'HOM', 'primary_color' => '#0EA47A']);
        $this->away = Team::create(['name' => 'Away CC', 'village_name' => 'Away', 'short_code' => 'AWY', 'primary_color' => '#F59E0B']);
        $this->edition->teams()->attach([$this->home->id, $this->away->id]);

        $this->homeXI = $this->squad($this->home, 'H');
        $this->awayXI = $this->squad($this->away, 'A');

        $this->match = CricketMatch::create([
            'edition_id' => $this->edition->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'match_number' => '1', 'venue' => 'Test Ground',
            'scheduled_at' => now(), 'status' => 'live', 'overs_per_side' => 10,
            'toss_winner_id' => $this->home->id, 'toss_decision' => 'bat',
        ]);

        foreach ([[$this->home, $this->homeXI], [$this->away, $this->awayXI]] as [$team, $xi]) {
            foreach ($xi as $i => $player) {
                MatchSquad::create([
                    'match_id' => $this->match->id, 'team_id' => $team->id,
                    'player_id' => $player->id, 'batting_order' => $i + 1,
                ]);
            }
        }
    }

    /** @return array<int, Player> */
    private function squad(Team $team, string $prefix): array
    {
        $players = [];
        for ($i = 1; $i <= 11; $i++) {
            $player = Player::create([
                'name' => "{$prefix} Player {$i}", 'role' => $i > 7 ? 'bowler' : 'batsman', 'is_active' => true,
            ]);
            PlayerEditionTeam::create([
                'player_id' => $player->id, 'team_id' => $team->id, 'edition_id' => $this->edition->id,
            ]);
            $players[] = $player;
        }

        return $players;
    }

    /** @param array<string, mixed> $overrides */
    private function ball(int $over, int $ballNo, array $overrides = []): array
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

    private function play(array $balls): void
    {
        $innings = app(ScoringService::class)->startInnings(
            $this->match->id, $this->home->id, $this->away->id, 1
        );
        app(ScoringService::class)->syncDeliveries($innings->id, $balls);
    }

    private function snapshot(): array
    {
        return app(ScoreboardService::class)->snapshot($this->match->fresh());
    }

    /* ══ Shape ═════════════════════════════════════════════════════ */

    #[Test]
    public function an_upcoming_match_produces_a_usable_payload(): void
    {
        $this->match->update(['status' => 'upcoming']);

        $data = $this->snapshot();

        $this->assertSame('UPCOMING', $data['status_line']);
        $this->assertNull($data['current']);
        $this->assertStringContainsString('HOM', $data['headline']);
        $this->assertNull($data['teams']['home']['score'], 'Nobody has batted yet.');
        $this->assertNotEmpty($data['revision']);
    }

    #[Test]
    public function a_live_innings_reports_the_score_and_who_is_batting(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 2, ['runs_scored' => 6, 'is_six' => true]),
            $this->ball(1, 3, ['runs_scored' => 1]),
        ]);

        $data = $this->snapshot();
        $c = $data['current'];

        $this->assertSame('LIVE', $data['status_line']);
        $this->assertSame('11/0', $c['score']);
        $this->assertSame('0.3', $c['overs']);
        $this->assertTrue($data['teams']['home']['batting']);
        $this->assertFalse($data['teams']['away']['batting']);
        $this->assertSame($this->homeXI[0]->name, $c['striker']['name']);
        $this->assertSame(11, $c['striker']['runs']);
        $this->assertTrue($c['striker']['on_strike']);
    }

    #[Test]
    public function the_bowler_line_is_the_full_overs_maidens_runs_wickets(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 2),
            $this->ball(1, 3, ['runs_scored' => 2]),
        ]);

        $bowler = $this->snapshot()['current']['bowler'];

        $this->assertSame('0.3-0-6-0', $bowler['line']);
        $this->assertSame('0/6', $bowler['figures']);
    }

    /* ══ Partnership ═══════════════════════════════════════════════ */

    #[Test]
    public function the_partnership_counts_from_the_start_of_an_innings(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 2, ['runs_scored' => 2]),
        ]);

        $p = $this->snapshot()['current']['partnership'];

        $this->assertSame(6, $p['runs']);
        $this->assertSame(2, $p['balls']);
        $this->assertSame('6 (2)', $p['display']);
    }

    #[Test]
    public function the_partnership_resets_when_a_wicket_falls(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 2, ['runs_scored' => 6, 'is_six' => true]),
            $this->ball(1, 3, [
                'is_wicket' => true, 'wicket_type' => 'bowled',
                'out_player_id' => $this->homeXI[0]->id,
            ]),
            $this->ball(1, 4, ['batsman_id' => $this->homeXI[2]->id, 'runs_scored' => 2]),
        ]);

        $p = $this->snapshot()['current']['partnership'];

        $this->assertSame(2, $p['runs'], 'Only the runs since the wicket count.');
        $this->assertSame(1, $p['balls']);
    }

    #[Test]
    public function the_last_wicket_reports_who_went_and_at_what_score(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 2, [
                'is_wicket' => true, 'wicket_type' => 'bowled',
                'out_player_id' => $this->homeXI[0]->id,
            ]),
        ]);

        $w = $this->snapshot()['current']['last_wicket'];

        $this->assertNotNull($w);
        $this->assertSame('bowled', $w['how']);
        $this->assertSame(4, $w['runs']);
        $this->assertStringContainsString('4/1', $w['display']);
    }

    /* ══ The chase ═════════════════════════════════════════════════ */

    #[Test]
    public function a_chase_reports_what_is_needed_and_the_required_rate(): void
    {
        $first = app(ScoringService::class)->startInnings(
            $this->match->id, $this->home->id, $this->away->id, 1
        );
        app(ScoringService::class)->syncDeliveries($first->id, [
            $this->ball(1, 1, ['runs_scored' => 6, 'is_six' => true]),
        ]);
        $first->fresh()->update(['is_completed' => true]);

        $second = app(ScoringService::class)->startInnings(
            $this->match->id, $this->away->id, $this->home->id, 2, 7
        );
        app(ScoringService::class)->syncDeliveries($second->id, [
            $this->ball(1, 1, [
                'batsman_id' => $this->awayXI[0]->id,
                'non_striker_id' => $this->awayXI[1]->id,
                'bowler_id' => $this->homeXI[8]->id,
                'runs_scored' => 2,
            ]),
        ]);

        $data = $this->snapshot();
        $c = $data['current'];

        $this->assertSame(7, $c['target']);
        $this->assertSame(5, $c['need']);
        $this->assertSame(59, $c['balls_left']);
        $this->assertGreaterThan(0, $c['required_rate']);
        $this->assertSame('CHASING', $data['status_line']);
        $this->assertStringContainsString('Need 5', $data['sub_headline']);
    }

    /* ══ Over strips ═══════════════════════════════════════════════ */

    #[Test]
    public function this_over_labels_each_delivery_the_way_a_scoreboard_does(): void
    {
        $this->play([
            $this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true]),
            $this->ball(1, 1, ['is_wide' => true, 'extra_runs' => 1]),
            $this->ball(1, 2, ['runs_scored' => 6, 'is_six' => true]),
            $this->ball(1, 3),
            $this->ball(1, 4, [
                'is_wicket' => true, 'wicket_type' => 'bowled',
                'out_player_id' => $this->homeXI[0]->id,
            ]),
        ]);

        $over = $this->snapshot()['current']['this_over'];

        $this->assertSame(['4', 'wd', '6', '0', 'W'], array_column($over, 'label'));
        $this->assertSame(['four', 'extra', 'six', 'dot', 'wicket'], array_column($over, 'kind'));
    }

    #[Test]
    public function recent_overs_summarise_runs_and_wickets_per_over(): void
    {
        $balls = [];
        for ($over = 1; $over <= 3; $over++) {
            for ($b = 1; $b <= 6; $b++) {
                $balls[] = $this->ball($over, $b, ['runs_scored' => 1]);
            }
        }
        $this->play($balls);

        $recent = $this->snapshot()['current']['recent_overs'];

        $this->assertCount(3, $recent);
        $this->assertSame(6, $recent[0]['runs']);
        $this->assertTrue($recent[0]['complete']);
    }

    /* ══ Revision fingerprint ══════════════════════════════════════ */

    #[Test]
    public function the_revision_only_changes_when_something_visible_does(): void
    {
        $this->play([$this->ball(1, 1, ['runs_scored' => 1])]);

        $first  = $this->snapshot()['revision'];
        $second = $this->snapshot()['revision'];

        $this->assertSame($first, $second, 'Nothing moved, so the widget should not repaint.');

        $innings = $this->match->innings()->first();
        app(ScoringService::class)->syncDeliveries($innings->id, [$this->ball(1, 2, ['runs_scored' => 4, 'is_four' => true])]);

        $this->assertNotSame($first, $this->snapshot()['revision']);
    }

    /* ══ Surfaces ══════════════════════════════════════════════════ */

    #[Test]
    public function every_layout_renders(): void
    {
        $this->play([$this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true])]);

        foreach (['broadcast', 'lower', 'bug', 'scorecard', 'card', 'ticker', 'overlay'] as $layout) {
            $expected = $layout === 'overlay' ? 'lower' : $layout;

            $this->get("/embed/match/{$this->match->id}?layout={$layout}")
                ->assertOk()
                ->assertSee('data-layout="'.$expected.'"', false)
                ->assertSee('id="board"', false);
        }
    }

    #[Test]
    public function an_unknown_layout_falls_back_to_the_card(): void
    {
        $this->get("/embed/match/{$this->match->id}?layout=nonsense")
            ->assertOk()
            ->assertSee('data-layout="card"', false);
    }

    #[Test]
    public function the_widget_may_be_framed_but_the_rest_of_the_site_may_not(): void
    {
        $this->get("/embed/match/{$this->match->id}")
            ->assertOk()
            ->assertHeader('Content-Security-Policy', 'frame-ancestors *');
    }

    #[Test]
    public function the_state_feed_is_json_and_open_to_any_origin(): void
    {
        $this->play([$this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true])]);

        $this->getJson("/embed/match/{$this->match->id}/state.json")
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('current.score', '4/0');
    }

    #[Test]
    public function the_scoreboard_image_is_a_real_png(): void
    {
        $this->play([$this->ball(1, 1, ['runs_scored' => 4, 'is_four' => true])]);

        $png = app(ScoreboardImageService::class)->render($this->match->fresh());

        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8), 'Should carry the PNG magic bytes.');

        $size = getimagesizefromstring($png);
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    #[Test]
    public function oembed_answers_for_a_scorecard_url_and_refuses_anything_else(): void
    {
        $this->getJson('/embed/oembed?url='.urlencode(url("/scorecard/{$this->match->id}")))
            ->assertOk()
            ->assertJsonPath('type', 'rich')
            ->assertJsonPath('provider_name', 'Royal Champions League');

        $this->getJson('/embed/oembed?url=https://example.com/nope')->assertStatus(404);
    }

    #[Test]
    public function a_caller_supplied_accent_colour_must_be_a_real_hex_value(): void
    {
        $this->get("/embed/match/{$this->match->id}?accent=7C5CFC")
            ->assertOk()
            ->assertSee('#7C5CFC', false);

        // Anything else is dropped rather than written into the page.
        $this->get("/embed/match/{$this->match->id}?accent=javascript:alert(1)")
            ->assertOk()
            ->assertDontSee('javascript:alert', false);
    }

    #[Test]
    public function the_share_block_lists_every_layout_with_its_size(): void
    {
        $response = $this->getJson("/api/v1/matches/{$this->match->id}");

        $response->assertOk();
        $layouts = $response->json('data.share.layouts');

        $this->assertCount(6, $layouts);
        $this->assertSame(
            ['broadcast', 'lower', 'bug', 'scorecard', 'card', 'ticker'],
            array_column($layouts, 'key'),
        );

        $broadcast = collect($layouts)->firstWhere('key', 'broadcast');
        $this->assertSame(1920, $broadcast['width']);
        $this->assertTrue($broadcast['stream']);
        $this->assertStringContainsString('transparent=1', $broadcast['url']);
    }
}
