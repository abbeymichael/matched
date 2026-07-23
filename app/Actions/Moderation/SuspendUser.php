<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Models\User;
use InvalidArgumentException;

/**
 * Suspend a user for a configurable number of days.
 *
 * Suspended users cannot log in via the web or API. Their existing matches
 * and messages are preserved but the account is effectively frozen.
 */
final class SuspendUser
{
    public function handle(User $user, int $days, ?string $reason = null): void
    {
        if ($days <= 0) {
            throw new InvalidArgumentException('Suspension must be for at least one day.');
        }

        $user->forceFill([
            'status' => UserStatus::Suspended->value,
            'suspension_ends_at' => now()->addDays($days),
            'ban_reason' => $reason,
            'banned_at' => null,
        ])->save();

        $user->tokens()->delete();
    }
}
