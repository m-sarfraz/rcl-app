<?php

namespace App\Http\Middleware;

use App\Services\ScoringAccessService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every scoring write. Accepts the token from `Authorization: Bearer …`
 * or an `X-Scoring-Token` header, and — as a convenience for one-shot calls —
 * the raw passkey in `X-Scoring-Key`.
 */
class VerifyScoringToken
{
    public function __construct(private readonly ScoringAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->access->isConfigured()) {
            return ApiResponse::error(
                'Scoring is not configured. An administrator must set the scoring passkey first.',
                503, [], 'scoring_not_configured'
            );
        }

        // Raw-passkey shortcut, still constant-time compared.
        $rawKey = $request->header('X-Scoring-Key');
        if ($rawKey && $this->access->verify($rawKey)) {
            return $next($request);
        }

        $token = $request->bearerToken()
            ?: $request->header('X-Scoring-Token')
            ?: $request->input('scoring_token');

        $result = $this->access->inspectToken($token);

        if ($result['valid']) {
            $request->attributes->set('scoring_claims', $result['claims']);

            return $next($request);
        }

        return ApiResponse::error(
            match ($result['reason']) {
                'expired' => 'Your scoring session has expired. Enter the passkey again.',
                'revoked' => 'The scoring passkey has changed. Enter the new passkey.',
                'missing' => 'Scoring access required. Enter the passkey to unlock scoring.',
                default   => 'Invalid scoring credentials.',
            },
            401,
            [],
            'scoring_'.$result['reason']
        );
    }
}
