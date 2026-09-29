<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemeritPointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'target_type'   => $this->target_type,
            'target_name'   => $this->entity_name,
            'player_id'     => $this->player_id,
            'player'        => $this->whenLoaded('player', fn() => $this->player ? new PlayerResource($this->player) : null),
            'team_id'       => $this->team_id,
            'team'          => $this->whenLoaded('team', fn() => $this->team ? new TeamResource($this->team) : null),
            'edition_id'    => $this->edition_id,
            'match_id'      => $this->match_id,
            'match_summary' => $this->match ? "Match #{$this->match->match_number}: {$this->match->homeTeam?->short_name} vs {$this->match->awayTeam?->short_name}" : null,
            'points'        => (int) $this->points,
            'reason'        => $this->reason,
            'incident_date' => $this->incident_date?->toDateString(),
            'is_active'     => (bool) $this->is_active,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
