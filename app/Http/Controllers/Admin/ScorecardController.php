<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Services\MatchSummaryService;

class ScorecardController extends Controller
{
    public function __construct(private readonly MatchSummaryService $summary) {}

    public function show(CricketMatch $match)
    {
        $data = $this->summary->generate($match->id);
        return view('admin.scorecard.show', $data);
    }

    public function print(CricketMatch $match)
    {
        $data = $this->summary->generate($match->id);
        return view('scorecard.printable', $data);
    }
}
