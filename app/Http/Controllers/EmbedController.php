<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Services\ScoreboardImageService;
use App\Services\ScoreboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public, embeddable scoreboards.
 *
 * Three surfaces, one data source:
 *
 *  • **card**    — a self-contained scoreboard for embedding in a web page
 *  • **overlay** — a transparent broadcast lower-third, built to be a browser
 *                  source in PRISM Live Studio or OBS, so a stream going out to
 *                  Facebook carries the live score as it is typed on the phone
 *  • **ticker**  — a thin bar for the top or bottom of a page
 *
 * Everything here is anonymous and read-only. Nothing on these routes can
 * change a score — scoring stays behind the passkey in the mobile app.
 */
class EmbedController extends Controller
{
    /**
     * Layouts the widget understands, with the pixel size each is designed for.
     * `overlay` is kept as an alias for `lower` so links already handed out
     * keep working.
     */
    private const LAYOUTS = ['broadcast', 'lower', 'bug', 'scorecard', 'card', 'ticker'];

    private const ALIASES = ['overlay' => 'lower'];

    /** Layouts meant to sit over video, so they default to no background. */
    private const PANEL_LAYOUTS = ['broadcast', 'lower', 'bug', 'scorecard'];

    /**
     * The size each layout is drawn for, and what it is for. Used by the embed
     * builder and returned with the share links so the app and the web page
     * quote the same numbers.
     */
    public const SPECS = [
        'broadcast' => [
            'label'  => 'Broadcast bar',
            'width'  => 1920, 'height' => 140,
            'blurb'  => 'The full bottom bar — both sides, batters, bowler, this over and recent overs. What a televised match puts on screen.',
            'best'   => 'Live streaming, full width',
            'stream' => true,
        ],
        'lower' => [
            'label'  => 'Lower third',
            'width'  => 940, 'height' => 170,
            'blurb'  => 'A compact strip. Less detail than the bar, and it leaves more of the picture visible.',
            'best'   => 'Live streaming, corner or bottom',
            'stream' => true,
        ],
        'bug' => [
            'label'  => 'Score bug',
            'width'  => 420, 'height' => 160,
            'blurb'  => 'The small corner score, the way a TV channel keeps one up all match.',
            'best'   => 'Live streaming, top corner',
            'stream' => true,
        ],
        'scorecard' => [
            'label'  => 'Full scorecard',
            'width'  => 1920, 'height' => 1080,
            'blurb'  => 'A full-screen card: both innings, the crease, bowling and the chase. Cut to it between overs or at the innings break.',
            'best'   => 'Live streaming, full screen',
            'stream' => true,
        ],
        'card' => [
            'label'  => 'Website card',
            'width'  => 560, 'height' => 430,
            'blurb'  => 'A self-refreshing scoreboard for a web page. No plugin, no script tag.',
            'best'   => 'Embedding on a site',
            'stream' => false,
        ],
        'ticker' => [
            'label'  => 'Ticker strip',
            'width'  => 940, 'height' => 56,
            'blurb'  => 'A thin bar for the top or bottom of a page.',
            'best'   => 'Embedding on a site',
            'stream' => false,
        ],
    ];

    /** `auto` follows the viewer's own system setting. */
    private const THEMES = ['auto', 'light', 'dark'];

    public function __construct(
        private readonly ScoreboardService $scoreboard,
        private readonly ScoreboardImageService $image,
    ) {}

    /* ── The widget ────────────────────────────────────────────── */

    public function widget(Request $request, CricketMatch $match): Response
    {
        $requested = (string) $request->query('layout', 'broadcast');
        $requested = self::ALIASES[$requested] ?? $requested;

        $layout = in_array($requested, self::LAYOUTS, true) ? $requested : 'card';

        $options = [
            'layout'      => $layout,
            'theme'       => in_array($request->query('theme'), self::THEMES, true)
                ? $request->query('theme')
                : 'auto',
            'transparent' => $request->boolean('transparent', in_array($layout, self::PANEL_LAYOUTS, true)),
            'accent'      => $this->safeColour($request->query('accent')) ?? '#0EA47A',
            'refresh'     => max(3, min(60, (int) $request->query('refresh', 8))),
            'compact'     => $request->boolean('compact'),
            'showLogo'    => $request->boolean('logo', true),
        ];

        $html = view('embed.scoreboard', [
            'match'   => $match,
            'data'    => $this->scoreboard->snapshot($match),
            'options' => $options,
            'stateUrl'=> route('embed.state', $match),
        ])->render();

        return response($html)
            // Framing is the entire point of this route.
            ->header('Content-Security-Policy', 'frame-ancestors *')
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Cache-Control', 'public, max-age=5, s-maxage=5');
    }

    /* ── The data the widget polls ─────────────────────────────── */

    public function state(CricketMatch $match): JsonResponse
    {
        return response()
            ->json($this->scoreboard->snapshot($match))
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=5');
    }

    /* ── The picture a social crawler grabs ────────────────────── */

    public function image(CricketMatch $match): StreamedResponse
    {
        $png = $this->image->render($match);

        return response()->stream(
            fn () => print($png),
            200,
            [
                'Content-Type'   => 'image/png',
                'Content-Length' => (string) strlen($png),
                // Live matches must not be cached hard, or a shared link keeps
                // showing the score as it was when the crawler first looked.
                'Cache-Control'  => $match->status === 'live'
                    ? 'public, max-age=20, must-revalidate'
                    : 'public, max-age=3600',
            ],
        );
    }

    /* ── oEmbed, so platforms can discover the widget ──────────── */

    public function oembed(Request $request): JsonResponse
    {
        $url = (string) $request->query('url');

        if (! preg_match('#/(?:scorecard|embed/match)/(\d+)#', $url, $m)) {
            return response()->json(['error' => 'Unrecognised URL.'], 404);
        }

        $match = CricketMatch::with(['homeTeam', 'awayTeam'])->find((int) $m[1]);

        if (! $match) {
            return response()->json(['error' => 'Match not found.'], 404);
        }

        $width  = (int) $request->query('maxwidth', 560);
        $height = (int) $request->query('maxheight', 260);

        return response()->json([
            'version'       => '1.0',
            'type'          => 'rich',
            'provider_name' => 'Royal Champions League',
            'provider_url'  => url('/'),
            'title'         => sprintf(
                '%s v %s',
                $match->homeTeam?->name ?? 'TBD',
                $match->awayTeam?->name ?? 'TBD',
            ),
            'width'         => $width,
            'height'        => $height,
            'thumbnail_url' => route('embed.image', $match),
            'thumbnail_width'  => 1200,
            'thumbnail_height' => 630,
            'html' => sprintf(
                '<iframe src="%s" width="%d" height="%d" frameborder="0" scrolling="no" '
                .'style="border:0;max-width:100%%" title="RCL live scoreboard" allowtransparency="true"></iframe>',
                route('embed.widget', $match),
                $width,
                $height,
            ),
        ])->header('Access-Control-Allow-Origin', '*');
    }

    /* ── The page a scorer copies snippets from ────────────────── */

    public function builder(CricketMatch $match): Response
    {
        $match->load(['homeTeam', 'awayTeam', 'edition']);

        return response(view('embed.builder', [
            'match' => $match,
            'data'  => $this->scoreboard->snapshot($match),
            'specs' => self::SPECS,
        ])->render());
    }

    /** Only ever let a caller-supplied colour through as a real hex value. */
    private function safeColour(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $hex = '#'.ltrim($value, '#');

        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : null;
    }
}
