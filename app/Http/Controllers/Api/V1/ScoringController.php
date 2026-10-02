<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FinalizeMatchRequest;
use App\Http\Requests\Api\RecordBallRequest;
use App\Http\Requests\Api\SaveSquadRequest;
use App\Http\Requests\Api\StartInningsRequest;
use App\Http\Requests\Api\SyncBallsRequest;
use App\Http\Requests\Api\TossRequest;
use App\Http\Requests\Api\VerifyPasskeyRequest;
use App\Http\Resources\BallResource;
use App\Http\Resources\InningsResource;
use App\Http\Resources\MatchDetailResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\PlayerResource;
use App\Models\BallByBallLog;
use App\Models\CricketMatch;
use App\Models\Innings;
use App\Models\MatchSquad;
use App\Models\Player;
use App\Models\PlayerEditionTeam;
use App\Services\MatchStatisticsService;
use App\Services\ScoringAccessService;
use App\Services\ScoringService;
use App\Support\ApiResponse;
use App\Support\Overs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The scoring surface. Everything here except `verify` sits behind the
 * `scoring.key` middleware, and this is now the *only* way a match can be
 * scored — the Laravel admin console and the web console were both retired so
 * scoring authority lives solely in the React Native app.
 */
class ScoringController extends Controller
{
    public function __construct(
        private readonly ScoringService $scoring,
        private readonly ScoringAccessService $access,
        private readonly MatchStatisticsService $stats,
    ) {}

    /* ══ Access ═══════════════════════════════════════════════════════ */

    /** Exchange the 10-character passkey for a time-limited scoring token. */
    public function verify(VerifyPasskeyRequest $request): JsonResponse
    {
        if (! $this->access->isConfigured()) {
            return ApiResponse::error(
                'Scoring is not configured yet. Ask an administrator to set the scoring passkey.',
                503, [], 'scoring_not_configured'
            );
        }

        if (! $this->access->verify($request->string('passkey')->toString())) {
            return ApiResponse::error('Incorrect passkey.', 401, [], 'invalid_passkey');
        }

        return ApiResponse::success(
            $this->access->issueToken($request->input('label')),
            'Scoring unlocked.'
        );
    }

