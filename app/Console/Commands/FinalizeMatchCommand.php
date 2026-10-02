<?php

namespace App\Console\Commands;

use App\Models\CricketMatch;
use App\Models\Player;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class FinalizeMatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:finalize 
                            {match? : The ID or match number of the match to finalise}
                            {--motm= : Player ID of Man of the Match (optional; auto-picked if omitted)}
                            {--result= : Custom result description (optional)}
                            {--notes= : Custom notes to attach to the match}
                            {--all-ended : Finalise all matches that have completed both innings but are still live}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalise a live match: resolves result, picks/assigns MOTM, updates edition stats, Orange/Purple cap & points table.';

    public function handle(ScoringService $scoring): int
    {
        $allEnded = $this->option('all-ended');

        if ($allEnded) {
            $matches = CricketMatch::where('status', '!=', 'completed')
                ->whereHas('innings', fn ($q) => $q->where('is_completed', true), '>=', 2)
                ->get();

            if ($matches->isEmpty()) {
                $this->info('No unfinalised matches with completed innings found.');
                return self::SUCCESS;
            }

            $this->info("Found {$matches->count()} match(es) to finalise.");
            foreach ($matches as $m) {
                $this->finalizeSingle($m, $scoring);
            }

            return self::SUCCESS;
        }

        $matchArg = $this->argument('match');

        if (! $matchArg) {
            // Interactive pick or list live matches
            $openMatches = CricketMatch::with(['homeTeam', 'awayTeam', 'innings'])
                ->whereIn('status', ['live', 'upcoming'])
                ->get();

            if ($openMatches->isEmpty()) {
                $this->warn('No live or upcoming matches found.');
                return self::FAILURE;
            }

            $choices = [];
            foreach ($openMatches as $m) {
                $innCount = $m->innings->count();
                $choices[$m->id] = "ID: {$m->id} | Match #{$m->match_number}: {$m->homeTeam?->name} vs {$m->awayTeam?->name} ({$m->status}, {$innCount} innings)";
            }

            $selectedText = $this->choice('Select a match to finalise:', $choices);
            $matchId = array_search($selectedText, $choices, true);
            $match = CricketMatch::find($matchId);
        } else {
            $match = CricketMatch::where('id', $matchArg)
                ->orWhere('match_number', $matchArg)
                ->first();
        }

        if (! $match) {
            $this->error("Match '{$matchArg}' not found.");
            return self::FAILURE;
        }

        return $this->finalizeSingle($match, $scoring);
    }

    private function finalizeSingle(CricketMatch $match, ScoringService $scoring): int
    {
        $this->info("--- Finalising Match #{$match->match_number} (ID: {$match->id}): {$match->homeTeam?->name} vs {$match->awayTeam?->name} ---");

        if ($match->status === 'completed') {
            $this->warn("Match #{$match->match_number} is already completed.");
            return self::SUCCESS;
        }

        if ($match->innings()->count() === 0) {
            $this->error("Match #{$match->match_number} has no innings recorded. Cannot finalise.");
            return self::FAILURE;
        }

        $motmOption = $this->option('motm');
        $motmId = null;

        if ($motmOption) {
            $player = is_numeric($motmOption)
                ? Player::find($motmOption)
                : Player::where('name', 'like', "%{$motmOption}%")->first();

            if ($player) {
                $motmId = $player->id;
                $this->info("Selected Man of the Match: {$player->name} (ID: {$player->id})");
            } else {
                $this->warn("Player '{$motmOption}' not found. Will auto-select best performer.");
            }
        }

        $options = array_filter([
            'man_of_match_player_id' => $motmId,
            'result_description'     => $this->option('result'),
            'notes'                  => $this->option('notes'),
            'finalized_by'           => 'artisan:match:finalize',
        ], fn ($v) => $v !== null);

        try {
            $outcome = $scoring->finalizeMatch($match, $options);

            $this->line("<fg=green;options=bold>✔ Match #{$match->match_number} successfully finalised!</>");
            $this->line("  Result: <fg=yellow>{$outcome['result']['result_description']}</>");

            $motmPlayer = $outcome['man_of_match_id'] ? Player::find($outcome['man_of_match_id']) : null;
            if ($motmPlayer) {
                $this->line("  Man of the Match: <fg=cyan>{$motmPlayer->name}</> (ID: {$motmPlayer->id})");
            }

            $this->line("  Players stats updated: <fg=white>{$outcome['players_updated']}</>");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to finalise match: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
