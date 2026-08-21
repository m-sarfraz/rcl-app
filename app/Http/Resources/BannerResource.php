<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'subtitle'      => $this->subtitle,
            'image_url'     => $this->mediaUrl($this->image_path),
            'link_url'      => $this->link_url,
            'display_order' => (int) ($this->display_order ?? 0),
        ];
    }
}
