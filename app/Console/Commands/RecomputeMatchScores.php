<?php

namespace App\Console\Commands;

use App\Jobs\ComputeMatchScoresForUser;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * php artisan matches:recompute (§6 Trigger 3). Run after an admin
 * activates/deactivates a field, changes a weight, or toggles a hard-filter
 * flag — this invalidates the entire score matrix.
 */
final class RecomputeMatchScores extends Command
{
    protected $signature = 'matches:recompute';

    protected $description = 'Recompute match scores for every locked, active user (run after field-config changes).';

    public function handle(): int
    {
        $count = 0;

        User::where('profile_locked', true)
            ->where('status', 'active')
            ->chunk(200, function ($users) use (&$count) {
                foreach ($users as $user) {
                    ComputeMatchScoresForUser::dispatch($user->id);
                    $count++;
                }
            });

        $this->info("Dispatched recomputation for {$count} users.");

        return self::SUCCESS;
    }
}
