<?php

namespace App\Support;

use App\Services\CalculationEngineService;

/**
 * Thin static façade over the VCC calculation engine so Blade views and API
 * resources can format overs without pulling the service through constructors.
 */
final class Overs
{
    private static function engine(): CalculationEngineService
    {
        return app(CalculationEngineService::class);
    }

    /** Conventional cricket notation: 33 balls → "5.3". */
    public static function display(int $balls): string
    {
        return self::engine()->formatOvers(max(0, $balls));
    }

    /** VCC house rule: one ball is 0.17 of an over, so 33 balls → 5.51. */
    public static function decimal(int $balls): float
    {
        return self::engine()->ballsToDecimalOvers(max(0, $balls));
    }

    public static function runRate(int $runs, int $balls): float
    {
        return self::engine()->calculateRunRate($runs, max(0, $balls));
    }
}
