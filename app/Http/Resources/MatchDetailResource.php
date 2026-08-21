<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Full scorecard payload — the list resource plus every innings, scorecard and
 * (optionally) the ball-by-ball log.
 */
class MatchDetailResource extends MatchResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'notes'   => $this->notes,

            /*
             | Links for sharing this match and for broadcasting it. Detail only
             | — putting seven URLs on every row of a fifty-match list is pure
             | payload for something no list screen reads.
             |
             | `overlay_url` is what goes into a PRISM Live Studio / OBS browser
             | source so a Facebook Live stream carries the score as it is typed.
             */
            'share' => [
                'scorecard_url' => route('scorecard', $this->resource),
                'builder_url'   => route('share', $this->resource),
                'embed_url'     => route('embed.widget', $this->resource),
                'overlay_url'   => route('embed.widget', $this->resource).'?layout=overlay&transparent=1',
                'ticker_url'    => route('embed.widget', $this->resource).'?layout=ticker',
                'image_url'     => route('embed.image', $this->resource),
                'state_url'     => route('embed.state', $this->resource),

                /* One entry per layout: URL, pixel size and what it is for. */
                'layouts' => collect(\App\Http\Controllers\EmbedController::SPECS)
                    ->map(fn (array $spec, string $key) => [
                        'key'    => $key,
                        'label'  => $spec['label'],
                        'blurb'  => $spec['blurb'],
                        'best'   => $spec['best'],
                        'stream' => $spec['stream'],
                        'width'  => $spec['width'],
                        'height' => $spec['height'],
                        'url'    => route('embed.widget', $this->resource)
                            .'?layout='.$key
                            .($spec['stream'] ? '&transparent=1' : ''),
                    ])
                    ->values()
                    ->all(),
            ],
            'innings' => InningsResource::collection(
                $this->whenLoaded('innings', fn () => $this->innings->sortBy('innings_number')->values())
            ),
        ]);
    }
}
