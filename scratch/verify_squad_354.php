<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$team = App\Models\Team::where('short_code', '354')->first();
echo "Team: {$team->name} (Code: {$team->short_code}, Village: {$team->village_name})\n";

$edition = App\Models\Edition::where('edition_number', 37)->first();
echo "Edition: {$edition->name} (ID: {$edition->id})\n\n";

$players = Illuminate\Support\Facades\DB::table('player_team_editions')
    ->join('players', 'players.id', '=', 'player_team_editions.player_id')
    ->where('player_team_editions.team_id', '=', $team->id)
    ->where('player_team_editions.edition_id', '=', $edition->id)
    ->select('players.*', 'player_team_editions.is_captain', 'player_team_editions.is_vice_captain')
    ->orderByDesc('player_team_editions.is_captain')
    ->orderBy('players.id')
    ->get();

echo "Total Players on Roster: " . $players->count() . "\n";
foreach ($players as $idx => $p) {
    $tag = $p->is_captain ? ' [C]' : ($p->is_vice_captain ? ' [VC]' : '');
    echo sprintf("%2d. ID: %-3d | %-20s%s | Jersey: #%-3s | Role: %-12s | Bat: %s\n", 
        $idx + 1, $p->id, $p->name, $tag, $p->jersey_number, $p->role, $p->batting_style);
}
