<?php

namespace App\Http\Resources;

use App\Support\Overs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BowlingScorecardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $balls = (int) $this->overs_bowled_balls;

        return [
            'id'                => $this->id,
            'player_id'         => $this->player_id,
            'player'            => new PlayerResource($this->whenLoaded('player')),
            'team_id'           => $this->team_id,
            'balls_bowled'      => $balls,
            'overs_display'     => Overs::display($balls),
            'overs_decimal'     => round((float) $this->overs_bowled, 2),
            'maidens'           => (int) $this->maidens,
            'runs_conceded'     => (int) $this->runs_conceded,
            'wickets'           => (int) $this->wickets,
            'wides'             => (int) $this->wides,
            'no_balls'          => (int) $this->no_balls,
            'economy'           => round((float) $this->economy, 2),
            'hat_trick_wickets' => (bool) $this->hat_trick_wickets,
            'five_wicket_haul'  => (bool) $this->five_wicket_haul,
        ];
    }
}
