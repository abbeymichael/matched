<?php

namespace App\Jobs;

use App\Actions\Matching\ComputePairScore;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued on lock-in (§6 Trigger 1) and via matches:recompute (§6 Trigger 3).
 * Loads all other locked, active users and computes both directions for
 * each pair, upserting match_scores. Chunked to avoid loading the entire
 * user table into memory at once.
 */
final class ComputeMatchScoresForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $userId) {}

    public function handle(ComputePairScore $computePairScore): void
    {
        $user = User::find($this->userId);

        if (! $user || ! $user->profile_locked || $user->isBannedOrSuspended()) {
            return;
        }

        User::where('profile_locked', true)
            ->where('status', 'active')
            ->where('id', '!=', $user->id)
            ->chunk(200, function ($others) use ($computePairScore, $user) {
                foreach ($others as $other) {
                    if ($other->isBannedOrSuspended()) {
                        continue;
                    }

                    $computePairScore->handle($user, $other);
                    $computePairScore->handle($other, $user);
                }
            });
    }
}
