<?php

namespace App\Services;

/**
 * VCC Custom Run Rate Calculation Engine
 *
 * VCC Rule: 1 ball = 0.17 overs (NOT 1/6 = 0.1666...)
 * Example: 5.3 overs (5 complete overs + 3 balls) = 5 + (3 * 0.17) = 5.51
 */
class CalculationEngineService
{
    private const BALL_FRACTION = 0.17;

    public function ballsToDecimalOvers(int $totalBalls): float
    {
        $completeOvers = intdiv($totalBalls, 6);
        $remainingBalls = $totalBalls % 6;

        return round($completeOvers + ($remainingBalls * self::BALL_FRACTION), 2);
    }

    public function decimalOversFromDotNotation(float $overs): float
    {
        $complete = (int) $overs;
        $balls = round(($overs - $complete) * 10);

        return round($complete + ($balls * self::BALL_FRACTION), 2);
    }

    public function calculateRunRate(int $runs, int $totalBalls): float
    {
        if ($totalBalls === 0) return 0.00;

        $decimalOvers = $this->ballsToDecimalOvers($totalBalls);

        if ($decimalOvers == 0) return 0.00;

        return round($runs / $decimalOvers, 2);
    }

    /**
     * Net run rate for a set of aggregated innings.
     *
     * When a side is bowled out it is charged the full allotted quota rather
     * than the overs it actually survived — otherwise a cheap collapse would
     * flatter the rate. Pass `$wasBowledOut`/`$opponentBowledOut` to apply it.
     */
    public function calculateNRR(
        int $runsScored,
        int $ballsFaced,
        int $runsConceded,
        int $ballsBowled,
        int $oversPerSide = 10,
        bool $wasBowledOut = false,
        bool $opponentBowledOut = false
    ): float {
        $quota = $this->ballsToDecimalOvers($oversPerSide * 6);

        $oversFor = $wasBowledOut
            ? max($this->ballsToDecimalOvers($ballsFaced), $quota)
            : $this->ballsToDecimalOvers($ballsFaced);

        $oversAgainst = $opponentBowledOut
            ? max($this->ballsToDecimalOvers($ballsBowled), $quota)
            : $this->ballsToDecimalOvers($ballsBowled);

        if ($oversFor <= 0 || $oversAgainst <= 0) {
            return 0.00;
        }

        return round(($runsScored / $oversFor) - ($runsConceded / $oversAgainst), 2);
    }

    public function formatOvers(int $totalBalls): string
    {
        $completeOvers = intdiv($totalBalls, 6);
        $remainingBalls = $totalBalls % 6;

        return "{$completeOvers}.{$remainingBalls}";
    }

    public function requiredRunRate(int $target, int $currentRuns, int $ballsRemaining): float
    {
        $runsNeeded = $target - $currentRuns;
        if ($runsNeeded <= 0 || $ballsRemaining <= 0) return 0.00;

        return $this->calculateRunRate($runsNeeded, $ballsRemaining);
    }

    public function strikeRate(int $runs, int $ballsFaced): float
    {
        if ($ballsFaced === 0) return 0.00;
        return round(($runs / $ballsFaced) * 100, 2);
    }
}
