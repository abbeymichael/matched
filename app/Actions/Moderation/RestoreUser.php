<?php

namespace App\Actions\Moderation;

use App\Enums\UserStatus;
use App\Models\User;

/**
 * Restore a previously suspended or banned user to active status.
 *
 * Also used to clear warnings (e.g. when a report is dismissed). Tokens are
 * not recreated; the user must log in again.
 */
final class RestoreUser
{
    public function handle(User $user): void
    {
        $user->forceFill([
            'status' => UserStatus::Active->value,
            'banned_at' => null,
            'suspension_ends_at' => null,
            'ban_reason' => null,
        ])->save();
    }
}
