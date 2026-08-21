<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'player_id'      => $this->player_id,
            'player'         => new PlayerResource($this->whenLoaded('player')),
            'team_id'        => $this->team_id,
            'team'           => new TeamResource($this->whenLoaded('team')),
            'edition_id'     => $this->edition_id,
            'match_id'       => $this->match_id,
            'violation_type' => $this->violation_type,
            'violation_label'=> ucwords(str_replace('_', ' ', (string) $this->violation_type)),
            'card_type'      => $this->card_type,
            'description'    => $this->description,
            'amount'         => (float) $this->amount,
            'status'         => $this->status,
            'due_date'       => $this->due_date?->toDateString(),
            'paid_date'      => $this->paid_date?->toDateString(),
        ];
    }
}
