<?php

namespace App\Http\Resources;

use App\Support\Overs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InningsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $balls = (int) $this->total_balls;

        return [
            'id'                => $this->id,
            'match_id'          => $this->match_id,
            'innings_number'    => (int) $this->innings_number,
            'batting_team_id'   => $this->batting_team_id,
            'batting_team'      => new TeamResource($this->whenLoaded('battingTeam')),
            'bowling_team_id'   => $this->bowling_team_id,
            'bowling_team'      => new TeamResource($this->whenLoaded('bowlingTeam')),
            'total_runs'        => (int) $this->total_runs,
            'total_wickets'     => (int) $this->total_wickets,
            'total_balls'       => $balls,
            'overs_display'     => Overs::display($balls),
            'overs_decimal'     => round((float) $this->overs_faced, 2),
            'run_rate'          => round((float) $this->run_rate, 2),
            'extras'            => [
                'wides'    => (int) $this->extras_wides,
                'no_balls' => (int) $this->extras_no_balls,
                'byes'     => (int) $this->extras_byes,
                'leg_byes' => (int) $this->extras_leg_byes,
                'penalty'  => (int) $this->extras_penalty,
                'total'    => (int) $this->total_extras,
            ],
            'is_completed'      => (bool) $this->is_completed,
            'target'            => $this->target ? (int) $this->target : null,
            'batting_scorecards' => BattingScorecardResource::collection($this->whenLoaded('battingScorecards')),
            'bowling_scorecards' => BowlingScorecardResource::collection($this->whenLoaded('bowlingScorecards')),
            'balls'             => BallResource::collection($this->whenLoaded('ballByBall')),
        ];
    }
}
