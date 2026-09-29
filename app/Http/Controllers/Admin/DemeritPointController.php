<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\DemeritPoint;
use App\Models\Edition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemeritPointController extends Controller
{
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'player'); // 'player', 'team', 'umpire', 'all', or custom tab
        $editionId  = $request->query('edition_id');
        $search     = $request->query('search');

        // Fetch distinct categories / tabs currently existing in DB
        $dbCategories = DemeritPoint::distinct()->pluck('target_type')->toArray();
        $allCategories = array_values(array_unique(array_merge(['player', 'team', 'umpire'], $dbCategories)));

        // Incidents Query
        $query = DemeritPoint::with(['team', 'player', 'edition', 'match', 'issuedBy'])
            ->when($currentTab !== 'all', fn($q) => $q->where('target_type', $currentTab))
            ->when($editionId, fn($q) => $q->where('edition_id', $editionId))
            ->when($search, function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('reason', 'like', "%{$search}%")
                        ->orWhere('target_name', 'like', "%{$search}%")
                        ->orWhereHas('player', fn($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('team', fn($t) => $t->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('incident_date');

        $incidents = $query->paginate(25)->withQueryString();

        // Standings / Aggregated Points for current tab
        $standings = [];
        if ($currentTab === 'player') {
            // Aggregate points per player
            $playerTotals = DemeritPoint::active()
                ->where('target_type', 'player')
                ->when($editionId, fn($q) => $q->where('edition_id', $editionId))
                ->select('player_id', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(*) as incident_count'))
                ->groupBy('player_id')
                ->get()
                ->keyBy('player_id');

            $players = Player::with(['currentTeam', 'demeritPoints' => fn($q) => $q->active()])
                ->whereIn('id', $playerTotals->keys())
                ->get()
                ->map(function($p) use ($playerTotals) {
                    $pts = (int) ($playerTotals[$p->id]->total_points ?? 0);
                    return [
                        'id'             => $p->id,
                        'name'           => $p->name,
                        'father_name'    => $p->father_name,
                        'team_name'      => $p->currentTeam?->name ?? 'No Team',
                        'photo_url'      => $p->photo_url,
                        'total_points'   => $pts,
                        'incident_count' => (int) ($playerTotals[$p->id]->incident_count ?? 0),
                        'status'         => $pts >= DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                        'color'          => $pts >= DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                    ];
                })
                ->sortByDesc('total_points')
                ->values();

            $standings = $players;
        } elseif ($currentTab === 'team') {
            // All teams with their total points (teams with 0 points show green!)
            $teamTotals = DemeritPoint::active()
                ->where('target_type', 'team')
                ->when($editionId, fn($q) => $q->where('edition_id', $editionId))
                ->select('team_id', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(*) as incident_count'))
                ->groupBy('team_id')
                ->get()
                ->keyBy('team_id');

            $teams = Team::active()
                ->orderBy('name')
                ->get()
                ->map(function($t) use ($teamTotals) {
                    $pts = (int) ($teamTotals[$t->id]->total_points ?? 0);
                    return [
                        'id'             => $t->id,
                        'name'           => $t->name,
                        'short_name'     => $t->short_name,
                        'logo_url'       => $t->logo_url,
                        'total_points'   => $pts,
                        'incident_count' => (int) ($teamTotals[$t->id]->incident_count ?? 0),
                        'status'         => $pts >= DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                        'color'          => $pts >= DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                    ];
                })
                ->sortByDesc('total_points')
                ->values();

            $standings = $teams;
        } elseif ($currentTab === 'umpire' || ($currentTab !== 'all' && !in_array($currentTab, ['player', 'team']))) {
            // Entities in this custom tab
            $entityTotals = DemeritPoint::active()
                ->where('target_type', $currentTab)
                ->when($editionId, fn($q) => $q->where('edition_id', $editionId))
                ->select('target_name', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(*) as incident_count'))
                ->groupBy('target_name')
                ->get()
                ->map(function($item) {
                    $pts = (int) $item->total_points;
                    return [
                        'id'             => null,
                        'name'           => $item->target_name ?: 'Unnamed',
                        'total_points'   => $pts,
                        'incident_count' => (int) $item->incident_count,
                        'status'         => $pts >= DemeritPoint::THRESHOLD_BAN ? 'banned' : ($pts === 2 ? 'critical' : ($pts === 1 ? 'warning' : 'safe')),
                        'color'          => $pts >= DemeritPoint::THRESHOLD_BAN ? '#ef4444' : ($pts === 2 ? '#ef4444' : ($pts === 1 ? '#eab308' : '#22c55e')),
                    ];
                })
                ->sortByDesc('total_points')
                ->values();

            $standings = $entityTotals;
        }

        // Stats summary
        $totalActivePoints = DemeritPoint::active()->sum('points');
        $bannedPlayersCount = DB::table('demerit_points')
            ->where('target_type', 'player')
            ->where('is_active', true)
            ->groupBy('player_id')
            ->havingRaw('SUM(points) >= ?', [DemeritPoint::THRESHOLD_BAN])
            ->select('player_id')
            ->get()
            ->count();

        $bannedTeamsCount = DB::table('demerit_points')
            ->where('target_type', 'team')
            ->where('is_active', true)
            ->groupBy('team_id')
            ->havingRaw('SUM(points) >= ?', [DemeritPoint::THRESHOLD_BAN])
            ->select('team_id')
            ->get()
            ->count();

        $editions = Edition::orderByDesc('edition_number')->get();
        $ruleNote = DemeritPoint::RULE_NOTE;

        return view('admin.demerit_points.index', compact(
            'incidents',
            'standings',
            'currentTab',
            'allCategories',
            'editions',
            'totalActivePoints',
            'bannedPlayersCount',
            'bannedTeamsCount',
            'ruleNote'
        ));
    }

    public function create()
    {
        $teams = Team::active()->orderBy('name')->get();
        $editions = Edition::orderByDesc('edition_number')->get();
        $currentEdition = Edition::where('is_current', 1)->first() ?? $editions->first();
        $matches = CricketMatch::with(['homeTeam', 'awayTeam'])
            ->when($currentEdition, fn($q) => $q->where('edition_id', $currentEdition->id))
            ->orderByDesc('scheduled_at')
            ->get();

        // Existing categories
        $dbCategories = DemeritPoint::distinct()->pluck('target_type')->toArray();
        $existingCategories = array_values(array_unique(array_merge(['player', 'team', 'umpire'], $dbCategories)));

        return view('admin.demerit_points.create', compact('teams', 'editions', 'currentEdition', 'matches', 'existingCategories'));
    }

    public function store(Request $request)
    {
        $category = strtolower(trim($request->input('target_type', 'player')));

        // If user typed a custom tab name
        if ($category === 'custom') {
            $customCategory = strtolower(trim($request->input('custom_target_type', '')));
            if (empty($customCategory)) {
                return back()->withInput()->withErrors(['custom_target_type' => 'Please provide a name for the new tab / category.']);
            }
            $category = preg_replace('/[^a-z0-9_-]/', '', $customCategory);
        }

        $rules = [
            'edition_id'    => 'nullable|exists:editions,id',
            'points'        => 'required|integer|min:1|max:10',
            'reason'        => 'required|string|max:1000',
            'incident_date' => 'required|date',
            'match_id'      => 'nullable|exists:cricket_matches,id',
            'notes'         => 'nullable|string|max:2000',
        ];

        if ($category === 'player') {
            $rules['team_id']   = 'required|exists:teams,id';
            $rules['player_id'] = 'required|exists:players,id';
        } elseif ($category === 'team') {
            $rules['team_id']   = 'required|exists:teams,id';
        } else {
            $rules['target_name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $demerit = DemeritPoint::create([
            'edition_id'    => $validated['edition_id'] ?? null,
            'target_type'   => $category,
            'team_id'       => in_array($category, ['player', 'team']) ? ($validated['team_id'] ?? null) : null,
            'player_id'     => $category === 'player' ? ($validated['player_id'] ?? null) : null,
            'target_name'   => !in_array($category, ['player', 'team']) ? ($validated['target_name'] ?? null) : null,
            'match_id'      => $validated['match_id'] ?? null,
            'points'        => (int) $validated['points'],
            'reason'        => $validated['reason'],
            'incident_date' => $validated['incident_date'],
            'notes'         => $validated['notes'] ?? null,
            'is_active'     => true,
            'issued_by'     => Auth::id(),
        ]);

        return redirect()->route('admin.demerit-points.index', ['tab' => $category])
            ->with('success', "Demerit point recorded successfully for {$demerit->entity_name}.");
    }

    public function edit(DemeritPoint $demeritPoint)
    {
        $teams = Team::active()->orderBy('name')->get();
        $editions = Edition::orderByDesc('edition_number')->get();
        $matches = CricketMatch::with(['homeTeam', 'awayTeam'])
            ->when($demeritPoint->edition_id, fn($q) => $q->where('edition_id', $demeritPoint->edition_id))
            ->orderByDesc('scheduled_at')
            ->get();

        $dbCategories = DemeritPoint::distinct()->pluck('target_type')->toArray();
        $existingCategories = array_values(array_unique(array_merge(['player', 'team', 'umpire'], $dbCategories)));

        // Fetch team players if editing a player demerit
        $teamPlayers = [];
        if ($demeritPoint->team_id) {
            $teamPlayers = DB::table('player_team_editions')
                ->join('players', 'players.id', '=', 'player_team_editions.player_id')
                ->where('player_team_editions.team_id', $demeritPoint->team_id)
                ->when($demeritPoint->edition_id, fn($q) => $q->where('player_team_editions.edition_id', $demeritPoint->edition_id))
                ->select('players.id', 'players.name', 'players.father_name')
                ->orderBy('players.name')
                ->get();
        }

        return view('admin.demerit_points.edit', compact(
            'demeritPoint', 'teams', 'editions', 'matches', 'existingCategories', 'teamPlayers'
        ));
    }

    public function update(Request $request, DemeritPoint $demeritPoint)
    {
        $category = strtolower(trim($request->input('target_type', $demeritPoint->target_type)));

        if ($category === 'custom') {
            $customCategory = strtolower(trim($request->input('custom_target_type', '')));
            if (!empty($customCategory)) {
                $category = preg_replace('/[^a-z0-9_-]/', '', $customCategory);
            } else {
                $category = $demeritPoint->target_type;
            }
        }

        $rules = [
            'edition_id'    => 'nullable|exists:editions,id',
            'points'        => 'required|integer|min:1|max:10',
            'reason'        => 'required|string|max:1000',
            'incident_date' => 'required|date',
            'match_id'      => 'nullable|exists:cricket_matches,id',
            'notes'         => 'nullable|string|max:2000',
            'is_active'     => 'boolean',
        ];

        if ($category === 'player') {
            $rules['team_id']   = 'required|exists:teams,id';
            $rules['player_id'] = 'required|exists:players,id';
        } elseif ($category === 'team') {
            $rules['team_id']   = 'required|exists:teams,id';
        } else {
            $rules['target_name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $demeritPoint->update([
            'edition_id'    => $validated['edition_id'] ?? null,
            'target_type'   => $category,
            'team_id'       => in_array($category, ['player', 'team']) ? ($validated['team_id'] ?? null) : null,
            'player_id'     => $category === 'player' ? ($validated['player_id'] ?? null) : null,
            'target_name'   => !in_array($category, ['player', 'team']) ? ($validated['target_name'] ?? null) : null,
            'match_id'      => $validated['match_id'] ?? null,
            'points'        => (int) $validated['points'],
            'reason'        => $validated['reason'],
            'incident_date' => $validated['incident_date'],
            'notes'         => $validated['notes'] ?? null,
            'is_active'     => $request->has('is_active') ? (bool) $request->is_active : $demeritPoint->is_active,
        ]);

        return redirect()->route('admin.demerit-points.index', ['tab' => $category])
            ->with('success', "Demerit point record updated.");
    }

    public function toggle(DemeritPoint $demeritPoint)
    {
        $demeritPoint->update(['is_active' => !$demeritPoint->is_active]);

        return back()->with('success', "Status toggled to " . ($demeritPoint->is_active ? 'Active' : 'Inactive') . ".");
    }

    public function destroy(DemeritPoint $demeritPoint)
    {
        $demeritPoint->delete();

        return back()->with('success', "Demerit point record deleted.");
    }

    public function playersByTeam(Request $request, Team $team)
    {
        $editionId = $request->integer('edition_id');

        $query = DB::table('player_team_editions')
            ->join('players', 'players.id', '=', 'player_team_editions.player_id')
            ->where('player_team_editions.team_id', $team->id);

        if ($editionId) {
            $query->where('player_team_editions.edition_id', $editionId);
        }

        $players = $query->select(
            'players.id',
            'players.name',
            'players.father_name',
            'players.jersey_number'
        )
        ->distinct()
        ->orderBy('players.name')
        ->get();

        // Fallback: If no records in player_team_editions, fetch by current_team_id
        if ($players->isEmpty()) {
            $players = Player::where('current_team_id', $team->id)
                ->select('id', 'name', 'father_name', 'jersey_number')
                ->orderBy('name')
                ->get();
        }

        return response()->json($players);
    }
}
