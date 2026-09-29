<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$team = App\Models\Team::where('short_code', '418')->first();
echo "Team: {$team->name} (Code: {$team->short_code}, Village: {$team->village_name})\n";

$edition = App\Models\Edition::where('edition_number', 37)->first();
echo "Edition: {$edition->name} (ID: {$edition->id})\n\n";

$players = Illuminate\Support\Facades\DB::table('player_team_editions')
    ->join('players', 'players.id', '=', 'player_team_editions.player_id')
    ->where('player_team_editions.team_id', '=', $team->id)
    ->where('player_team_editions.edition_id', '=', $edition->id)
    ->select('players.*', 'player_team_editions.is_captain', 'player_team_editions.is_vice_captain')
    ->orderByDesc('player_team_editions.is_captain')
    ->orderByDesc('player_team_editions.is_vice_captain')
    ->get();

echo "Total Players: " . $players->count() . "\n";
foreach ($players as $idx => $p) {
    $tag = $p->is_captain ? ' [C]' : ($p->is_vice_captain ? ' [VC]' : '');
    echo sprintf("%2d. %-20s%s s/o %-20s | Jersey: #%-3s | Role: %s\n", 
        $idx + 1, $p->name, $tag, $p->father_name, $p->jersey_number, $p->role);
}
