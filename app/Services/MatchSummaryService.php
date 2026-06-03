<?php

namespace App\Services;

use App\Models\CricketMatch;

class MatchSummaryService
{
    public function generate(int $matchId): array
    {
        $match = CricketMatch::with([
            'edition', 'homeTeam', 'awayTeam', 'winner',
            'innings.battingTeam',
            'innings.bowlingTeam',
            'innings.battingScorecards.player',
            'innings.battingScorecards.bowledBy',
            'innings.bowlingScorecards.player',
        ])->findOrFail($matchId);

        return [
            'match'  => $match,
            'inn1'   => $match->innings->firstWhere('innings_number', 1),
            'inn2'   => $match->innings->firstWhere('innings_number', 2),
        ];
    }

    /** Build plain-HTML scorecard for browser printing / screenshot */
    public function htmlScorecard(int $matchId): string
    {
        $data = $this->generate($matchId);
        return view('scorecard.printable', $data)->render();
    }
}
