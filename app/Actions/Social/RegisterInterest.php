<?php

namespace App\Actions\Social;

use App\Models\Interest;
use App\Models\MutualMatch;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Record that one user is interested in another.
 *
 * Rules:
 *   - Both users must have locked profiles.
 *   - Users cannot express interest in themselves.
 *   - Repeated interest is idempotent.
 *   - If the target has already expressed interest in the viewer, a mutual match is created.
 *
 * No notification is sent to the target until the match is mutual.
 */
final class RegisterInterest
{
    public function handle(User $viewer, User $target): Interest
    {
        if ($viewer->id === $target->id) {
            throw ValidationException::withMessages(['target' => 'You cannot express interest in yourself.']);
        }

        if (! $viewer->profile_locked || ! $target->profile_locked) {
            throw ValidationException::withMessages(['target' => 'Both profiles must be locked to express interest.']);
        }

        if ($target->isBannedOrSuspended()) {
            throw ValidationException::withMessages(['target' => 'This user is not available right now.']);
        }

        $interest = Interest::firstOrCreate(
            ['from_id' => $viewer->id, 'to_id' => $target->id],
            ['created_at' => now()]
        );

        if ($this->isMutual($viewer, $target)) {
            $this->createMutualMatch($viewer, $target);
        }

        return $interest;
    }

    private function isMutual(User $viewer, User $target): bool
    {
        return Interest::query()
            ->where('from_id', $target->id)
            ->where('to_id', $viewer->id)
            ->exists();
    }

    private function createMutualMatch(User $a, User $b): MutualMatch
    {
        // Enforce UUID ordering so pairs are always stored consistently.
        [$first, $second] = $a->id < $b->id ? [$a, $b] : [$b, $a];

        return MutualMatch::firstOrCreate(
            ['user_a_id' => $first->id, 'user_b_id' => $second->id],
            ['matched_at' => now(), 'is_active' => true]
        );
    }
}
