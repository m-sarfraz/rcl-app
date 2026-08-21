<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerStatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'player_id'           => $this->player_id,
            'player'              => new PlayerResource($this->whenLoaded('player')),
            'edition_id'          => $this->edition_id,
            'edition'             => new EditionResource($this->whenLoaded('edition')),
            'team_id'             => $this->team_id,
            'team'                => new TeamResource($this->whenLoaded('team')),

            'matches_played'      => (int) $this->matches_played,
            'innings_batted'      => (int) $this->innings_batted,
            'total_runs'          => (int) $this->total_runs,
            'highest_score'       => (int) $this->highest_score,
            'balls_faced'         => (int) $this->balls_faced,
            'not_outs'            => (int) $this->not_outs,
            'batting_average'     => round((float) $this->batting_average, 2),
            'batting_strike_rate' => round((float) $this->batting_strike_rate, 2),
            'fifties'             => (int) $this->fifties,
            'centuries'           => (int) $this->centuries,
            'total_fours'         => (int) $this->total_fours,
            'total_sixes'         => (int) $this->total_sixes,
            'hat_trick_sixes_count'    => (int) $this->hat_trick_sixes_count,
            'five_sixes_in_over_count' => (int) $this->five_sixes_in_over_count,

            'innings_bowled'      => (int) $this->innings_bowled,
            'overs_bowled'        => round((float) $this->overs_bowled, 2),
            'balls_bowled'        => (int) $this->balls_bowled,
            'runs_conceded'       => (int) $this->runs_conceded,
            'total_wickets'       => (int) $this->total_wickets,
            'total_maidens'       => (int) $this->total_maidens,
            'bowling_average'     => round((float) $this->bowling_average, 2),
            'bowling_economy'     => round((float) $this->bowling_economy, 2),
            'hat_trick_wickets_count' => (int) $this->hat_trick_wickets_count,
            'five_wicket_hauls'   => (int) $this->five_wicket_hauls,
            'best_bowling'        => $this->best_bowling_wickets > 0
                ? "{$this->best_bowling_wickets}/{$this->best_bowling_runs}"
                : null,

            'total_catches'       => (int) $this->total_catches,
            'total_run_outs'      => (int) $this->total_run_outs,
            'total_stumpings'     => (int) $this->total_stumpings,
            'mvp_count'           => (int) ($this->mvp_count ?? 0),
            'mvp_points'          => round((float) ($this->mvp_points ?? 0), 2),
        ];
    }
}
