<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SponsorResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'tier'          => $this->tier,
            'logo_url'      => $this->mediaUrl($this->logo),
            'website'       => $this->website,
            'description'   => $this->description,
            'display_order' => (int) ($this->display_order ?? 0),
            'is_active'     => (bool) $this->is_active,
        ];
    }
}
