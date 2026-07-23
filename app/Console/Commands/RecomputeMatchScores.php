<?php

namespace App\Console\Commands;

use App\Actions\Matching\ComputePairScore;
use App\Models\FieldDefinition;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * Recompute all match scores for locked users.
 *
 * Options:
 *   --user=ID    Only recompute for a single viewer (useful for debugging).
 *   --stale      Only recompute for users whose scores_stale_since is set.
 *   --chunk=N    Batch size for eager loading (default 100).
 */
class RecomputeMatchScores extends Command
{
    protected $signature = 'matchlock:recompute-scores
                            {--user= : UUID of a single viewer to recompute}
                            {--stale : Only recompute for users marked as stale}
                            {--chunk=100 : How many candidate IDs to load per batch}';

    protected $description = 'Recompute match scores for all (or selected) locked users.';

    public function handle(ComputePairScore $pairScore): int
    {
        $chunk = max(1, (int) $this->option('chunk'));

        $viewers = User::query()
            ->where('profile_locked', true)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['banned', 'suspended', 'under_review']);
            });

        if ($userId = $this->option('user')) {
            $viewers->where('id', $userId);
        }

        if ($this->option('stale')) {
            $staleUserIds = FieldDefinition::query()
                ->whereNotNull('scores_stale_since')
                ->pluck('scores_stale_since')
                ->map(fn ($ts) => $ts->toDateTimeString())
                ->unique()
                ->toArray();

            // When a field changes, all locked users are effectively stale. We mark
            // the lock timestamp and compare against it. A simpler interpretation:
            // recompute every locked user. To make --stale meaningful, we only run
            // users whose locked_at is before the most recent stale mark.
            $latestStale = FieldDefinition::query()->max('scores_stale_since');
            if ($latestStale) {
                $viewers->where(function ($query) use ($latestStale) {
                    $query->whereNull('locked_at')
                        ->orWhere('locked_at', '<=', $latestStale);
                });
            }
        }

        $count = $viewers->count();

        if ($count === 0) {
            $this->info('No locked users to recompute.');
            return self::SUCCESS;
        }

        $this->info("Recomputing scores for {$count} locked user(s)...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $viewers->chunkById(100, function ($viewerChunk) use ($pairScore, $chunk, $bar) {
            $viewerChunk->loadMissing(['profile', 'preferences', 'profileFieldValues', 'preferenceFieldValues']);

            foreach ($viewerChunk as $viewer) {
                $this->computeForViewer($viewer, $pairScore, $chunk);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Clear stale markers after a successful full recompute unless a single user was targeted.
        if (! $this->option('user') && $this->option('stale')) {
            FieldDefinition::query()->whereNotNull('scores_stale_since')->update(['scores_stale_since' => null]);
            $this->info('Stale markers cleared.');
        }

        $this->info('Recompute complete.');
        return self::SUCCESS;
    }

    private function computeForViewer(User $viewer, ComputePairScore $pairScore, int $chunk): void
    {
        $targetIds = User::query()
            ->where('profile_locked', true)
            ->where('id', '!=', $viewer->id)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['banned', 'suspended', 'under_review']);
            })
            ->pluck('id');

        foreach ($targetIds->chunk($chunk) as $chunkIds) {
            $targets = User::query()
                ->whereIn('id', $chunkIds)
                ->with(['profile', 'preferences', 'profileFieldValues', 'preferenceFieldValues'])
                ->get();

            foreach ($targets as $target) {
                $pairScore->handle($viewer, $target);
            }
        }
    }
}
