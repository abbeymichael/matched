<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Models\User;
use InvalidArgumentException;

/**
 * Permanently ban a user.
 *
 * Banned users cannot log in. All their active Sanctum tokens are revoked.
 * Their existing matches and messages are preserved for moderation records.
 */
final class BanUser
{
    public function handle(User $user, ?string $reason = null): void
    {
        if ($user->is_admin) {
            throw new InvalidArgumentException('Admin users cannot be banned.');
        }

        $user->forceFill([
            'status' => UserStatus::Banned->value,
            'banned_at' => now(),
            'suspension_ends_at' => null,
            'ban_reason' => $reason,
        ])->save();

        $user->tokens()->delete();
    }
}
