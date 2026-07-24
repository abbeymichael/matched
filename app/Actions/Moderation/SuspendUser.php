<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Models\MatchScore;
use App\Models\User;

/**
 * Suspends (or moves to under_review) a user and immediately excludes them
 * from matching (§12.5): every match_scores row where they are viewer or
 * target is removed so they disappear from everyone's list right away.
 */
final class SuspendUser
{
    public function handle(User $user, ?int $suspensionDays = null, ?string $status = null): User
    {
        $user->forceFill([
            'status' => $status ?? UserStatus::Suspended->value,
            'suspension_ends_at' => $suspensionDays ? now()->addDays($suspensionDays) : null,
        ])->save();

        MatchScore::where('viewer_id', $user->id)->orWhere('target_id', $user->id)->delete();

        return $user;
    }
}
