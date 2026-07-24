<?php

namespace App\Console\Commands;

use App\Models\MatchScore;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Clean up match_scores for banned/deleted/suspended users (§9, run weekly).
 */
final class PruneStaleMatchScores extends Command
{
    protected $signature = 'matches:prune-stale';

    protected $description = 'Delete match_scores rows for banned, suspended, or deleted users.';

    public function handle(): int
    {
        $staleUserIds = User::whereIn('status', ['banned', 'suspended', 'under_review'])
            ->pluck('id')
            ->merge(User::onlyTrashed()->pluck('id'))
            ->unique();

        $deleted = MatchScore::whereIn('viewer_id', $staleUserIds)
            ->orWhereIn('target_id', $staleUserIds)
            ->delete();

        $this->info("Pruned {$deleted} stale match_scores rows.");

        return self::SUCCESS;
    }
}
