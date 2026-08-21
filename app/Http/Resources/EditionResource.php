<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditionResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'edition_number' => (int) $this->edition_number,
            'host_village'   => $this->host_village,
            'thumbnail_url'  => $this->mediaUrl($this->thumbnail),
            'banner_url'     => $this->mediaUrl($this->banner),
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
            'status'         => $this->status,
            'description'    => $this->description,
            'is_current'     => (bool) $this->is_current,
            'teams_count'    => $this->whenCounted('teams'),
            'matches_count'  => $this->whenCounted('matches'),
            'teams'          => TeamResource::collection($this->whenLoaded('teams')),
        ];
    }
}
