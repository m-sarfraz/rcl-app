<?php

namespace App\Http\Resources;

use App\Support\Overs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * List-level match payload: enough for a fixture card, never the full scorecard.
 */
class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'edition_id'         => $this->edition_id,
            'edition'            => new EditionResource($this->whenLoaded('edition')),
            'match_number'       => $this->match_number,
            'match_type'         => $this->match_type,
            'match_type_label'   => ucwords(str_replace('_', ' ', (string) $this->match_type)),
            'venue'              => $this->venue,
            'scheduled_at'       => $this->scheduled_at?->timezone('Asia/Karachi')->toIso8601String(),
            'scheduled_time'     => $this->scheduled_at?->timezone('Asia/Karachi')->format('h:i A'),
            'scheduled_date'     => $this->scheduled_at?->timezone('Asia/Karachi')->format('d M Y'),
            'status'             => $this->status,
            'overs_per_side'     => (int) $this->overs_per_side,
            'notes'              => $this->notes,

            'home_team'          => new TeamResource($this->whenLoaded('homeTeam')),
            'away_team'          => new TeamResource($this->whenLoaded('awayTeam')),

            'toss_winner_id'     => $this->toss_winner_id,
            'toss_decision'      => $this->toss_decision,
            'toss_summary'       => $this->tossSummary(),

            'winner_id'          => $this->winner_id,
            'winner'             => new TeamResource($this->whenLoaded('winner')),
            'result_type'        => $this->result_type,
            'result_margin'      => $this->result_margin,
            'result_description' => $this->result_description,

            'man_of_match_player_id' => $this->man_of_match_player_id ? (int) $this->man_of_match_player_id : null,
            'is_super_over'      => (bool) $this->is_super_over,

            'innings_summary'    => $this->when(
                $this->relationLoaded('innings'),
                fn () => $this->innings->sortBy('innings_number')->values()->map(fn ($inn) => [
                    'innings_number'  => (int) $inn->innings_number,
                    'batting_team_id' => $inn->batting_team_id,
                    'total_runs'      => (int) $inn->total_runs,
                    'total_wickets'   => (int) $inn->total_wickets,
                    'total_balls'     => (int) $inn->total_balls,
                    'overs_display'   => Overs::display((int) $inn->total_balls),
                    'is_completed'    => (bool) $inn->is_completed,
                ])
            ),
        ];
    }

    private function tossSummary(): ?string
    {
        if (! $this->toss_winner_id || ! $this->toss_decision) {
            return null;
        }

        $name = $this->relationLoaded('tossWinner') && $this->tossWinner
            ? $this->tossWinner->name
            : ($this->toss_winner_id === $this->home_team_id
                ? ($this->relationLoaded('homeTeam') ? $this->homeTeam?->name : null)
                : ($this->relationLoaded('awayTeam') ? $this->awayTeam?->name : null));

        return $name ? "{$name} won the toss and chose to {$this->toss_decision}" : null;
    }
}
