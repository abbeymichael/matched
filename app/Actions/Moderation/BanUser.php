<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Models\MatchScore;
use App\Models\User;

/**
 * Permanent ban (§12.3). Excludes the user from matching immediately and
 * blocks login entirely (enforced by EnsureNotBanned middleware).
 */
final class BanUser
{
    public function handle(User $user, string $reason): User
    {
        $user->forceFill([
            'status' => UserStatus::Banned->value,
            'banned_at' => now(),
            'ban_reason' => $reason,
        ])->save();

        MatchScore::where('viewer_id', $user->id)->orWhere('target_id', $user->id)->delete();

        return $user;
    }
}
