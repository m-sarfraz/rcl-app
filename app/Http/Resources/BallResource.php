<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'innings_id'     => $this->innings_id,
            'over_number'    => (int) $this->over_number,
            'ball_number'    => (int) $this->ball_number,
            'batsman_id'     => $this->batsman_id,
            'batsman'        => new PlayerResource($this->whenLoaded('batsman')),
            'non_striker_id' => $this->non_striker_id,
            'bowler_id'      => $this->bowler_id,
            'bowler'         => new PlayerResource($this->whenLoaded('bowler')),
            'runs_scored'    => (int) $this->runs_scored,
            'extra_runs'     => (int) $this->extra_runs,
            'total_runs'     => (int) $this->runs_scored + (int) $this->extra_runs,
            'is_wide'        => (bool) $this->is_wide,
            'is_no_ball'     => (bool) $this->is_no_ball,
            'is_bye'         => (bool) $this->is_bye,
            'is_leg_bye'     => (bool) $this->is_leg_bye,
            'is_penalty'     => (bool) $this->is_penalty,
            'is_four'        => (bool) $this->is_four,
            'is_six'         => (bool) $this->is_six,
            'is_wicket'      => (bool) $this->is_wicket,
            'wicket_type'    => $this->wicket_type,
            'fielder_id'     => $this->fielder_id,
            'score_after'    => (int) $this->batting_team_score_after,
            'wickets_after'  => (int) $this->batting_team_wickets_after,
            'commentary'     => $this->commentary,
            'display'        => $this->display(),
        ];
    }

    /** Short chip label for the over-strip: "4", "W", "wd", "1nb"… */
    private function display(): string
    {
        if ($this->is_wicket) {
            return 'W';
        }
        if ($this->is_wide) {
            return 'wd'.($this->extra_runs > 1 ? ($this->extra_runs - 1) : '');
        }
        if ($this->is_no_ball) {
            return 'nb'.($this->runs_scored > 0 ? $this->runs_scored : '');
        }
        if ($this->is_bye) {
            return $this->extra_runs.'b';
        }
        if ($this->is_leg_bye) {
            return $this->extra_runs.'lb';
        }

        return (string) $this->runs_scored;
    }
}
