<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VccMemberResource extends JsonResource
{
    use HasMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'role_title'    => $this->role_title,
            'photo_url'     => $this->mediaUrl($this->photo),
            'bio'           => $this->bio,
            'phone'         => $this->phone,
            'village'       => $this->village,
            'display_order' => (int) ($this->display_order ?? 0),
            'is_active'     => (bool) $this->is_active,
        ];
    }
}
