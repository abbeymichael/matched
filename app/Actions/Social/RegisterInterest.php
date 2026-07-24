<?php

namespace App\Actions\Social;

use App\Models\Interest;
use App\Models\MutualMatch;
use App\Models\User;

/**
 * Creates an Interest row; if the target has also independently marked
 * interest in the actor, creates a MutualMatch (§5.3 pair-ordering: always
 * store the lexicographically smaller UUID as user_a_id).
 */
final class RegisterInterest
{
    public function handle(User $from, User $to): ?MutualMatch
    {
        if ($from->id === $to->id) {
            return null;
        }

        Interest::firstOrCreate(
            ['from_id' => $from->id, 'to_id' => $to->id],
            ['created_at' => now()]
        );

        $reciprocal = Interest::where('from_id', $to->id)->where('to_id', $from->id)->exists();

        if (! $reciprocal) {
            return null;
        }

        [$userAId, $userBId] = $from->id < $to->id ? [$from->id, $to->id] : [$to->id, $from->id];

        return MutualMatch::firstOrCreate(
            ['user_a_id' => $userAId, 'user_b_id' => $userBId],
            ['matched_at' => now(), 'is_active' => true]
        );
    }
}
