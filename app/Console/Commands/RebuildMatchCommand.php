<?php

namespace App\Console\Commands;

use App\Models\CricketMatch;
use App\Models\Edition;
use App\Services\MatchStatisticsService;
use Illuminate\Console\Command;

class RebuildMatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:rebuild
                            {match? : The ID or match number of the match to rebuild}
                            {--edition= : Rebuild all matches and player statistics for an entire tournament edition}
                            {--all : Rebuild all matches across all editions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild and synchronize match statistics, running totals, scorecards, fielding, player career/edition stats, and leaderboards.';

    public function handle(MatchStatisticsService $stats): int
    {
        $editionOpt = $this->option('edition');
        $allOpt     = $this->option('all');
        $matchArg   = $this->argument('match');

        if ($allOpt) {
            $matches = CricketMatch::orderBy('id')->get();
            $this->info("Rebuilding all {$matches->count()} matches...");
            $bar = $this->output->createProgressBar($matches->count());
            $bar->start();

            foreach ($matches as $m) {
                $stats->rebuildMatch($m);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Rebuild all editions
            foreach (Edition::pluck('id') as $edId) {
                $stats->rebuildEdition((int) $edId);
            }

            $this->info("✔ All matches and tournament statistics rebuilt successfully!");
            return self::SUCCESS;
        }

        if ($editionOpt) {
            $edition = Edition::where('id', $editionOpt)
                ->orWhere('edition_number', $editionOpt)
                ->first();

            if (! $edition) {
                $this->error("Edition '{$editionOpt}' not found.");
                return self::FAILURE;
            }

            $matches = CricketMatch::where('edition_id', $edition->id)->orderBy('id')->get();
            $this->info("Rebuilding {$matches->count()} matches for Edition #{$edition->edition_number} ({$edition->name})...");

            $bar = $this->output->createProgressBar($matches->count());
            $bar->start();

            foreach ($matches as $m) {
                $stats->rebuildMatch($m);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $stats->rebuildEdition((int) $edition->id);

            $this->info("✔ Edition #{$edition->edition_number} matches, standings, and leaderboards rebuilt successfully!");
            return self::SUCCESS;
        }

        if (! $matchArg) {
            $matches = CricketMatch::with(['homeTeam', 'awayTeam', 'edition'])
                ->orderByDesc('id')
                ->limit(30)
                ->get();

            if ($matches->isEmpty()) {
                $this->warn('No matches found.');
                return self::FAILURE;
            }

            $choices = [];
            foreach ($matches as $m) {
                $choices[$m->id] = "ID: {$m->id} | Match #{$m->match_number}: {$m->homeTeam?->name} vs {$m->awayTeam?->name} ({$m->status})";
            }

            $selectedText = $this->choice('Select a match to rebuild:', $choices);
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

        $this->info("--- Rebuilding Match #{$match->match_number} (ID: {$match->id}) ---");
        $refreshed = $stats->rebuildMatch($match);

        $this->line("<fg=green;options=bold>✔ Match #{$refreshed->match_number} successfully rebuilt!</>");
        $this->line("  Status: <fg=white>{$refreshed->status}</>");
        if ($refreshed->result_description) {
            $this->line("  Result: <fg=yellow>{$refreshed->result_description}</>");
        }

        foreach ($refreshed->innings as $inn) {
            $overs = \App\Support\Overs::display((int) $inn->total_balls);
            $this->line("  Innings {$inn->innings_number}: <fg=cyan>{$inn->total_runs}/{$inn->total_wickets}</> ({$overs} ov)");
        }

        if ($refreshed->edition_id) {
            $touched = $stats->syncEditionStatsForMatch($refreshed);
            $this->line("  Player edition stats updated: <fg=white>{$touched}</>");
        }

        return self::SUCCESS;
    }
}
