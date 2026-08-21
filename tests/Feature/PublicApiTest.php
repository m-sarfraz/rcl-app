<?php

namespace Tests\Feature;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Smoke cover for every public endpoint the mobile app calls.
 *
 * These exist because the API previously answered several of these URLs with a
 * 500 and nothing caught it — the envelope shape and the status code are the
 * contract the app is written against.
 */
class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;
    private Team $home;
    private Team $away;
    private CricketMatch $match;
    private Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::create([
            'name' => '35th Edition', 'edition_number' => 35,
            'host_village' => 'Testville', 'status' => 'active', 'is_current' => true,
        ]);

        $this->home = Team::create(['name' => 'Home CC', 'village_name' => 'Home', 'short_code' => 'HOM']);
        $this->away = Team::create(['name' => 'Away CC', 'village_name' => 'Away', 'short_code' => 'AWY']);
        $this->edition->teams()->attach([$this->home->id, $this->away->id]);

        $this->player = Player::create(['name' => 'Test Player', 'role' => 'batsman', 'is_active' => true]);
        PlayerEditionTeam::create([
            'player_id'  => $this->player->id,
            'team_id'    => $this->home->id,
            'edition_id' => $this->edition->id,
            'is_captain' => true,
        ]);

        $this->match = CricketMatch::create([
            'edition_id' => $this->edition->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'match_number' => '1', 'venue' => 'Test Ground',
            'scheduled_at' => now()->addDay(), 'status' => 'upcoming',
            'overs_per_side' => 10,
        ]);
    }

    public static function endpointProvider(): array
    {
        return [
            'root'         => ['/api/v1'],
            'health'       => ['/api/v1/health'],
            'home'         => ['/api/v1/home'],
            'ticker'       => ['/api/v1/ticker'],
            'settings'     => ['/api/v1/settings'],
            'schedule'     => ['/api/v1/schedule'],
            'editions'     => ['/api/v1/editions'],
            'teams'        => ['/api/v1/teams'],
            'players'      => ['/api/v1/players'],
            'stats'        => ['/api/v1/stats'],
            'leaderboards' => ['/api/v1/leaderboards'],
            'bans'         => ['/api/v1/bans'],
            'fines'        => ['/api/v1/fines'],
            'suspensions'  => ['/api/v1/suspensions'],
            'captains'     => ['/api/v1/captains'],
            'vcc'          => ['/api/v1/vcc'],
            'sponsors'     => ['/api/v1/sponsors'],
        ];
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('endpointProvider')]
    public function collection_endpoints_answer_in_the_standard_envelope(string $url): void
    {
        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data']);
    }

    #[Test]
    public function resource_endpoints_answer_for_a_known_id(): void
    {
        foreach ([
            "/api/v1/matches/{$this->match->id}",
            "/api/v1/matches/{$this->match->id}/live",
            "/api/v1/matches/{$this->match->id}/commentary",
            "/api/v1/editions/{$this->edition->id}",
            "/api/v1/editions/{$this->edition->id}/points-table",
            "/api/v1/editions/{$this->edition->id}/leaderboards",
            "/api/v1/teams/{$this->home->id}",
            "/api/v1/players/{$this->player->id}",
        ] as $url) {
            $this->getJson($url)->assertOk()->assertJsonPath('success', true);
        }
    }

    #[Test]
    public function an_unknown_id_returns_a_json_404_not_an_html_error_page(): void
    {
        $this->getJson('/api/v1/matches/999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'not_found');
    }

    #[Test]
    public function an_unknown_endpoint_returns_a_json_404(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function invalid_query_parameters_return_a_422_with_field_errors(): void
    {
        $this->getJson('/api/v1/schedule?status=not-a-status')
            ->assertStatus(422)
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonStructure(['errors' => ['status']]);
    }

    #[Test]
    public function paginated_endpoints_flatten_data_and_expose_meta(): void
    {
        $this->getJson('/api/v1/schedule')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'has_more'],
            ]);
    }

    #[Test]
    public function the_same_device_cannot_vote_twice_in_one_poll(): void
    {
        $poll = Poll::create([
            'edition_id' => $this->edition->id,
            'question'   => 'Who wins?',
            'starts_at'  => now()->subDay(),
            'ends_at'    => now()->addWeek(),
            'is_active'  => true,
        ]);

        $optionA = PollOption::create(['poll_id' => $poll->id, 'option_text' => 'Home', 'display_order' => 1]);
        PollOption::create(['poll_id' => $poll->id, 'option_text' => 'Away', 'display_order' => 2]);

        $payload = ['option_id' => $optionA->id, 'device_token' => 'device-abc'];

        $this->postJson("/api/v1/polls/{$poll->id}/vote", $payload)
            ->assertOk()
            ->assertJsonPath('data.total_votes', 1);

        $this->postJson("/api/v1/polls/{$poll->id}/vote", $payload)
            ->assertStatus(409)
            ->assertJsonPath('code', 'already_voted');
    }

    #[Test]
    public function a_vote_for_another_polls_option_is_rejected(): void
    {
        $pollA = Poll::create([
            'edition_id' => $this->edition->id, 'question' => 'A?',
            'starts_at' => now()->subDay(), 'ends_at' => now()->addWeek(), 'is_active' => true,
        ]);
        $pollB = Poll::create([
            'edition_id' => $this->edition->id, 'question' => 'B?',
            'starts_at' => now()->subDay(), 'ends_at' => now()->addWeek(), 'is_active' => true,
        ]);

        $foreign = PollOption::create(['poll_id' => $pollB->id, 'option_text' => 'X', 'display_order' => 1]);

        $this->postJson("/api/v1/polls/{$pollA->id}/vote", ['option_id' => $foreign->id])
            ->assertStatus(422)
            ->assertJsonPath('code', 'invalid_option');
    }

    #[Test]
    public function image_paths_leave_the_api_as_absolute_urls(): void
    {
        $this->home->update(['logo' => 'teams/home.png']);

        $response = $this->getJson("/api/v1/teams/{$this->home->id}");

        $response->assertOk();
        $this->assertStringStartsWith('http', $response->json('data.team.logo_url'));
        $this->assertStringContainsString('teams/home.png', $response->json('data.team.logo_url'));
    }
}
