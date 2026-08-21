<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * Passkey gate for the mobile scoring console.
 *
 * The console lives entirely on the phone, so there is no session to lean on.
 * A scorer types the 10-character passkey once; we hand back an opaque, signed,
 * self-expiring token that every subsequent scoring write must carry.
 *
 * The token embeds a fingerprint of the passkey that minted it, so rotating the
 * key in the admin panel instantly invalidates every token already in the wild.
 */
class ScoringAccessService
{
    /** Setting key holding the plaintext passkey (admins need to read it out to scorers). */
    public const SETTING_KEY = 'scoring_secret_key';

    /** Shipped default — seeded, and changeable from Admin → Settings → Scoring Key. */
    public const DEFAULT_KEY = '&58+@@34AZ';

    /** How long a scoring session stays valid before the passkey must be re-entered. */
    public const TTL_HOURS = 12;

    public function currentKey(): ?string
    {
        $key = SiteSetting::get(self::SETTING_KEY);

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function isConfigured(): bool
    {
        return $this->currentKey() !== null;
    }

    /** Constant-time comparison — never a plain `!==`, this is the whole perimeter. */
    public function verify(string $candidate): bool
    {
        $stored = $this->currentKey();

        return $stored !== null && hash_equals($stored, $candidate);
    }

    public function issueToken(?string $label = null): array
    {
        $issuedAt  = Carbon::now();
        $expiresAt = $issuedAt->copy()->addHours(self::TTL_HOURS);

        $token = Crypt::encryptString(json_encode([
            'v'   => 1,
            'fp'  => $this->fingerprint(),
            'iat' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
            'lbl' => $label,
        ], JSON_THROW_ON_ERROR));

        return [
            'token'      => $token,
            'issued_at'  => $issuedAt->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => self::TTL_HOURS * 3600,
            'label'      => $label,
        ];
    }

    /** @return array{valid:bool, reason:?string, claims:?array} */
    public function inspectToken(?string $token): array
    {
        if (! $token) {
            return ['valid' => false, 'reason' => 'missing', 'claims' => null];
        }

        try {
            $claims = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return ['valid' => false, 'reason' => 'malformed', 'claims' => null];
        }

        if (! is_array($claims) || ! isset($claims['exp'], $claims['fp'])) {
            return ['valid' => false, 'reason' => 'malformed', 'claims' => null];
        }

        if (Carbon::now()->timestamp > (int) $claims['exp']) {
            return ['valid' => false, 'reason' => 'expired', 'claims' => $claims];
        }

        if (! hash_equals($this->fingerprint(), (string) $claims['fp'])) {
            return ['valid' => false, 'reason' => 'revoked', 'claims' => $claims];
        }

        return ['valid' => true, 'reason' => null, 'claims' => $claims];
    }

    private function fingerprint(): string
    {
        return hash('sha256', (string) $this->currentKey());
    }
}
