<?php

namespace App\Actions\Onboarding;

use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileFieldValue;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SaveProfileStep
{
    public function handle(User $user, array $payload): void
    {
        $core = [
            'display_name' => $payload['display_name'] ?? null,
            'date_of_birth' => $payload['date_of_birth'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'city' => $payload['city'] ?? null,
        ];

        if (in_array(null, $core, true)) {
            throw ValidationException::withMessages(['core' => 'All core profile fields are required.']);
        }

        $city = City::where('name', $core['city'])->first();

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $core['display_name'],
                'date_of_birth' => $core['date_of_birth'],
                'gender' => $core['gender'],
                'city' => $core['city'],
                'lat' => $city?->lat ?? 0,
                'lng' => $city?->lng ?? 0,
            ]
        );

        if (! empty($payload['field_key'])) {
            ProfileFieldValue::updateOrCreate(
                ['user_id' => $user->id, 'field_key' => $payload['field_key']],
                ['value' => $payload['field_value'] ?? null]
            );
        }
    }
}
