<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'father_name'           => $this->father_name,
            'jersey_number'         => $this->jersey_number,
            'photo_url'             => $this->mediaUrl($this->photo),
            'role'                  => $this->role,
            'role_label'            => ucwords(str_replace('_', ' ', (string) $this->role)),
            'batting_style'         => $this->batting_style,
            'bowling_style'         => $this->bowling_style,
            'bowling_action_status' => $this->bowling_action_status,
            'is_active'             => (bool) $this->is_active,
            'team'                  => new TeamResource($this->whenLoaded('currentTeam')),
            'is_captain'            => $this->whenPivotLoaded('player_team_editions', fn () => (bool) $this->pivot->is_captain),
            'is_vice_captain'       => $this->whenPivotLoaded('player_team_editions', fn () => (bool) $this->pivot->is_vice_captain),
        ];
    }
}
