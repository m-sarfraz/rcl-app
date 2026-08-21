<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BattingScorecardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'player_id'          => $this->player_id,
            'player'             => new PlayerResource($this->whenLoaded('player')),
            'team_id'            => $this->team_id,
            'batting_position'   => $this->batting_position,
            'runs_scored'        => (int) $this->runs_scored,
            'balls_faced'        => (int) $this->balls_faced,
            'fours'              => (int) $this->fours,
            'sixes'              => (int) $this->sixes,
            'strike_rate'        => round((float) $this->strike_rate, 2),
            'dismissal_type'     => $this->dismissal_type,
            'is_out'             => $this->is_out,
            'dismissal_text'     => $this->dismissalText(),
            'bowled_by'          => new PlayerResource($this->whenLoaded('bowledBy')),
            'caught_by'          => new PlayerResource($this->whenLoaded('caughtBy')),
            'is_fifty'           => (bool) $this->is_fifty,
            'is_century'         => (bool) $this->is_century,
            'hat_trick_sixes'    => (bool) $this->hat_trick_sixes,
            'five_sixes_in_over' => (bool) $this->five_sixes_in_over,
        ];
    }

    private function dismissalText(): string
    {
        if ($this->dismissal_type === 'did_not_bat') {
            return 'did not bat';
        }
        if (! $this->is_out) {
            return 'not out';
        }

        $bowler  = $this->relationLoaded('bowledBy') ? $this->bowledBy?->name : null;
        $fielder = $this->relationLoaded('caughtBy') ? $this->caughtBy?->name : null;

        return match ($this->dismissal_type) {
            'bowled'  => $bowler ? "b {$bowler}" : 'bowled',
            'lbw'     => $bowler ? "lbw b {$bowler}" : 'lbw',
            'caught'  => trim(($fielder ? "c {$fielder} " : 'caught ').($bowler ? "b {$bowler}" : '')),
            'stumped' => trim(($fielder ? "st {$fielder} " : 'stumped ').($bowler ? "b {$bowler}" : '')),
            'run_out' => $fielder ? "run out ({$fielder})" : 'run out',
            'hit_wicket' => $bowler ? "hit wicket b {$bowler}" : 'hit wicket',
            'retired_hurt' => 'retired hurt',
            default   => str_replace('_', ' ', (string) $this->dismissal_type),
        };
    }
}
