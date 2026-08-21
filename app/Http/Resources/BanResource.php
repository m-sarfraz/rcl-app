<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A bowling-action ban. Fans see these on the "Banned Bowlers" screen; the
 * scoring console uses `is_active` to lock the player out of the bowler picker.
 */
class BanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'player_id'    => $this->player_id,
            'player'       => new PlayerResource($this->whenLoaded('player')),
            'reason'       => $this->reason,
            'banned_from'  => $this->banned_from?->toDateString(),
            'banned_until' => $this->banned_until?->toDateString(),
            'is_active'    => (bool) $this->is_active,
            'is_indefinite'=> (bool) $this->is_active && $this->banned_until === null,
            'notes'        => $this->notes,
        ];
    }
}
