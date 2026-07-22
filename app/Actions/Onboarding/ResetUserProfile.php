<?php

namespace App\Actions\Onboarding;

use App\Models\MatchScore;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class ResetUserProfile
{
    public function handle(User $user): void
    {
        if ($user->reset_used) {
            throw ValidationException::withMessages(['reset' => 'You have already used your lifetime reset.']);
        }

        MatchScore::where('viewer_id', $user->id)->orWhere('target_id', $user->id)->delete();
        Interest::where('from_id', $user->id)->orWhere('to_id', $user->id)->delete();

        $user->forceFill([
            'profile_locked' => false,
            'reset_used' => true,
            'locked_at' => null,
        ])->save();
    }
}
