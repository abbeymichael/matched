<?php

namespace App\Actions\Onboarding;

use App\Models\Preferences;
use App\Models\PreferenceFieldValue;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SavePreferenceStep
{
    public function handle(User $user, array $payload): void
    {
        $core = [
            'age_min' => $payload['age_min'] ?? null,
            'age_max' => $payload['age_max'] ?? null,
            'accepted_genders' => $payload['accepted_genders'] ?? null,
            'max_distance_km' => $payload['max_distance_km'] ?? null,
        ];

        if (in_array(null, $core, true)) {
            throw ValidationException::withMessages(['core' => 'All core preference fields are required.']);
        }

        Preferences::updateOrCreate(
            ['user_id' => $user->id],
            [
                'age_min' => max(18, (int) $core['age_min']),
                'age_max' => min(100, (int) $core['age_max']),
                'accepted_genders' => $core['accepted_genders'],
                'max_distance_km' => max(0, (int) $core['max_distance_km']),
            ]
        );

        if (! empty($payload['field_key'])) {
            PreferenceFieldValue::updateOrCreate(
                ['user_id' => $user->id, 'field_key' => $payload['field_key']],
                ['value' => $payload['field_value'] ?? null]
            );
        }
    }
}
