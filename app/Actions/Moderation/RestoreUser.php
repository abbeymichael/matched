<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Jobs\ComputeMatchScoresForUser;
use App\Models\User;

/**
 * Un-suspends a user and re-triggers match computation for them (§12.5, §6
 * Trigger 4). Does not un-ban — banning is permanent by design (§12.3).
 */
final class RestoreUser
{
    public function handle(User $user): User
    {
        $user->forceFill([
            'status' => UserStatus::Active->value,
            'suspension_ends_at' => null,
        ])->save();

        if ($user->profile_locked) {
            ComputeMatchScoresForUser::dispatch($user->id);
        }

        return $user;
    }
}
