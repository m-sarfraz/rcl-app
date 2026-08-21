<?php

namespace Tests\Unit;

use App\Services\CalculationEngineService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The VCC run-rate rule is a house rule no other cricket software implements:
 * one ball counts as exactly 0.17 of an over, not one sixth. A regression here
 * silently corrupts every points table in the league, so it is pinned down.
 */
class CalculationEngineTest extends TestCase
{
    private CalculationEngineService $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new CalculationEngineService();
    }

    #[Test]
    public function one_ball_is_seventeen_hundredths_of_an_over(): void
    {
        $this->assertSame(0.17, $this->engine->ballsToDecimalOvers(1));
        $this->assertSame(0.34, $this->engine->ballsToDecimalOvers(2));
        $this->assertSame(0.85, $this->engine->ballsToDecimalOvers(5));
    }

    #[Test]
    public function complete_overs_are_whole_numbers(): void
    {
        $this->assertSame(1.0, $this->engine->ballsToDecimalOvers(6));
        $this->assertSame(10.0, $this->engine->ballsToDecimalOvers(60));
        $this->assertSame(0.0, $this->engine->ballsToDecimalOvers(0));
    }

    #[Test]
    public function the_spec_worked_example_holds(): void
    {
        // 5.3 overs = 5 + (3 × 0.17) = 5.51, and 120 runs off it = 21.78 rpo.
        $balls = (5 * 6) + 3;

        $this->assertSame(5.51, $this->engine->ballsToDecimalOvers($balls));
        $this->assertSame(21.78, $this->engine->calculateRunRate(120, $balls));
    }

    #[Test]
    public function overs_display_uses_conventional_notation(): void
    {
        $this->assertSame('5.3', $this->engine->formatOvers(33));
        $this->assertSame('10.0', $this->engine->formatOvers(60));
        $this->assertSame('0.0', $this->engine->formatOvers(0));
    }

    #[Test]
    public function run_rate_of_a_scoreless_innings_is_zero_not_a_division_error(): void
    {
        $this->assertSame(0.00, $this->engine->calculateRunRate(0, 0));
        $this->assertSame(0.00, $this->engine->calculateRunRate(15, 0));
    }

    #[Test]
    public function required_run_rate_is_zero_once_the_target_is_passed(): void
    {
        $this->assertSame(0.00, $this->engine->requiredRunRate(100, 100, 12));
        $this->assertSame(0.00, $this->engine->requiredRunRate(100, 120, 12));
        $this->assertSame(0.00, $this->engine->requiredRunRate(100, 80, 0));
    }

    #[Test]
    public function required_run_rate_uses_the_custom_over_fraction(): void
    {
        // 20 needed off 6 balls → 1.00 over → 20.00 rpo.
        $this->assertSame(20.00, $this->engine->requiredRunRate(100, 80, 6));

        // 20 needed off 3 balls → 0.51 overs → 39.22 rpo.
        $this->assertSame(39.22, $this->engine->requiredRunRate(100, 80, 3));
    }

    #[Test]
    public function net_run_rate_charges_the_full_quota_when_a_side_is_bowled_out(): void
    {
        // Bowled out for 60 in 5 overs, having conceded 61 in the full 10.
        $withoutRule = $this->engine->calculateNRR(60, 30, 61, 60, 10, false, false);
        $withRule    = $this->engine->calculateNRR(60, 30, 61, 60, 10, true, false);

        // Surviving only half the innings must not flatter the rate.
        $this->assertGreaterThan($withRule, $withoutRule);
        $this->assertSame(-0.10, $withRule);
    }

    #[Test]
    public function net_run_rate_is_zero_when_a_side_has_not_batted_or_bowled(): void
    {
        $this->assertSame(0.00, $this->engine->calculateNRR(0, 0, 50, 60));
        $this->assertSame(0.00, $this->engine->calculateNRR(50, 60, 0, 0));
    }

    #[Test]
    public function strike_rate_is_runs_per_hundred_balls(): void
    {
        $this->assertSame(150.00, $this->engine->strikeRate(30, 20));
        $this->assertSame(0.00, $this->engine->strikeRate(30, 0));
    }
}
