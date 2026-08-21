<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'village_name'    => $this->village_name,
            'short_code'      => $this->short_code,
            'logo_url'        => $this->mediaUrl($this->logo),
            'cover_photo_url' => $this->mediaUrl($this->cover_photo),
            'primary_color'   => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'description'     => $this->description,
            'is_active'       => (bool) $this->is_active,
            'players_count'   => $this->whenCounted('players'),
            'group_number'    => $this->whenPivotLoaded('edition_teams', fn () => $this->pivot->group_number),
        ];
    }
}
