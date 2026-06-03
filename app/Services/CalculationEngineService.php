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

    public function calculateNRR(
        int $runsScored,
        int $ballsFaced,
        int $runsConced,
        int $ballsBowled,
        int $oversPerSide = 10
    ): float {
        $oversForBalls = $this->ballsToDecimalOvers($ballsFaced);
        $oversAgainstBalls = $this->ballsToDecimalOvers($ballsBowled);

        // If team was bowled out, count full allotted overs
        $oversFor = max($oversForBalls, 0.01);
        $oversAgainst = max($oversAgainstBalls, 0.01);

        $rrFor = round($runsScored / $oversFor, 4);
        $rrAgainst = round($runsConced / $oversAgainst, 4);

        return round($rrFor - $rrAgainst, 2);
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
