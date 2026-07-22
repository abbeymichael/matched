<?php

namespace App\Actions\Onboarding;

use App\Jobs\ComputeMatchScoresForUser;
use App\Models\FieldDefinition;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class LockUserProfile
{
    public function handle(User $user): void
    {
        if (! $user->profile) {
            throw ValidationException::withMessages(['profile' => 'Profile is incomplete.']);
        }

        if (! $user->preferences) {
            throw ValidationException::withMessages(['preferences' => 'Preferences are incomplete.']);
        }

        $requiredFields = FieldDefinition::activeOrdered()->where('is_required', true)->where('is_core', false)->pluck('key');
        foreach ($requiredFields as $key) {
            if (! $user->profileFieldValues()->where('field_key', $key)->exists()) {
                throw ValidationException::withMessages([$key => "Field {$key} is required."]);
            }
        }

        $user->forceFill([
            'profile_locked' => true,
            'locked_at' => now(),
        ])->save();

        ComputeMatchScoresForUser::dispatch($user->id);
    }
}
