<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Edition;
use App\Models\Fine;
use App\Models\FinanceTransaction;
use App\Models\Player;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $currentEdition = Edition::where('is_current', true)->first();

        $stats = [
            'teams'            => Team::count(),
            'players'          => Player::count(),
            'live_matches'     => CricketMatch::where('status', 'live')->count(),
            'upcoming_matches' => CricketMatch::where('status', 'upcoming')->count(),
            'total_fines'      => Fine::where('status', 'unpaid')->count(),
        ];

        $liveMatches = CricketMatch::with(['homeTeam', 'awayTeam', 'innings'])
            ->where('status', 'live')
            ->latest()
            ->limit(5)
            ->get();

        $recentMatches = CricketMatch::with(['homeTeam', 'awayTeam', 'edition'])
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        $financeBalance = null;
        if ($currentEdition) {
            $income  = FinanceTransaction::where('edition_id', $currentEdition->id)->where('type', 'income')->sum('amount');
            $expense = FinanceTransaction::where('edition_id', $currentEdition->id)->where('type', 'expense')->sum('amount');
            $financeBalance = ['income' => $income, 'expense' => $expense, 'balance' => $income - $expense];
        }

        return view('admin.dashboard', compact('stats','liveMatches','recentMatches','currentEdition','financeBalance'));
    }
}