    /** Cheap check the app can run on launch to see if a stored token still works. */
    public function session(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'valid'  => true,
            'claims' => $request->attributes->get('scoring_claims'),
        ]);
    }

    /* ══ Match selection ══════════════════════════════════════════════ */

    /** Matches a scorer is allowed to open: upcoming or already live. */
    public function matches(): JsonResponse
    {
        $matches = CricketMatch::with(['homeTeam', 'awayTeam', 'edition', 'innings'])
            ->whereIn('status', ['upcoming', 'live'])
            ->orderByRaw("FIELD(status,'live','upcoming')")
            ->orderBy('scheduled_at')
            ->get();

        return ApiResponse::success(MatchResource::collection($matches));
    }

    /**
     * Everything the console needs before the first ball: both squads with
     * eligibility flags, any XI already named, and the innings in progress.
     */
    public function setup(CricketMatch $match): JsonResponse
    {
        $match->load(['homeTeam', 'awayTeam', 'edition', 'innings.battingTeam', 'innings.bowlingTeam']);

        return ApiResponse::success([
            'match'          => new MatchDetailResource($match),
            'home_squad'     => $this->availablePlayers($match, (int) $match->home_team_id),
            'away_squad'     => $this->availablePlayers($match, (int) $match->away_team_id),
            'named_xi'       => $this->namedXI($match),
            'innings'        => InningsResource::collection($match->innings->sortBy('innings_number')->values()),
            'current_innings'=> $this->currentInnings($match)?->id,
            'wickets_available' => [
                (string) $match->home_team_id => $this->wicketsAvailable($match, (int) $match->home_team_id),
                (string) $match->away_team_id => $this->wicketsAvailable($match, (int) $match->away_team_id),
            ],
        ]);
    }

    /* ══ Setup writes ═════════════════════════════════════════════════ */

    public function toss(TossRequest $request, CricketMatch $match): JsonResponse
    {
        $this->guardScoreable($match);

        $updated = $this->scoring->recordToss(
            $match,
            $request->integer('toss_winner_id'),
            $request->string('decision')->toString()
        );

        return ApiResponse::success(new MatchResource($updated), 'Toss recorded.');
    }

    public function saveSquad(SaveSquadRequest $request, CricketMatch $match): JsonResponse
    {
        $this->guardScoreable($match);

        $teamId  = $request->integer('team_id');
        $players = $request->input('players');

        $roster = PlayerEditionTeam::where('team_id', $teamId)
            ->where('edition_id', $match->edition_id)
            ->pluck('player_id')
            ->all();

        if ($roster) {
            $strays = collect($players)->pluck('player_id')
                ->reject(fn ($id) => in_array((int) $id, array_map('intval', $roster), true));

            if ($strays->isNotEmpty()) {
                return ApiResponse::error(
                    'Some selected players are not on this team\'s roster for this edition.',
                    422,
                    ['players' => $strays->values()->all()],
                    'player_not_on_roster'
                );
            }
        }

        $blocked = Player::whereIn('id', collect($players)->pluck('player_id'))
            ->with(['fines', 'suspensions'])
            ->get()
            ->map(fn ($p) => ['player_id' => $p->id, 'name' => $p->name, 'reason' => $p->ineligibilityReason()])
            ->filter(fn ($row) => $row['reason'] !== null)
            ->values();

        if ($blocked->isNotEmpty()) {
            return ApiResponse::error(
                'One or more selected players are not eligible.',
                422,
                ['ineligible' => $blocked->all()],
                'ineligible_players'
            );
        }

        $count = $this->scoring->saveSquad($match, $teamId, $players);

        return ApiResponse::success([
            'team_id'  => $teamId,
            'saved'    => $count,
            'named_xi' => $this->namedXI($match->fresh()),
        ], 'Playing XI saved.');
    }

    /**
     * Add a player to the team and match squad on the fly during scoring runtime.
     */
    public function addQuickPlayer(Request $request, CricketMatch $match): JsonResponse
    {
        $this->guardScoreable($match);

        $validated = $request->validate([
            'team_id'       => ['required', 'integer', Rule::in([$match->home_team_id, $match->away_team_id])],
            'name'          => ['required', 'string', 'min:2', 'max:100'],
            'role'          => ['nullable', 'string', 'in:batsman,bowler,all_rounder,wicket_keeper'],
            'jersey_number' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $teamId = (int) $validated['team_id'];
        $name   = trim($validated['name']);
        $role   = $validated['role'] ?? 'all_rounder';
        $jersey = $validated['jersey_number'] ?? null;

        $player = Player::create([
            'name'          => $name,
            'role'          => $role,
            'jersey_number' => $jersey,
            'is_active'     => true,
        ]);

        if ($match->edition_id) {
            PlayerEditionTeam::firstOrCreate([
                'player_id'  => $player->id,
                'team_id'    => $teamId,
                'edition_id' => $match->edition_id,
            ]);
        }

        $currentSquadCount = MatchSquad::where('match_id', $match->id)->where('team_id', $teamId)->count();
        MatchSquad::create([
            'match_id'         => $match->id,
            'team_id'          => $teamId,
            'player_id'        => $player->id,
            'batting_order'    => $currentSquadCount + 1,
            'is_captain'       => false,
            'is_wicket_keeper' => $role === 'wicket_keeper',
        ]);

        return ApiResponse::created([
            'player' => array_merge((new PlayerResource($player))->resolve($request), [
                'is_eligible'          => true,
                'ineligibility_reason' => null,
                'roster_jersey'        => $jersey,
            ]),
            'squad_member' => [
                'player_id'        => $player->id,
                'name'             => $player->name,
                'role'             => $player->role,
                'batting_order'    => $currentSquadCount + 1,
                'is_captain'       => false,
                'is_wicket_keeper' => $role === 'wicket_keeper',
            ],
            'named_xi' => $this->namedXI($match->fresh()),
        ], "Player {$player->name} added to squad.");
    }

    public function startInnings(StartInningsRequest $request, CricketMatch $match): JsonResponse
    {
        $this->guardScoreable($match);

        $inningsNumber = $request->integer('innings_number');
        $target        = $request->input('target');

        // Innings 2 chases innings 1; the second super over chases the first.
        if ($target === null && in_array($inningsNumber, [2, 4], true)) {
            $chasing = $match->innings()
                ->where('innings_number', $inningsNumber - 1)
                ->first();

            $target = $chasing ? (int) $chasing->total_runs + 1 : null;
        }

        if ($inningsNumber >= 3) {
            $match->update(['is_super_over' => true]);
        }

        $innings = $this->scoring->startInnings(
            $match->id,
            $request->integer('batting_team_id'),
            $request->integer('bowling_team_id'),
            $inningsNumber,
            $target
        );

        $innings->load(['battingTeam', 'bowlingTeam']);

        return ApiResponse::created(new InningsResource($innings), 'Innings started.');
    }

    /* ══ Ball-by-ball ═════════════════════════════════════════════════ */

    public function recordBall(RecordBallRequest $request, Innings $innings): JsonResponse
    {
        $this->guardInningsOpen($innings);

        $result = $this->scoring->recordDelivery($innings->id, $request->validated());

        return ApiResponse::created([
            'ball'              => new BallResource($result['ball']),
            'innings'           => new InningsResource($result['innings']->load(['battingTeam', 'bowlingTeam'])),
            'is_innings_over'   => $result['is_innings_over'],
            'current_run_rate'  => $result['current_run_rate'],
            'required_run_rate' => $result['required_run_rate'],
        ], 'Ball recorded.');
    }

    /** Replay the phone's offline queue. Safe to call repeatedly. */
    public function syncBalls(SyncBallsRequest $request, Innings $innings): JsonResponse
    {
        $result = $this->scoring->syncDeliveries($innings->id, $request->input('balls'));

        return ApiResponse::success([
            'accepted'        => $result['accepted'],
            'duplicates'      => $result['duplicates'],
            'innings'         => new InningsResource($result['innings']->load(['battingTeam', 'bowlingTeam'])),
            'is_innings_over' => $result['is_innings_over'],
        ], "Synced {$result['accepted']} ball(s), skipped {$result['duplicates']} duplicate(s).");
    }

    public function undoBall(Innings $innings): JsonResponse
    {
        $undone = $this->scoring->undoLastBall($innings->id);

        if (! $undone) {
            return ApiResponse::error('There is nothing left to undo in this innings.', 422, [], 'nothing_to_undo');
        }

        return ApiResponse::success([
            'innings' => new InningsResource($innings->fresh()->load(['battingTeam', 'bowlingTeam'])),
        ], 'Last ball undone.');
    }

    /** Full authoritative state for one match — used to resume a console. */
    public function state(CricketMatch $match): JsonResponse
    {
        $match->load([
            'homeTeam', 'awayTeam', 'edition',
            'innings.battingTeam', 'innings.bowlingTeam',
            'innings.battingScorecards' => fn ($q) => $q->orderBy('batting_position'),
            'innings.battingScorecards.player',
            'innings.bowlingScorecards.player',
        ]);

        $current = $this->currentInnings($match);

        $balls = $current
            ? BallByBallLog::where('innings_id', $current->id)->orderBy('id')->get()
            : collect();

        return ApiResponse::success([
            'match'           => new MatchDetailResource($match),
            'named_xi'        => $this->namedXI($match),
            'current_innings' => $current ? new InningsResource($current) : null,
            'balls'           => BallResource::collection($balls),
            'overs_display'   => $current ? Overs::display((int) $current->total_balls) : '0.0',
            'required_run_rate' => $current ? $this->scoring->requiredRunRate($current) : 0.0,
        ]);
    }

    /* ══ Finalisation ═════════════════════════════════════════════════ */

    /**
     * A dry-run summary the console shows in the confirmation sheet before the
     * scorer commits: innings breakdown, milestones, and who the engine would
     * pick as player of the match.
     */
    public function finalizePreview(CricketMatch $match): JsonResponse
    {
        $this->stats->rebuildMatch($match);
        $match->refresh()->load([
            'homeTeam', 'awayTeam',
            'innings.battingTeam', 'innings.bowlingTeam',
            'innings.battingScorecards.player', 'innings.bowlingScorecards.player',
        ]);

        $result = $this->stats->resolveResult($match);
        $impact = $this->stats->matchImpactPoints($match);
        $motmId = $this->stats->pickManOfTheMatch($match);

        return ApiResponse::success([
            'match'   => new MatchDetailResource($match),
            'result'  => $result,
            'innings' => $match->innings->sortBy('innings_number')->values()->map(fn ($inn) => [
                'innings_number' => (int) $inn->innings_number,
                'team'           => $inn->battingTeam?->name,
                'score'          => "{$inn->total_runs}/{$inn->total_wickets}",
                'overs'          => Overs::display((int) $inn->total_balls),
                'run_rate'       => round((float) $inn->run_rate, 2),
                'extras'         => (int) $inn->total_extras,
                'top_scorer'     => optional($inn->battingScorecards->sortByDesc('runs_scored')->first(), fn ($b) => [
                    'name'  => $b->player?->name,
                    'runs'  => (int) $b->runs_scored,
                    'balls' => (int) $b->balls_faced,
                ]),
                'best_bowler'    => optional($inn->bowlingScorecards->sortBy([['wickets', 'desc'], ['runs_conceded', 'asc']])->first(), fn ($b) => [
                    'name'    => $b->player?->name,
                    'wickets' => (int) $b->wickets,
                    'runs'    => (int) $b->runs_conceded,
                    'overs'   => Overs::display((int) $b->overs_bowled_balls),
                ]),
            ]),
            'milestones'   => $this->milestones($match),
            'impact_points'=> collect($impact)->take(5)->map(fn ($pts, $id) => [
                'player_id' => (int) $id,
                'name'      => Player::find($id)?->name,
                'points'    => round($pts, 2),
            ])->values(),
            'suggested_man_of_match' => $motmId ? [
                'player_id' => $motmId,
                'name'      => Player::find($motmId)?->name,
            ] : null,
        ]);
    }

    /** Seal the match and push every derived number into the edition tables. */
    public function finalize(FinalizeMatchRequest $request, CricketMatch $match): JsonResponse
    {
        if ($match->status === 'completed') {
            return ApiResponse::error('This match has already been finalised.', 409, [], 'already_finalized');
        }

        if ($match->innings()->count() === 0) {
            return ApiResponse::error('Cannot finalise a match with no innings.', 422, [], 'no_innings');
        }

        $outcome = $this->scoring->finalizeMatch($match, array_filter([
            'man_of_match_player_id' => $request->input('man_of_match_player_id'),
            'result_description'     => $request->input('result_description'),
            'notes'                  => $request->input('notes'),
            'finalized_by'           => $request->input('finalized_by'),
        ], fn ($v) => $v !== null));

        return ApiResponse::success([
            'match'           => new MatchResource($outcome['match']),
            'result'          => $outcome['result'],
            'man_of_match_id' => $outcome['man_of_match_id'],
            'players_updated' => $outcome['players_updated'],
        ], 'Match finalised — player and tournament statistics updated.');
    }

    /** Abandon a match without a result (rain, crowd trouble, no-show). */
    public function abandon(Request $request, CricketMatch $match): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:abandoned,postponed'],
        ]);

        $match->update([
            'status'             => $validated['status'] ?? 'abandoned',
            'result_type'        => 'no_result',
            'result_description' => $validated['reason'],
            'winner_id'          => null,
            'result_margin'      => 0,
        ]);

        $this->stats->syncEditionStatsForMatch($match->fresh());

        return ApiResponse::success(new MatchResource($match->fresh(['homeTeam', 'awayTeam'])), 'Match marked as no result.');
    }

    /* ══ Helpers ══════════════════════════════════════════════════════ */

    private function guardScoreable(CricketMatch $match): void
    {
        if (! in_array($match->status, ['upcoming', 'live'], true)) {
            abort(response()->json([
                'success' => false,
                'message' => 'This match is not open for scoring.',
                'code'    => 'match_not_scoreable',
            ], 422));
        }
    }

    private function guardInningsOpen(Innings $innings): void
    {
        if ($innings->is_completed) {
            abort(response()->json([
                'success' => false,
                'message' => 'This innings is already closed. Start the next innings or finalise the match.',
                'code'    => 'innings_closed',
            ], 422));
        }
    }

    private function currentInnings(CricketMatch $match): ?Innings
    {
        return $match->innings()
            ->where('is_completed', false)
            ->orderByDesc('innings_number')
            ->first()
            ?? $match->innings()->orderByDesc('innings_number')->first();
    }

    private function wicketsAvailable(CricketMatch $match, int $teamId): int
    {
        $squad = MatchSquad::where('match_id', $match->id)->where('team_id', $teamId)->count();

        return $squad > 1 ? $squad - 1 : 10;
    }

    /**
     * The full edition roster for a side, each entry flagged with why (if at
     * all) that player may not be picked. The console greys these out rather
     * than hiding them, so the scorer can see the reason.
     */
    private function availablePlayers(CricketMatch $match, int $teamId): array
    {
        $players = Player::whereHas(
            'rosterEntries',
            fn ($q) => $q->where('team_id', $teamId)->where('edition_id', $match->edition_id)
        )
            ->with(['fines', 'suspensions'])
            ->orderBy('name')
            ->get();

        if ($players->isEmpty()) {
            $players = Player::whereHas('rosterEntries', fn ($q) => $q->where('team_id', $teamId))
                ->with(['fines', 'suspensions'])
                ->orderBy('name')
                ->get();
        }

        $roster = PlayerEditionTeam::where('team_id', $teamId)
            ->where('edition_id', $match->edition_id)
            ->get()
            ->keyBy('player_id');

        return $players->map(function (Player $p) use ($roster) {
            $reason = $p->ineligibilityReason();

            return array_merge((new PlayerResource($p))->resolve(request()), [
                'is_eligible'         => $reason === null,
                'ineligibility_reason'=> $reason,
                'roster_jersey'       => $roster[$p->id]->jersey_number ?? $p->jersey_number,
            ]);
        })->all();
    }

    /** @return array<string, array<int, array<string, mixed>>> keyed by team id */
    private function namedXI(CricketMatch $match): array
    {
        return MatchSquad::where('match_id', $match->id)
            ->with('player')
            ->orderBy('batting_order')
            ->get()
            ->groupBy('team_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'player_id'        => $r->player_id,
                'name'             => $r->player?->name,
                'batting_order'    => (int) $r->batting_order,
                'is_captain'       => (bool) $r->is_captain,
                'is_wicket_keeper' => (bool) $r->is_wicket_keeper,
                'role'             => $r->player?->role,
            ])->values()->all())
            ->all();
    }

    /** Fifties, tons, five-fors, hat-tricks and the VCC six-hitting oddities. */
    private function milestones(CricketMatch $match): array
    {
        $out = [];

        foreach ($match->innings as $inn) {
            foreach ($inn->battingScorecards as $bat) {
                $name = $bat->player?->name ?? 'Unknown';
                if ($bat->is_century) {
                    $out[] = ['type' => 'century', 'player_id' => $bat->player_id, 'label' => "{$name} — {$bat->runs_scored} ({$bat->balls_faced})"];
                } elseif ($bat->is_fifty) {
                    $out[] = ['type' => 'fifty', 'player_id' => $bat->player_id, 'label' => "{$name} — {$bat->runs_scored} ({$bat->balls_faced})"];
                }
                if ($bat->hat_trick_sixes) {
                    $out[] = ['type' => 'hat_trick_sixes', 'player_id' => $bat->player_id, 'label' => "{$name} hit three sixes in a row"];
                }
                if ($bat->five_sixes_in_over) {
                    $out[] = ['type' => 'five_sixes_in_over', 'player_id' => $bat->player_id, 'label' => "{$name} hit five sixes in one over"];
                }
            }

            foreach ($inn->bowlingScorecards as $bowl) {
                $name = $bowl->player?->name ?? 'Unknown';
                if ($bowl->five_wicket_haul) {
                    $out[] = ['type' => 'five_wicket_haul', 'player_id' => $bowl->player_id, 'label' => "{$name} — {$bowl->wickets}/{$bowl->runs_conceded}"];
                }
                if ($bowl->hat_trick_wickets) {
                    $out[] = ['type' => 'hat_trick_wickets', 'player_id' => $bowl->player_id, 'label' => "{$name} took a hat-trick"];
                }
                if ($bowl->maidens > 0) {
                    $out[] = ['type' => 'maidens', 'player_id' => $bowl->player_id, 'label' => "{$name} bowled {$bowl->maidens} maiden over(s)"];
                }
            }
        }

        return $out;
    }
}
