<?php

namespace App\Jobs;

use App\Actions\Matching\ComputePairScore;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Compute match scores for a single viewer against every other locked user.
 *
 * Runs in the queue so that locking a profile (or re-locking after reset)
 * feels instant to the user while the N^2 score work happens asynchronously.
 */
class ComputeMatchScoresForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(public string $userId) {}

    public function handle(ComputePairScore $pairScore): void
    {
        $viewer = User::find($this->userId);

        if (! $viewer || ! $viewer->profile_locked) {
            return;
        }

        // Eager load everything the scoring engine needs in a single batch per target.
        $lockedUserIds = User::query()
            ->where('profile_locked', true)
            ->where('id', '!=', $viewer->id)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['banned', 'suspended', 'under_review']);
            })
            ->pluck('id');

        foreach ($lockedUserIds->chunk(100) as $chunk) {
            $targets = User::query()
                ->whereIn('id', $chunk)
                ->with(['profile', 'preferences', 'profileFieldValues', 'preferenceFieldValues'])
                ->get();

            foreach ($targets as $target) {
                $pairScore->handle($viewer, $target);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
