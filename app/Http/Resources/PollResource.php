<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Vote counts live in `poll_votes` rows — `poll_options` has no counter column.
 * Load options with `withCount('votes')` before handing a poll to this resource.
 */
class PollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $options = $this->relationLoaded('options')
            ? $this->options->sortBy('display_order')->values()
            : collect();

        $total = $options->sum(fn ($o) => (int) ($o->votes_count ?? 0));

        return [
            'id'          => $this->id,
            'question'    => $this->question,
            'description' => $this->description,
            'is_active'   => (bool) $this->is_active,
            'starts_at'   => $this->starts_at?->toIso8601String(),
            'ends_at'     => $this->ends_at?->toIso8601String(),
            'total_votes' => $total,
            'options'     => $options->map(fn ($o) => [
                'id'          => $o->id,
                'option_text' => $o->option_text,
                'votes'       => (int) ($o->votes_count ?? 0),
                'percentage'  => $total > 0 ? round(((int) ($o->votes_count ?? 0) / $total) * 100) : 0,
            ])->values(),
        ];
    }
}
